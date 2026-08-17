# Mission — Audit, structuration et finalisation du backoffice Lavex

Tu interviens comme **architecte logiciel senior et développeur full-stack expérimenté**, avec une approche rigoureuse orientée production, sécurité, maintenabilité et évolutivité.

## 1. Contexte du projet

Ce projet correspond au **backoffice d'une marketplace de services de pressing appelée Lavex**.

La plateforme est séparée en deux parties :

* **Application cliente** : les clients recherchent les pressings, consultent les services et passent leurs commandes. Cette partie est développée sur une autre plateforme.
* **Backoffice Lavex** : c'est le projet sur lequel tu dois travailler actuellement. Il permet aux pressings et aux administrateurs de gérer toute l'activité de la marketplace.

Le client **ne passe donc pas directement sa commande depuis ce backoffice**. Les commandes sont créées depuis la plateforme cliente puis doivent être correctement réceptionnées, traitées et suivies dans ce backoffice.

---

# 2. Règle fondamentale : `db.sql` est la seule source de vérité

Le fichier :

`db.sql`

est **la seule source de vérité concernant la structure et les données prévues de la base de données**.

Tu dois impérativement :

* analyser entièrement `db.sql` avant toute modification ;
* identifier les tables, colonnes, clés primaires, clés étrangères, contraintes, ENUM, relations et statuts existants ;
* comprendre les relations entre les différentes entités ;
* adapter le code existant à cette structure ;
* utiliser les noms réels des tables et colonnes ;
* ne pas inventer de tables ;
* ne pas inventer de colonnes ;
* ne pas modifier arbitrairement la structure SQL ;
* ne pas supprimer des éléments existants ;
* ne pas ajouter de données ou de fonctionnalités qui ne peuvent pas être justifiées par le schéma existant.

**Si une fonctionnalité semble nécessaire mais que la base actuelle ne permet pas de la gérer, signale-le avant de modifier quoi que ce soit.**

Ne suppose jamais qu'une colonne ou une table existe.

---

# 3. Objectif principal

Ton objectif est de transformer le projet actuel en un **backoffice propre, cohérent, sécurisé et réellement exploitable pour une marketplace de pressing**.

Avant de développer, tu dois effectuer un **audit complet du projet existant**.

Analyse notamment :

* architecture ;
* routing ;
* controllers ;
* models ;
* services ;
* API ;
* vues/pages ;
* JavaScript ;
* CSS ;
* authentification ;
* gestion des sessions ;
* rôles ;
* permissions ;
* gestion des commandes ;
* gestion des pressings ;
* gestion des utilisateurs ;
* gestion des services ;
* gestion des statuts ;
* accès aux données ;
* validations côté serveur ;
* gestion des erreurs ;
* sécurité.

---

# 4. Modules fonctionnels attendus

## A. Espace Pressing

Chaque pressing doit pouvoir accéder uniquement aux fonctionnalités autorisées par son rôle et ses permissions.

### 1. Profil du pressing

Prévoir/vérifier :

* consultation du profil ;
* modification des informations autorisées ;
* informations de contact ;
* informations nécessaires à l'activité ;
* statut du pressing ;
* informations opérationnelles disponibles dans `db.sql`.

Ne crée aucun champ qui n'existe pas dans la base.

### 2. Gestion des services

Le pressing doit pouvoir :

* consulter ses services ;
* ajouter un service si le schéma le permet ;
* modifier un service ;
* activer/désactiver un service si le modèle de données le permet ;
* gérer les informations liées au tarif ;
* consulter les services associés à son pressing.

Respecter strictement les relations présentes dans `db.sql`.

### 3. Réception des commandes

Le pressing doit pouvoir consulter les commandes qui lui sont destinées.

Prévoir/vérifier :

* liste des commandes ;
* détail d'une commande ;
* informations du client nécessaires au traitement ;
* articles commandés ;
* quantités ;
* services ;
* montant ;
* adresse/informations de collecte si présentes ;
* date ;
* statut ;
* informations de livraison disponibles.

Le pressing ne doit **jamais pouvoir consulter ou modifier les commandes d'un autre pressing**.

