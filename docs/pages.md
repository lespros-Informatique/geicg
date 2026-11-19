# Inventaire des Pages avec Filtre de Sélection de l'Année Académique

Ce document répertorie les pages et interfaces de l'application **GEICG** disposant d'un **filtre de sélection d'année académique interactif et modifiable** (les champs verrouillés en `readonly`, `disabled` ou relevant de simples formulaires modaux ont été retirés).

---

## 1. Barre de Navigation Globale (Toutes les pages)

| Composant | Fichier Vue | Élément HTML / ID | Type | Description |
| :--- | :--- | :--- | :--- | :--- |
| **Sélecteur Global Topbar** | [`public/inc/nav.php`](file:///c:/wamp64/www/geicg/public/inc/nav.php#L78) | `#anneeSwitcherBtn` / `#anneeSwitcherPanel` | Bouton déroulant interactif | Permet de basculer dynamiquement l'année académique active de la session courante. |

---

## 2. Module Pédagogie, Notes & Examens

| Route / URL | Fichier Vue | Contrôleur | Élément / ID Filtre | Description |
| :--- | :--- | :--- | :--- | :--- |
| `/note/list` | [`views/notes/list.php`](file:///c:/wamp64/www/geicg/views/notes/list.php#L15) | [`NoteController`](file:///c:/wamp64/www/geicg/controllers/notes/NoteController.php) | `<select id="filter-annee">` | Filtrage des sessions d'évaluation et saisies de notes. |
| `/composition/list` | [`views/compositions/list.php`](file:///c:/wamp64/www/geicg/views/compositions/list.php#L12) | [`CompositionController`](file:///c:/wamp64/www/geicg/controllers/compositions/CompositionController.php) | `<select id="filter-annee">` | Filtrage de la programmation des compositions et examens. |
| `/bulletin/list` | [`views/bulletin/list.php`](file:///c:/wamp64/www/geicg/views/bulletin/list.php#L11) | [`BulletinController`](file:///c:/wamp64/www/geicg/controllers/bulletin/BulletinController.php) | `<select id="filter-annee">` | Filtrage des étudiants pour la génération des bulletins semestriels. |
| `/absence/list` | [`views/absences/list.php`](file:///c:/wamp64/www/geicg/views/absences/list.php#L30) | [`AbsenceController`](file:///c:/wamp64/www/geicg/controllers/absences/AbsenceController.php) | `<select id="filter-annee">` | Filtrage des registres d'appels et d'absences étudiants. |
| `/emploi/list` | [`views/emplois_temps/list.php`](file:///c:/wamp64/www/geicg/views/emplois_temps/list.php#L20) | [`EmploiController`](file:///c:/wamp64/www/geicg/controllers/emplois_temps/EmploiController.php) | `<select id="filter-annee">` | Grille et planning des emplois du temps par classe et semestre. |
| `/maquette/list` | [`views/maquettes/list.php`](file:///c:/wamp64/www/geicg/views/maquettes/list.php#L22) | [`MaquetteController`](file:///c:/wamp64/www/geicg/controllers/maquettes/MaquetteController.php) | `<select id="filter-annee">` | Filtrage des maquettes pédagogiques par filière, niveau et année. |

---

## 3. Module Finances, Caisse & Comptabilité

| Route / URL | Fichier Vue | Contrôleur | Élément / ID Filtre | Description |
| :--- | :--- | :--- | :--- | :--- |
| `/paiement/list` | [`views/paiements/list.php`](file:///c:/wamp64/www/geicg/views/paiements/list.php#L78) | [`PaiementController`](file:///c:/wamp64/www/geicg/controllers/paiements/PaiementController.php) | `<select id="filter-annee">` | Journal des encaissements de scolarité et reçus de caisse. |
| `/paiement/comptabiliteEtudiants` | [`views/paiements/comptabilite_etudiants.php`](file:///c:/wamp64/www/geicg/views/paiements/comptabilite_etudiants.php#L20) | [`PaiementController`](file:///c:/wamp64/www/geicg/controllers/paiements/PaiementController.php) | `<select id="filter_annee_code">` | États financiers des étudiants, soldes et situations de compte. |
| `/impayes/list` | [`views/impayes/list.php`](file:///c:/wamp64/www/geicg/views/impayes/list.php#L113) | [`ImpayesController`](file:///c:/wamp64/www/geicg/controllers/impayes/ImpayesController.php) | `<select id="filter-annee">` | Suivi des retards de versements, échéances échues et relances. |
| `/arrieres/list` | [`views/arrieres/list.php`](file:///c:/wamp64/www/geicg/views/arrieres/list.php#L32) | [`ArriereController`](file:///c:/wamp64/www/geicg/controllers/arrieres/ArriereController.php) | `<select id="filter-annee-origine">` | Apurement des dettes scolaires antérieures par année d'origine. |
| `/sessioncaisse/list` | [`views/sessions_caisse/list.php`](file:///c:/wamp64/www/geicg/views/sessions_caisse/list.php#L51) | [`SessionCaisseController`](file:///c:/wamp64/www/geicg/controllers/sessions_caisse/SessionCaisseController.php) | `<select id="filter-annee">` | Sessions de caisse journalières, ouvertures et clôtures. |
| `/caisseannexe/list` | [`views/caisse_annexe/list.php`](file:///c:/wamp64/www/geicg/views/caisse_annexe/list.php#L88) | [`CaisseAnnexeController`](file:///c:/wamp64/www/geicg/controllers/caisse_annexe/CaisseAnnexeController.php) | `<select id="filter-annee">` | Caisse annexe (uniformes, kits d'inscription, macarons). |
| `/fraisannexe/list` | [`views/frais_annexes/list.php`](file:///c:/wamp64/www/geicg/views/frais_annexes/list.php#L29) | [`FraisAnnexeController`](file:///c:/wamp64/www/geicg/controllers/frais_annexes/FraisAnnexeController.php) | `<select id="filter-annee">` | Grille tarifaire des frais annexes par niveau et filière. |
| `/scolarite/list` | [`views/scolarites/list.php`](file:///c:/wamp64/www/geicg/views/scolarites/list.php#L38) | [`ScolariteController`](file:///c:/wamp64/www/geicg/controllers/scolarites/ScolariteController.php) | `<select id="filter-annee">` | Grille tarifaire des droits de scolarité et tranches d'échéancier. |

---

## 4. Module Scolarité & Inscriptions

| Route / URL | Fichier Vue | Contrôleur | Élément / ID Filtre | Description |
| :--- | :--- | :--- | :--- | :--- |
| `/inscription/list` | [`views/inscriptions/list.php`](file:///c:/wamp64/www/geicg/views/inscriptions/list.php#L47) | [`InscriptionController`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php) | `<select id="filter-annee">` | Registre général des réinscriptions et inscriptions annuelles. |
| `/inscription/priseDeVue` | [`views/inscriptions/prise_de_vue.php`](file:///c:/wamp64/www/geicg/views/inscriptions/prise_de_vue.php#L25) | [`InscriptionController`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php) | `<select id="filter-annee">` | Prise de vue photographique des étudiants par promotion. |
| `/inscription/sansPhoto` | [`views/inscriptions/sans_photo.php`](file:///c:/wamp64/www/geicg/views/inscriptions/sans_photo.php#L25) | [`InscriptionController`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php) | `<select id="filter-annee">` | Liste des dossiers d'étudiants en attente de photo d'identité. |
| `/dossieretudiant/list` | [`views/dossier_etudiant/list.php`](file:///c:/wamp64/www/geicg/views/dossier_etudiant/list.php#L26) | [`DossierEtudiantController`](file:///c:/wamp64/www/geicg/controllers/dossier_etudiant/DossierEtudiantController.php) | `<select id="filter-annee">` | Suivi des pièces à fournir et complétude des dossiers. |
| `/accessoire/list` | [`views/accessoires/list.php`](file:///c:/wamp64/www/geicg/views/accessoires/list.php#L42) | [`AccessoireController`](file:///c:/wamp64/www/geicg/controllers/accessoires/AccessoireController.php) | `<select id="filter-annee">` | Suivi des dotations en accessoires et kits étudiants. |
| `/accessoire/registre` | [`views/accessoires/registre.php`](file:///c:/wamp64/www/geicg/views/accessoires/registre.php#L36) | [`AccessoireController`](file:///c:/wamp64/www/geicg/controllers/accessoires/AccessoireController.php) | `<select id="filter-annee">` | Registre d'émargement pour la remise des fournitures scolaires. |

---

## 5. Module Structure Académique & Enseignants

| Route / URL | Fichier Vue | Contrôleur | Élément / ID Filtre | Description |
| :--- | :--- | :--- | :--- | :--- |
| `/classe/list` | [`views/classes/list.php`](file:///c:/wamp64/www/geicg/views/classes/list.php#L57) | [`ClasseController`](file:///c:/wamp64/www/geicg/controllers/classes/ClasseController.php) | `<select id="filter-annee-classe">` | Filtrage des classes créées pour une année donnée. |
| `/enseignant/list` | [`views/enseignants/list.php`](file:///c:/wamp64/www/geicg/views/enseignants/list.php#L95) | [`EnseignantController`](file:///c:/wamp64/www/geicg/controllers/enseignants/EnseignantController.php) | `<select id="filter-annee">` | Affectation des professeurs et volume d'enseignement annuel. |

---

## 6. Éléments Retirés de la Liste

1. **`/etudiant/list`** ([`views/etudiants/list.php`](file:///c:/wamp64/www/geicg/views/etudiants/list.php#L54)) :
   - Le sélecteur `<select id="filter-annee">` possède les attributs `readonly tabindex="-1"` et `style="cursor: not-allowed; pointer-events: none;"` (figé sur la session active).
2. **`/semestre/list`** ([`views/semestres/list.php`](file:///c:/wamp64/www/geicg/views/semestres/list.php#L81)) :
   - Le champ `<select name="annee_code" id="semestre_annee">` est situé dans un formulaire modal d'ajout et non dans la barre de filtrage du tableau.
