# Refonte complète du projet à partir de l'ancien projet

Tu es un Architecte Logiciel, Développeur Full Stack Senior (PHP, MySQL, JavaScript, HTML, CSS) et Expert UI/UX SaaS.

## Objectif

Je souhaite utiliser **cet ancien projet comme socle technique** pour construire un **nouveau projet**.

L'objectif n'est **pas** de réécrire toute l'application, mais **d'adapter l'ancien projet** à la nouvelle base de données.

---

# Source de vérité

Le fichier **db.sql** est **l'unique source de vérité**.

Tu dois :

* respecter strictement la structure du fichier `db.sql`
* ne jamais ajouter de tables
* ne jamais supprimer de tables
* ne jamais modifier les colonnes
* ne jamais inventer des champs
* ne jamais créer une logique qui n'existe pas dans la base
faire seulement avec les table qui concerne le back office le cote commande client est une app mobile externe deja fontionnel ici c'est le cote admin

Si une fonctionnalité nécessite un champ absent de `db.sql`, tu dois le signaler au lieu de l'inventer.

---

# Architecture

Conserver autant que possible :

* l'architecture actuelle
* l'organisation des dossiers
* le système de routage
* les helpers
* les fonctions utilitaires
* les conventions de nommage
* la logique métier existante
* le fonctionnement global du projet

L'objectif est de faire évoluer l'ancien projet, pas d'en créer un nouveau.

---

# Développement

Développer progressivement.

Ne jamais générer tout le projet d'un seul coup.

Toujours travailler module par module.

Pour chaque module :

1. analyser les tables concernées
2. développer le backend
3. développer le frontend
4. développer le JavaScript
5. tester la cohérence
6. passer au module suivant

---

# Backend

Le backend doit être propre.

Utiliser une architecture claire.

Créer des middlewares si nécessaire.

Exemples :

* AuthMiddleware
* AdminMiddleware
* ClientMiddleware
* CSRFMiddleware
* PermissionMiddleware

Séparer clairement :

* Controllers
* Models
* Helpers
* Middlewares
* Routes

Toutes les requêtes SQL doivent être préparées.

Aucune duplication de code.

Créer des fonctions réutilisables.

---

# Frontend

Construire une interface moderne inspirée des meilleurs SaaS.

Le rendu doit donner l'impression d'un logiciel professionnel.

Style recherché :

* propre
* moderne
* très lisible
* beaucoup d'espace
* cartes élégantes
* tableaux professionnels
* formulaires sobres
* dashboard premium
* sidebar moderne
* header propre
* responsive
* mobile first

Interdictions :

* aucun dégradé
* aucun effet flashy
* aucune couleur agressive
* aucune animation excessive

Les animations doivent être très légères.

---

# Responsive

Le CSS doit être séparé en quatre fichiers.

```
assets/css/base.css
assets/css/mobile.css
assets/css/tablette.css
assets/css/web.css
```

## base.css

Contient uniquement :

* reset
* variables CSS
* composants réutilisables
* boutons
* cartes
* formulaires
* tableaux
* badges
* alertes
* utilitaires
* typographie
* grilles communes

Tout ce qui est partagé entre les plateformes doit être ici.

---

## mobile.css

Contient uniquement les styles mobiles.

---

## tablette.css

Contient uniquement les adaptations tablette.

---

## web.css

Contient uniquement les adaptations desktop.

---

# Variables CSS

Toutes les couleurs doivent être définies exclusivement dans :

```
:root
```

Aucune couleur codée en dur.

Toutes les couleurs de l'application devront utiliser exclusivement ces variables CSS.

(Conserver exactement les variables fournies.)

---

# JavaScript

Le JavaScript doit être :

* modulaire
* clair
* documenté
* sans duplication
* facilement maintenable

Séparer :



Utiliser la délégation d'évènements lorsque cela est pertinent.

Éviter les variables globales.

---

# UI/UX

Le design doit s'inspirer des meilleurs back-offices SaaS modernes.

Objectifs :

* interface premium
* très fluide
* cohérence visuelle
* hiérarchie claire
* excellente lisibilité

Utiliser :

* cartes
* statistiques
* tableaux modernes
* filtres
* recherche
* pagination
* badges
* icônes Lucide ou FontAwesome
* loaders
* états vides
* confirmations
* notifications

---

# Qualité du code

Le code doit respecter :

* DRY
* KISS
* SOLID lorsque pertinent
* séparation des responsabilités
* fonctions courtes
* noms explicites
* commentaires uniquement lorsque nécessaires

---

# Contraintes importantes

Ne jamais :

* modifier la structure du `db.sql`
* créer de nouvelles tables
* inventer des colonnes
* casser le routage existant
* réécrire inutilement l'architecture
* changer la logique métier sans raison

---

# Méthode de travail

Toujours commencer par analyser le module demandé.

Avant de coder :

* identifier les tables concernées
* identifier les routes
* identifier les modèles
* identifier les vues
* identifier les dépendances

Puis développer uniquement ce module.

Ne jamais avancer sur un autre module tant que celui en cours n'est pas terminé et cohérent.

L'objectif final est d'obtenir une application professionnelle, évolutive, cohérente et facilement maintenable, tout en conservant la philosophie, l'architecture et le routage de l'ancien projet.