### 4. Gestion du traitement des commandes

Mettre en place une gestion claire des différentes étapes de traitement prévues par le système.

Exemple de logique à vérifier par rapport à `db.sql` :

`Commande reçue → Collecte → En traitement → Prête → Livraison → Livrée`

**Ne crée pas ces statuts s'ils n'existent pas dans la base.**

Utilise les statuts réellement prévus par `db.sql`.

Les transitions doivent être contrôlées côté serveur.

Un pressing ne doit pas pouvoir effectuer une transition interdite simplement en envoyant une requête HTTP manuellement.

### 5. Suivi des collectes

Le pressing doit pouvoir suivre les informations de collecte disponibles dans le système :

* commandes concernées ;
* statut de collecte ;
* date/heure si disponible ;
* informations nécessaires au livreur ;
* historique des changements si prévu par la base.

### 6. Suivi des livraisons

Permettre au pressing de consulter l'état des livraisons associées à ses commandes.

Respecter strictement les données réellement disponibles.

### 7. Historique

Créer/vérifier un historique permettant au pressing de retrouver ses anciennes commandes.

Prévoir si le schéma le permet :

* recherche ;
* filtrage par statut ;
* filtrage par période ;
* recherche par référence ;
* consultation du détail.

---

# 5. Espace Administration

L'administration doit avoir une vision globale de la marketplace.

## 1. Gestion des utilisateurs

L'administrateur doit pouvoir, selon ses permissions :

* consulter les utilisateurs ;
* consulter leur profil ;
* créer un utilisateur si prévu ;
* modifier un utilisateur ;
* activer/désactiver un compte si prévu ;
* gérer les rôles ;
* gérer les permissions.

Attention : **ne donne jamais automatiquement tous les droits à un utilisateur simplement parce qu'il est connecté.**

---

## 2. Gestion des pressings

L'administration doit pouvoir :

* consulter les pressings ;
* rechercher un pressing ;
* consulter le détail ;
* modifier les informations autorisées ;
* gérer leur statut ;
* consulter leurs services ;
* consulter leurs commandes ;
* superviser leur activité.

Un administrateur ne doit pouvoir modifier que ce que ses permissions autorisent.

---

## 3. Gestion des commandes

L'administration doit avoir une vue globale des commandes de la marketplace.

Prévoir/vérifier :

* liste globale ;
* recherche ;
* filtres ;
* détail ;
* pressing concerné ;
* client concerné ;
* services/articles ;
* montant ;
* statut ;
* collecte ;
* livraison ;
* historique.

L'administration doit pouvoir superviser les commandes sans casser les règles métier.

---

## 4. Gestion des services

L'administration doit pouvoir superviser les services disponibles sur la marketplace.

Selon ce que permet `db.sql` :

* consultation ;
* création ;
* modification ;
* activation/désactivation ;
* association avec les pressings ;
* gestion des tarifs.

---

## 5. Supervision de la plateforme

Créer/vérifier un dashboard permettant d'avoir une vision synthétique de l'activité.

Les indicateurs doivent être **calculés à partir des données réellement présentes dans la base**.

Exemples à vérifier selon le schéma :

* nombre de commandes ;
* commandes en attente ;
* commandes en traitement ;
* commandes livrées ;
* nombre de pressings ;
* nombre d'utilisateurs ;
* activité récente.

Ne crée pas de KPI artificiels.

---

# 6. Rôles et permissions — point critique

La gestion des rôles et permissions doit être traitée comme une **fonctionnalité de sécurité**, pas simplement comme un élément d'interface.

Tu dois commencer par identifier dans `db.sql` :

* les tables utilisateurs ;
* les tables rôles ;
* les tables permissions ;
* les tables de liaison ;
* les statuts ;
* les relations entre utilisateur, pressing, rôle et permissions.

Ensuite, vérifie que chaque module possède un contrôle d'accès cohérent.

Le contrôle doit être effectué :

### Frontend

Pour :

* masquer les menus non autorisés ;
* masquer les boutons non autorisés ;
* empêcher les actions inutiles.

### Backend

**Obligatoirement.**

Chaque endpoint sensible doit vérifier :

