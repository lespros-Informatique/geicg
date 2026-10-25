<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Composer & Chargement automatique de .env
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }
}

define('ROOT', $_SERVER['DOCUMENT_ROOT'] ?? 'C:/wamp64/www');

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalEnvironment = (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false);

if (!defined('RACINE')) {
    define('RACINE', $isLocalEnvironment ? 'http://localhost/geicg/' : 'https://test.groupe-eicg.net/');
}

define('LOGO', '<span class="fw-bold fs-4 text-primary" style="letter-spacing: 1px;">GEICG</span>');
define('ICON', '<span class="fw-bold fs-4 text-primary">G</span>');
define('TITLE', 'GEICG - Administration Grande École');

const USERS_AUTH = 'users_auth';

class TABLES
{
    // INSTITUTION & ACCÈS
    public const ETABLISSEMENTS = 'etablissements';
    public const SERVICES = 'services';
    public const FONCTIONS = 'fonctions';
    public const USERS = 'users';
    public const ROLES = 'roles';
    public const PERMISSIONS = 'permissions';
    public const ROLE_PERMISSIONS = 'role_permissions';
    public const USER_ROLES = 'user_roles';
    public const USER_ANNEE_ACCES = 'user_annee_acces';

    // ACADÉMIQUE & LMD
    public const ANNEES = 'annees';
    public const CYCLES = 'cycles';
    public const FILIERES = 'filieres';
    public const FILIERE_CYCLES = 'filiere_cycles';
    public const NIVEAUX = 'niveaux';
    public const FILIERE_NIVEAUX = 'filiere_niveaux';
    public const CLASSES = 'classes';
    public const SEMESTRES = 'semestres';
    public const UNITES_ENSEIGNEMENT = 'unites_enseignement';
    public const MATIERES = 'matieres';
    public const SALLES = 'salles';

    // SCOLARITÉ & ÉTUDIANTS
    public const ETUDIANTS = 'etudiants';
    public const PARENTS = 'parents';
    public const INSCRIPTIONS = 'inscriptions';
    public const ACCESSOIRES = 'accessoires';
    public const ACCESSOIRE_INSCRIPTION = 'accessoire_inscription';
    public const DOSSIER_ETUDIANT = 'dossier_etudiant';
    public const PIECES_FOURNIR = 'pieces_fournir';
    public const PIECES_FOURNIR_CYCLES = 'pieces_fournir_cycles';

    // FINANCE & CAISSE
    public const SCOLARITES = 'scolarites';
    public const TRANCHES_SCOLARITE = 'tranches_scolarite';
    public const PAIEMENTS = 'paiements';
    public const SESSIONS_CAISSE = 'sessions_caisse';
    public const CLOTURES_CAISSE = 'clotures_caisse';
    public const CAISSE_ANNEXE = 'caisse_annexe';
    public const DEPENSES = 'depenses';
    public const TYPE_DEPENSES = 'type_depenses';
    public const ARRIERES = 'arrieres';
    public const IMPAYES = 'impayes';

    // PÉDAGOGIE & ÉVALUATIONS
    public const ENSEIGNANTS = 'enseignants';
    public const ENSEIGNANT_MATIERE = 'enseignant_matiere';
    public const EMPLOIS_TEMPS = 'emplois_temps';
    public const NOTES = 'notes';
    public const ABSENCES = 'absences';
    public const COMPOSITIONS = 'compositions';
    public const COMPOSITION_MATIERES = 'composition_matieres';
    public const BULLETINS = 'bulletins';

    // COMMUNICATION & MÉDIAS
    public const DOCUMENTS = 'documents';
    public const EVENEMENTS = 'evenements';
    public const GALERIES = 'galeries';
    public const GALERIE_MEDIAS = 'galerie_medias';
}

class ROLES
{
    public const SUPER_ADMIN    = 'ROLE_SUPERADMIN';
    public const DIR_GENERAL    = 'ROLE_DIR_GENERAL';
    public const DIR_ETUDES     = 'ROLE_DIR_ETUDES';
    public const CHEF_DEP       = 'ROLE_CHEF_DEP';
    public const SCOLARITE      = 'ROLE_SCOLARITE';
    public const COMPTABLE      = 'ROLE_COMPTABLE';
    public const CAISSIER       = 'ROLE_CAISSIER';
    public const ENSEIGNANT     = 'ROLE_ENSEIGNANT';
    public const COMMUNICATION  = 'ROLE_COMMUNICATION';
    public const ALL_CONTROL    = 'all_control';
}

class STATUTS
{
    // INSCRIPTIONS
    public const INSCRIPTIONS = ['en_attente', 'valide', 'solde', 'annule'];
    public const CANAUX_INSCRIPTION = ['guichet', 'en_ligne'];
    public const AFFECTATIONS_ETAT = ['affecte', 'non_affecte'];

    // PAIEMENTS
    public const PAIEMENTS = ['valide', 'annule', 'en_attente'];
    public const CANAUX_PAIEMENT = ['guichet', 'en_ligne'];

    // SESSIONS DE CAISSE
    public const SESSIONS_CAISSE = ['ouvert', 'ferme', 'valide', 'rejete'];

    // ANNÉES ACADÉMIQUES
    public const ANNEES = ['actif', 'cloture', 'archive', 'inactif'];

    // COMPOSITIONS / EXAMENS
    public const COMPOSITIONS = ['programme', 'en_cours', 'termine', 'annule'];

    // ABSENCES
    public const ABSENCES = ['justifie', 'non_justifie'];

    // STATUT GÉNÉRAL D'ACTIVITÉ
    public const GENERAL = ['actif', 'inactif'];

    // TYPES DE FILIÈRES
    public const TYPES_FILIERES = [
        'INDUSTRIELLE' => 'Filière Industrielle',
        'TERTIAIRE'    => 'Filière Tertiaire'
    ];
}
