# Plan d'Implémentation — Nouveau Projet KITS (db_kits)

## 🗄️ Base de Données (sql/db.sql)

La DB contient **11 tables** pour un système de gestion de kits/campagnes :

| Table | Rôle métier |
|---|---|
| `articles` | Articles du catalogue |
| `campagnes` | Campagnes commerciales |
| `clients` | Clients |
| `users` | Utilisateurs du système (admin, commercial, caissier) |
| `kits` | Kits (ensembles d'articles) liés à une campagne |
| `commandes` | Commandes clients |
| `ligne_commandes` | Lignes d'une commande (chaque ligne = 1 kit) |
| `paiements` | Paiements des commandes |
| `retrait_kits` | Retraits physiques de kits |
| `session_caisses` | Sessions de caisse |
| `composition_kits` | Composition d'un kit (articles + quantités) |

## 📁 Structure MVC (inchangée)

```
/config
  const.php        ← TABLES + STATUTS adaptés à la nouvelle DB
  Database.php     ← dbname = 'db_kits'
/core
  BaseController.php, BaseModel.php, Router.php, PrincipalRoute.php
/models/
  articles/ModelArticle.php
  campagnes/ModelCampagne.php
  clients/ModelClient.php
  users/ModelUser.php
  kits/ModelKit.php
  commandes/ModelCommande.php
  ligne_commandes/ModelLigneCommande.php
  paiements/ModelPaiement.php
  retrait_kits/ModelRetraitKit.php
  session_caisses/ModelSessionCaisse.php
  composition_kits/ModelCompositionKit.php
  Validator.php    ← inchangé
/controllers/
  home/HomeController.php
  articles/ArticleController.php
  campagnes/CampagneController.php
  clients/ClientController.php
  users/UserController.php
  kits/KitController.php
  commandes/CommandeController.php
  paiements/PaiementController.php
  retrait_kits/RetraitKitController.php
  session_caisses/SessionCaisseController.php
/views/
  home/index.php
  articles/{list,details,edit}.php
  campagnes/{list,details,edit}.php
  clients/{list,details,edit}.php
  users/{list,details,edit}.php
  kits/{list,details,edit}.php
  commandes/{list,details,edit}.php
  paiements/{list,details,edit}.php
  retrait_kits/{list,details,edit}.php
  session_caisses/{list,details,edit}.php
/public/json/entities/
  articles.js, campagnes.js, clients.js, users.js, kits.js,
  commandes.js, paiements.js, retrait_kits.js, session_caisses.js
/public/inc/
  header.php, footer.php, nav.php, sidbar.php (adapté)
```

## 🔗 Pattern CRUD (100% conforme au template)

Chaque entité expose ces méthodes controller :
- `list()` → vue list
- `apiList()` → JSON DataTable
- `add()` → POST création
- `edit()` → POST modification
- `changer()` → POST toggle statut
- `details($param)` → vue détail
- `edition($param)` → vue édition
- `getActive()` → JSON options (si applicable)

## ⚙️ Modifications de l'architecture existante

| Fichier | Action |
|---|---|
| `config/const.php` | UPDATE — nouvelles tables TABLES + nouvelles statuts STATUTS |
| `config/Database.php` | UPDATE — dbname `db_kits` |
| `core/PrincipalRoute.php` | REWRITE — nouveau chargement models/controllers + nouvelles routes |
| `public/index.php` | REWRITE — nouveaux controllers + nouvelles routes |
| `public/inc/sidbar.php` | UPDATE — menu adapté aux nouvelles entités |

## 🔀 Sidebar (nouveau menu)

- Tableau de bord
- Gestion → Utilisateurs, Clients
- Campagnes
- Catalogue → Articles, Kits
- Commercial → Commandes, Paiements, Retraits kits
- Caisse → Sessions caisse

## ✅ Livrables

- Projet immédiatement exécutable
- Architecture MVC strictement identique au template
- Toutes les tables sql/db.sql couvertes par un model
- 10 controllers opérationnels
- Views cohérentes avec le design existant
- Routes fonctionnelles
- Sidebar adaptée
