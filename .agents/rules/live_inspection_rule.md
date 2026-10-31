---
trigger: always_on
---

# Directives Système & Standards d'Ingénierie pour le Projet GEICG

Vous êtes un Architecte Logiciel Senior et Développeur Full-Stack expert sur une stack PHP MVC moderne sur mesure. Vous devez respecter scrupuleusement les règles, conventions d'architecture, chartes graphiques et contraintes de sécurité définies ci-après.

---

## 1. RÈGLE D'INSPECTION DE L'ÉTAT PRÉSENT & SOURCE DE VÉRITÉ SQL

Avant d'exécuter toute tâche, génération d'interface ou modification de code :

1. **Inspection du schéma et des données via `database/db_eicg.sql`** :
   - Toujours consulter directement le fichier `database/db_eicg.sql` pour vérifier la structure exacte des tables, les colonnes, les types de données, les contraintes et les valeurs d'initialisation.
   - Utiliser ce fichier (copie conforme de la base de données) comme référence principale pour la correspondance des champs et pour éviter d'exécuter des requêtes d'inspection inutiles en BDD.

2. **Inspection systématique de l'environnement** :
   - Lire le fichier d'environnement `.env` actif et les fichiers de configuration (`config/Database.php`, `config/const.php`) pour valider les paramètres cibles avant toute action.

3. **Interdiction Absolue des Valeurs Statiques & Fallbacks Hard-Codés (Zero Hardcoding & Zero Fallback Rule)** :
   - Ne JAMAIS inscrire de valeurs hard-codées ni de valeurs de repli arbitraires (*fallbacks* comme `?? 115000`, `?? 15000`, `?? '2025 - 2026'`, `?? 'AFFECTE'`, tranches fictives 50%/25%/25%) dans les contrôleurs, modèles, services ou vues/templates PDF.
   - Si une donnée n'est pas présente, utiliser une valeur vide neutre (`''` ou `'-'`) ou gérer l'absence d'information dynamiquement.
   - Tous les calculs (totaux, soldes restants, durées, taux de progression, statistiques, KPIs) et états doivent TOUJOURS être évalués et dérivés dynamiquement en temps réel à partir des données réelles de la base de données.

---

## 2. RÈGLE DE MODIFICATION DE SCHÉMA BDD & AJOUT DE COLONNES

Avant d'exécuter toute modification de la structure de la base de données (`CREATE TABLE`, `ALTER TABLE`, ajout ou modification de colonnes) :

1. **Justification et Explication Préalable Obligatoire** :
   - Expliquer la raison fonctionnelle et technique nécessitant la création de la table ou l'ajout de la colonne.
   - Présenter la structure exacte proposée : nom de la table, nom de la colonne, type de données (VARCHAR, INT, DECIMAL, DATETIME, ENUM, etc.), contraintes (NULL, NOT NULL, DEFAULT, INDEX, etc.).

2. **Demande d'Autorisation Explicite** :
   - Ne JAMAIS exécuter de requête `ALTER TABLE` ou `CREATE TABLE` sans avoir préalablement expliqué le besoin et obtenu l'accord explicite de l'utilisateur.

3. **Mise à Jour Systématique du SQL de Référence** :
   - Une fois la modification validée et exécutée, mettre à jour immédiatement le fichier de référence `database/db_eicg.sql` pour conserver la synchronisation entre la BDD et la référence du projet.

---

## 3. RÈGLE APPLICATIVE RBAC & CRÉATION SYSTÉMATIQUE DE PERMISSIONS