1. utilisateur authentifié ;
2. session valide ;
3. rôle ;
4. permission ;
5. ressource concernée ;
6. appartenance au pressing lorsque nécessaire.

Ne considère jamais que cacher un bouton constitue une sécurité.

Un utilisateur ne disposant pas d'une permission doit recevoir un refus côté serveur même s'il appelle directement l'URL ou l'API.

---

# 7. Isolation des données entre pressings

C'est une règle métier fondamentale.

Un utilisateur appartenant au pressing A ne doit jamais pouvoir :

* voir les commandes du pressing B ;
* modifier les services du pressing B ;
* modifier les informations du pressing B ;
* accéder aux données internes du pressing B ;
* récupérer les données du pressing B en modifiant simplement un `id` dans l'URL ou une requête AJAX.

Chaque requête doit appliquer les restrictions d'accès au niveau serveur.

Exemple conceptuel :

Ne fais pas simplement :

`SELECT * FROM commandes WHERE id = ?`

mais vérifie également que la commande appartient bien au pressing de l'utilisateur connecté lorsque cela est nécessaire.

---

# 8. Architecture du projet

Avant de modifier le code, analyse l'architecture existante.

Identifie clairement :

* routes ;
* controllers ;
* models ;
* services ;
* repositories s'ils existent ;
* middlewares ;
* authentification ;
* autorisation ;
* API ;
* vues ;
* JavaScript ;
* CSS.

Ne réécris pas inutilement le projet.

**Conserve la logique existante lorsqu'elle est correcte.**

Améliore uniquement ce qui doit réellement l'être.

L'objectif n'est pas de refaire le projet pour refaire le projet, mais de le rendre cohérent et robuste.

---

# 9. API et communication avec l'application cliente

L'application cliente étant séparée, le backoffice doit être capable de recevoir et gérer correctement les données issues de la plateforme cliente.

Analyse les endpoints existants et vérifie :
mais tu n'a pas a cree une api client ici car tout est gerer dans le cote mobile
* création des commandes ;
* récupération des commandes ;
* changement de statut ;
* récupération des services ;
* informations des pressings ;
* authentification ;
* autorisation ;
* validation des données ;
* réponses JSON ;
* codes HTTP ;
* gestion des erreurs.

Ne crée pas une nouvelle API si une API existante répond déjà correctement au besoin.

---

# 10. Sécurité

Effectue un audit de sécurité complet.

Vérifie notamment :

* authentification ;
* autorisation ;
* contrôle des rôles ;
* contrôle des permissions ;
* sessions ;
* régénération de session après connexion ;
* CSRF pour les requêtes concernées ;
* validation des entrées ;
* requêtes préparées ;
* protection SQL Injection ;
* XSS ;
* IDOR ;
* contrôle d'accès horizontal ;
* contrôle d'accès vertical ;
* upload de fichiers si présent ;
* gestion des erreurs ;
* exposition des informations sensibles ;
* headers de sécurité ;
* sécurisation des endpoints API.

Ne jamais faire confiance aux données provenant du frontend.

---

# 11. UX / interface

Le backoffice doit être professionnel et cohérent.

Chaque module doit avoir :

* une page claire ;
* un titre ;
* une navigation cohérente ;
* des états de chargement ;
* des états vides ;
* des messages d'erreur ;
* des confirmations pour les actions sensibles ;
* des notifications de succès/échec ;
* une gestion responsive correcte.

Ne modifie pas inutilement les textes métier existants.

Si un écran existe déjà et fonctionne correctement, conserve sa logique et améliore uniquement ce qui est nécessaire.

---

# 12. Méthode de travail obligatoire — STEP BY STEP

**Ne développe pas tout le projet en une seule fois.**

Travaille par étapes.

### ÉTAPE 1 — Audit

Analyse :

* `db.sql` ;
* architecture ;
* routes ;
* modules existants ;
* rôles ;
* permissions ;
* endpoints ;
* pages ;
* relations entre les données.

À cette étape, **ne modifie pas encore le code**.

Présente-moi :

