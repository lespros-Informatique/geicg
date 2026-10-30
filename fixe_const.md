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
| **2. Permissions codées en dur** | `PERMISSIONS::*` | **45** |
| **3. Tables SQL codées en dur** | `TABLES::*` | **35** |
| **4. Vues SQL codées en dur** | `TABLES::*` ou `VIEWS::*` (à ajouter dans `const.php`) | **11** |
| **5. Statuts métier codés en dur** | `STATUTS::*` | **38** |
| **6. Accès directs à `$_SESSION`** | `Session::get()` / `Auth::user()` / `USERS_AUTH` | **43** |
| **7. Variables PHP locales dans les Vues/Includes** | `BaseController` / `Auth` / `Session` | **138** |

---

## 1. Rôles codés en dur au lieu de `ROLES::*` (0 fichiers)


---

## 2. Permissions codées en dur au lieu de `PERMISSIONS::*` (45 fichiers)

- **`controllers/absences/AbsenceController.php`** (10 occ.) : `PERMISSIONS::MANAGE_ABSENCES` (x7), `PERMISSIONS::VIEW_ABSENCES` (x3)
- **`controllers/annees/AnneeController.php`** (6 occ.) : `PERMISSIONS::MANAGE_ANNEES` (x3), `PERMISSIONS::VIEW_ANNEES` (x3)
- **`controllers/bulletin/BulletinController.php`** (14 occ.) : `PERMISSIONS::GENERATE_BULLETINS` (x2), `PERMISSIONS::VIEW_BULLETINS` (x6), `PERMISSIONS::VIEW_NOTES` (x6)
- **`controllers/classes/ClasseController.php`** (20 occ.) : `PERMISSIONS::MANAGE_CLASSES` (x7), `PERMISSIONS::PRINT_CLASSES` (x2), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::VIEW_CLASSES` (x6), `PERMISSIONS::MANAGE_ETUDIANTS` (x1), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x1), `PERMISSIONS::VIEW_ETUDIANTS` (x1), `PERMISSIONS::VIEW_INSCRIPTIONS` (x1)
- **`controllers/cycles/CycleController.php`** (2 occ.) : `PERMISSIONS::MANAGE_CYCLES` (x2)
- **`controllers/depenses/DepenseController.php`** (11 occ.) : `PERMISSIONS::RECORD_DEPENSES` (x5), `PERMISSIONS::VALIDATE_DEPENSES` (x2), `PERMISSIONS::VIEW_DEPENSES` (x4)
- **`controllers/dossier_etudiant/DossierEtudiantController.php`** (12 occ.) : `PERMISSIONS::MANAGE_DEPOT_DOSSIERS` (x5), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x2), `PERMISSIONS::MANAGE_PIECES` (x2), `PERMISSIONS::VIEW_DEPOT_DOSSIERS` (x3)
- **`controllers/emplois_temps/EmploiController.php`** (13 occ.) : `PERMISSIONS::MANAGE_EMPLOI_TEMPS` (x7), `PERMISSIONS::VIEW_EMPLOI_TEMPS` (x6)
- **`controllers/enseignant_matiere/EnseignantMatiereController.php`** (6 occ.) : `PERMISSIONS::MANAGE_AFFECTATIONS` (x3), `PERMISSIONS::VIEW_AFFECTATIONS` (x3)
- **`controllers/enseignants/EnseignantController.php`** (8 occ.) : `PERMISSIONS::MANAGE_ENSEIGNANTS` (x5), `PERMISSIONS::VIEW_ENSEIGNANTS` (x3)
- **`controllers/etudiants/EtudiantController.php`** (14 occ.) : `PERMISSIONS::ANNULER_INSCRIPTION` (x1), `PERMISSIONS::CHANGER_CLASSE_ETUDIANT` (x2), `PERMISSIONS::EDIT_ETUDIANT` (x2), `PERMISSIONS::MANAGE_ETUDIANTS` (x7), `PERMISSIONS::VIEW_ETUDIANTS` (x2)
- **`controllers/filiere_cycles/FiliereCycleController.php`** (15 occ.) : `PERMISSIONS::MANAGE_CLASSES` (x2), `PERMISSIONS::MANAGE_CYCLES` (x5), `PERMISSIONS::MANAGE_FILIERES` (x5), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::VIEW_CYCLES` (x1), `PERMISSIONS::VIEW_FILIERES` (x1)
- **`controllers/filieres/FiliereController.php`** (11 occ.) : `PERMISSIONS::MANAGE_FILIERES` (x5), `PERMISSIONS::PRINT_FILIERES` (x1), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::VIEW_FILIERES` (x4)
- **`controllers/inscriptions/InscriptionController.php`** (24 occ.) : `PERMISSIONS::VIEW_CLASSES` (x1), `PERMISSIONS::MANAGE_PAIEMENTS` (x1), `PERMISSIONS::RECORD_PAIEMENTS` (x1), `PERMISSIONS::MANAGE_DEROGATION_INSCRIPTION` (x3), `PERMISSIONS::MANAGE_ETUDIANTS` (x2), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x8), `PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO` (x3), `PERMISSIONS::VIEW_ETUDIANTS` (x1), `PERMISSIONS::VIEW_INSCRIPTIONS` (x2), `PERMISSIONS::VIEW_INSCRIPTIONS_SANS_PHOTO` (x2)
- **`controllers/matieres/MatiereController.php`** (8 occ.) : `PERMISSIONS::MANAGE_MATIERES` (x5), `PERMISSIONS::VIEW_MATIERES` (x3)
- **`controllers/notes/NoteController.php`** (12 occ.) : `PERMISSIONS::ENTER_NOTES` (x9), `PERMISSIONS::VIEW_NOTES` (x3)
- **`controllers/paiements/PaiementController.php`** (49 occ.) : `PERMISSIONS::CANCEL_PAIEMENTS` (x3), `PERMISSIONS::CLOTURE_CAISSE` (x2), `PERMISSIONS::MANAGE_CAISSE` (x4), `PERMISSIONS::MANAGE_PAIEMENTS` (x10), `PERMISSIONS::OUVERTURE_CAISSE` (x2), `PERMISSIONS::RECORD_PAIEMENTS` (x8), `PERMISSIONS::VIEW_COMPTABILITE_ETUDIANTS` (x5), `PERMISSIONS::VIEW_PAIEMENTS` (x11), `PERMISSIONS::VIEW_ETUDIANTS` (x2), `PERMISSIONS::VIEW_INSCRIPTIONS` (x2)
- **`controllers/pieces_fournir/PieceFournirController.php`** (14 occ.) : `PERMISSIONS::CONFIG_ACADEMIQUE` (x5), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x2), `PERMISSIONS::MANAGE_PIECES` (x5), `PERMISSIONS::VIEW_PIECES` (x2)
- **`controllers/pieces_fournir/PieceFournirCycleController.php`** (28 occ.) : `PERMISSIONS::CONFIG_ACADEMIQUE` (x10), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x4), `PERMISSIONS::MANAGE_PIECES` (x10), `PERMISSIONS::VIEW_PIECES` (x4)
- **`controllers/salles/SalleController.php`** (9 occ.) : `PERMISSIONS::MANAGE_SALLES` (x3), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::PRINT_SALLES` (x1), `PERMISSIONS::VIEW_SALLES` (x4)
- **`controllers/scolarites/ScolariteController.php`** (3 occ.) : `PERMISSIONS::MANAGE_FRAIS_SCOLARITE` (x1), `PERMISSIONS::PRINT_FRAIS_SCOLARITE` (x1), `PERMISSIONS::VIEW_FRAIS_SCOLARITE` (x1)
- **`controllers/semestres/SemestreController.php`** (8 occ.) : `PERMISSIONS::MANAGE_SEMESTRES` (x5), `PERMISSIONS::VIEW_SEMESTRES` (x3)
- **`controllers/tranches_scolarite/TrancheController.php`** (8 occ.) : `PERMISSIONS::MANAGE_FRAIS_SCOLARITE` (x5), `PERMISSIONS::VIEW_FRAIS_SCOLARITE` (x3)
- **`controllers/type_depenses/TypeDepenseController.php`** (6 occ.) : `PERMISSIONS::MANAGE_TYPES_DEPENSE` (x3), `PERMISSIONS::VIEW_TYPES_DEPENSE` (x3)
- **`controllers/users/UserController.php`** (4 occ.) : `PERMISSIONS::MANAGE_USERS` (x3), `PERMISSIONS::VIEW_USERS` (x1)
- **`core/BaseController.php`** (2 occ.) : `PERMISSIONS::EDIT_ANNEES_ANTERIEURES` (x1), `PERMISSIONS::VIEW_ANNEES_ANTERIEURES` (x1)
- **`models/annees/ModelAnnee.php`** (1 occ.) : `PERMISSIONS::VIEW_ANNEES_ANTERIEURES` (x1)
- **`public/inc/nav.php`** (2 occ.) : `PERMISSIONS::EDIT_ANNEES_ANTERIEURES` (x1), `PERMISSIONS::VIEW_ANNEES_ANTERIEURES` (x1)
- **`public/inc/sidbar.php`** (127 occ.) : `PERMISSIONS::CONFIG_ACADEMIQUE` (x8), `PERMISSIONS::MANAGE_ANNEES` (x1), `PERMISSIONS::MANAGE_CLASSES` (x1), `PERMISSIONS::MANAGE_CYCLES` (x1), `PERMISSIONS::MANAGE_FILIERES` (x1), `PERMISSIONS::MANAGE_MATIERES` (x1), `PERMISSIONS::MANAGE_NIVEAUX` (x1), `PERMISSIONS::MANAGE_SALLES` (x1), `PERMISSIONS::MANAGE_SEMESTRES` (x1), `PERMISSIONS::MANAGE_UE` (x1), `PERMISSIONS::VIEW_ANNEES` (x1), `PERMISSIONS::VIEW_CLASSES` (x1), `PERMISSIONS::VIEW_CYCLES` (x1), `PERMISSIONS::VIEW_FILIERES` (x1), `PERMISSIONS::VIEW_MATIERES` (x1), `PERMISSIONS::VIEW_NIVEAUX` (x1), `PERMISSIONS::VIEW_SALLES` (x1), `PERMISSIONS::VIEW_SEMESTRES` (x1), `PERMISSIONS::VIEW_UE` (x1), `PERMISSIONS::CONFIG_SYSTEM` (x1), `PERMISSIONS::MANAGE_ETABLISSEMENT` (x1), `PERMISSIONS::MANAGE_FONCTIONS` (x1), `PERMISSIONS::MANAGE_PERMISSIONS` (x1), `PERMISSIONS::MANAGE_ROLES` (x1), `PERMISSIONS::MANAGE_USERS` (x1), `PERMISSIONS::VIEW_FONCTIONS` (x1), `PERMISSIONS::VIEW_PERMISSIONS` (x1), `PERMISSIONS::VIEW_ROLES` (x1), `PERMISSIONS::VIEW_USERS` (x1), `PERMISSIONS::MANAGE_COMMUNICATION` (x1), `PERMISSIONS::MANAGE_DOCUMENTS` (x1), `PERMISSIONS::MANAGE_EVENEMENTS` (x1), `PERMISSIONS::MANAGE_GALLERY` (x1), `PERMISSIONS::VIEW_DOCUMENTS` (x1), `PERMISSIONS::VIEW_EVENEMENTS` (x1), `PERMISSIONS::VIEW_GALLERY` (x1), `PERMISSIONS::CLOTURE_CAISSE` (x1), `PERMISSIONS::EXPORT_FINANCE` (x1), `PERMISSIONS::MANAGE_ARRIERES` (x1), `PERMISSIONS::MANAGE_CAISSE` (x1), `PERMISSIONS::MANAGE_CAISSE_ANNEXE` (x1), `PERMISSIONS::MANAGE_FRAIS_ANNEXES` (x2), `PERMISSIONS::MANAGE_FRAIS_SCOLARITE` (x2), `PERMISSIONS::MANAGE_IMPAYES` (x2), `PERMISSIONS::MANAGE_MORATOIRES` (x1), `PERMISSIONS::MANAGE_PAIEMENTS` (x5), `PERMISSIONS::MANAGE_TYPES_DEPENSE` (x1), `PERMISSIONS::OUVERTURE_CAISSE` (x1), `PERMISSIONS::RECORD_DEPENSES` (x1), `PERMISSIONS::RECORD_PAIEMENTS` (x2), `PERMISSIONS::SEND_RELANCES` (x1), `PERMISSIONS::VALIDATE_DEPENSES` (x1), `PERMISSIONS::VIEW_ARRIERES` (x1), `PERMISSIONS::VIEW_CAISSE_ANNEXE` (x1), `PERMISSIONS::VIEW_COMPTABILITE_ETUDIANTS` (x1), `PERMISSIONS::VIEW_DEPENSES` (x1), `PERMISSIONS::VIEW_FRAIS_ANNEXES` (x2), `PERMISSIONS::VIEW_FRAIS_SCOLARITE` (x2), `PERMISSIONS::VIEW_IMPAYES` (x1), `PERMISSIONS::VIEW_PAIEMENTS` (x3), `PERMISSIONS::VIEW_RAPPORTS_FINANCIERS` (x3), `PERMISSIONS::VIEW_TYPES_DEPENSE` (x1), `PERMISSIONS::ENTER_NOTES` (x2), `PERMISSIONS::GENERATE_BULLETINS` (x1), `PERMISSIONS::LOCK_NOTES` (x2), `PERMISSIONS::MANAGE_EMPLOI_TEMPS` (x1), `PERMISSIONS::MANAGE_ENSEIGNANTS` (x1), `PERMISSIONS::MANAGE_EXAMENS` (x1), `PERMISSIONS::VIEW_BULLETINS` (x1), `PERMISSIONS::VIEW_EMPLOI_TEMPS` (x1), `PERMISSIONS::VIEW_ENSEIGNANTS` (x1), `PERMISSIONS::VIEW_EXAMENS` (x1), `PERMISSIONS::VIEW_NOTES` (x2), `PERMISSIONS::VIEW_REPORTS` (x1), `PERMISSIONS::MANAGE_ABSENCES` (x1), `PERMISSIONS::MANAGE_ACCESSOIRES` (x1), `PERMISSIONS::MANAGE_DEPOT_DOSSIERS` (x1), `PERMISSIONS::MANAGE_ETUDIANTS` (x2), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x4), `PERMISSIONS::MANAGE_INSCRIPTIONS_SANS_PHOTO` (x1), `PERMISSIONS::MANAGE_PARENTS` (x1), `PERMISSIONS::MANAGE_PIECES` (x1), `PERMISSIONS::MANAGE_REMISE_KITS` (x1), `PERMISSIONS::VIEW_ABSENCES` (x1), `PERMISSIONS::VIEW_ACCESSOIRES` (x1), `PERMISSIONS::VIEW_DEPOT_DOSSIERS` (x1), `PERMISSIONS::VIEW_ETUDIANTS` (x1), `PERMISSIONS::VIEW_INSCRIPTIONS` (x1), `PERMISSIONS::VIEW_INSCRIPTIONS_SANS_PHOTO` (x1), `PERMISSIONS::VIEW_PARENTS` (x1), `PERMISSIONS::VIEW_PIECES` (x1), `PERMISSIONS::VIEW_REMISE_KITS` (x1), `PERMISSIONS::MENU_ADMINISTRATION` (x1), `PERMISSIONS::MENU_COMMUNICATION` (x1), `PERMISSIONS::MENU_FINANCE` (x1), `PERMISSIONS::MENU_PEDAGOGIE` (x1), `PERMISSIONS::MENU_PLANIFICATION` (x1), `PERMISSIONS::MENU_SCOLARITE` (x1), `PERMISSIONS::MENU_STRUCTURE` (x1)
- **`views/bulletin/list.php`** (2 occ.) : `PERMISSIONS::VIEW_BULLETINS` (x1), `PERMISSIONS::VIEW_NOTES` (x1)
- **`views/classes/details.php`** (1 occ.) : `PERMISSIONS::MANAGE_CLASSES` (x1)
- **`views/classes/list.php`** (5 occ.) : `PERMISSIONS::MANAGE_CLASSES` (x2), `PERMISSIONS::PRINT_CLASSES` (x1), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::VIEW_CLASSES` (x1)
- **`views/compositions/details.php`** (1 occ.) : `PERMISSIONS::ENTER_NOTES` (x1)
- **`views/depenses/list.php`** (2 occ.) : `PERMISSIONS::RECORD_DEPENSES` (x1), `PERMISSIONS::VALIDATE_DEPENSES` (x1)
- **`views/etudiants/details.php`** (2 occ.) : `PERMISSIONS::EDIT_ETUDIANT` (x1), `PERMISSIONS::MANAGE_ETUDIANTS` (x1)
- **`views/etudiants/list.php`** (8 occ.) : `PERMISSIONS::ANNULER_INSCRIPTION` (x1), `PERMISSIONS::CHANGER_CLASSE_ETUDIANT` (x1), `PERMISSIONS::EDIT_ETUDIANT` (x1), `PERMISSIONS::MANAGE_ETUDIANTS` (x3), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x2)
- **`views/filiere_cycles/hub.php`** (3 occ.) : `PERMISSIONS::PRINT_FILIERES` (x1), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::VIEW_FILIERES` (x1)
- **`views/filieres/list.php`** (9 occ.) : `PERMISSIONS::PRINT_FILIERES` (x1), `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x3), `PERMISSIONS::VIEW_CYCLES` (x2), `PERMISSIONS::VIEW_FILIERES` (x3)
- **`views/frais_annexes/list.php`** (5 occ.) : `PERMISSIONS::MANAGE_FRAIS_ANNEXES` (x1), `PERMISSIONS::PRINT_FRAIS_ANNEXES` (x1), `PERMISSIONS::PRINT_FRAIS_SCOLARITE` (x1), `PERMISSIONS::VIEW_FRAIS_ANNEXES` (x1), `PERMISSIONS::VIEW_FRAIS_SCOLARITE` (x1)
- **`views/home/index.php`** (85 occ.) : `PERMISSIONS::MANAGE_CLASSES` (x2), `PERMISSIONS::MANAGE_FILIERES` (x2), `PERMISSIONS::MANAGE_MATIERES` (x2), `PERMISSIONS::VIEW_CLASSES` (x2), `PERMISSIONS::VIEW_FILIERES` (x2), `PERMISSIONS::VIEW_MATIERES` (x2), `PERMISSIONS::MANAGE_ROLES` (x2), `PERMISSIONS::MANAGE_USERS` (x2), `PERMISSIONS::VIEW_USERS` (x2), `PERMISSIONS::VIEW_DASHBOARD_ACTIONS` (x1), `PERMISSIONS::VIEW_DASHBOARD_COMMUNICATION` (x1), `PERMISSIONS::VIEW_DASHBOARD_ENSEIGNANT` (x1), `PERMISSIONS::VIEW_DASHBOARD_EXECUTIVE` (x1), `PERMISSIONS::VIEW_DASHBOARD_FINANCE` (x1), `PERMISSIONS::VIEW_DASHBOARD_PEDAGOGIE` (x1), `PERMISSIONS::VIEW_DASHBOARD_SCOLARITE` (x1), `PERMISSIONS::CLOTURE_CAISSE` (x2), `PERMISSIONS::MANAGE_CAISSE` (x2), `PERMISSIONS::MANAGE_IMPAYES` (x3), `PERMISSIONS::OUVERTURE_CAISSE` (x2), `PERMISSIONS::RECORD_DEPENSES` (x2), `PERMISSIONS::RECORD_PAIEMENTS` (x3), `PERMISSIONS::VIEW_DEPENSES` (x2), `PERMISSIONS::VIEW_IMPAYES` (x2), `PERMISSIONS::VIEW_PAIEMENTS` (x3), `PERMISSIONS::ENTER_NOTES` (x2), `PERMISSIONS::GENERATE_BULLETINS` (x2), `PERMISSIONS::MANAGE_ENSEIGNANTS` (x2), `PERMISSIONS::VIEW_BULLETINS` (x2), `PERMISSIONS::VIEW_ENSEIGNANTS` (x2), `PERMISSIONS::VIEW_NOTES` (x2), `PERMISSIONS::MANAGE_ABSENCES` (x2), `PERMISSIONS::MANAGE_ACCESSOIRES` (x2), `PERMISSIONS::MANAGE_DEPOT_DOSSIERS` (x3), `PERMISSIONS::MANAGE_ETUDIANTS` (x2), `PERMISSIONS::MANAGE_INSCRIPTIONS` (x4), `PERMISSIONS::MANAGE_REMISE_KITS` (x3), `PERMISSIONS::VIEW_ABSENCES` (x2), `PERMISSIONS::VIEW_DEPOT_DOSSIERS` (x2), `PERMISSIONS::VIEW_ETUDIANTS` (x2), `PERMISSIONS::VIEW_INSCRIPTIONS` (x3), `PERMISSIONS::VIEW_REMISE_KITS` (x2)
- **`views/notes/list.php`** (1 occ.) : `PERMISSIONS::ENTER_NOTES` (x1)
- **`views/paiements/list.php`** (2 occ.) : `PERMISSIONS::CANCEL_PAIEMENTS` (x2)
- **`views/pieces_fournir/list.php`** (4 occ.) : `PERMISSIONS::CONFIG_ACADEMIQUE` (x2), `PERMISSIONS::MANAGE_PIECES` (x2)
- **`views/salles/list.php`** (3 occ.) : `PERMISSIONS::PRINT_OFFRE_ACADEMIQUE` (x1), `PERMISSIONS::PRINT_SALLES` (x1), `PERMISSIONS::VIEW_SALLES` (x1)
- **`views/scolarites/list.php`** (3 occ.) : `PERMISSIONS::MANAGE_FRAIS_SCOLARITE` (x1), `PERMISSIONS::PRINT_FRAIS_SCOLARITE` (x1), `PERMISSIONS::VIEW_FRAIS_SCOLARITE` (x1)

---

## 3. Noms de Tables SQL en dur au lieu de `TABLES::*` (35 fichiers)

- **`controllers/sessions_caisse/SessionCaisseController.php`** : `TABLES::PAIEMENTS` (`paiements`) (x1)
- **`core/Auth.php`** : `TABLES::PERMISSIONS` (`permissions`) (x1), `TABLES::ROLE_PERMISSIONS` (`role_permissions`) (x1)
- **`core/BaseController.php`** : `TABLES::ETABLISSEMENTS` (`etablissements`) (x4), `TABLES::PERMISSIONS` (`permissions`) (x1), `TABLES::ROLE_PERMISSIONS` (`role_permissions`) (x1), `TABLES::USER_ANNEE_ACCES` (`user_annee_acces`) (x2), `TABLES::ANNEES` (`annees`) (x4)
- **`core/PrincipalRoute.php`** : `TABLES::ANNEES` (`annees`) (x2)
- **`models/ModelFiliereCycle.php`** : `TABLES::CYCLES` (`cycles`) (x3), `TABLES::FILIERES` (`filieres`) (x3), `TABLES::FILIERE_CYCLES` (`filiere_cycles`) (x6), `TABLES::NIVEAUX` (`niveaux`) (x3), `TABLES::CLASSES` (`classes`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x1)
- **`models/accessoires/ModelAccessoire.php`** : `TABLES::ETABLISSEMENTS` (`etablissements`) (x1), `TABLES::ANNEES` (`annees`) (x1), `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::CLASSES` (`classes`) (x2), `TABLES::ETUDIANTS` (`etudiants`) (x2), `TABLES::INSCRIPTIONS` (`inscriptions`) (x2), `TABLES::ACCESSOIRES` (`accessoires`) (x4), `TABLES::ACCESSOIRE_INSCRIPTION` (`accessoire_inscription`) (x5)
- **`models/annees/ModelAnnee.php`** : `TABLES::ANNEES` (`annees`) (x7)
- **`models/arrieres/ModelArriere.php`** : `TABLES::ANNEES` (`annees`) (x2), `TABLES::CLASSES` (`classes`) (x3), `TABLES::ETUDIANTS` (`etudiants`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x5), `TABLES::PAIEMENTS` (`paiements`) (x1)
- **`models/bulletin/ModelBulletin.php`** : `TABLES::USERS` (`users`) (x1), `TABLES::ANNEES` (`annees`) (x1), `TABLES::FILIERES` (`filieres`) (x1), `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::CLASSES` (`classes`) (x2), `TABLES::SEMESTRES` (`semestres`) (x1), `TABLES::MATIERES` (`matieres`) (x5), `TABLES::ETUDIANTS` (`etudiants`) (x2), `TABLES::INSCRIPTIONS` (`inscriptions`) (x5), `TABLES::ENSEIGNANTS` (`enseignants`) (x1), `TABLES::ENSEIGNANT_MATIERE` (`enseignant_matiere`) (x3), `TABLES::NOTES` (`notes`) (x6), `TABLES::ABSENCES` (`absences`) (x1), `TABLES::COMPOSITIONS` (`compositions`) (x5)
- **`models/caisse_annexe/ModelCaisseAnnexe.php`** : `TABLES::ANNEES` (`annees`) (x1), `TABLES::FILIERES` (`filieres`) (x1), `TABLES::CLASSES` (`classes`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x1), `TABLES::PAIEMENTS` (`paiements`) (x1), `TABLES::FRAIS_ANNEXES` (`frais_annexes`) (x1)
- **`models/classes/ModelClasse.php`** : `TABLES::FILIERES` (`filieres`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x2), `TABLES::CLASSES` (`classes`) (x5), `TABLES::SCOLARITES` (`scolarites`) (x2)
- **`models/compositions/ModelComposition.php`** : `TABLES::FILIERES` (`filieres`) (x4), `TABLES::NIVEAUX` (`niveaux`) (x4), `TABLES::CLASSES` (`classes`) (x5), `TABLES::SEMESTRES` (`semestres`) (x3), `TABLES::MATIERES` (`matieres`) (x3), `TABLES::EMPLOIS_TEMPS` (`emplois_temps`) (x1), `TABLES::COMPOSITIONS` (`compositions`) (x4), `TABLES::COMPOSITION_MATIERES` (`composition_matieres`) (x5)
- **`models/depenses/ModelDepense.php`** : `TABLES::USERS` (`users`) (x2), `TABLES::DEPENSES` (`depenses`) (x9), `TABLES::TYPE_DEPENSES` (`type_depenses`) (x2)
- **`models/documents/ModelDocument.php`** : `TABLES::FILIERES` (`filieres`) (x1), `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::DOCUMENTS` (`documents`) (x1)
- **`models/dossier_etudiant/ModelDossierEtudiant.php`** : `TABLES::ETABLISSEMENTS` (`etablissements`) (x2), `TABLES::ANNEES` (`annees`) (x1), `TABLES::FILIERE_CYCLES` (`filiere_cycles`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x2), `TABLES::CLASSES` (`classes`) (x4), `TABLES::ETUDIANTS` (`etudiants`) (x2), `TABLES::INSCRIPTIONS` (`inscriptions`) (x4), `TABLES::DOSSIER_ETUDIANT` (`dossier_etudiant`) (x3), `TABLES::PIECES_FOURNIR` (`pieces_fournir`) (x2)
- **`models/enseignants/ModelEnseignant.php`** : `TABLES::USERS` (`users`) (x5), `TABLES::ENSEIGNANTS` (`enseignants`) (x5)
- **`models/etudiants/ModelEtudiant.php`** : `TABLES::ETABLISSEMENTS` (`etablissements`) (x1), `TABLES::ANNEES` (`annees`) (x2), `TABLES::FILIERES` (`filieres`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::CLASSES` (`classes`) (x3), `TABLES::ETUDIANTS` (`etudiants`) (x5), `TABLES::PARENTS` (`parents`) (x2), `TABLES::INSCRIPTIONS` (`inscriptions`) (x3), `TABLES::ACCESSOIRE_INSCRIPTION` (`accessoire_inscription`) (x2), `TABLES::DOSSIER_ETUDIANT` (`dossier_etudiant`) (x2), `TABLES::PIECES_FOURNIR` (`pieces_fournir`) (x1), `TABLES::SCOLARITES` (`scolarites`) (x2), `TABLES::PAIEMENTS` (`paiements`) (x1), `TABLES::ABSENCES` (`absences`) (x1)
- **`models/filieres/ModelFiliere.php`** : `TABLES::CYCLES` (`cycles`) (x1), `TABLES::FILIERES` (`filieres`) (x1), `TABLES::FILIERE_CYCLES` (`filiere_cycles`) (x1)
- **`models/fonctions/ModelFonction.php`** : `TABLES::USERS` (`users`) (x1), `TABLES::ROLES` (`roles`) (x1), `TABLES::USER_ROLES` (`user_roles`) (x1)
- **`models/frais_annexes/ModelFraisAnnexe.php`** : `TABLES::ANNEES` (`annees`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x2), `TABLES::FRAIS_ANNEXES` (`frais_annexes`) (x5)
- **`models/home/ModelHome.php`** : `TABLES::USERS` (`users`) (x1), `TABLES::ANNEES` (`annees`) (x2), `TABLES::CYCLES` (`cycles`) (x1), `TABLES::FILIERES` (`filieres`) (x2), `TABLES::FILIERE_CYCLES` (`filiere_cycles`) (x1), `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::CLASSES` (`classes`) (x2), `TABLES::MATIERES` (`matieres`) (x1), `TABLES::SALLES` (`salles`) (x1), `TABLES::PARENTS` (`parents`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x6), `TABLES::ACCESSOIRES` (`accessoires`) (x1), `TABLES::ACCESSOIRE_INSCRIPTION` (`accessoire_inscription`) (x1), `TABLES::DOSSIER_ETUDIANT` (`dossier_etudiant`) (x1), `TABLES::PAIEMENTS` (`paiements`) (x2), `TABLES::DEPENSES` (`depenses`) (x1), `TABLES::ENSEIGNANTS` (`enseignants`) (x2), `TABLES::NOTES` (`notes`) (x1), `TABLES::ABSENCES` (`absences`) (x1), `TABLES::DOCUMENTS` (`documents`) (x1), `TABLES::EVENEMENTS` (`evenements`) (x1)
- **`models/impayes/ModelImpayes.php`** : `TABLES::NIVEAUX` (`niveaux`) (x1), `TABLES::CLASSES` (`classes`) (x1), `TABLES::ETUDIANTS` (`etudiants`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x1), `TABLES::SCOLARITES` (`scolarites`) (x1), `TABLES::TRANCHES_SCOLARITE` (`tranches_scolarite`) (x1)
- **`models/inscriptions/ModelInscription.php`** : `TABLES::ETUDIANTS` (`etudiants`) (x1), `TABLES::PARENTS` (`parents`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x1), `TABLES::ACCESSOIRES` (`accessoires`) (x2), `TABLES::ACCESSOIRE_INSCRIPTION` (`accessoire_inscription`) (x1), `TABLES::SCOLARITES` (`scolarites`) (x3), `TABLES::TRANCHES_SCOLARITE` (`tranches_scolarite`) (x3)
- **`models/niveaux/ModelNiveau.php`** : `TABLES::FILIERE_CYCLES` (`filiere_cycles`) (x1), `TABLES::NIVEAUX` (`niveaux`) (x3), `TABLES::CLASSES` (`classes`) (x1)
- **`models/parents/ModelParent.php`** : `TABLES::CLASSES` (`classes`) (x1), `TABLES::ETUDIANTS` (`etudiants`) (x1), `TABLES::PARENTS` (`parents`) (x1), `TABLES::INSCRIPTIONS` (`inscriptions`) (x1)
- **`models/permissions/ModelPermission.php`** : `TABLES::PERMISSIONS` (`permissions`) (x1)
- **`models/pieces_fournir/ModelPieceFournir.php`** : `TABLES::PIECES_FOURNIR` (`pieces_fournir`) (x8)
- **`models/pieces_fournir/ModelPieceFournirCycle.php`** : `TABLES::CYCLES` (`cycles`) (x3), `TABLES::NIVEAUX` (`niveaux`) (x3), `TABLES::PIECES_FOURNIR` (`pieces_fournir`) (x3)
- **`models/roles/ModelRole.php`** : `TABLES::PERMISSIONS` (`permissions`) (x1), `TABLES::ROLE_PERMISSIONS` (`role_permissions`) (x4)
- **`models/scolarites/ModelScolarite.php`** : `TABLES::ANNEES` (`annees`) (x2), `TABLES::FILIERES` (`filieres`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x2), `TABLES::CLASSES` (`classes`) (x1), `TABLES::SCOLARITES` (`scolarites`) (x2)
- **`models/semestres/ModelSemestre.php`** : `TABLES::ANNEES` (`annees`) (x1), `TABLES::SEMESTRES` (`semestres`) (x5)
- **`models/sessions_caisse/ModelSessionCaisse.php`** : `TABLES::USERS` (`users`) (x5), `TABLES::PAIEMENTS` (`paiements`) (x2), `TABLES::SESSIONS_CAISSE` (`sessions_caisse`) (x3)
- **`models/tranches_scolarite/ModelTranche.php`** : `TABLES::ANNEES` (`annees`) (x2), `TABLES::FILIERES` (`filieres`) (x2), `TABLES::NIVEAUX` (`niveaux`) (x2), `TABLES::SCOLARITES` (`scolarites`) (x2), `TABLES::TRANCHES_SCOLARITE` (`tranches_scolarite`) (x2)
- **`models/users/ModelUser.php`** : `TABLES::USERS` (`users`) (x1), `TABLES::ROLES` (`roles`) (x2), `TABLES::USER_ROLES` (`user_roles`) (x3), `TABLES::USER_ANNEE_ACCES` (`user_annee_acces`) (x3)
- **`public/inc/nav.php`** : `TABLES::USER_ANNEE_ACCES` (`user_annee_acces`) (x2), `TABLES::ANNEES` (`annees`) (x1)

