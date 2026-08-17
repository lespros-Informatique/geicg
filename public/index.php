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

$route->addRoute('/livreur/list', [$livreurController, 'list']);
$route->addRoute('/livreur/apiList', [$livreurController, 'apiList']);
$route->addRoute('/livreur/add', [$livreurController, 'add']);
$route->addRoute('/livreur/edit', [$livreurController, 'edit']);
$route->addRoute('/livreur/changer', [$livreurController, 'changer']);
$route->addRoute('/livreur/edition/{param}', [$livreurController, 'edition']);
$route->addRoute('/livreur/details/{param}', [$livreurController, 'details']);
$route->addRoute('/livreur/getActive', [$livreurController, 'getActive']);
$route->addRoute('/livreur/formulaire', [$livreurController, 'formulaire']);

$route->addRoute('/mission/list', [$missionController, 'list']);
$route->addRoute('/mission/apiList', [$missionController, 'apiList']);
$route->addRoute('/mission/add', [$missionController, 'add']);
$route->addRoute('/mission/edit', [$missionController, 'edit']);
$route->addRoute('/mission/changer', [$missionController, 'changer']);
$route->addRoute('/mission/edition/{param}', [$missionController, 'edition']);
$route->addRoute('/mission/details/{param}', [$missionController, 'details']);
$route->addRoute('/mission/getActive', [$missionController, 'getActive']);
$route->addRoute('/mission/formulaire', [$missionController, 'formulaire']);

$route->addRoute('/horaire/list', [$horairePressingController, 'list']);
$route->addRoute('/horaire/apiList', [$horairePressingController, 'apiList']);
$route->addRoute('/horaire/add', [$horairePressingController, 'add']);
$route->addRoute('/horaire/edit', [$horairePressingController, 'edit']);
$route->addRoute('/horaire/changer', [$horairePressingController, 'changer']);
$route->addRoute('/horaire/edition/{param}', [$horairePressingController, 'edition']);
$route->addRoute('/horaire/details/{param}', [$horairePressingController, 'details']);
$route->addRoute('/horaire/getActive', [$horairePressingController, 'getActive']);
$route->addRoute('/horaire/formulaire', [$horairePressingController, 'formulaire']);

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

$route->addRoute('/user/profil', [$userController, 'profil']);
$route->addRoute('/user/list', [$userController, 'list']);
$route->addRoute('/user/apiList', [$userController, 'apiList']);
$route->addRoute('/user/decon', [$userController, 'decon']);
$route->addRoute('/user/details/{param}', [$userController, 'details']);
$route->addRoute('/user/edition/{param}', [$userController, 'edition']);
$route->addRoute('/user/connexion', [$userController, 'connexion']);
$route->addRoute('/user/add', [$userController, 'add']);
$route->addRoute('/user/edit', [$userController, 'edit']);
$route->addRoute('/user/editPassword', [$userController, 'editPassword']);
$route->addRoute('/user/changer', [$userController, 'changer']);

$route->addRoute('/commande/list', [$commandeController, 'list']);
$route->addRoute('/commande/apiList', [$commandeController, 'apiList']);
$route->addRoute('/commande/add', [$commandeController, 'add']);
$route->addRoute('/commande/edit', [$commandeController, 'edit']);
$route->addRoute('/commande/changer', [$commandeController, 'changer']);
$route->addRoute('/commande/edition/{param}', [$commandeController, 'edition']);
$route->addRoute('/commande/details/{param}', [$commandeController, 'details']);
$route->addRoute('/commande/transition', [$commandeController, 'transition']);

$route->addRoute('/paiement/list', [$paiementController, 'list']);
$route->addRoute('/paiement/apiList', [$paiementController, 'apiList']);
$route->addRoute('/paiement/add', [$paiementController, 'add']);
$route->addRoute('/paiement/edit', [$paiementController, 'edit']);
$route->addRoute('/paiement/changer', [$paiementController, 'changer']);
$route->addRoute('/paiement/edition/{param}', [$paiementController, 'edition']);
$route->addRoute('/paiement/details/{param}', [$paiementController, 'details']);


$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($url, '/admin-lavex/public') === 0) {
    $url = str_replace('/admin-lavex/public', '', $url);
} elseif (strpos($url, '/admin-lavex') === 0) {
    $url = str_replace('/admin-lavex', '', $url);
}
$route->run($url);
