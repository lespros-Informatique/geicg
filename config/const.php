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

// Chargement automatique du socle Core MVC (Session, Auth, Helpers)
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Helper.php';
require_once __DIR__ . '/../core/Auth.php';

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
    public const USER           = 'ROLE_USER';
    public const ALL_CONTROL    = 'all_control';
}

class PERMISSIONS
{
    // MODULE: ACADEMIQUE
    public const CONFIG_ACADEMIQUE                = 'CONFIG_ACADEMIQUE';
    public const MANAGE_ANNEES                    = 'MANAGE_ANNEES';
    public const MANAGE_CLASSES                   = 'MANAGE_CLASSES';
    public const MANAGE_CYCLES                    = 'MANAGE_CYCLES';
    public const MANAGE_FILIERES                  = 'MANAGE_FILIERES';
    public const MANAGE_MATIERES                  = 'MANAGE_MATIERES';
    public const MANAGE_NIVEAUX                   = 'MANAGE_NIVEAUX';
    public const MANAGE_SALLES                    = 'MANAGE_SALLES';
    public const MANAGE_SEMESTRES                 = 'MANAGE_SEMESTRES';
    public const MANAGE_UE                        = 'MANAGE_UE';
    public const PRINT_CLASSES                    = 'PRINT_CLASSES';
    public const PRINT_FILIERES                   = 'PRINT_FILIERES';
    public const PRINT_OFFRE_ACADEMIQUE           = 'PRINT_OFFRE_ACADEMIQUE';
    public const PRINT_SALLES                     = 'PRINT_SALLES';
    public const VIEW_ANNEES                      = 'VIEW_ANNEES';
    public const VIEW_CLASSES                     = 'VIEW_CLASSES';
    public const VIEW_CYCLES                      = 'VIEW_CYCLES';
    public const VIEW_FILIERES                    = 'VIEW_FILIERES';
    public const VIEW_MATIERES                    = 'VIEW_MATIERES';
    public const VIEW_NIVEAUX                     = 'VIEW_NIVEAUX';
    public const VIEW_SALLES                      = 'VIEW_SALLES';
    public const VIEW_SEMESTRES                   = 'VIEW_SEMESTRES';
    public const VIEW_UE                          = 'VIEW_UE';

    // MODULE: ADMINISTRATION
    public const BACKUP_DATABASE                  = 'BACKUP_DATABASE';
    public const CONFIG_SYSTEM                    = 'CONFIG_SYSTEM';
    public const MANAGE_ETABLISSEMENT             = 'MANAGE_ETABLISSEMENT';
    public const MANAGE_FONCTIONS                 = 'MANAGE_FONCTIONS';
    public const MANAGE_PERMISSIONS               = 'MANAGE_PERMISSIONS';
    public const MANAGE_ROLES                     = 'MANAGE_ROLES';
    public const MANAGE_USERS                     = 'MANAGE_USERS';
    public const VIEW_FONCTIONS                   = 'VIEW_FONCTIONS';
    public const VIEW_PERMISSIONS                 = 'VIEW_PERMISSIONS';
    public const VIEW_ROLES                       = 'VIEW_ROLES';
    public const VIEW_USERS                       = 'VIEW_USERS';

    // MODULE: ANNEES
    public const EDIT_ANNEES_ANTERIEURES          = 'EDIT_ANNEES_ANTERIEURES';
    public const VIEW_ANNEES_ANTERIEURES          = 'VIEW_ANNEES_ANTERIEURES';

    // MODULE: COMMUNICATION
    public const MANAGE_COMMUNICATION             = 'MANAGE_COMMUNICATION';
    public const MANAGE_DOCUMENTS                 = 'MANAGE_DOCUMENTS';
    public const MANAGE_EVENEMENTS                = 'MANAGE_EVENEMENTS';
    public const MANAGE_GALLERY                   = 'MANAGE_GALLERY';
    public const SEND_MESSAGES                    = 'SEND_MESSAGES';
    public const VIEW_DOCUMENTS                   = 'VIEW_DOCUMENTS';
    public const VIEW_EVENEMENTS                  = 'VIEW_EVENEMENTS';
    public const VIEW_GALLERY                     = 'VIEW_GALLERY';
    public const VIEW_HISTORIQUE                  = 'VIEW_HISTORIQUE';

