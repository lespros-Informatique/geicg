<?php
define('ROOT', $_SERVER['DOCUMENT_ROOT'] ?? 'C:/wamp64/www');
define('RACINE', 'http://localhost/admin-lavex/');

define('LOGO', '<img src="' .RACINE. 'public/assets/images/logo/logo.png" class="img-circle" alt="Logo" width="80" style="border-radius: 70%; object-fit: covers;">');

define('ICON', '<img src="'.RACINE.'public/assets/images/logo/icon.png" alt="LOGO" style="position: relative; bottom:12px; border-radius: 70%; object-fit: covers;" width="50" height="50";>');

define('TITLE', 'LAVEX Admin');

const USERS_AUTH = 'users_auth';

class TABLES
{
    // PRESSING
    public const PRESSINGS           = 'pressings';

    // CATALOGUE
    public const CATEGORIES_ARTICLES = 'categories_articles';
    public const ARTICLES_PRESSINGS   = 'articles_pressings';
    public const TARIFS_ARTICLES      = 'tarifs_articles';
    public const SERVICES             = 'services';

    // CLIENTS
    public const CLIENTS             = 'clients';

    // UTILISATEURS
    public const USERS               = 'users';
    public const ROLES               = 'roles';
    public const PERMISSIONS         = 'permissions';
    public const ROLES_PERMISSIONS   = 'roles_permissions';
    public const USERS_PRESSINGS     = 'users_pressings';

    // COMMANDES
    public const COMMANDES           = 'commandes';
    public const COMMANDE_DETAILS    = 'commande_details';

    // PAIEMENTS
    public const PAIEMENTS           = 'paiements';
    public const PAIEMENTS_ABONNEMENTS = 'paiements_abonnements';

    // ABONNEMENTS
    public const ABONNEMENTS_PRESSINGS = 'abonnements_pressings';
    public const FORFAITS              = 'forfaits';

    // FAVORIS
    public const FAVORIS             = 'favoris';

    // PANIERS
    public const PANIERS             = 'paniers';
    public const PANIER_DETAILS      = 'panier_details';

    // LIVRAISONS
    public const LIVREURS            = 'livreurs';
    public const MISSIONS            = 'missions';

    // HORAIRES
    public const HORAIRES_PRESSINGS  = 'horaires_pressings';

    // NOTIFICATIONS
    public const NOTIFICATIONS       = 'notifications';
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
}
