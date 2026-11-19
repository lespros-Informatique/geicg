<?php

require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

/**
 * Service de Sécurité Académique & Cloisonnement Temporel (Principes SOLID)
 * 
 * Responsabilité Unique : Valider et autoriser l'accès aux années académiques (actives, planifiées, clôturées)
 * selon les permissions RBAC pures et les affectations individuelles (user_annee_acces).
 * Zéro vérification basée sur un rôle, zéro fallback arbitraire.
 */
class AcademicSecurityGuard
{
    private static ?AcademicSecurityGuard $instance = null;
    private ?PDO $db = null;

    private function __construct()
    {
        $this->db = (new Database())->getCon();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // =========================================================================
    // 1. TÂCHES ÉLÉMENTAIRES D'INSPECTION D'UNE ANNÉE (Basse granularité)
    // =========================================================================

    /**
     * Tâche élémentaire : Récupère l'enregistrement complet d'une année par son code
     */
    public function getYearRecord(string $code): ?array
    {
        $cleanCode = trim($code);
        if ($cleanCode === '') {
            return null;
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM `" . TABLES::ANNEES . "` WHERE `code_annee` = ? LIMIT 1");
            $stmt->execute([$cleanCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log("AcademicSecurityGuard::getYearRecord error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tâche élémentaire : Récupère uniquement le statut d'une année académique
     */
    public function getYearStatus(string $code): ?string
    {
        $record = $this->getYearRecord($code);
        return $record ? ($record['statut_annee'] ?? null) : null;
    }

    /**
     * Tâche élémentaire : Vérifie UNIQUEMENT si l'année est active
     */
    public function isYearActive(string $anneeCode): bool
    {
        return $this->getYearStatus($anneeCode) === STATUTS::ACTIF;
    }

    /**
     * Tâche élémentaire : Vérifie UNIQUEMENT si l'année est planifiée (future / préparation)
     */
    public function isYearPlanned(string $anneeCode): bool
    {
        return $this->getYearStatus($anneeCode) === 'planifie';
    }

    /**
     * Tâche élémentaire : Vérifie UNIQUEMENT si l'année est clôturée
     */
    public function isYearClosed(string $anneeCode): bool
    {
        return $this->getYearStatus($anneeCode) === STATUTS::CLOTURE;
    }

    /**
     * Tâche élémentaire : Vérifie UNIQUEMENT si l'année est archivée
     */
    public function isYearArchived(string $anneeCode): bool
    {
        return $this->getYearStatus($anneeCode) === STATUTS::ARCHIVE;
    }

    /**
     * Tâche élémentaire : Vérifie si l'année relève de l'historique (clôturée ou archivée)
     */
    public function isYearPastOrClosed(string $anneeCode): bool
    {
        return $this->isYearClosed($anneeCode) || $this->isYearArchived($anneeCode);
    }

    // =========================================================================
    // 2. TÂCHES ÉLÉMENTAIRES DE VÉRIFICATION DES PERMISSIONS RBAC
    // =========================================================================

    /**
     * Tâche élémentaire : Vérifie si l'utilisateur possède la permission globale de consultation historique
     */
    public function hasGlobalHistoryViewPermission(): bool
    {
        return Auth::can(PERMISSIONS::VIEW_ANNEES_ANTERIEURES);
    }

    /**
     * Tâche élémentaire : Vérifie si l'utilisateur possède la permission globale de modification historique
     */
    public function hasGlobalHistoryEditPermission(): bool
    {
        return Auth::can(PERMISSIONS::EDIT_ANNEES_ANTERIEURES);
    }

    // =========================================================================
    // 3. TÂCHES ÉLÉMENTAIRES D'INSPECTION D'ACCÈS SPÉCIFIQUE (user_annee_acces)
    // =========================================================================

    /**
     * Tâche élémentaire : Récupère la ligne d'affectation nominative dans user_annee_acces
     */
    public function getSpecificUserAccessRow(string $anneeCode, ?string $userCode = null): ?array
    {
        $uCode = $userCode ?? Auth::code();
        $cleanAnnee = trim($anneeCode);

        if (empty($uCode) || empty($cleanAnnee)) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT `annee_code`, `niveau_acces` 
                FROM `" . TABLES::USER_ANNEE_ACCES . "` 
                WHERE `user_code` = ? AND `annee_code` = ? 
                LIMIT 1
            ");
            $stmt->execute([$uCode, $cleanAnnee]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log("AcademicSecurityGuard::getSpecificUserAccessRow error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tâche élémentaire : Récupère le niveau d'accès brut ('lecture' ou 'ecriture')
     */
    public function getSpecificUserAccessLevel(string $anneeCode, ?string $userCode = null): ?string
    {
        $row = $this->getSpecificUserAccessRow($anneeCode, $userCode);
        return $row ? ($row['niveau_acces'] ?? null) : null;
    }

    /**
     * Tâche élémentaire : Vérifie si l'utilisateur a une affectation nominative quelconque
     */
    public function hasSpecificUserAccess(string $anneeCode, ?string $userCode = null): bool
    {
        return $this->getSpecificUserAccessRow($anneeCode, $userCode) !== null;
    }

    /**
     * Tâche élémentaire : Vérifie si l'utilisateur a une affectation nominative en lecture
     */
    public function hasSpecificUserReadAccess(string $anneeCode, ?string $userCode = null): bool
    {
        $level = $this->getSpecificUserAccessLevel($anneeCode, $userCode);
        return $level === 'lecture' || $level === 'ecriture';
    }

    /**
     * Tâche élémentaire : Vérifie si l'utilisateur a une affectation nominative en écriture
     */
    public function hasSpecificUserWriteAccess(string $anneeCode, ?string $userCode = null): bool
    {
        return $this->getSpecificUserAccessLevel($anneeCode, $userCode) === 'ecriture';
    }

    // =========================================================================
    // 4. TÂCHES D'AUTORISATION SYNTHÉTIQUE (LECTURE / ÉCRITURE)
    // =========================================================================

    /**
     * Tâche de synthèse : Contrôle d'autorisation en lecture pour une année donnée
     */
    public function canUserReadYear(string $anneeCode, ?string $userCode = null): bool
    {
        $cleanAnnee = trim($anneeCode);
        if ($cleanAnnee === '') {
            return false;
        }

        // 1. Année active : accessible à tous les utilisateurs authentifiés
        if ($this->isYearActive($cleanAnnee)) {
            return true;
        }

        // 2. Année planifiée : accessible
        if ($this->isYearPlanned($cleanAnnee)) {
            return true;
        }

        // 3. Permission globale de consultation historique
        if ($this->hasGlobalHistoryViewPermission()) {
            return true;
        }

        // 4. Habilitation individuelle nominative
        return $this->hasSpecificUserReadAccess($cleanAnnee, $userCode);
    }

    /**
     * Tâche de synthèse : Contrôle d'autorisation en écriture / saisie pour une année donnée
     */
    public function canUserWriteYear(string $anneeCode, ?string $userCode = null): bool
    {
        $cleanAnnee = trim($anneeCode);
        if ($cleanAnnee === '') {
            return false;
        }

        // 1. Année active : écriture autorisée selon permissions métier normales
        if ($this->isYearActive($cleanAnnee)) {
            return true;
        }

        // 2. Année planifiée : écriture autorisée pour configuration
        if ($this->isYearPlanned($cleanAnnee)) {
            return true;
        }

        // 3. Permission globale de modification historique
        if ($this->hasGlobalHistoryEditPermission()) {
            return true;
        }

        // 4. Habilitation individuelle nominative en écriture
        return $this->hasSpecificUserWriteAccess($cleanAnnee, $userCode);
    }

    // =========================================================================
    // 5. TÂCHES DE GESTION DE CONTEXTE D'ANNÉE ACTIVE & SESSION
    // =========================================================================

    /**
     * Tâche élémentaire : Récupère l'unique année active réelle en base (ZÉRO FALLBACK)
     */
    public function getActiveYearFromDatabase(): ?array
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM `" . TABLES::ANNEES . "` 
                WHERE `statut_annee` = '" . STATUTS::ACTIF . "' 
                LIMIT 1
            ");
            $res = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            return $res ?: null;
        } catch (Exception $e) {
            error_log("AcademicSecurityGuard::getActiveYearFromDatabase error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tâche élémentaire : Récupère le code de l'année active réelle en base
     */
    public function getActiveYearCodeFromDatabase(): ?string
    {
        $act = $this->getActiveYearFromDatabase();
        return $act ? ($act['code_annee'] ?? null) : null;
    }

    /**
     * Tâche élémentaire : Récupère le code d'année stocké en session
     */
    public function getYearCodeFromSession(): ?string
    {
        $code = (string)Session::get('annee_active_code', '');
        return ($code !== '') ? $code : null;
    }

    /**
     * Tâche élémentaire : Récupère le libellé d'année stocké en session
     */
    public function getYearLibelleFromSession(): ?string
    {
        $libelle = (string)Session::get('annee_active_libelle', '');
        return ($libelle !== '') ? $libelle : null;
    }

    /**
     * Tâche élémentaire : Détermine si une interrogation sans filtre d'année est permise
     */
    public function isQueryWithoutYearAllowed(): bool
    {
        return $this->hasGlobalHistoryViewPermission();
    }

    // =========================================================================
    // 6. TÂCHE D'ORCHESTRATION & RÉSOLUTION D'ANNÉE OPÉRATIONNELLE SÉCURISÉE
    // =========================================================================

    /**
     * Résout le code de l'année opérationnelle pour la requête courante en garantissant
     * que l'utilisateur a strictement le droit d'y accéder.
     * 
     * Priorité de résolution :
     * 1. Code passé explicitement (ex: $_GET['annee_code'])
     * 2. Code stocké en session de l'utilisateur
     * 3. Code de l'année active en base de données
     * 
     * @return string|null Code de l'année validée, ou NULL si aucune année n'est active et que l'utilisateur n'a pas les droits.
     */
    public function resolveOperationalYearCode(?string $requestedCode = null): ?string
    {
        $target = null;

        // 1. Candidat prioritaire : requête explicite
        if ($requestedCode !== null && trim($requestedCode) !== '') {
            $target = trim($requestedCode);
        }
        // 2. Candidat secondaire : session utilisateur
        elseif ($this->getYearCodeFromSession() !== null) {
            $target = $this->getYearCodeFromSession();
        }
        // 3. Candidat tertiaire : année active en BDD
        else {
            $target = $this->getActiveYearCodeFromDatabase();
        }

        // Si aucun candidat disponible
        if ($target === null || $target === '') {
            return null;
        }

        // Validation stricte d'autorisation
        if ($this->canUserReadYear($target)) {
            return $target;
        }

        // Accès interdit à cette année
        return null;
    }

    /**
     * Alias de commodité pour compatibilité
     */
    public function canAccessAcademicYear(string $anneeCode): bool
    {
        return $this->canUserReadYear($anneeCode);
    }
}

