<?php
define('ROOT', $_SERVER['DOCUMENT_ROOT'] ?? 'C:/wamp64/www');

$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalEnvironment = (strpos($httpHost, 'localhost') !== false || strpos($httpHost, '127.0.0.1') !== false);

if (!defined('RACINE')) {
    define('RACINE', $isLocalEnvironment ? 'http://localhost/geicg/' : 'https://app.groupe-eicg.net/');
}

if (!defined('ONESIGNAL_APP_ID')) {
    define('ONESIGNAL_APP_ID', '54d8db10-a446-4542-9b2c-2d49d1433d59');
}
if (!defined('ONESIGNAL_REST_API_KEY')) {
    define('ONESIGNAL_REST_API_KEY', getenv('ONESIGNAL_REST_API_KEY') ?: '');
}


define('LOGO', '<span class="fw-bold fs-4 text-primary" style="letter-spacing: 1px;">GEICG</span>');

define('ICON', '<span class="fw-bold fs-4 text-primary">G</span>');

define('TITLE', 'GEICG - Administration Grande École');

const USERS_AUTH = 'users_auth';

class TABLES
{
    // INSTITUTION & ACCÈS
    public const ETABLISSEMENTS         = 'etablissements';
    public const SERVICES               = 'services';
    public const FONCTIONS              = 'fonctions';
    public const USERS                  = 'users';
    public const ROLES                  = 'roles';
    public const PERMISSIONS            = 'permissions';
    public const ROLE_PERMISSIONS       = 'role_permissions';
    public const USER_PERMISSIONS       = 'user_permissions';
    public const USER_ROLES             = 'user_roles';

    // ACADÉMIQUE & LMD (STRUCTURE GLOBALE OU ANNUELLE)
    public const ANNEES                 = 'annees';
    public const CYCLES                 = 'cycles';
    public const FILIERES               = 'filieres';
    public const FILIERE_CYCLES         = 'filiere_cycles';
    public const NIVEAUX                = 'niveaux';
    public const FILIERE_NIVEAUX        = 'filiere_niveaux';
    public const CLASSES                = 'classes';
    public const SEMESTRES              = 'semestres';
    public const UNITES_ENSEIGNEMENT    = 'unites_enseignement';
    public const MATIERES               = 'matieres';
    public const SALLES                 = 'salles';

    // SCOLAIRITÉ & ÉTUDIANTS
    public const ETUDIANTS              = 'etudiants';
    public const PARENTS                = 'parents';
    public const INSCRIPTIONS           = 'inscriptions';
    public const ACCESSOIRES            = 'accessoires';
    public const ACCESSOIRE_INSCRIPTION = 'accessoire_inscription';
    public const DOSSIER_ETUDIANT       = 'dossier_etudiant';

    // FINANCE & CAISSE
    public const SCOLARITES             = 'scolarites';
    public const TRANCHES_SCOLARITE     = 'tranches_scolarite';
    public const PAIEMENTS              = 'paiements';
    public const CLOTURES_CAISSE        = 'clotures_caisse';
    public const DEPENSES               = 'depenses';
    public const TYPE_DEPENSES          = 'type_depenses';

    // PÉDAGOGIE & ÉVALUATIONS
    public const ENSEIGNANTS            = 'enseignants';
    public const ENSEIGNANT_MATIERE     = 'enseignant_matiere';
    public const EMPLOIS_TEMPS          = 'emplois_temps';
    public const NOTES                  = 'notes';
    public const ABSENCES               = 'absences';

    // COMMUNICATION & MÉDIAS
    public const DOCUMENTS              = 'documents';
    public const MESSAGES               = 'messages';
    public const EVENEMENTS             = 'evenements';
    public const GALERIES               = 'galeries';
    public const GALERIE_MEDIAS         = 'galerie_medias';
}

class ROLES
{
    public const SUPER_ADMIN = 'ROLE-ADMIN';
    public const PRESSING    = 'ROLE-PRO';
    public const LIVREUR     = 'ROLE-LIV';
}

class STATUTS
{
    // PRESSINGS
    public const PRESSINGS           = ['actif','inactif','suspendu'];

    // CATEGORIES ARTICLES
    public const CATEGORIES_ARTICLES = ['actif','inactif'];

    // ARTICLES PRESSINGS
    public const ARTICLES_PRESSINGS   = ['actif','inactif'];

    // TARIFS ARTICLES
    public const TARIFS_ARTICLES      = ['actif','inactif'];

    // SERVICES
    public const SERVICES             = ['actif','inactif'];

    // CLIENTS
    public const CLIENTS             = ['actif','inactif'];

    // UTILISATEURS
    public const USERS               = ['actif','inactif'];

    // ROLES
    public const ROLES               = ['actif','inactif'];

    // PERMISSIONS
    public const PERMISSIONS         = ['actif','inactif'];

    // ROLES PERMISSIONS
    public const ROLES_PERMISSIONS   = ['actif','inactif'];

    // USERS PRESSINGS
    public const USERS_PRESSINGS     = ['actif','inactif'];

    // COMMANDES
    public const COMMANDES           = ['actif','inactif'];

    // COMMANDE DETAILS
    public const COMMANDE_DETAILS    = ['actif','inactif'];

    // PAIEMENTS
    public const PAIEMENTS           = ['valide','annule','en_attente'];

    // PAIEMENTS ABONNEMENTS
    public const PAIEMENTS_ABONNEMENTS = ['valide','annule','en_attente'];

    // ABONNEMENTS PRESSINGS
    public const ABONNEMENTS_PRESSINGS = ['actif','expire','suspendu'];

    // FORFAITS
    public const FORFAITS              = ['actif','inactif'];

    // FAVORIS
    public const FAVORIS             = ['actif','inactif'];

    // PANIERS
    public const PANIERS             = ['actif','valide','annule'];

    // PANIER DETAILS
    public const PANIER_DETAILS      = ['actif','inactif'];

    // LIVREURS
    public const LIVREURS            = ['actif','inactif'];

    // MISSIONS
    public const MISSIONS            = ['en_attente','en_cours','terminee','annulee'];

    // SUIVI COMMANDES
    public const SUIVI_COMMANDES = [
        'creee' => 'Commande reçue',
        'acceptee' => 'Acceptée',
        'refusee' => 'Refusée',
        'collecte_programmee' => 'Collecte programmée',
        'collecte_assignee' => 'Livreur assigné (Collecte)',
        'livreur_en_route_collecte' => 'Livreur en route pour la collecte',
        'collectee' => 'Linge collecté',
        'recue_pressing' => 'Reçue au pressing',
        'prix_a_valider' => 'Devis à valider',
        'en_traitement' => 'En traitement',
        'prete' => 'Prête au pressing',
        'en_livraison' => 'En cours de livraison',
        'livree' => 'Livrée au client',
        'annulee' => 'Annulée'
    ];

    // TYPES DE COMMANDES
    public const TYPES_COMMANDES = [
        'detaillee' => 'Détaillée',
        'colis' => 'Collecte au sac sans détail'
    ];

    // TYPES DE MISSIONS
    public const TYPES_MISSIONS = [
        'collecte' => 'Collecte',
        'livraison' => 'Livraison'
    ];

    // NOTIFICATIONS
    public const NOTIFICATIONS       = ['envoyee','echec'];

    // ZONES TERRITORIALES
    public const VILLES              = ['actif','inactif'];
    public const QUARTIERS           = ['actif','inactif'];
}