---

## 4. Vues SQL (`v_*`) en dur à intégrer dans `config/const.php` (11 fichiers)

- **`models/absences/ModelAbsence.php`** : `v_absences_completes` → `VIEWS::ABSENCES_COMPLETES` (x2)
- **`models/arrieres/ModelArriere.php`** : `v_bilan_paiements_inscriptions` → `VIEWS::BILAN_PAIEMENTS_INSCRIPTIONS` (x2)
- **`models/bulletin/ModelBulletin.php`** : `v_inscriptions_completes` → `VIEWS::INSCRIPTIONS_COMPLETES` (x1)
- **`models/caisse_annexe/ModelCaisseAnnexe.php`** : `v_paiements_complets` → `VIEWS::PAIEMENTS_COMPLETS` (x1)
- **`models/emplois_temps/ModelEmploi.php`** : `v_dash_pedagogie_affectations` → `VIEWS::DASH_PEDAGOGIE_AFFECTATIONS` (x2), `v_emplois_temps_complets` → `VIEWS::EMPLOIS_TEMPS_COMPLETS` (x2)
- **`models/enseignant_matiere/ModelEnseignantMatiere.php`** : `v_dash_pedagogie_affectations` → `VIEWS::DASH_PEDAGOGIE_AFFECTATIONS` (x2)
- **`models/home/ModelHome.php`** : `v_dash_pedagogie_affectations` → `VIEWS::DASH_PEDAGOGIE_AFFECTATIONS` (x2)
- **`models/impayes/ModelImpayes.php`** : `v_inscriptions_completes` → `VIEWS::INSCRIPTIONS_COMPLETES` (x3), `v_bilan_paiements_inscriptions` → `VIEWS::BILAN_PAIEMENTS_INSCRIPTIONS` (x1)
- **`models/inscriptions/ModelInscription.php`** : `v_inscriptions_completes` → `VIEWS::INSCRIPTIONS_COMPLETES` (x2)
- **`models/notes/ModelNote.php`** : `v_notes_completes` → `VIEWS::NOTES_COMPLETES` (x2)
- **`models/paiements/ModelPaiement.php`** : `v_inscriptions_completes` → `VIEWS::INSCRIPTIONS_COMPLETES` (x2), `v_paiements_complets` → `VIEWS::PAIEMENTS_COMPLETS` (x4), `v_bilan_paiements_inscriptions` → `VIEWS::BILAN_PAIEMENTS_INSCRIPTIONS` (x2)

