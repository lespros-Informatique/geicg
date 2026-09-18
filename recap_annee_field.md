# Récapitulatif des Interfaces du Projet GEICG avec Champ / Filtre Année Académique

Document généré suite à l'analyse intégrale des vues du projet GEICG (`/views`).
Ce document recense toutes les vues/interfaces disposant d'un champ ou filtre d'année académique, réparties par type d'interaction : **READONLY** (verrouillé / imposé) et **NON-READONLY** (interactif / modifiable).

---

## 🔒 1. GROUPE 1 : Interfaces avec Champ/Filtre Année en READONLY (Verrouillé / Session Active)

Ces interfaces intègrent un champ ou filtre d'année académique qui est **bloqué en lecture seule** (attribut `readonly`, `disabled`, `pointer-events: none`, ou valeur injectée via un champ `hidden`), généralement imposé par l'année académique active en session utilisateur.

| N° | Fichier Vue | Libellé / Identifiant du Champ | Nature du Champ & Justification Technique |
|---|---|---|---|
| 1 | [`views/etudiants/list.php`](file:///var/www/html/geicg/views/etudiants/list.php#L84) | `<select id="filter-annee">` | **Filtre Année Liste Étudiants** : Verrouillé sur la session active via `readonly tabindex="-1"` et style CSS `cursor: not-allowed; pointer-events: none;`. |
| 2 | [`views/classes/edit.php`](file:///var/www/html/geicg/views/classes/edit.php#L52) | `<select>` + `<input type="hidden" name="annee_code">` | **Formulaire Classe** : Champ année désactivé avec `readonly tabindex="-1"` et `pointer-events: none;`, forcé sur la session active. |
| 3 | [`views/scolarites/edit.php`](file:///var/www/html/geicg/views/scolarites/edit.php#L118) | `<select>` + `<input type="hidden" name="annee_code">` | **Formulaire Barème Scolarité** : Champ année verrouillé en lecture seule avec `readonly tabindex="-1"` et `pointer-events: none;`. |
| 4 | [`views/compositions/edit.php`](file:///var/www/html/geicg/views/compositions/edit.php#L87) | `<select>` + `<input type="hidden" name="annee_code">` | **Formulaire Examen / Composition** : Liste déroulante désactivée (`disabled`), verrouillée sur l'année active. |
| 5 | [`views/notes/saisie_classe.php`](file:///var/www/html/geicg/views/notes/saisie_classe.php#L78) | `<input type="text" class="input-readonly" readonly>` | **Saisie des Notes par Classe** : Champ textuel d'affichage de l'année en `readonly` couplé à un champ masqué (`annee_code`). |
| 6 | [`views/notes/edit.php`](file:///var/www/html/geicg/views/notes/edit.php#L189) | Texte statique + `<input type="hidden" name="annee_code">` | **Formulaire Édition Note** : Libellé fixe non modifiable avec transmission masquée du code de l'année. |

---

## 🔓 2. GROUPE 2 : Interfaces avec Champ/Filtre Année NON-READONLY (Modifiable / Sélectionnable)

Ces interfaces intègrent une liste déroulante ou un filtre d'année académique **totalement interactif** permettant à l'utilisateur de choisir ou de naviguer à travers différentes années académiques.

> **Note d'Ingénierie & Alignement Navbar** : L'ensemble des 21 contrôleurs et vues ci-dessous utilise désormais la méthode centralisée `getAccessibleAnnees()`. Les options proposées dans leurs sélecteurs d'année sont **strictement identiques** aux années autorisées et affichées dans le sélecteur d'année de la navbar (`public/inc/nav.php`).


| N° | Fichier Vue | Libellé / Identifiant du Champ | Nature du Champ & Rôle Fonctionnel |
|---|---|---|---|
| 1 | [`views/inscriptions/list.php`](file:///var/www/html/geicg/views/inscriptions/list.php#L73) | `<select id="filter-annee" class="select2">` | **Filtre Liste Inscriptions** : Permet de choisir n'importe quelle année pour consulter les étudiants inscrits. |
| 2 | [`views/inscriptions/prise_de_vue.php`](file:///var/www/html/geicg/views/inscriptions/prise_de_vue.php#L31) | `<select id="filter-annee" class="select2">` | **Filtre Studio Photos** : Filtre interactif pour cibler la prise de vue des étudiants par année. |
| 3 | [`views/inscriptions/sans_photo.php`](file:///var/www/html/geicg/views/inscriptions/sans_photo.php#L31) | `<select id="filter-annee" class="select2">` | **Filtre Relance Photo** : Permet de filtrer les étudiants sans photo de n'importe quelle année. |
| 4 | [`views/inscriptions/edit.php`](file:///var/www/html/geicg/views/inscriptions/edit.php#L353) | `<select id="sel_annee_inscription" name="annee_code">` | **Formulaire Inscription / Réinscription** : Sélection dynamique de l'année d'inscription. |
| 5 | [`views/etudiants/wizard.php`](file:///var/www/html/geicg/views/etudiants/wizard.php#L296) | `<select id="wiz_annee" name="annee_code">` | **Wizard Inscription Étudiant** : Sélection interactive de l'année académique d'intégration. |
| 6 | [`views/paiements/list.php`](file:///var/www/html/geicg/views/paiements/list.php#L118) | `<select id="filter-annee" class="select2">` | **Filtre Journal des Encaissements** : Filtre dynamique pour consulter les reçus/paiements par année. |
| 7 | [`views/scolarites/list.php`](file:///var/www/html/geicg/views/scolarites/list.php#L24) | `<select id="filter-annee" class="select2">` | **Filtre Barèmes Scolarité** : Permet de consulter les tarifs et tranches selon l'année choisie. |
| 8 | [`views/impayes/list.php`](file:///var/www/html/geicg/views/impayes/list.php#L29) | `<select id="filter-annee" class="select2">` | **Filtre Suivi des Impayés** : Permet de filtrer le reste à payer des élèves par année. |
| 9 | [`views/arrieres/list.php`](file:///var/www/html/geicg/views/arrieres/list.php#L35) | `<select id="filter-annee-origine" class="select2">` | **Filtre Arriérés de Scolarité** : Sélection dynamique de l'année d'origine de la dette. |
| 10 | [`views/depenses/list.php`](file:///var/www/html/geicg/views/depenses/list.php#L43) | `<select id="filter-annee" class="select2">` | **Filtre Journal des Dépenses** : Filtre interactif permettant d'afficher les dépenses d'une année. |
| 11 | [`views/sessions_caisse/list.php`](file:///var/www/html/geicg/views/sessions_caisse/list.php#L54) | `<select id="filter-annee" class="select2">` | **Filtre Sessions de Caisse** : Consulter les ouvertures et clôtures de caisse par année. |
| 12 | [`views/notes/list.php`](file:///var/www/html/geicg/views/notes/list.php#L33) | `<select id="filter-annee" class="select2">` | **Filtre Consultation des Notes** : Sélectionner l'année académique d'évaluation. |
| 13 | [`views/absences/list.php`](file:///var/www/html/geicg/views/absences/list.php#L33) | `<select id="filter-annee" class="select2">` | **Filtre Registre des Absences** : Sélectionner l'année académique pour la gestion des absences. |
| 14 | [`views/compositions/list.php`](file:///var/www/html/geicg/views/compositions/list.php#L54) | `<select id="filter-annee" class="select2">` | **Filtre Planning Compositions** : Consulter les sessions d'examens/compositions par année. |
| 15 | [`views/emplois_temps/list.php`](file:///var/www/html/geicg/views/emplois_temps/list.php#L35) | `<select id="filter-annee" class="select2">` | **Filtre Emplois du Temps** : Filtrer le planning hebdomadaire des cours par année. |
| 16 | [`views/enseignant_matiere/list.php`](file:///var/www/html/geicg/views/enseignant_matiere/list.php#L30) | `<select id="filter-annee" class="select2">` | **Filtre Affectations Enseignants** : Sélectionner l'année académique d'attribution des cours. |
| 17 | [`views/semestres/list.php`](file:///var/www/html/geicg/views/semestres/list.php#L34) | `<select id="filter-annee" class="select2">` | **Filtre Liste des Semestres** : Filtrer la liste des semestres par année. |
| 18 | [`views/semestres/edit.php`](file:///var/www/html/geicg/views/semestres/edit.php#L54) | `<select id="sel_annee_semestre" name="annee_code">` | **Formulaire Semestre** : Choix dynamique de l'année à associer au semestre. |
| 19 | [`views/accessoires/list.php`](file:///var/www/html/geicg/views/accessoires/list.php#L44) | `<select id="filter-annee" class="select2">` | **Filtre Kit & Accessoires** : Filtrer les distributions d'équipements par année. |
| 20 | [`views/accessoires/registre.php`](file:///var/www/html/geicg/views/accessoires/registre.php#L39) | `<select id="filter-annee" class="select2">` | **Filtre Registre de Retrait** : Filtrer le registre des retraits par année. |
| 21 | [`views/dossier_etudiant/list.php`](file:///var/www/html/geicg/views/dossier_etudiant/list.php#L31) | `<select id="filter-annee" class="select2">` | **Filtre Conformité Dossiers** : Filtrer le statut du dossier administratif par année. |

---

## 📊 Synthèse Statistique
- **Nombre d'interfaces avec champ Année Readonly** : **6**
- **Nombre d'interfaces avec filtre Année Non-Readonly** : **21**
- **Total des interfaces auditées** : **27**
