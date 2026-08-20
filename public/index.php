<?php 
require_once __DIR__ . '/../core/PrincipalRoute.php';

$route = new Router();

$homeController = new HomeController();
$articleController = new ArticleController();
$serviceController = new ServiceController();
$pressingController = new PressingController();
$livreurController = new LivreurController();
$missionController = new MissionController();
$horairePressingController = new HorairePressingController();
$categorieArticleController = new CategorieArticleController();
$tarifArticleController = new TarifArticleController();
$clientController = new ClientController();
$userController = new UserController();
$commandeController = new CommandeController();
$paiementController = new PaiementController();
$villeController = new VilleController();
$quartierController = new QuartierController();
$roleController = new RoleController();
$permissionController = new PermissionController();
$abonnementPressingController = new AbonnementPressingController();
$forfaitController = new ForfaitController();
$favoriController = new FavoriController();
$notificationController = new NotificationController();
$panierController = new PanierController();
$panierDetailController = new PanierDetailController();
$retraitController = new RetraitController();
$geniusPayWebhookController = new GeniusPayWebhookController();

$route->addRoute('/', [$homeController, 'index']);
$route->addRoute('/home/dashboardData', [$homeController, 'dashboardData']);

$route->addRoute('/article/list', [$articleController, 'list']);
$route->addRoute('/article/apiList', [$articleController, 'apiList']);
$route->addRoute('/article/add', [$articleController, 'add']);
$route->addRoute('/article/edit', [$articleController, 'edit']);
$route->addRoute('/article/changer', [$articleController, 'changer']);
$route->addRoute('/article/edition/{param}', [$articleController, 'edition']);
$route->addRoute('/article/details/{param}', [$articleController, 'details']);
$route->addRoute('/article/getActive', [$articleController, 'getActive']);
$route->addRoute('/article/formulaire', [$articleController, 'formulaire']);

$route->addRoute('/service/list', [$serviceController, 'list']);
$route->addRoute('/service/apiList', [$serviceController, 'apiList']);
$route->addRoute('/service/add', [$serviceController, 'add']);
$route->addRoute('/service/edit', [$serviceController, 'edit']);
$route->addRoute('/service/changer', [$serviceController, 'changer']);
$route->addRoute('/service/edition/{param}', [$serviceController, 'edition']);
$route->addRoute('/service/details/{param}', [$serviceController, 'details']);
$route->addRoute('/service/getActive', [$serviceController, 'getActive']);
$route->addRoute('/service/formulaire', [$serviceController, 'formulaire']);

$route->addRoute('/pressing/list', [$pressingController, 'list']);
$route->addRoute('/pressing/apiList', [$pressingController, 'apiList']);
$route->addRoute('/pressing/add', [$pressingController, 'add']);
$route->addRoute('/pressing/edit', [$pressingController, 'edit']);
$route->addRoute('/pressing/changer', [$pressingController, 'changer']);
$route->addRoute('/pressing/edition/{param}', [$pressingController, 'edition']);
$route->addRoute('/pressing/details/{param}', [$pressingController, 'details']);
$route->addRoute('/pressing/getActive', [$pressingController, 'getActive']);
$route->addRoute('/pressing/formulaire', [$pressingController, 'formulaire']);
$route->addRoute('/pressing/addUser', [$pressingController, 'addUser']);
$route->addRoute('/pressing/config', [$pressingController, 'config']);

$route->addRoute('/livreur/list', [$livreurController, 'list']);
$route->addRoute('/livreur/apiList', [$livreurController, 'apiList']);
$route->addRoute('/livreur/add', [$livreurController, 'add']);
$route->addRoute('/livreur/edit', [$livreurController, 'edit']);
$route->addRoute('/livreur/changer', [$livreurController, 'changer']);
$route->addRoute('/livreur/edition/{param}', [$livreurController, 'edition']);
$route->addRoute('/livreur/details/{param}', [$livreurController, 'details']);
$route->addRoute('/livreur/getActive', [$livreurController, 'getActive']);
$route->addRoute('/livreur/formulaire', [$livreurController, 'formulaire']);
$route->addRoute('/livreur/updatePosition', [$livreurController, 'updatePosition']);
$route->addRoute('/livreur/livePositions', [$livreurController, 'livePositions']);

