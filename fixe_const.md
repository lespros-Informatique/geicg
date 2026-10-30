# Audit des Variables & Constantes Non Unifiées (`fixe_const.md`)

Ce document recense l'ensemble des **variables locales redondantes**, **accès directs à `$_SESSION`** et **chaînes codées en dur** qui continuent de circuler dans le code au lieu d'utiliser les constantes et classes centralisées dans `config/const.php` (`TABLES`, `ROLES`, `PERMISSIONS`, `STATUTS`, `USERS_AUTH`, `Session`, `Auth`, `Helper`).

## Pourquoi ces variables et valeurs en dur existent-elles encore ?

1. **Création de `config/const.php` sans remplacement systématique dans les modules existants** : Les classes `TABLES`, `ROLES`, `PERMISSIONS`, `STATUTS` ainsi que `core/Session.php` et `core/Auth.php` ont bien été définies dans `config/const.php`, mais les contrôleurs, modèles et vues existants ont conservé leurs chaînes littérales (`'VIEW_PAIEMENTS'`, `'ROLE_SUPERADMIN'`, `FROM inscriptions`, `'actif'`).
2. **Initialisations locales répétées en haut des vues (`views/**/*.php` et `public/inc/*.php`)** : Chaque vue réinitialise ses propres variables (`$isCaisseOuverte = $isCaisseOuverte ?? false;`, `$selectedAnneeCode = $_SESSION['annee_active_code'] ?? '';`, `$currentUserCode = $_SESSION[USERS_AUTH]['code_user'] ?? '';`) au lieu de consommer directement `Auth::*`, `Session::*` ou un contexte global injecté par `BaseController::render()`.
3. **Nouvelles vues SQL (`v_*`) absentes de `config/const.php`** : Les 7 vues SQL d'optimisation (`v_inscriptions_completes`, `v_paiements_complets`, etc.) ne sont pas encore déclarées comme constantes dans `config/const.php`.

---

## Synthèse Globale

| Catégorie | Classe / Constante cible (`config/const.php`) | Fichiers impactés |
| :--- | :--- | :---: |
| **1. Rôles codés en dur** | `ROLES::*` | **0** |
| **2. Permissions codées en dur** | `PERMISSIONS::*` | **0** |
| **3. Tables SQL codées en dur** | `TABLES::*` | **0** |
| **4. Vues SQL codées en dur** | `TABLES::*` ou `VIEWS::*` (à ajouter dans `const.php`) | **0** |
| **5. Statuts métier codés en dur** | `STATUTS::*` | **0** |
| **6. Accès directs à `$_SESSION`** | `Session::get()` / `Auth::user()` / `USERS_AUTH` | **0** |
| **7. Variables PHP locales dans les Vues/Includes** | `BaseController` / `Auth` / `Session` | **0** |

---

## 1. Rôles codés en dur au lieu de `ROLES::*` (0 fichiers)


---

## 2. Permissions codées en dur au lieu de `PERMISSIONS::*` (0 fichiers)


---

## 3. Noms de Tables SQL en dur au lieu de `TABLES::*` (0 fichiers)


---

## 4. Vues SQL (`v_*`) en dur à intégrer dans `config/const.php` (0 fichiers)


---

## 5. Statuts codés en dur au lieu de `STATUTS::*` (0 fichiers)


---

## 6. Variables de Session accédées en dur (`$_SESSION[...]`) au lieu de `Session::*` / `Auth::*` (0 fichiers)


---

## 7. Variables PHP redondantes dans les Vues et Includes (0 fichiers)

> Contexte global centralisé dans `core/ViewContext.php` (`headerData()`, `sidebarData()`, `navData()`, `defaults()`, `setScripts()`), injecté via `BaseController::loadView()`. Les variables locales de boucle/présentation (ex. `$rowClass`, `$isAffecte`) restent légitimes et ne sont pas comptées.


---

## Annexe (informatif, hors périmètre `const.php`) : Modèles instanciés directement dans les vues (20 fichiers)

> Dette d'architecture MVC : ces données devraient être chargées par le contrôleur. À traiter dans un chantier dédié.

- **`views/accessoires/list.php`** : `ModelEtudiant`, `ModelAccessoire`
- **`views/accessoires/registre.php`** : `ModelEtudiant`, `ModelAccessoire`
- **`views/bulletin/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/classes/edit.php`** : `ModelFiliere`, `ModelNiveau`, `ModelAnnee`
- **`views/classes/list.php`** : `ModelFiliere`, `ModelNiveau`
- **`views/depenses/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/depenses/list.php`** : `ModelDepense`, `ModelTypeDepense`
- **`views/documents/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/emplois_temps/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/enseignant_matiere/edit.php`** : `ModelClasse`, `ModelMatiere`, `ModelEnseignant`
- **`views/enseignant_matiere/list.php`** : `ModelMatiere`, `ModelEnseignant`
- **`views/etudiants/wizard.php`** : `ModelAnnee`, `ModelClasse`, `ModelAccessoire`, `ModelPieceFournir`
- **`views/evenements/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/filieres/list.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`
- **`views/galeries/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/inscriptions/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`, `ModelAnnee`
- **`views/parents/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/piece_fournir_cycle/edit.php`** : `ModelNiveau`
- **`views/tranches_scolarite/edit.php`** : `ModelCycle`, `ModelFiliere`, `ModelNiveau`, `ModelClasse`, `ModelSalle`, `ModelScolarite`, `ModelMatiere`, `ModelSemestre`, `ModelEtudiant`, `ModelInscription`, `ModelTypeDepense`, `ModelUser`, `ModelEnseignant`
- **`views/users/edit.php`** : `ModelRole`, `ModelFonction`, `ModelAnnee`, `ModelUser`
