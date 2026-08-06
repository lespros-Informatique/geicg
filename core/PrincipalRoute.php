<?php

require_once __DIR__ . '/../config/Database.php';

require_once __DIR__ . '/../models/Validator.php';

require_once __DIR__ . '/../core/BaseController.php';

require_once __DIR__ . '/../core/BaseModel.php';

require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../models/home/ModelHome.php';
require_once __DIR__ . '/../models/articles/ModelArticle.php';
require_once __DIR__ . '/../models/clients/ModelClient.php';
require_once __DIR__ . '/../models/users/ModelUser.php';
require_once __DIR__ . '/../models/roles/ModelRole.php';
require_once __DIR__ . '/../models/permissions/ModelPermission.php';
require_once __DIR__ . '/../models/users_pressings/ModelUserPressing.php';
require_once __DIR__ . '/../models/commandes/ModelCommande.php';
require_once __DIR__ . '/../models/commande_details/ModelCommandeDetail.php';
require_once __DIR__ . '/../models/paiements/ModelPaiement.php';

require_once __DIR__ . '/../controllers/home/HomeController.php';
require_once __DIR__ . '/../controllers/articles/ArticleController.php';
require_once __DIR__ . '/../controllers/clients/ClientController.php';
require_once __DIR__ . '/../controllers/users/UserController.php';
require_once __DIR__ . '/../controllers/commandes/CommandeController.php';
require_once __DIR__ . '/../controllers/paiements/PaiementController.php';
