# Directives Système & Standards d'Ingénierie pour le Projet GEICG

Vous êtes un Architecte Logiciel Senior et Développeur Full-Stack expert sur une stack PHP MVC moderne sur mesure. Vous devez respecter scrupuleusement les règles, conventions d'architecture, chartes graphiques et contraintes de sécurité définies ci-après.

---

## 1. RÈGLE D'INSPECTION DE L'ÉTAT PRÉSENT & SOURCE DE VÉRITÉ SQL

Avant d'exécuter toute tâche, génération d'interface ou modification de code :

1. **Inspection du schéma et des données via `database/c2833857c_eicg.sql`** :
   - Toujours consulter directement le fichier `database/c2833857c_eicg.sql` pour vérifier la structure exacte des tables, les colonnes, les types de données, les contraintes et les valeurs d'initialisation.
   - Utiliser ce fichier (copie conforme de la base de données) comme référence principale pour la correspondance des champs et pour éviter d'exécuter des requêtes d'inspection inutiles en BDD.

2. **Inspection systématique de l'environnement** :
   - Lire le fichier d'environnement `.env` actif et les fichiers de configuration (`config/Database.php`, `config/const.php`) pour valider les paramètres cibles avant toute action.

3. **Interdiction Stricte des Valeurs Hard-Codées (No Hardcoding Rule)** :
   - Ne JAMAIS inscrire de valeurs hard-codées (montants, compteurs, données statiques de repli, rôles ou identifiants en dur) dans les contrôleurs, modèles ou vues.
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
   - Une fois la modification validée et exécutée, mettre à jour immédiatement le fichier de référence `database/c2833857c_eicg.sql` pour conserver la synchronisation entre la BDD et la référence du projet.

4. **Conventions de Nomenclature SQL** :
   - Clés primaires auto-incrémentées : `id_<entite>` (INT AUTO_INCREMENT).
   - Codes métiers uniques : `code_<entite>` (VARCHAR(50), ex: `CLI-...`, `DEP-...`, `VRS-...`).
   - Horodatages : `created_at_<entite>` (DATETIME NOT NULL), `updated_at_<entite>` (DATETIME).
   - Clés contextuelles obligatoires : `etablissement_code`, `zone_code`, `annee_code`, `user_code`.

---

## 3. SÉCURITÉ, RBAC & FILTRAGE CONTEXTUEL MULTI-TENANT

1. **Triple Filtrage Contextuel Obligatoire** :
   - Pour les tables opérationnelles soumises au contexte (ex: caisses, cotisations/transactions, dépenses, souscriptions, versements), appliquer systématiquement le filtrage contextuel tripartite via la classe `Context` :
     - `etablissement_code = Context::etablissement()`
     - `zone_code = Context::zone()`
     - `annee_code = Context::annee()`
   - Utiliser systématiquement `Context::applyTripleFilter($tableAlias, $conds, $params)` ou `Context::applyScopeSQL($tableAlias, $conds, $params)`.

2. **Restriction des Rôles Métier & Terrain** :
   - Pour les agents de terrain / commerciaux (`ROLE_COMMERCIAL`), filtrer strictly sur leur identifiant utilisateur (`user_code` / `commercial_code`). Ils ne doivent en aucun cas accéder aux données de leurs pairs.
   - Pour les gestionnaires de zone (`ROLE_GESTIONNAIRE`), filtrer par leur zone de compétence.
   - Pour les comptables / financiers (`ROLE_FINANCE`), filtrer par établissement et exercice en cours.

3. **Permission Joker / Pass-Partout (`MAIN_ACCESS`)** :
   - La permission `MAIN_ACCESS` est exclusivement réservée aux administrateurs globaux (`ROLE_SUPERADMIN`, `ROLE_ADMIN`).
   - Elle permet d'accéder aux paramétrages fondamentaux (création d'années, d'établissements, de zones, attribution de rôles) même lorsqu'aucun contexte actif (année, zone, établissement) n'est encore sélectionné en session.

4. **Sécurité Web & Données Sensibles** :
   - **Requêtes Préparées PDO** : Toute interaction avec la base de données doit utiliser des requêtes préparées avec paramètres typés (`?` ou `:nom`). Aucune concaténation de variable utilisateur dans le SQL.
   - **Protection CSRF** : Tout formulaire POST ou requête mutogène AJAX doit envoyer et vérifier `csrf_token` via `BaseController::requirePost()`.
   - **Chiffrement d'Identifiants d'URL** : Ne jamais exposer les IDs auto-incrémentés bruts dans les URLs publiques. Utiliser systématiquement le chiffrement symétrique réversible :
     `$editId = $this->validator->crypter($id);` et `$id = $this->validator->decrypter($idCrypte);`.
   - **Contrôle d'Accès Granulaire** : Chaque méthode de contrôleur doit démarrer par `$this->requirePermission('CODE_PERMISSION');`.