$route->addRoute('/mission/list', [$missionController, 'list']);
$route->addRoute('/mission/apiList', [$missionController, 'apiList']);
$route->addRoute('/mission/add', [$missionController, 'add']);
$route->addRoute('/mission/edit', [$missionController, 'edit']);
$route->addRoute('/mission/changer', [$missionController, 'changer']);
$route->addRoute('/mission/edition/{param}', [$missionController, 'edition']);
$route->addRoute('/mission/details/{param}', [$missionController, 'details']);
$route->addRoute('/mission/getActive', [$missionController, 'getActive']);
$route->addRoute('/mission/formulaire', [$missionController, 'formulaire']);
$route->addRoute('/mission/enRouteCollecte', [$missionController, 'enRouteCollecte']);
$route->addRoute('/mission/lingeCollecte', [$missionController, 'lingeCollecte']);
$route->addRoute('/mission/deposeAuPressing', [$missionController, 'deposeAuPressing']);
$route->addRoute('/mission/enRouteLivraison', [$missionController, 'enRouteLivraison']);
$route->addRoute('/mission/remiseAuClient', [$missionController, 'remiseAuClient']);
$route->addRoute('/mission/carte', [$missionController, 'carte']);

$route->addRoute('/horaire/list', [$horairePressingController, 'list']);
$route->addRoute('/horaire/apiList', [$horairePressingController, 'apiList']);
$route->addRoute('/horaire/add', [$horairePressingController, 'add']);
$route->addRoute('/horaire/edit', [$horairePressingController, 'edit']);
$route->addRoute('/horaire/changer', [$horairePressingController, 'changer']);
$route->addRoute('/horaire/edition/{param}', [$horairePressingController, 'edition']);
$route->addRoute('/horaire/details/{param}', [$horairePressingController, 'details']);
$route->addRoute('/horaire/getActive', [$horairePressingController, 'getActive']);
$route->addRoute('/horaire/formulaire', [$horairePressingController, 'formulaire']);

$route->addRoute('/horaires/list', [$horairePressingController, 'list']);
$route->addRoute('/horaires/apiList', [$horairePressingController, 'apiList']);
$route->addRoute('/horaires/add', [$horairePressingController, 'add']);
$route->addRoute('/horaires/edit', [$horairePressingController, 'edit']);
$route->addRoute('/horaires/changer', [$horairePressingController, 'changer']);
$route->addRoute('/horaires/edition/{param}', [$horairePressingController, 'edition']);
$route->addRoute('/horaires/details/{param}', [$horairePressingController, 'details']);
$route->addRoute('/horaires/getActive', [$horairePressingController, 'getActive']);
$route->addRoute('/horaires/formulaire', [$horairePressingController, 'formulaire']);

$route->addRoute('/categorie/list', [$categorieArticleController, 'list']);
$route->addRoute('/categorie/apiList', [$categorieArticleController, 'apiList']);
$route->addRoute('/categorie/add', [$categorieArticleController, 'add']);
$route->addRoute('/categorie/edit', [$categorieArticleController, 'edit']);
$route->addRoute('/categorie/changer', [$categorieArticleController, 'changer']);
$route->addRoute('/categorie/edition/{param}', [$categorieArticleController, 'edition']);
$route->addRoute('/categorie/details/{param}', [$categorieArticleController, 'details']);
$route->addRoute('/categorie/getActive', [$categorieArticleController, 'getActive']);
$route->addRoute('/categorie/formulaire', [$categorieArticleController, 'formulaire']);