---

## 5. Statuts codés en dur au lieu de `STATUTS::*` (38 fichiers)

- **`core/Auth.php`** : `'actif'` (x1)
- **`core/BaseController.php`** : `'actif'` (x4)
- **`core/PrincipalRoute.php`** : `'actif'` (x1)
- **`models/ModelFiliereCycle.php`** : `'actif'` (x3)
- **`models/absences/ModelAbsence.php`** : `'annule'` (x1)
- **`models/accessoires/ModelAccessoire.php`** : `'actif'` (x10)
- **`models/annees/ModelAnnee.php`** : `'annule'` (x3), `'actif'` (x2)
- **`models/arrieres/ModelArriere.php`** : `'annule'` (x3)
- **`models/bulletin/ModelBulletin.php`** : `'actif'` (x9)
- **`models/caisse_annexe/ModelCaisseAnnexe.php`** : `'annule'` (x5), `'actif'` (x4)
- **`models/classes/ModelClasse.php`** : `'actif'` (x6), `'annule'` (x1)
- **`models/cycles/ModelCycle.php`** : `'actif'` (x3)
- **`models/depenses/ModelDepense.php`** : `'annule'` (x3), `'en_attente'` (x2)
- **`models/dossier_etudiant/ModelDossierEtudiant.php`** : `'actif'` (x2)
- **`models/emplois_temps/ModelEmploi.php`** : `'actif'` (x4)
- **`models/enseignant_matiere/ModelEnseignantMatiere.php`** : `'actif'` (x1)
- **`models/enseignants/ModelEnseignant.php`** : `'actif'` (x3)
- **`models/etudiants/ModelEtudiant.php`** : `'annule'` (x6), `'actif'` (x11)
- **`models/filieres/ModelFiliere.php`** : `'actif'` (x9)
- **`models/frais_annexes/ModelFraisAnnexe.php`** : `'actif'` (x2)
- **`models/home/ModelHome.php`** : `'actif'` (x15), `'annule'` (x6), `'en_attente'` (x1)
- **`models/impayes/ModelImpayes.php`** : `'annule'` (x4), `'actif'` (x3)
- **`models/inscriptions/ModelInscription.php`** : `'annule'` (x11), `'actif'` (x13)
- **`models/matieres/ModelMatiere.php`** : `'actif'` (x1)
- **`models/niveaux/ModelNiveau.php`** : `'actif'` (x6)
- **`models/notes/ModelNote.php`** : `'actif'` (x5), `'annule'` (x1)
- **`models/paiements/ModelPaiement.php`** : `'annule'` (x19), `'actif'` (x15), `'solde'` (x2), `'valide'` (x1)
- **`models/parents/ModelParent.php`** : `'actif'` (x1)
- **`models/permissions/ModelPermission.php`** : `'actif'` (x1)
- **`models/pieces_fournir/ModelPieceFournir.php`** : `'actif'` (x4), `'inactif'` (x1)
- **`models/pieces_fournir/ModelPieceFournirCycle.php`** : `'actif'` (x3)
- **`models/roles/ModelRole.php`** : `'actif'` (x3)
- **`models/salles/ModelSalle.php`** : `'actif'` (x1)
- **`models/scolarites/ModelScolarite.php`** : `'actif'` (x1)
- **`models/semestres/ModelSemestre.php`** : `'actif'` (x1)
- **`models/sessions_caisse/ModelSessionCaisse.php`** : `'annule'` (x2)
- **`models/tranches_scolarite/ModelTranche.php`** : `'actif'` (x1)
- **`models/users/ModelUser.php`** : `'actif'` (x5)