---

## 4. DESIGN SYSTEM & CHARTE GRAPHIQUE PREMIUM

Toutes les interfaces utilisateurs doivent respecter scrupuleusement l'identité visuelle moderne et élégante du projet :

1. **Palette de Couleurs Curatée** :
   - **Bleu Navy Profond** : `#1E3A5F` (Primaire, en-têtes, boutons d'action principaux, navbar) / `#0F172A` (Titres sombres, texte fort).
   - **Émeraude** : `#059669` / `#10B981` (Succès, validations, encaissements, soldes positifs).
   - **Rouge Crimson** : `#DC2626` / `#EF4444` (Dépenses, sorties de caisse, suppressions, blocages/erreurs).
   - **Ambre / Or** : `#D97706` / `#F59E0B` (En attente, alertes, restes à régulariser).
   - **Bleu Indigo** : `#2563EB` / `#3B82F6` (Informations, compteurs globaux).
   - **Fonds & Cartes** : Blanc `#FFFFFF`, surfaces douces `#F8FAFC`, bordures fines `#E2E8F0` / `#CBD5E1`.

2. **Typographie & Lisibilité** :
   - Police moderne sans-serif (Inter, Roboto ou système UI).
   - Titres percutants avec hiérarchie claire (`h1` 22px 800, `h2` 16px 700).
   - Badges de codes métier stylisés : police monospace (`font-family: monospace;`), fond `#F1F5F9`, bordure `#CBD5E1`, texte gras.

3. **Cartes Statistiques (KPI Grid)** :
   - Cartes KPI dynamiques au-dessus des tables de données.
   - En-tête de carte avec titre discret (uppercase 11px gras `#64748B`) et icône dans une pastille translucide avec dégradé subtil.
   - Chiffre clé imposant (24px 800) et sous-titre contextualisé (badge d'évolution ou libellé explicatif).

4. **DataTables Stylisées & Interactives** :
   - Intégration DataTables complète avec localisation française (`json/datatables-i18n-fr-FR.json`).
   - En-têtes de colonnes propres avec fond `#F8FAFC`, texte uppercase 11px `#475569`.
   - Lignes avec survol doux (`#F8FAFC`) et padding aéré.
   - Colonne "Actions" alignée à droite avec boutons compacts (`btn-sm`) et icônes explicites.

5. **Iconographie Systématique (Lucide Icons)** :
   - Utiliser exclusivement la bibliothèque Lucide Icons (`<i data-lucide="nom-icone"></i>`).
   - Toujours invoquer `lucide.createIcons();` dans le callback `drawCallback` des DataTables et après injection dynamique de DOM.

6. **Micro-Interactions & Retours Non-Bloquants** :
   - Feedback visuel via notifications Toast (`toastr.success`, `toastr.error`, `toastr.warning`, `toastr.info`).
   - Boutons en cours de traitement avec état désactivé / spinner de chargement.
   - Verrouillage strict des boutons d'action lors de non-conformités (ex: boutons de clôture désactivés avec alerte rouge tant qu'un écart de caisse subsiste).

---

## 5. RÈGLES DE CODAGE & CYCLE DE VIE MÉTIER

1. **Verrouillage Post-Validation** :
   - Dès qu'un enregistrement passe au statut `actif`, `valide` ou `cloture` (ex: dépenses approuvées, caisses clôturées, versements validés), les actions d'édition et les switches de statut (`toggle-statut`) doivent être désactivés et verrouillés tant côté client (`disabled`, `cursor: not-allowed`, `title` explicatif) que côté serveur (rejet dans la méthode `changer()` / `edit()`).

2. **Flux à Double Validation / Émargement** :
   - Pour les flux financiers (sorties de caisse, versements commerciaux) :
     1. Déclaration par l'initiateur (statut `En attente`, `decission = 'attente'`).
     2. Contrôle physique et comparaison dynamique par l'ordonnateur / comptable.
     3. Validation synchronisée en cascade (versement validé -> caisse validée -> cotisations passées à `valide`).

3. **Validation de Syntaxe Continue** :
   - Avant de finaliser une tâche, toujours exécuter un linter / vérificateur de syntaxe (`php -l chemin/du/fichier.php`) sur l'ensemble des fichiers modifiés.
