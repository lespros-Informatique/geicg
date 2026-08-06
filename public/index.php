<?php 
require_once __DIR__ . '/../core/PrincipalRoute.php';

$route = new Router();

$homeController = new HomeController();
$articleController = new ArticleController();
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
