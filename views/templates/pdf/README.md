# Templates d'Édition PDF (mPDF) — Projet GEICG

Ce répertoire contient l'ensemble des gabarits HTML/PHP exploités par `core/PdfService.php` pour la génération des documents officiels, reçus financiers et fiches administratives de l'établissement.

---

## ⚙️ Moteur de Rendu & Architecture

* **Service central** : `core/PdfService.php`
* **Moteur d'impression** : `mPDF`
* **Méthodes clés** :
  * `PdfService::renderTemplate(string $templateName, array $dataView): string`
  * `PdfService::generate(string $html, string $filename, array $options): void`
* **Options standards** :
  * `format` : `'A4'`
  * `orientation` : `'P'` (Portrait) ou `'L'` (Landscape / Paysage)
  * `margin_left`, `margin_right`, `margin_top`, `margin_bottom` : de `6mm` à `10mm`

---

## 📑 Répertoire des Modèles PDF

| Fichier Template | Orientation | Description Métier | Contrôleur Associé |
|---|---|---|---|
| `registre_encaissements.php` | **L (Paysage)** | **Registre Chronologique des Encaissements** : ventilation par canaux (`Guichet` vs `En Ligne`), modes de paiement, statistiques financières et journal détaillé. | `PaiementController::imprimerRegistreEncaissements()` |
| `recu_versement.php` | **P (Portrait)** | Reçu officiel de versement de scolarité remis à l'étudiant avec signature et cachet. | `PaiementController::imprimerPdf()` |
| `recu_inscription.php` | **P (Portrait)** | Reçu attestant le paiement des droits d'inscription initiale. | `InscriptionController` |
| `fiche_versement_etudiant.php` | **P (Portrait)** | Historique complet et situation financière individuelle de l'étudiant (frais dus, versés, solde restant). | `PaiementController::imprimerFicheVersement()` |
| `bulletin_notes.php` | **P (Portrait)** | Bulletin semestriel de notes avec moyennes, crédits ECTS, rang et appréciations. | `BulletinController` |
| `liste_classe.php` | **P (Portrait)** | Liste officielle d'émargement et des effectifs d'une classe / groupe pour une année donnée. | `ClasseController` |
| `liste_etudiants.php` | **L (Paysage)** | Répertoire général des étudiants inscrits selon filtres académiques. | `EtudiantController` |
| `liste_impayes.php` | **L (Paysage)** | État comptable des étudiants présentant un solde débiteur sur les échéances échues. | `ImpayesController` |
| `comptabilite_etudiants.php` | **L (Paysage)** | Bilan financier global des scolarités par promotion. | `PaiementController` |
| `grille_scolarites.php` | **P (Portrait)** | Barème officiel des tarifs de scolarité par filière et niveau d'étude. | `ScolariteController` |
| `grille_frais_annexes.php` | **P (Portrait)** | Tarification des frais annexes, tenues et accessoires obligatoires. | `FraisAnnexeController` |
| `convocation_examen.php` | **P (Portrait)** | Convocation nominative aux épreuves et compositions semestrielles. | `CompositionController` |
| `offre_academique.php` | **P (Portrait)** | Brochure descriptive de l'offre de formation et des cycles de l'établissement. | `FiliereController` |
| `catalogue_filieres.php` | **P (Portrait)** | Catalogue détaillé des filières, débouchés professionnels et prérequis. | `FiliereController` |
| `repertoire_classes.php` | **P (Portrait)** | Répertoire des classes ouvertes par filière pour l'exercice académique. | `ClasseController` |
| `repertoire_salles.php` | **P (Portrait)** | Inventaire des salles de cours, capacités d'accueil et affectations. | `SalleController` |

---

## 🛡️ Règles de Conception Graphique pour les Templates PDF

1. **Charte Institutionnelle** :
   * En-tête centré avec logo institutionnel base64 (`$logo_src`), mentions légales, agrément État / FDFP et contacts officiels.
   * Bannière titre `#1E3A5F` avec typographie uppercase contrastée.
2. **Boîte des Filtres & Traçabilité** :
   * Tableau de métadonnées (`.meta-box-table`) affichant l'année académique, le niveau, la classe, le canal d'opération, la date d'édition et le nom de l'agent éditeur.
3. **Tableaux Financiers** :
   * Lignes totales bien distinctes, montants formatés en FCFA sans décimales inutiles (`number_format($montant, 0, ',', ' ')`).
   * Zéro fallback statique : toutes les sommes sont issues directement des calculs de la base de données.