$route->addRoute('/tarif/list', [$tarifArticleController, 'list']);
$route->addRoute('/tarif/apiList', [$tarifArticleController, 'apiList']);
$route->addRoute('/tarif/add', [$tarifArticleController, 'add']);
$route->addRoute('/tarif/edit', [$tarifArticleController, 'edit']);
$route->addRoute('/tarif/changer', [$tarifArticleController, 'changer']);
$route->addRoute('/tarif/edition/{param}', [$tarifArticleController, 'edition']);
$route->addRoute('/tarif/details/{param}', [$tarifArticleController, 'details']);
$route->addRoute('/tarif/getActive', [$tarifArticleController, 'getActive']);
$route->addRoute('/tarif/formulaire', [$tarifArticleController, 'formulaire']);

$route->addRoute('/client/list', [$clientController, 'list']);
$route->addRoute('/client/apiList', [$clientController, 'apiList']);
$route->addRoute('/client/add', [$clientController, 'add']);
$route->addRoute('/client/edit', [$clientController, 'edit']);
$route->addRoute('/client/changer', [$clientController, 'changer']);
$route->addRoute('/client/edition/{param}', [$clientController, 'edition']);
$route->addRoute('/client/details/{param}', [$clientController, 'details']);
$route->addRoute('/client/getActive', [$clientController, 'getActive']);
$route->addRoute('/client/formulaire', [$clientController, 'formulaire']);

$route->addRoute('/user/list', [$userController, 'list']);
$route->addRoute('/user/apiList', [$userController, 'apiList']);
$route->addRoute('/user/add', [$userController, 'add']);
$route->addRoute('/user/edit', [$userController, 'edit']);
$route->addRoute('/user/changer', [$userController, 'changer']);
$route->addRoute('/user/login', [$userController, 'login']);
$route->addRoute('/user/logout', [$userController, 'logout']);
$route->addRoute('/user/connexion', [$userController, 'connexion']);
$route->addRoute('/user/profil', [$userController, 'profil']);
$route->addRoute('/user/edition/{param}', [$userController, 'edition']);
$route->addRoute('/user/details/{param}', [$userController, 'details']);
$route->addRoute('/user/getActive', [$userController, 'getActive']);
$route->addRoute('/user/formulaire', [$userController, 'formulaire']);
$route->addRoute('/user/checkPhone', [$userController, 'checkPhone']);

$route->addRoute('/commande/list', [$commandeController, 'list']);
$route->addRoute('/commande/apiList', [$commandeController, 'apiList']);
$route->addRoute('/commande/add', [$commandeController, 'add']);
$route->addRoute('/commande/edit', [$commandeController, 'edit']);
$route->addRoute('/commande/changer', [$commandeController, 'changer']);
$route->addRoute('/commande/edition/{param}', [$commandeController, 'edition']);
$route->addRoute('/commande/details/{param}', [$commandeController, 'details']);
$route->addRoute('/commande/getActive', [$commandeController, 'getActive']);
$route->addRoute('/commande/formulaire', [$commandeController, 'formulaire']);
$route->addRoute('/commande/saisirDevisColis', [$commandeController, 'saisirDevisColis']);
$route->addRoute('/commande/accepter', [$commandeController, 'accepter']);
$route->addRoute('/commande/refuser', [$commandeController, 'refuser']);
$route->addRoute('/commande/lancerTraitement', [$commandeController, 'lancerTraitement']);
$route->addRoute('/commande/marquerPrete', [$commandeController, 'marquerPrete']);
$route->addRoute('/commande/assignerLivreur', [$commandeController, 'assignerLivreur']);
$route->addRoute('/commande/ticket/{param}', [$commandeController, 'ticket']);

$route->addRoute('/paiement/list', [$paiementController, 'list']);
$route->addRoute('/paiement/apiList', [$paiementController, 'apiList']);
$route->addRoute('/paiement/add', [$paiementController, 'add']);
$route->addRoute('/paiement/edit', [$paiementController, 'edit']);
$route->addRoute('/paiement/changer', [$paiementController, 'changer']);
$route->addRoute('/paiement/edition/{param}', [$paiementController, 'edition']);
$route->addRoute('/paiement/details/{param}', [$paiementController, 'details']);

