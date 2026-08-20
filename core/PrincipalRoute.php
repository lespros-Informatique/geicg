<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/GeniusPayConfig.php';
require_once __DIR__ . '/../core/GeniusPayService.php';

require_once __DIR__ . '/../models/Validator.php';

require_once __DIR__ . '/../core/PressingAware.php';

require_once __DIR__ . '/../core/NotificationService.php';

require_once __DIR__ . '/../core/BaseController.php';

require_once __DIR__ . '/../core/BaseModel.php';

require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../models/home/ModelHome.php';
require_once __DIR__ . '/../models/articles/ModelArticle.php';
require_once __DIR__ . '/../models/services/ModelService.php';
require_once __DIR__ . '/../models/clients/ModelClient.php';
require_once __DIR__ . '/../models/users/ModelUser.php';
require_once __DIR__ . '/../models/roles/ModelRole.php';
require_once __DIR__ . '/../models/permissions/ModelPermission.php';
require_once __DIR__ . '/../models/users_pressings/ModelUserPressing.php';
require_once __DIR__ . '/../models/commandes/ModelCommande.php';
require_once __DIR__ . '/../models/commande_details/ModelCommandeDetail.php';
require_once __DIR__ . '/../models/paiements/ModelPaiement.php';
require_once __DIR__ . '/../models/retraits/ModelRetrait.php';
require_once __DIR__ . '/../models/pressings/ModelPressing.php';
require_once __DIR__ . '/../models/livreurs/ModelLivreur.php';
require_once __DIR__ . '/../models/missions/ModelMission.php';
require_once __DIR__ . '/../models/horaires_pressings/ModelHorairePressing.php';
require_once __DIR__ . '/../models/categories_articles/ModelCategorieArticle.php';
require_once __DIR__ . '/../models/tarifs_articles/ModelTarifArticle.php';
require_once __DIR__ . '/../models/villes/ModelVille.php';
require_once __DIR__ . '/../models/quartiers/ModelQuartier.php';
require_once __DIR__ . '/../models/abonnements_pressings/ModelAbonnementPressing.php';
require_once __DIR__ . '/../models/forfaits/ModelForfait.php';
require_once __DIR__ . '/../models/favoris/ModelFavori.php';
require_once __DIR__ . '/../models/notifications/ModelNotification.php';
require_once __DIR__ . '/../models/paniers/ModelPanier.php';
require_once __DIR__ . '/../models/panier_details/ModelPanierDetail.php';

require_once __DIR__ . '/../controllers/home/HomeController.php';
require_once __DIR__ . '/../controllers/articles/ArticleController.php';
require_once __DIR__ . '/../controllers/services/ServiceController.php';
require_once __DIR__ . '/../controllers/pressings/PressingController.php';
require_once __DIR__ . '/../controllers/livreurs/LivreurController.php';
require_once __DIR__ . '/../controllers/missions/MissionController.php';
require_once __DIR__ . '/../controllers/horaires_pressings/HorairePressingController.php';
require_once __DIR__ . '/../controllers/categories_articles/CategorieArticleController.php';
require_once __DIR__ . '/../controllers/tarifs_articles/TarifArticleController.php';
require_once __DIR__ . '/../controllers/villes/VilleController.php';
require_once __DIR__ . '/../controllers/quartiers/QuartierController.php';
require_once __DIR__ . '/../controllers/abonnements_pressings/AbonnementPressingController.php';
require_once __DIR__ . '/../controllers/forfaits/ForfaitController.php';
require_once __DIR__ . '/../controllers/favoris/FavoriController.php';
require_once __DIR__ . '/../controllers/notifications/NotificationController.php';
require_once __DIR__ . '/../controllers/paniers/PanierController.php';
require_once __DIR__ . '/../controllers/panier_details/PanierDetailController.php';
require_once __DIR__ . '/../controllers/clients/ClientController.php';
require_once __DIR__ . '/../controllers/users/UserController.php';
require_once __DIR__ . '/../controllers/roles/RoleController.php';
require_once __DIR__ . '/../controllers/permissions/PermissionController.php';
require_once __DIR__ . '/../controllers/commandes/CommandeController.php';
require_once __DIR__ . '/../controllers/paiements/PaiementController.php';
require_once __DIR__ . '/../controllers/retraits/RetraitController.php';
require_once __DIR__ . '/../controllers/webhooks/GeniusPayWebhookController.php';
require_once __DIR__ . '/../models/settings/ModelSetting.php';
require_once __DIR__ . '/../controllers/settings/SettingController.php';
require_once __DIR__ . '/../models/promotions/ModelPromotion.php';
require_once __DIR__ . '/../controllers/promotions/PromotionController.php';