    // MODULE: DASHBOARD
    public const VIEW_DASHBOARD_ACTIONS           = 'VIEW_DASHBOARD_ACTIONS';
    public const VIEW_DASHBOARD_COMMUNICATION     = 'VIEW_DASHBOARD_COMMUNICATION';
    public const VIEW_DASHBOARD_ENSEIGNANT        = 'VIEW_DASHBOARD_ENSEIGNANT';
    public const VIEW_DASHBOARD_EXECUTIVE         = 'VIEW_DASHBOARD_EXECUTIVE';
    public const VIEW_DASHBOARD_FINANCE           = 'VIEW_DASHBOARD_FINANCE';
    public const VIEW_DASHBOARD_PEDAGOGIE         = 'VIEW_DASHBOARD_PEDAGOGIE';
    public const VIEW_DASHBOARD_SCOLARITE         = 'VIEW_DASHBOARD_SCOLARITE';

    // MODULE: FINANCE
    public const CANCEL_PAIEMENTS                 = 'CANCEL_PAIEMENTS';
    public const CLOTURE_CAISSE                   = 'CLOTURE_CAISSE';
    public const EXPORT_FINANCE                   = 'EXPORT_FINANCE';
    public const MANAGE_CAISSE                    = 'MANAGE_CAISSE';
    public const MANAGE_CAISSE_ANNEXE             = 'MANAGE_CAISSE_ANNEXE';
    public const MANAGE_FRAIS_ANNEXES             = 'MANAGE_FRAIS_ANNEXES';
    public const MANAGE_FRAIS_SCOLARITE           = 'MANAGE_FRAIS_SCOLARITE';
    public const MANAGE_IMPAYES                   = 'MANAGE_IMPAYES';
    public const MANAGE_MORATOIRES                = 'MANAGE_MORATOIRES';
    public const MANAGE_PAIEMENTS                 = 'MANAGE_PAIEMENTS';
    public const MANAGE_TYPES_DEPENSE             = 'MANAGE_TYPES_DEPENSE';
    public const OUVERTURE_CAISSE                 = 'OUVERTURE_CAISSE';
    public const PRINT_FRAIS_ANNEXES              = 'PRINT_FRAIS_ANNEXES';
    public const PRINT_FRAIS_SCOLARITE            = 'PRINT_FRAIS_SCOLARITE';
    public const RECORD_DEPENSES                  = 'RECORD_DEPENSES';
    public const RECORD_PAIEMENTS                 = 'RECORD_PAIEMENTS';
    public const SEND_RELANCES                    = 'SEND_RELANCES';
    public const VALIDATE_DEPENSES                = 'VALIDATE_DEPENSES';
    public const VIEW_CAISSE_ANNEXE               = 'VIEW_CAISSE_ANNEXE';
    public const VIEW_COMPTABILITE_ETUDIANTS      = 'VIEW_COMPTABILITE_ETUDIANTS';
    public const VIEW_DEPENSES                    = 'VIEW_DEPENSES';
    public const VIEW_FRAIS_ANNEXES               = 'VIEW_FRAIS_ANNEXES';
    public const VIEW_FRAIS_SCOLARITE             = 'VIEW_FRAIS_SCOLARITE';
    public const VIEW_IMPAYES                     = 'VIEW_IMPAYES';
    public const VIEW_PAIEMENTS                   = 'VIEW_PAIEMENTS';
    public const VIEW_RAPPORTS_FINANCIERS         = 'VIEW_RAPPORTS_FINANCIERS';
    public const VIEW_TYPES_DEPENSE               = 'VIEW_TYPES_DEPENSE';