---

## 6. Variables de Session accédées en dur (`$_SESSION[...]`) au lieu de `Session::*` / `Auth::*` (43 fichiers)

- **`core/BaseController.php`** : `$_SESSION['annee_active_code']` (x7), `$_SESSION['annee_active_libelle']` (x6), `$_SESSION['etablissement_active_code']` (x3), `$_SESSION[USERS_AUTH]` (x6), `$_SESSION['flash_success']` (x2), `$_SESSION['flash_error']` (x2), `$_SESSION['etablissement_config']` (x5), `$_SESSION['role_code']` (x1)
- **`core/PrincipalRoute.php`** : `$_SESSION['annee_active_code']` (x2), `$_SESSION['annee_active_libelle']` (x1)
- **`models/Validator.php`** : `$_SESSION['roles']` (x1), `$_SESSION['csrf_token']` (x8), `$_SESSION['csrf_token_time']` (x4), `$_SESSION[USERS_AUTH]` (x2)
- **`models/home/ModelHome.php`** : `$_SESSION['annee_active_code']` (x5), `$_SESSION['annee_active_libelle']` (x1)
- **`public/inc/header.php`** : `$_SESSION[USERS_AUTH]` (x10)
- **`public/inc/nav.php`** : `$_SESSION['annee_active_code']` (x4), `$_SESSION['annee_active_libelle']` (x4), `$_SESSION['flash_success']` (x3), `$_SESSION['flash_error']` (x3), `$_SESSION[USERS_AUTH]` (x7)
- **`public/inc/securite.php`** : `$_SESSION['client']` (x1)
- **`public/inc/sidbar.php`** : `$_SESSION['annee_active_libelle']` (x3)
- **`views/absences/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/accessoires/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/accessoires/registre.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/arrieres/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/bulletin/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/bulletin/pv_classe.php`** : `$_SESSION['annee_active_libelle']` (x1)
- **`views/caisse_annexe/list.php`** : `$_SESSION['prenom_utilisateur']` (x1), `$_SESSION['nom_utilisateur']` (x1), `$_SESSION['annee_active_libelle']` (x1)
- **`views/caisse_annexe/print.php`** : `$_SESSION['nom_user']` (x1), `$_SESSION['prenom_user']` (x1)
- **`views/classes/edit.php`** : `$_SESSION['annee_active_code']` (x1), `$_SESSION['annee_active_libelle']` (x1)
- **`views/classes/list.php`** : `$_SESSION['annee_active_code']` (x1), `$_SESSION['annee_active_libelle']` (x1)
- **`views/compositions/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/depenses/list.php`** : `$_SESSION['annee_active_code']` (x1), `$_SESSION[USERS_AUTH]` (x4)
- **`views/dossier_etudiant/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/emplois_temps/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/enseignant_matiere/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/errors/403.php`** : `$_SESSION[USERS_AUTH]` (x1)
- **`views/errors/404.php`** : `$_SESSION[USERS_AUTH]` (x1)
- **`views/errors/500.php`** : `$_SESSION[USERS_AUTH]` (x1)
- **`views/etablissements/config.php`** : `$_SESSION['flash_success']` (x3), `$_SESSION['flash_error']` (x3)
- **`views/etudiants/wizard.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/home/index.php`** : `$_SESSION[USERS_AUTH]` (x5), `$_SESSION['permissions']` (x1)
- **`views/impayes/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/inscriptions/edit.php`** : `$_SESSION['annee_active_libelle']` (x1), `$_SESSION['annee_active_code']` (x1)
- **`views/inscriptions/list.php`** : `$_SESSION['annee_active_libelle']` (x1)
- **`views/inscriptions/prise_de_vue.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/inscriptions/sans_photo.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/notes/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/notes/saisie_classe.php`** : `$_SESSION['annee_active_code']` (x1), `$_SESSION['annee_active_libelle']` (x1)
- **`views/paiements/comptabilite_etudiants.php`** : `$_SESSION['annee_active_libelle']` (x1)
- **`views/paiements/list.php`** : `$_SESSION['prenom_utilisateur']` (x1), `$_SESSION['nom_utilisateur']` (x1), `$_SESSION['annee_active_libelle']` (x2)
- **`views/scolarites/edit.php`** : `$_SESSION['annee_active_code']` (x1), `$_SESSION['annee_active_libelle']` (x1)
- **`views/semestres/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/sessions_caisse/list.php`** : `$_SESSION['annee_active_code']` (x1)
- **`views/users/editPassword.php`** : `$_SESSION[USERS_AUTH]` (x1)
- **`views/users/profil.php`** : `$_SESSION[USERS_AUTH]` (x3)