$route->addRoute('/retrait/list', [$retraitController, 'list']);
$route->addRoute('/retrait/apiList', [$retraitController, 'apiList']);
$route->addRoute('/retrait/apiSolde', [$retraitController, 'apiSolde']);
$route->addRoute('/retrait/demander', [$retraitController, 'demander']);
$route->addRoute('/retrait/changerStatut', [$retraitController, 'changerStatut']);
$route->addRoute('/retrait/simulerWebhookCashout', [$retraitController, 'simulerWebhookCashout']);

$route->addRoute('/webhooks/geniuspay', [$geniusPayWebhookController, 'handle']);

$route->addRoute('/ville/list', [$villeController, 'list']);
$route->addRoute('/ville/apiList', [$villeController, 'apiList']);
$route->addRoute('/ville/add', [$villeController, 'add']);
$route->addRoute('/ville/edit', [$villeController, 'edit']);
$route->addRoute('/ville/changer', [$villeController, 'changer']);
$route->addRoute('/ville/edition/{param}', [$villeController, 'edition']);
$route->addRoute('/ville/details/{param}', [$villeController, 'details']);
$route->addRoute('/ville/getActive', [$villeController, 'getActive']);
$route->addRoute('/ville/formulaire', [$villeController, 'formulaire']);

$route->addRoute('/quartier/list', [$quartierController, 'list']);
$route->addRoute('/quartier/apiList', [$quartierController, 'apiList']);
$route->addRoute('/quartier/add', [$quartierController, 'add']);
$route->addRoute('/quartier/edit', [$quartierController, 'edit']);
$route->addRoute('/quartier/changer', [$quartierController, 'changer']);
$route->addRoute('/quartier/edition/{param}', [$quartierController, 'edition']);
$route->addRoute('/quartier/details/{param}', [$quartierController, 'details']);
$route->addRoute('/quartier/getActive', [$quartierController, 'getActive']);
$route->addRoute('/quartier/formulaire', [$quartierController, 'formulaire']);

$route->addRoute('/abonnement/list', [$abonnementPressingController, 'list']);
$route->addRoute('/abonnement/apiList', [$abonnementPressingController, 'apiList']);
$route->addRoute('/abonnement/add', [$abonnementPressingController, 'add']);
$route->addRoute('/abonnement/edit', [$abonnementPressingController, 'edit']);
$route->addRoute('/abonnement/changer', [$abonnementPressingController, 'changer']);
$route->addRoute('/abonnement/edition/{param}', [$abonnementPressingController, 'edition']);
$route->addRoute('/abonnement/details/{param}', [$abonnementPressingController, 'details']);
$route->addRoute('/abonnement/getActive', [$abonnementPressingController, 'getActive']);
$route->addRoute('/abonnement/formulaire', [$abonnementPressingController, 'formulaire']);

$route->addRoute('/forfait/list', [$forfaitController, 'list']);
$route->addRoute('/forfait/apiList', [$forfaitController, 'apiList']);
$route->addRoute('/forfait/add', [$forfaitController, 'add']);
$route->addRoute('/forfait/edit', [$forfaitController, 'edit']);
$route->addRoute('/forfait/changer', [$forfaitController, 'changer']);
$route->addRoute('/forfait/edition/{param}', [$forfaitController, 'edition']);
$route->addRoute('/forfait/details/{param}', [$forfaitController, 'details']);
$route->addRoute('/forfait/getActive', [$forfaitController, 'getActive']);
$route->addRoute('/forfait/formulaire', [$forfaitController, 'formulaire']);