    // MODULE: PEDAGOGIE
    public const ENTER_NOTES                      = 'ENTER_NOTES';
    public const GENERATE_BULLETINS               = 'GENERATE_BULLETINS';
    public const LOCK_NOTES                       = 'LOCK_NOTES';
    public const MANAGE_AFFECTATIONS              = 'MANAGE_AFFECTATIONS';
    public const MANAGE_EMPLOI_TEMPS              = 'MANAGE_EMPLOI_TEMPS';
    public const MANAGE_ENSEIGNANTS               = 'MANAGE_ENSEIGNANTS';
    public const MANAGE_EXAMENS                   = 'MANAGE_EXAMENS';
    public const VIEW_AFFECTATIONS                = 'VIEW_AFFECTATIONS';
    public const VIEW_BULLETINS                   = 'VIEW_BULLETINS';
    public const VIEW_EMPLOI_TEMPS                = 'VIEW_EMPLOI_TEMPS';
    public const VIEW_ENSEIGNANTS                 = 'VIEW_ENSEIGNANTS';
    public const VIEW_EXAMENS                     = 'VIEW_EXAMENS';
    public const VIEW_NOTES                       = 'VIEW_NOTES';
    public const VIEW_REPORTS                     = 'VIEW_REPORTS';

    // MODULE: SCOLAIRITE
    public const ANNULER_INSCRIPTION              = 'ANNULER_INSCRIPTION';
    public const CHANGER_CLASSE_ETUDIANT          = 'CHANGER_CLASSE_ETUDIANT';
    public const EDIT_ETUDIANT                    = 'EDIT_ETUDIANT';
    public const MANAGE_ABSENCES                  = 'MANAGE_ABSENCES';
    public const MANAGE_ACCESSOIRES               = 'MANAGE_ACCESSOIRES';
    public const MANAGE_DEPOT_DOSSIERS            = 'MANAGE_DEPOT_DOSSIERS';
    public const MANAGE_DEROGATION_INSCRIPTION    = 'MANAGE_DEROGATION_INSCRIPTION';
    public const MANAGE_DISCIPLINE                = 'MANAGE_DISCIPLINE';
    public const MANAGE_ETUDIANTS                 = 'MANAGE_ETUDIANTS';
    public const MANAGE_INSCRIPTIONS              = 'MANAGE_INSCRIPTIONS';
    public const MANAGE_INSCRIPTIONS_SANS_PHOTO   = 'MANAGE_INSCRIPTIONS_SANS_PHOTO';
    public const MANAGE_PARENTS                   = 'MANAGE_PARENTS';
    public const MANAGE_PIECES                    = 'MANAGE_PIECES';
    public const MANAGE_REMISE_KITS               = 'MANAGE_REMISE_KITS';
    public const PRINT_ATTESTATIONS               = 'PRINT_ATTESTATIONS';
    public const VIEW_ABSENCES                    = 'VIEW_ABSENCES';
    public const VIEW_ACCESSOIRES                 = 'VIEW_ACCESSOIRES';
    public const VIEW_DEPOT_DOSSIERS              = 'VIEW_DEPOT_DOSSIERS';
    public const VIEW_DISCIPLINE                  = 'VIEW_DISCIPLINE';
    public const VIEW_ETUDIANTS                   = 'VIEW_ETUDIANTS';
    public const VIEW_INSCRIPTIONS                = 'VIEW_INSCRIPTIONS';
    public const VIEW_INSCRIPTIONS_SANS_PHOTO     = 'VIEW_INSCRIPTIONS_SANS_PHOTO';
    public const VIEW_PARENTS                     = 'VIEW_PARENTS';
    public const VIEW_PIECES                      = 'VIEW_PIECES';
    public const VIEW_REMISE_KITS                 = 'VIEW_REMISE_KITS';

    // MODULE: SIDEBAR
    public const MENU_ADMINISTRATION              = 'MENU_ADMINISTRATION';
    public const MENU_COMMUNICATION               = 'MENU_COMMUNICATION';
    public const MENU_FINANCE                     = 'MENU_FINANCE';
    public const MENU_PEDAGOGIE                   = 'MENU_PEDAGOGIE';
    public const MENU_PLANIFICATION               = 'MENU_PLANIFICATION';
    public const MENU_SCOLARITE                   = 'MENU_SCOLARITE';
    public const MENU_STRUCTURE                   = 'MENU_STRUCTURE';

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
    public const ACTIF   = 'actif';
    public const INACTIF = 'inactif';
    public const GENERAL = ['actif', 'inactif'];

    // TYPES DE FILIÈRES
    public const TYPES_FILIERES = [
        'INDUSTRIELLE' => 'Filière Industrielle',
        'TERTIAIRE'    => 'Filière Tertiaire'
    ];
}
