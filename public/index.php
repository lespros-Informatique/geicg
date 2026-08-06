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

$route->addRoute('/campagne/list', [$campagneController, 'list']);
$route->addRoute('/campagne/apiList', [$campagneController, 'apiList']);
$route->addRoute('/campagne/add', [$campagneController, 'add']);
$route->addRoute('/campagne/edit', [$campagneController, 'edit']);
$route->addRoute('/campagne/changer', [$campagneController, 'changer']);
$route->addRoute('/campagne/edition/{param}', [$campagneController, 'edition']);
$route->addRoute('/campagne/details/{param}', [$campagneController, 'details']);
$route->addRoute('/campagne/getActive', [$campagneController, 'getActive']);
$route->addRoute('/campagne/setActive', [$campagneController, 'setActive']);
$route->addRoute('/campagne/getCurrent', [$campagneController, 'getCurrent']);

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
$route->addRoute('/paiement/clients', [$paiementController, 'clients']);
$route->addRoute('/paiement/kits/{param}', [$paiementController, 'kits']);
$route->addRoute('/paiement/calendar/{param}', [$paiementController, 'calendar']);
$route->addRoute('/paiement/pay', [$paiementController, 'pay']);
$route->addRoute('/paiement/sessions', [$paiementController, 'sessions']);

$route->addRoute('/retrait/clients', [$retraitController, 'clients']);
$route->addRoute('/retrait/kits/{param}', [$retraitController, 'kits']);
$route->addRoute('/retrait/list', [$retraitController, 'list']);
$route->addRoute('/retrait/apiList', [$retraitController, 'apiList']);
$route->addRoute('/retrait/add', [$retraitController, 'add']);
$route->addRoute('/retrait/edit', [$retraitController, 'edit']);
$route->addRoute('/retrait/changer', [$retraitController, 'changer']);
$route->addRoute('/retrait/edition/{param}', [$retraitController, 'edition']);
$route->addRoute('/retrait/details/{param}', [$retraitController, 'details']);

$route->addRoute('/session/list', [$sessionController, 'list']);
$route->addRoute('/session/apiList', [$sessionController, 'apiList']);
$route->addRoute('/session/add', [$sessionController, 'add']);
$route->addRoute('/session/edit', [$sessionController, 'edit']);
$route->addRoute('/session/changer', [$sessionController, 'changer']);
$route->addRoute('/session/edition/{param}', [$sessionController, 'edition']);
$route->addRoute('/session/details/{param}', [$sessionController, 'details']);
$route->addRoute('/session/getActive', [$sessionController, 'getActive']);
$route->addRoute('/session/current', [$sessionController, 'current']);
$route->addRoute('/session/open', [$sessionController, 'open']);
$route->addRoute('/session/close/{param}', [$sessionController, 'close']);

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($url, '/kits/public') === 0) {
    $url = substr($url, strlen('/kits/public'));
} elseif (strpos($url, '/kits') === 0) {
    $url = substr($url, strlen('/kits'));
}

$route->run($url);