---

## 7. Variables PHP locales déclarées dans les Vues et Includes (138 fichiers)

- **`public/inc/footer-link.php`** (3 var.) : `$scriptClean`, `$physicalFile`, `$v`
- **`public/inc/header.php`** (10 var.) : `$currentUserCode`, `$currentRoleCode`, `$currentRoles`, `$isSuperAdmin`, `$userRoles`, `$currentUserName`, `$currentUserEmail`, `$currentUserPhoto`, `$recentAdminNotifs`, `$unreadNotifsCount`
- **`public/inc/nav.php`** (22 var.) : `$activeAnneeCode`, `$activeAnneeLibelle`, `$dbNav`, `$allAnneesNav`, `$foundNav`, `$userCodeNav`, `$userPermsNav`, `$hasGlobalViewNav`, `$userAccesNav`, `$stNav`, `$isCurrentActiveDb`, `$isSelected`, `$canAccessThisYear`, `$accessLevelThisYear`, `$navRoles`, `$isPastAnneeNav`, `$isReadOnlyAnneeNav`, `$uCodeBanner`, `$uPermsBanner`, `$hasEditPermBanner`, `$stChk`, `$lvlNav`
- **`public/inc/sidbar.php`** (9 var.) : `$etabCfg`, `$rawLogo`, `$globalEtablissementLogo`, `$globalEtablissementNom`, `$currentUri`, `$isSuperAdmin`, `$permMap`, `$canAccess`, `$isActiveRoute`
- **`views/absences/details.php`** (2 var.) : `$item`, `$isJustifie`
- **`views/absences/edit.php`** (9 var.) : `$classes`, `$matieres`, `$etudiantsDetails`, `$studentsByClass`, `$cCode`, `$nomAffiche`, `$selectedClasseCode`, `$selectedEtudiantCode`, `$pageScripts`
- **`views/absences/list.php`** (5 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/absences/saisie_classe.php`** (12 var.) : `$classes`, `$matieres`, `$selectedClasseCode`, `$selectedDate`, `$selectedMatiereCode`, `$etudiants`, `$existingAbsences`, `$etudCode`, `$hasAbsence`, `$abs`, `$isJustifiee`, `$pageScripts`
- **`views/accessoires/details.php`** (2 var.) : `$item`, `$tf`
- **`views/accessoires/list.php`** (6 var.) : `$etudiants`, `$accessoires`, `$stats`, `$annees`, `$selectedAnneeCode`, `$pageScripts`
- **`views/accessoires/registre.php`** (7 var.) : `$etudiants`, `$accessoires`, `$stats`, `$annees`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/annees/details.php`** (7 var.) : `$item`, `$stats`, `$classes`, `$semestres`, `$stAnnee`, `$clIdCrypte`, `$isClActif`
- **`views/annees/list.php`** (1 var.) : `$pageScripts`
- **`views/arrieres/list.php`** (5 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$stats`
- **`views/bulletin/details.php`** (16 var.) : `$item`, `$encryptedId`, `$semestres`, `$selectedSemestreCode`, `$matieresNotes`, `$totalPoints`, `$totalCoefficients`, `$moyenneGenerale`, `$mention`, `$decision`, `$rang`, `$totalElevesClasse`, `$moyenneMinClasse`, `$moyenneMaxClasse`, `$moyenneMoyClasse`, `$heuresAbsences`
- **`views/bulletin/edit.php`** (15 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$pageScripts`
- **`views/bulletin/list.php`** (5 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/bulletin/pv_classe.php`** (11 var.) : `$classes`, `$semestres`, `$selectedClasseCode`, `$selectedSemestreCode`, `$classeInfo`, `$matieres`, `$pvRows`, `$statsClasse`, `$encryptedClasseCode`, `$isAdmis`, `$val`
- **`views/caisse_annexe/list.php`** (9 var.) : `$isCaisseOuverte`, `$activeSession`, `$encryptedSessionId`, `$canRecord`, `$canOpenCaisse`, `$canCloseCaisse`, `$photoPath`, `$photoUrl`, `$pageScripts`
- **`views/caisse_annexe/print.php`** (14 var.) : `$anneeLibelle`, `$paiements`, `$stats`, `$sumTable`, `$dateDebutFmt`, `$dateFinFmt`, `$periodeTexte`, `$agentNom`, `$i`, `$mt`, `$mode`, `$isEspece`, `$isDig`, `$badgeCls`
- **`views/classes/details.php`** (10 var.) : `$item`, `$etudiants`, `$matieres`, `$capacite`, `$nbInscrits`, `$tauxRemplissage`, `$encryptedId`, `$dob`, `$dobFormatted`, `$isAffecte`
- **`views/classes/edit.php`** (8 var.) : `$filieres`, `$exists`, `$currentFil`, `$niveaux`, `$annees`, `$currentAnneeCode`, `$currentAnneeLibelle`, `$pageScripts`
- **`views/classes/list.php`** (10 var.) : `$rawFilieres`, `$filieres`, `$niveaux`, `$currentAnneeCode`, `$canEditClasse`, `$canManageClasses`, `$cSlug`, `$fSlug`, `$nSlug`, `$pageScripts`
- **`views/compositions/details.php`** (11 var.) : `$item`, `$targetClasses`, `$matieres`, `$encryptedId`, `$typ`, `$badgeBg`, `$rowNum`, `$classeCode`, `$targetName`, `$nbMatieres`, `$pageScripts`
- **`views/compositions/edit.php`** (22 var.) : `$item`, `$annees`, `$niveaux`, `$filieres`, `$classes`, `$matieres`, `$activeAnneeCode`, `$semestres`, `$salles`, `$teachersWithUsers`, `$activeAnneeLibelle`, `$selectedClasseCode`, `$selectedMatiereCode`, `$selectedType`, `$isEditMode`, `$existingNiveaux`, `$currentType`, `$rowNivCode`, `$rowFilCodes`, `$isNewRow`, `$isChecked`, `$pageScripts`
- **`views/compositions/list.php`** (6 var.) : `$annees`, `$niveaux`, `$classes`, `$matieres`, `$selectedAnneeCode`, `$pageScripts`
- **`views/cycles/details.php`** (3 var.) : `$item`, `$stats`, `$filieres`
- **`views/depenses/details.php`** (14 var.) : `$item`, `$montant`, `$codeDepense`, `$statut`, `$dateEngagement`, `$dateCreation`, `$dateModification`, `$dateConfirm`, `$beneficiaire`, `$modeReglement`, `$labelsMode`, `$modePaiementLibelle`, `$auteur`, `$confirmateur`
- **`views/depenses/edit.php`** (15 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$pageScripts`
- **`views/depenses/list.php`** (8 var.) : `$stats`, `$annees`, `$selectedAnneeCode`, `$typeDepenses`, `$canValidate`, `$canRecord`, `$depensesListConfig`, `$pageScripts`
- **`views/documents/details.php`** (2 var.) : `$item`, `$fileUrl`
- **`views/documents/edit.php`** (15 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$pageScripts`
- **`views/documents/list.php`** (1 var.) : `$pageScripts`
- **`views/dossier_etudiant/list.php`** (4 var.) : `$annees`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/emplois_temps/details.php`** (1 var.) : `$item`
- **`views/emplois_temps/edit.php`** (20 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$currentClasse`, `$activeClasseLibelle`, `$nomProf`, `$gradeProf`, `$labelProf`, `$pageScripts`
- **`views/emplois_temps/list.php`** (5 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/enseignant_matiere/details.php`** (2 var.) : `$item`, `$nbNotes`
- **`views/enseignant_matiere/edit.php`** (5 var.) : `$classes`, `$matieres`, `$enseignants`, `$nomAffiche`, `$pageScripts`
- **`views/enseignant_matiere/list.php`** (7 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$matieres`, `$enseignants`, `$pageScripts`
- **`views/enseignants/details.php`** (2 var.) : `$item`, `$cours`
- **`views/enseignants/edit.php`** (4 var.) : `$item`, `$availableUsers`, `$isEdit`, `$pageScripts`
- **`views/enseignants/list.php`** (1 var.) : `$pageScripts`
- **`views/errors/403.php`** (3 var.) : `$message`, `$permissionCode`, `$auth`
- **`views/errors/404.php`** (2 var.) : `$message`, `$auth`
- **`views/errors/500.php`** (2 var.) : `$message`, `$auth`
- **`views/etablissements/config.php`** (3 var.) : `$rawLogo`, `$logoSrc`, `$pageScripts`
- **`views/etudiants/changer_classe.php`** (11 var.) : `$item`, `$inscription`, `$classes`, `$annees`, `$encryptedId`, `$activeAnneeCode`, `$nomComplet`, `$classeActuelle`, `$regimeActuel`, `$isAffecte`, `$pageScripts`
- **`views/etudiants/details.php`** (24 var.) : `$item`, `$parent`, `$inscription`, `$etablissement`, `$paiements`, `$absences`, `$scolariteTotale`, `$totalPaye`, `$soldeRestant`, `$tauxPaiement`, `$nomComplet`, `$matricule`, `$classeLibelle`, `$anneeLibelle`, `$etabNom`, `$etabTel`, `$etabEmail`, `$etabAdresse`, `$encryptedId`, `$totalPieces`, `$deposedCount`, `$isComplet`, `$isDep`, `$pageScripts`
- **`views/etudiants/edit.php`** (9 var.) : `$encryptedId`, `$valDateNais`, `$rawDate`, `$ts`, `$valLieuNais`, `$valNationalite`, `$valResidence`, `$etudiantsEditConfig`, `$pageScripts`
- **`views/etudiants/list.php`** (7 var.) : `$annees`, `$niveaux`, `$filieres`, `$classes`, `$anneeActive`, `$displayFil`, `$pageScripts`
- **`views/etudiants/wizard.php`** (18 var.) : `$annees`, `$modelAnnee`, `$activeYearRow`, `$activeAnneeCode`, `$classes`, `$accessoires`, `$pieces`, `$kitTypeFiliere`, `$niveauCode`, `$libelleNiveau`, `$filiereBadgeText`, `$filiereBadgeBg`, `$filiereBadgeColor`, `$niveauBadgeText`, `$nbExemplaires`, `$natureDoc`, `$natureLabel`, `$pageScripts`
- **`views/evenements/details.php`** (1 var.) : `$item`
- **`views/evenements/edit.php`** (14 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`
- **`views/evenements/list.php`** (1 var.) : `$pageScripts`
- **`views/filiere_cycles/details.php`** (2 var.) : `$item`, `$classes`
- **`views/filiere_cycles/edit.php`** (2 var.) : `$filieresList`, `$cyclesList`
- **`views/filiere_cycles/hub.php`** (1 var.) : `$pageScripts`
- **`views/filieres/details.php`** (5 var.) : `$item`, `$cycles`, `$classes`, `$stats`, `$tf`
- **`views/filieres/list.php`** (4 var.) : `$cycles`, `$filieres`, `$niveaux`, `$pageScripts`
- **`views/fonctions/details.php`** (2 var.) : `$item`, `$users`
- **`views/fonctions/list.php`** (1 var.) : `$pageScripts`
- **`views/frais_annexes/list.php`** (1 var.) : `$pageScripts`
- **`views/galeries/details.php`** (3 var.) : `$item`, `$typeMedia`, `$urlMedia`
- **`views/galeries/edit.php`** (15 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$pageScripts`
- **`views/galeries/list.php`** (1 var.) : `$pageScripts`
- **`views/home/index.php`** (29 var.) : `$stats`, `$annees`, `$auth`, `$recentInscriptions`, `$recentPaiements`, `$recentDepenses`, `$teacherCourses`, `$rawPerms`, `$userPermissions`, `$hasPerm`, `$isAdminOrDG`, `$isFinance`, `$isPedagogie`, `$isScolarite`, `$isEnseignant`, `$isCommunication`, `$canViewActions`, `$monthlyFinancials`, `$filieresDistribution`, `$guichetAlerts`, `$caAttendu`, `$caEncaisse`, `$tauxRecouvrement`, `$canGroup1`, `$canGroup2`, `$canGroup3`, `$canGroup4`, `$canGroup5`, `$pageScripts`
- **`views/impayes/details.php`** (2 var.) : `$item`, `$montantDu`
- **`views/impayes/list.php`** (6 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$canSendRelance`, `$canRecordPayment`
- **`views/inscriptions/details.php`** (8 var.) : `$item`, `$paiements`, `$scolarite`, `$totalPaye`, `$solde`, `$tauxPaiement`, `$encryptedId`, `$pageScripts`
- **`views/inscriptions/edit.php`** (21 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$annees`, `$globalEtablissementLogo`, `$globalEtablissementNom`, `$selectedEtu`, `$isReadonly`, `$selectedAnneeIns`, `$pageScripts`
- **`views/inscriptions/list.php`** (1 var.) : `$pageScripts`
- **`views/inscriptions/prise_de_vue.php`** (5 var.) : `$annees`, `$filieres`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/inscriptions/sans_photo.php`** (5 var.) : `$annees`, `$filieres`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/matieres/details.php`** (3 var.) : `$item`, `$affectations`, `$stats`
- **`views/matieres/list.php`** (1 var.) : `$pageScripts`
- **`views/niveaux/details.php`** (3 var.) : `$item`, `$classes`, `$stats`
- **`views/niveaux/list.php`** (1 var.) : `$pageScripts`
- **`views/notes/details.php`** (3 var.) : `$item`, `$encryptedId`, `$noteVal`
- **`views/notes/edit.php`** (16 var.) : `$anneeCode`, `$anneeLibelle`, `$niveaux`, `$classes`, `$matieres`, `$semestres`, `$inscriptionsDetails`, `$studentsByClass`, `$cCode`, `$nomAffiche`, `$selectedInscriptionCode`, `$selectedClasseCode`, `$typeEvals`, `$selectedTypeEval`, `$studentCount`, `$pageScripts`
- **`views/notes/list.php`** (5 var.) : `$annees`, `$niveaux`, `$classes`, `$selectedAnneeCode`, `$pageScripts`
- **`views/notes/partials/grille_notes_table.php`** (10 var.) : `$lblClasse`, `$lblMatiere`, `$lblSemestre`, `$typeLabel`, `$finalLibelle`, `$inscCode`, `$note`, `$valNote`, `$obs`, `$isAbsent`
- **`views/notes/saisie_classe.php`** (25 var.) : `$anneeCode`, `$anneeLibelle`, `$classes`, `$matieres`, `$semestres`, `$selectedClasseCode`, `$selectedMatiereCode`, `$selectedSemestreCode`, `$selectedTypeEval`, `$selectedCompositionCode`, `$selectedLibelleEval`, `$selectedCoefEval`, `$tousExamens`, `$etudiants`, `$existingNotes`, `$compositionsProgrammees`, `$typeEvals`, `$coef`, `$isSel`, `$jsonClasses`, `$isParamsVisible`, `$coefVal`, `$coefSuffix`, `$profSuffix`, `$pageScripts`
- **`views/paiements/comptabilite_etudiants.php`** (12 var.) : `$isCaisseOuverte`, `$activeSession`, `$encryptedSessionId`, `$canRecord`, `$canOpenCaisse`, `$canCloseCaisse`, `$annees`, `$selectedAnneeCode`, `$niveaux`, `$classes`, `$comptaConfig`, `$pageScripts`
- **`views/paiements/details.php`** (49 var.) : `$item`, `$encryptedId`, `$montantOp`, `$scolarite`, `$totalPayeCumul`, `$soldeRestant`, `$numRecu`, `$datePaiement`, `$datePrint`, `$matricule`, `$nomComplet`, `$filiereLib`, `$niveauLib`, `$filiereNiveau`, `$statutAffectation`, `$anneeLibelle`, `$caissierNom`, `$isFirstPayment`, `$totalScolariteCumul`, `$totalFraisAnnexesCumul`, `$trancheCodeItem`, `$catItem`, `$isFAItem`, `$mainRecuTitle`, `$copieRecuTitle`, `$pageTitle`, `$typeOp`, `$opScolarite`, `$opDroit`, `$opFA`, `$montantOpAfficher`, `$montantEnLettres`, `$refSeed`, `$refCaissHash1`, `$refCaissHash2`, `$refCaiss`, `$barcodeCode`, `$studentPhotoUrl`, `$rawPhoto`, `$cleanPhotoPath`, `$slugNom`, `$anneeFolder`, `$potentialPath`, `$placeholderRel`, `$code`, `$generator`, `$pngData`, `$logoSrc`, `$pageScripts`
- **`views/paiements/list.php`** (11 var.) : `$isCaisseOuverte`, `$activeSession`, `$encryptedSessionId`, `$canRecord`, `$canOpenCaisse`, `$canCloseCaisse`, `$canCancelPaiement`, `$photoPath`, `$photoUrl`, `$paiementsListConfig`, `$pageScripts`
- **`views/parents/details.php`** (1 var.) : `$item`
- **`views/parents/edit.php`** (15 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$pageScripts`
- **`views/parents/list.php`** (1 var.) : `$pageScripts`
- **`views/permissions/details.php`** (1 var.) : `$item`
- **`views/permissions/edit.php`** (5 var.) : `$item`, `$isEdit`, `$existingModules`, `$curMod`, `$pageScripts`
- **`views/permissions/list.php`** (1 var.) : `$pageScripts`
- **`views/piece_fournir_cycle/edit.php`** (3 var.) : `$editNiveaux`, `$pieceFournirCycleEditConfig`, `$pageScripts`
- **`views/piece_fournir_cycle/list.php`** (2 var.) : `$pieceFournirCycleListConfig`, `$pageScripts`
- **`views/pieces_fournir/edit.php`** (2 var.) : `$isEdit`, `$pageScripts`
- **`views/pieces_fournir/list.php`** (2 var.) : `$piecesFournirListConfig`, `$pageScripts`
- **`views/roles/details.php`** (3 var.) : `$role`, `$permissions`, `$users`
- **`views/roles/edit.php`** (8 var.) : `$role`, `$allPermissions`, `$assignedCodes`, `$grpIdx`, `$grpId`, `$totalCount`, `$checkedCount`, `$pageScripts`
- **`views/roles/list.php`** (1 var.) : `$pageScripts`
- **`views/salles/details.php`** (2 var.) : `$item`, `$planning`
- **`views/salles/list.php`** (1 var.) : `$pageScripts`
- **`views/scolarites/details.php`** (6 var.) : `$item`, `$tranches`, `$montantScolarite`, `$totalTranches`, `$diffTranches`, `$nbTranches`
- **`views/scolarites/edit.php`** (20 var.) : `$annees`, `$currentAnneeCode`, `$cycles`, `$filieres`, `$niveaux`, `$filiereCyclesMap`, `$cycleToNiveaux`, `$filiereToCycles`, `$cCode`, `$fCode`, `$nCode`, `$currentAnneeLibelle`, `$selectedFiliereCycles`, `$isCycleSelected`, `$typesFilieres`, `$selectedTypeFiliere`, `$fCycles`, `$cyclesAttr`, `$scolariteEditConfig`, `$pageScripts`
- **`views/semestres/details.php`** (2 var.) : `$item`, `$stats`
- **`views/semestres/list.php`** (3 var.) : `$annees`, `$selectedAnneeCode`, `$pageScripts`
- **`views/sessions_caisse/cloturer.php`** (8 var.) : `$session`, `$financials`, `$fondInitial`, `$totalEspeces`, `$totalMobile`, `$totalCheque`, `$totalGeneral`, `$soldeTheorique`
- **`views/sessions_caisse/details.php`** (22 var.) : `$item`, `$paiements`, `$kpis`, `$fondInitial`, `$totalValide`, `$totalAnnule`, `$nbValides`, `$nbAnnules`, `$nbTotal`, `$totalEspeces`, `$totalMobile`, `$totalCheque`, `$totalScolarite`, `$totalFraisAnnexes`, `$soldeAttendu`, `$ecart`, `$logoUrl`, `$isAnnule`, `$rowBg`, `$cat`, `$typeLib`, `$trancheLib`
- **`views/sessions_caisse/list.php`** (7 var.) : `$activeSession`, `$dailyTotals`, `$totalSessions`, `$totalOuvertes`, `$totalCloturees`, `$annees`, `$selectedAnneeCode`
- **`views/templates/pdf/bilan_filiere.php`** (2 var.) : `$logoPath`, `$mockList`
- **`views/templates/pdf/bulletin_notes.php`** (6 var.) : `$logoPath`, `$hasNotes`, `$moy`, `$felicitations`, `$tableauHonneur`, `$encouragements`
- **`views/templates/pdf/catalogue_filieres.php`** (12 var.) : `$logoPath`, `$nomEtab`, `$totalFilieres`, `$nbTertiaire`, `$nbIndustrielle`, `$allCyclesSet`, `$t`, `$cList`, `$cl`, `$nbCyclesDistincts`, `$rowClass`, `$tFiliere`
- **`views/templates/pdf/convocation_examen.php`** (3 var.) : `$logoPath`, `$defaultEcrites`, `$listEcrites`
- **`views/templates/pdf/fiche_etudiant.php`** (1 var.) : `$logoPath`
- **`views/templates/pdf/fiche_versement_etudiant.php`** (4 var.) : `$logoPath`, `$logo_src`, `$placeholderFile`, `$photo_etudiant`
- **`views/templates/pdf/grille_frais_annexes.php`** (19 var.) : `$logoPath`, `$nomEtab`, `$filtreInfo`, `$totalLignes`, `$nbInscription`, `$nbAutre`, `$nbIndustrielle`, `$nbTertiaire`, `$nbToutes`, `$cumulMontantsActifs`, `$nbActifs`, `$tf`, `$rowClass`, `$catLabel`, `$tfLabel`, `$tfStyle`, `$nivLabel`, `$montant`, `$isActif`
- **`views/templates/pdf/grille_scolarites.php`** (8 var.) : `$logoPath`, `$nomEtab`, `$filtreInfo`, `$totalGrilles`, `$nbAffectes`, `$nbNonAffectes`, `$rowClass`, `$isAffecte`
- **`views/templates/pdf/liste_classe.php`** (4 var.) : `$candidateLogos`, `$logoSrc`, `$isAffecte`, `$nomComplet`
- **`views/templates/pdf/liste_etudiants.php`** (6 var.) : `$candidateLogos`, `$logoSrc`, `$isAffecte`, `$nomComplet`, `$rawDate`, `$dateFormatted`
- **`views/templates/pdf/liste_impayes.php`** (2 var.) : `$logoFile`, `$logoSrc`
- **`views/templates/pdf/offre_academique.php`** (4 var.) : `$logoPath`, `$nomEtab`, `$rowClass`, `$typeFiliere`
- **`views/templates/pdf/pv_classe_pdf.php`** (11 var.) : `$classeInfo`, `$matieres`, `$pvRows`, `$statsClasse`, `$selectedClasseCode`, `$selectedSemestreCode`, `$selectedSemestreLibelle`, `$anneeLibelle`, `$logoPath`, `$isAdmis`, `$val`
- **`views/templates/pdf/rappel_scolarite.php`** (8 var.) : `$logoPath`, `$logoSrc`, `$photoSrc`, `$cleanP`, `$candidateP`, `$ext`, `$mime`, `$defaultPlaceholder`
- **`views/templates/pdf/recu_arriere.php`** (1 var.) : `$logoPath`
- **`views/templates/pdf/recu_inscription.php`** (35 var.) : `$code`, `$generator`, `$pngData`, `$logoSrc`, `$logoEicgFile`, `$photo_src`, `$placeholderPhotoFile`, `$annee_universitaire`, `$numero_recu`, `$date_operation`, `$matricule`, `$nom_prenoms`, `$filiere_niveau`, `$statut_etudiant`, `$type_operation`, `$montant_operation`, `$montant_operation_formatted`, `$montant_en_lettres`, `$scolarite_total`, `$total_verse`, `$reste_a_payer`, `$caissier_nom`, `$date_impression`, `$code_barre_val`, `$premiere_tranche`, `$frais_annexes_inscription`, `$droit_tot_payer`, `$droit_op_jour`, `$droit_tot_verse`, `$scolarite_op_jour`, `$scolarite_tot_payer`, `$scolarite_tot_verse`, `$scolarite_reste`, `$initsPDF`, `$pParts`
- **`views/templates/pdf/recu_tenue.php`** (3 var.) : `$logoPath`, `$defaultKits`, `$kitsList`
- **`views/templates/pdf/recu_versement.php`** (7 var.) : `$code`, `$generator`, `$pngData`, `$barcodeVal`, `$badgeText`, `$isSolde`, `$numVersementText`
- **`views/templates/pdf/registre_encaissements.php`** (2 var.) : `$canalVal`, `$isEnLigne`
- **`views/templates/pdf/repertoire_classes.php`** (13 var.) : `$logoPath`, `$nomEtab`, `$totalCount`, `$totalCapacite`, `$activesCount`, `$isEven`, `$libelle`, `$filiere`, `$cycleSlug`, `$niveauSlug`, `$capacite`, `$statut`, `$isActif`
- **`views/templates/pdf/repertoire_salles.php`** (10 var.) : `$logoPath`, `$nomEtab`, `$totalCount`, `$activesCount`, `$inactivesCount`, `$isEven`, `$libelle`, `$capacite`, `$statut`, `$isActif`
- **`views/tranches_scolarite/details.php`** (4 var.) : `$item`, `$montantTranche`, `$montantScolarite`, `$pct`
- **`views/tranches_scolarite/edit.php`** (21 var.) : `$cycles`, `$filieres`, `$niveaux`, `$classes`, `$salles`, `$scolarites`, `$ues`, `$matieres`, `$semestres`, `$etudiants`, `$inscriptions`, `$typeDepenses`, `$users`, `$enseignants`, `$filiereNom`, `$niveauNom`, `$anneeNom`, `$regimeLabel`, `$montantVal`, `$montantFmt`, `$labelOpt`
- **`views/type_depenses/details.php`** (3 var.) : `$item`, `$depenses`, `$totalDepenses`
- **`views/type_depenses/list.php`** (2 var.) : `$typeDepensesConfig`, `$pageScripts`
- **`views/users/connexion.php`** (1 var.) : `$loginLogo`
- **`views/users/details.php`** (3 var.) : `$user`, `$role`, `$displayRoles`
- **`views/users/edit.php`** (10 var.) : `$user`, `$role`, `$roles`, `$fonctions`, `$selectedRoleCodes`, `$allAnneesList`, `$userAnneeAcces`, `$isActif`, `$curVal`, `$pageScripts`
- **`views/users/editPassword.php`** (2 var.) : `$user`, `$pageScripts`
- **`views/users/list.php`** (1 var.) : `$pageScripts`