$route->addRoute('/favori/list', [$favoriController, 'list']);
$route->addRoute('/favori/apiList', [$favoriController, 'apiList']);
$route->addRoute('/favori/add', [$favoriController, 'add']);
$route->addRoute('/favori/edit', [$favoriController, 'edit']);
$route->addRoute('/favori/changer', [$favoriController, 'changer']);
$route->addRoute('/favori/edition/{param}', [$favoriController, 'edition']);
$route->addRoute('/favori/details/{param}', [$favoriController, 'details']);

$route->addRoute('/notification/list', [$notificationController, 'list']);
$route->addRoute('/notification/apiList', [$notificationController, 'apiList']);
$route->addRoute('/notification/add', [$notificationController, 'add']);
$route->addRoute('/notification/edit', [$notificationController, 'edit']);
$route->addRoute('/notification/changer', [$notificationController, 'changer']);
$route->addRoute('/notification/marquerLu', [$notificationController, 'marquerLu']);
$route->addRoute('/notification/marquerToutLu', [$notificationController, 'marquerToutLu']);
$route->addRoute('/notification/delete', [$notificationController, 'delete']);
$route->addRoute('/notification/stats', [$notificationController, 'stats']);
$route->addRoute('/notification/edition/{param}', [$notificationController, 'edition']);
$route->addRoute('/notification/details/{param}', [$notificationController, 'details']);

$route->addRoute('/panier/list', [$panierController, 'list']);
$route->addRoute('/panier/apiList', [$panierController, 'apiList']);
$route->addRoute('/panier/add', [$panierController, 'add']);
$route->addRoute('/panier/edit', [$panierController, 'edit']);
$route->addRoute('/panier/changer', [$panierController, 'changer']);
$route->addRoute('/panier/edition/{param}', [$panierController, 'edition']);
$route->addRoute('/panier/details/{param}', [$panierController, 'details']);

$route->addRoute('/panier-detail/list', [$panierDetailController, 'list']);
$route->addRoute('/panier-detail/apiList', [$panierDetailController, 'apiList']);
$route->addRoute('/panier-detail/add', [$panierDetailController, 'add']);
$route->addRoute('/panier-detail/edit', [$panierDetailController, 'edit']);
$route->addRoute('/panier-detail/changer', [$panierDetailController, 'changer']);
$route->addRoute('/panier-detail/edition/{param}', [$panierDetailController, 'edition']);
$route->addRoute('/panier-detail/details/{param}', [$panierDetailController, 'details']);

$route->addRoute('/role/list', [$roleController, 'list']);
$route->addRoute('/role/apiList', [$roleController, 'apiList']);
$route->addRoute('/role/add', [$roleController, 'add']);
$route->addRoute('/role/edit', [$roleController, 'edit']);
$route->addRoute('/role/changer', [$roleController, 'changer']);
$route->addRoute('/role/edition/{param}', [$roleController, 'edition']);
$route->addRoute('/role/details/{param}', [$roleController, 'details']);
$route->addRoute('/role/formulaire', [$roleController, 'formulaire']);
$route->addRoute('/role/updatePermissions', [$roleController, 'updatePermissions']);
$route->addRoute('/role/permissions', [$roleController, 'updatePermissions']);

$route->addRoute('/permission/list', [$permissionController, 'list']);
$route->addRoute('/permission/apiList', [$permissionController, 'apiList']);
$route->addRoute('/permission/add', [$permissionController, 'add']);
$route->addRoute('/permission/edit', [$permissionController, 'edit']);
$route->addRoute('/permission/changer', [$permissionController, 'changer']);
$route->addRoute('/permission/edition/{param}', [$permissionController, 'edition']);
$route->addRoute('/permission/details/{param}', [$permissionController, 'details']);
$route->addRoute('/permission/formulaire', [$permissionController, 'formulaire']);


$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($url, '/admin-lavex/public') === 0) {
    $url = str_replace('/admin-lavex/public', '', $url);
} elseif (strpos($url, '/admin-lavex') === 0) {
    $url = str_replace('/admin-lavex', '', $url);
}
$url = rtrim($url, '/');
if ($url === '') {
    $url = '/';
}
$route->run($url);