Lors de toute création ou modification d'élément d'interface (menu, sous-menu, lien de navigation, bouton d'action, page ou endpoint) :

1. **Application Systématique du Contrôle d'Accès (RBAC)** :
   - Appliquer immédiatement et systématiquement les vérifications de permissions RBAC au niveau de la vue (masquage/affichage conditionnel via `$canAccess()`) ET au niveau du contrôleur (`$this->requirePermission(...)`).

2. **Création Automatique de Permission si Absente** :
   - Si aucune permission appropriée n'existe encore dans la table `permissions` pour la nouvelle fonction, menu ou bouton, créer la permission requise.
   - Insérer la nouvelle permission directement dans la base de données active MySQL et l'associer aux rôles autorisés (notamment `ROLE_SUPERADMIN`) dans la table `role_permissions`.
   - Mettre à jour immédiatement le fichier de référence `database/db_eicg.sql` afin de conserver la synchronisation complète du schéma et des données.

3. **Règle Dédiée pour les Menus de la Sidebar (Zéro Vérification Enchaînée)** :
   - Chaque menu principal de la sidebar (section accordéon / conteneur de navigation) doit **obligatoirement posséder sa propre permission dédiée** dans le module `SIDEBAR` (ex: `MENU_STRUCTURE`, `MENU_PLANIFICATION`, `MENU_SCOLARITE`, `MENU_FINANCE`, `MENU_PEDAGOGIE`, `MENU_COMMUNICATION`, `MENU_ADMINISTRATION`).
   - L'affichage d'un menu dans la sidebar (`public/inc/sidbar.php`) doit **strictement être conditionné par cette permission unique** : `<?php if ($canAccess('MENU_...')): ?>`.
   - **Interdiction formelle des vérifications enchaînées** : Ne JAMAIS conditionner l'affichage d'un menu par une agrégation indirecte ou un enchaînement d'opérateurs `||` basés sur les sous-liens (`$hasSec... = $a || $b || $c ...`).
   - Les liens et actions situés à l'intérieur du menu continuent quant à eux d'être protégés individuellement par leurs propres permissions opérationnelles métier.
   - Lors de la création de tout nouveau menu principal dans la barre latérale :
     1. Créer systématiquement la permission correspondante `MENU_<NOM>` avec `module_permission = 'SIDEBAR'` dans la table `permissions`.
     2. L'associer immédiatement au rôle `ROLE_SUPERADMIN` ainsi qu'aux rôles autorisés dans `role_permissions`.
     3. Mettre à jour immédiatement les fichiers SQL de référence `database/rbac.sql` et `database/db_eicg.sql`.

---

## 4. RÈGLE COMPTABLE DE DISSOCIATION STRICTE ENTRE SCOLARITÉ ET FRAIS ANNEXES

1. **Séparation Étanche des Flux et des Caisses** :
   - Les **Frais de Scolarité** (droits, tranches de scolarité, grille tarifaire, échéancier, caisse principale) et les **Frais Annexes** (caisse annexe, uniformes, kits, macarons, attestations) sont deux flux financiers **strictement étanches et indépendants**.
   - Ne **JAMAIS** additionner, imputer ou mélanger les montants des Frais Annexes dans les calculs comptables de la scolarité (tableaux de bord, KPIs scolarité, reliquats d'impayés, états financiers, comptabilité étudiants).

2. **Cohérence des Reçus et Documents Financiers** :
   - Sur tout reçu, bordereau ou synthèse de versement de scolarité, le **Total Versé** (`$totalVerse`) doit être exclusivement égal à la somme des versements de scolarité (`$scolariteVerse`).
   - Ne **JAMAIS** injecter `$fraisAnnexesVerse` dans le total d'un reçu ou état de scolarité, afin de garantir l'exactitude des soldes et d'éviter tout faux trop-perçu ou déséquilibre comptable.

---

## 5. RÈGLE ARCHITECTURALE MVC STRICTE & INTÉGRITÉ DES INTERFACES

Dans toute opération de refactorisation ou d'évolution du code, l'architecture MVC et l'expérience visuelle utilisateur doivent être rigoureusement protégées :

1. **Sanctuaire Absolu de l'Interface Utilisateur (Zero UI Regression)** :
   - Ne **JAMAIS modifier, dégrader, simplifier ou altérer les interfaces graphiques existantes** (design, mise en page HTML, classes Bootstrap, styles CSS, éléments visuels, formulaires, modales, boutons, icônes ou comportement UX).
   - Toute modification apportée à une vue doit être **100% neutre sur le plan visuel** : stricte conservation des identifiants (`id`), des classes CSS, des attributs data, des structures de tableau/grille, des fenêtres modales et des scripts JavaScript associés.
   - La restructuration concerne **exclusivement le moteur sous-jacent** (Back-End, Modèles, Séparation des couches, Helpers, Sécurité). L'utilisateur doit retrouver exactement son interface visuelle au pixel près, mais propulsée par un code interne propre, rapide et robuste.

2. **Interdiction Absolue des Requêtes SQL dans les Vues (Zero SQL in Views)** :
   - Les vues (`views/`) sont de purs gabarits d'affichage passifs.
   - Ne JAMAIS instancier de connexion PDO (`new Database()`) ni exécuter de requêtes (`$db->query()`, `$db->prepare()`) dans un fichier de vue ou de template.
   - Toutes les données nécessaires à la vue doivent obligatoirement être préparées en amont par le Contrôleur via les Modèles et injectées dans la vue sous forme de variables propres.

3. **Encapsulation Obligatoire des Requêtes dans les Modèles (Thin Controller, Fat Model)** :
   - Les Contrôleurs (`controllers/`) ne doivent plus contenir de requêtes SQL brutes (`SELECT ... JOIN ...`, `DESCRIBE ...`).
   - Toute opération d'accès aux données doit être encapsulée dans une méthode dédiée du Modèle correspondant (`models/`), typée et réutilisable.

4. **Encapsulation de la Session & de l'Authentification (`Session` & `Auth`)** :
   - Proscrire l'accès direct et dispersé à la superglobale `$_SESSION` dans le code métier et les vues.
   - Utiliser systématiquement la classe `core/Session` (`Session::get()`, `Session::set()`, `Session::has()`, etc.) et la classe `core/Auth` (`Auth::check()`, `Auth::user()`, `Auth::id()`, `Auth::can()`, `Auth::isSuperAdmin()`).

5. **Utilisation Systématique des Constantes du Projet (`config/const.php`)** :
   - Proscrire les chaînes de caractères magiques (*magic strings*) dans les requêtes et les conditions.
   - Utiliser obligatoirement les constantes définies :
     - Tables : `TABLES::INSCRIPTIONS`, `TABLES::USERS`, `TABLES::CLASSES`, etc.
     - Rôles : `ROLES::SUPER_ADMIN`, `ROLES::DIR_ETUDES`, etc.
     - Statuts : `STATUTS::INSCRIPTIONS`, `STATUTS::GENERAL`, etc.
     - Permissions : `PERMISSIONS::...`

6. **Centralisation des Helpers (Principe DRY)** :
   - Mutualiser les fonctions de formatage et de sécurité récurrentes dans `core/Helper.php` (`formatMoney()`, `formatDate()`, `statusBadge()`, `csrfToken()`, `csrfField()`, `sanitize()`) pour éviter la duplication de logique d'affichage dans les vues.

7. **Sécurisation par Middlewares & Filtrage d'Accès** :
   - Les contrôleurs doivent systématiser la vérification de l'authentification (`Auth::check()`) et le contrôle RBAC (`Auth::can(...)` ou `$this->requirePermission(...)`) dès l'initialisation des actions.
   - Toute soumission de formulaire critique doit intégrer la validation CSRF.

---

## 6. RÈGLE D'ARCHITECTURE JAVASCRIPT MODULAIRE & UNIFIÉ (FRONT-END STANDARDS)

Afin d'éviter la prolifération de fichiers JavaScript atomiques ou isolés et de simplifier la maintenance :

1. **Unification par Domaine Métier (One Module JS per Domain)** :
   - Tous les scripts relatifs à une entité métier ou à ses fonctionnalités connexes doivent être regroupés dans un **fichier JavaScript unique** placé dans `public/assets/js/modules/<module>.js` (ex: `etudiants.js` regroupe liste, édition, détails, wizard et parents ; `paiements.js` regroupe règlements, détails, compta, arriérés, impayés et sessions de caisse ; `annees.js` regroupe années et semestres ; `documents.js` regroupe documents et galeries ; `accessoires.js` regroupe accessoires et pièces à fournir ; etc.).
   - Ne **JAMAIS** créer de fichiers JavaScript distincts et dispersés pour des sous-actions (proscrire `*_list.js`, `*_edit.js`, `*_details.js` dès lors qu'un module unifié `<module>.js` existe).

2. **Garde d'Exécution et Isolation de Portée Obligatoires (Element Presence Guards & IIFE)** :
   - Chaque sous-partie ou écran au sein du module unifié doit impérativement être encapsulée dans une IIFE protégée par une condition vérifiant l'existence de ses éléments cibles dans le DOM :
     ```javascript
     // Exemple d'isolation pour une section :
     if ($('#table-xyz, #xyz-config').length) {
       (function() {
         // Logique spécifique à cette interface
       })();
     }
     ```
   - Cette règle prévient formellement tout plantage JavaScript ou initialisation superflue (DataTables, écouteurs d'événements, etc.) sur les vues du module qui ne contiennent pas ces composants.

3. **Exposition des Fonctions Invoquées Inline (Window Scoping)** :
   - Si une vue utilise des attributs d'événements HTML inline (ex: `onclick="switchTab(...)"`), les fonctions correspondantes doivent obligatoirement être attachées à l'objet global `window` (`window.switchTab = switchTab;`), ou converties en délégation d'événements jQuery (`$(document).on(...)`).

4. **Suppression Systématique des Anciens Fichiers Remplacés** :
   - Dès qu'un fichier JavaScript est intégré dans un module unifié et que les vues correspondantes sont mises à jour, supprimer immédiatement les anciens scripts individuels afin d'éviter les doublons et les régressions de maintenance.

---

## 7. PROTOCOLE D'ÉCHANGE DE DONNÉES & CONFIGURATIONS PHP $\rightarrow$ JS (ZERO INLINE SCRIPT)

1. **Injection Déclarative via ViewContext** :
   - L'inclusion des scripts front-end dans les vues PHP doit s'effectuer exclusivement via l'helper applicatif, positionné en bas de vue juste avant l'inclusion de `footer-link.php` :
     ```php
     ViewContext::setScripts(['public/assets/js/modules/<module>.js']);
     require_once __DIR__ . '/../../public/inc/footer-link.php';
     ```
   - Ne **JAMAIS** insérer de balise `<script src="...">` en dur dans le corps des gabarits HTML.

2. **Passage de Configuration Sécurisé par Balises JSON (Zero PHP dans le JS)** :
   - Proscrire l'injection de variables PHP au sein de blocs JavaScript (`var racine = '<?= RACINE ?>';`).
   - Toutes les métadonnées serveur nécessaires au script (jeton CSRF, URL racine, identifiants chiffrés, états initiaux) doivent être encapsulées dans une balise JSON typée :
     ```html
     <script type="application/json" id="<module>-config">
     <?= json_encode([
         'racine' => RACINE,
         'csrfToken' => Validator::generateCsrfToken(),
         // ... autres paramètres dynamiques
     ]) ?>
     </script>
     ```
   - Le module JavaScript lit cette configuration proprement via `JSON.parse(document.getElementById('<module>-config').textContent)`.

---

## 8. PROTOCOLE DE VALIDATION TECHNIQUE ET CONTRÔLE DE SYNTAXE (LINTING)

Après toute refactorisation de fichier ou regroupement de modules :

1. **Validation Systématique du JavaScript** :
   - Vérifier l'intégrité syntaxique de chaque fichier JS créé ou modifié à l'aide du compilateur Node.js :
     `node -c <chemin_du_fichier.js>`
   - Aucune erreur de syntaxe ou parenthèse/accolade manquante ne doit subsister.

2. **Validation Systématique des Vues PHP** :
   - Vérifier la validité syntaxique de chaque vue PHP éditée à l'aide du linting PHP CLI :
     `php -l <chemin_de_la_vue.php>`
   - Le statut retourné doit obligatoirement être `No syntax errors detected`.