* ce qui existe ;
* ce qui fonctionne ;
* ce qui est incomplet ;
* ce qui est incorrect ;
* ce qui est dangereux ;
* ce qui manque ;
* les incohérences entre code et base de données.

### ÉTAPE 2 — Cartographie

Construis mentalement puis dans ton plan une cartographie :

`Rôle → Permission → Module → Action → Endpoint → Ressource`

et :

`Utilisateur → Pressing → Services → Commandes → Collecte → Traitement → Livraison`

Tout doit être cohérent avec `db.sql`.

### ÉTAPE 3 — Authentification / autorisation

Corrige d'abord :

* authentification ;
* sessions ;
* rôles ;
* permissions ;
* middleware ;
* isolation des données.

Ne commence pas par le design.

### ÉTAPE 4 — Modules Pressing

Travaille ensuite dans cet ordre :

1. Profil pressing
2. Services
3. Commandes
4. Traitement
5. Collectes
6. Livraisons
7. Historique

### ÉTAPE 5 — Modules Administration

Ensuite :

1. Utilisateurs
2. Pressings
3. Commandes
4. Services
5. Supervision/dashboard

### ÉTAPE 6 — API

Vérifie et sécurise les endpoints permettant la communication avec l'application cliente.

### ÉTAPE 7 — UX/UI

Une fois la logique métier stable :

* corriger les interfaces ;
* uniformiser les composants ;
* corriger les états loading/empty/error ;
* responsive ;
* navigation ;
* feedback utilisateur.

### ÉTAPE 8 — Tests

Pour chaque module, teste :

* utilisateur non connecté ;
* utilisateur connecté sans permission ;
* utilisateur avec permission ;
* pressing A essayant d'accéder aux données du pressing B ;
* administrateur ;
* identifiants inexistants ;
* requêtes malformées ;
* manipulation des IDs ;
* accès direct aux endpoints ;
* changement de statut interdit ;
* données invalides.

---

# 13. Règles strictes de développement

Tu dois respecter les règles suivantes :

* `db.sql` = source de vérité ;
* ne pas inventer de structure SQL ;
* ne pas inventer de fonctionnalités métier ;
* ne pas supprimer une fonctionnalité existante sans justification ;
* ne pas contourner les règles métier ;
* ne pas mettre la sécurité uniquement dans JavaScript ;
* ne jamais faire confiance aux IDs envoyés par le frontend ;
* ne jamais utiliser le rôle comme unique mécanisme de sécurité si le système possède des permissions ;
* ne jamais permettre à un pressing d'accéder aux données d'un autre pressing ;
* privilégier les requêtes préparées ;
* respecter l'architecture existante lorsqu'elle est saine ;
* éviter les duplications de logique ;
* centraliser les contrôles d'autorisation ;
* garder un code lisible et maintenable ;
* ne pas faire de modifications massives sans vérifier leurs impacts.

---

# 14. Principe d'exécution

Pour chaque étape :

1. analyse ;
2. identification des problèmes ;
3. plan de correction ;
4. modification ;
5. vérification ;
6. test ;
7. passage au module suivant.

**Ne passe jamais à l'étape suivante si l'étape actuelle introduit des régressions ou si les règles d'accès ne sont pas correctement sécurisées.**

À chaque modification importante, explique brièvement :

* ce qui a été modifié ;
* pourquoi ;
* quels fichiers sont concernés ;
* quelle règle métier est appliquée ;
* comment l'accès est sécurisé ;
* comment le changement a été vérifié.

## Résultat attendu

À la fin, le projet doit être un **véritable backoffice de marketplace de pressing**, séparé de l'application cliente, avec :

* une architecture claire ;
* des modules bien séparés ;
* une gestion correcte des rôles ;
* une gestion correcte des permissions ;
* une isolation stricte des données entre pressings ;
* une gestion complète du cycle de commande ;
* une administration globale ;
* des API sécurisées ;
* une interface professionnelle ;
* une base de code maintenable ;
* aucune incohérence entre le code et `db.sql`.

**Commence obligatoirement par l'ÉTAPE 1 : audit complet du projet et de `db.sql`. Ne modifie aucun code avant d'avoir terminé cet audit.**
