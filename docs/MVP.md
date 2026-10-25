# Spécifications & Architecture Système GEICG (MVP & Backoffice Grande École)

Ce document constitue la référence fonctionnelle et technique du système de gestion intégrée du **GROUPE EICG (Groupe École Internationale de Commerce et de Gestion)**.

---

## 1. Contexte & Enjeux du Système

Le **GROUPE EICG** est un établissement d'enseignement supérieur et technique agréé par l'État et le FDFP. Le système d'information centralise la gestion administrative, pédagogique, financière et comptable de l'établissement.

### Architecture Bi-Plateforme
1. **Backoffice d'Administration GEICG (Ce projet)** :
   - Exploité par la Direction Générale, le Service Financier & Comptable, le Secrétariat des Admissions, et la Direction Pédagogique.
   - Contrôle d'accès granulaire (RBAC), audits chronologiques, gestion des inscriptions et validation des encaissements.
2. **Portail Étudiant Distant (Projet satellite / futur)** :
   - Application dédiée permettant aux candidats et étudiants d'effectuer leur pré-inscription et d'initier leurs règlements de scolarité à distance.
   - Partage la même base de données MySQL normalisée (`db_eicg`), avec ségrégation stricte des canaux (`guichet` vs `en_ligne`).

---

## 2. Règle Fondamentale : `database/db_eicg.sql` comme Source Unique de Vérité

Le fichier `database/db_eicg.sql` est la source de vérité absolue de la structure de données :
* Aucune colonne, table, clé étrangère ou énumération ne doit être supposée sans figurer dans ce schéma.
* Tout calcul (totaux de scolarité, soldes restants, statistiques de caisse, progression des effectifs) est évalué dynamiquement sans fallbacks ni montants statiques (*Zero Hardcoding Rule*).
* Toute modification de schéma (`ALTER TABLE`, `CREATE TABLE`) requiert une justification préalable, validation explicite, et synchronisation immédiate de `database/db_eicg.sql`.

---

## 3. Modules Métier Principaux

### A. Admissions, Inscriptions & Gestion des Étudiants
* **Étudiants & Parents** :
  * Matricule unique, civilité, état civil, contact, coordonnées des tuteurs légaux.
* **Inscriptions & Réinscriptions** :
  * Liaison Étudiant - Classe - Année académique.
  * Statut d'affectation de l'État : `affecte` (subventionné) ou `non_affecte` (frais complets).
  * **Canal d'inscription (`canal_inscription`)** :
    * `guichet` : Enregistrement physique réalisé au secrétariat de l'école.
    * `en_ligne` : Pré-inscription saisie à distance via le portail étudiant.
  * **Statut d'inscription (`statut_inscription`)** :
    * `en_attente` : Dossier pré-inscrit en attente de vérification administrative.
    * `valide` : Inscription définitivement validée par l'administration.
    * `solde` : Scolarité intégralement soldée.
    * `annule` : Dossier annulé ou désisté.
  * **Validation Administrative** :
    * Bouton d'action et modal AJAX permettant aux gestionnaires munis du privilège `VALIDER_INSCRIPTION_EN_LIGNE` de valider instantanément un dossier `en_attente`.

### B. Comptabilité Scolarité, Paiements & Caisses
* **Barèmes de Scolarité & Tranches** :
  * Définition des coûts annuels par filière, cycle et régime (`affecté` / `non affecté`).
  * Tranches d'échéancier (Inscription, Tranche 1, Tranche 2, Tranche 3).
* **Paiements & Encaissements (`paiements`)** :
  * Enregistrement des règlements avec génération de reçu officiel unique (`PAI-...`).
  * **Canal de paiement (`canal_paiement`)** :
    * `guichet` : Versement physique en espèces ou terminal auprès du caissier.
    * `en_ligne` : Règlement digital à distance (Mobile Money : Wave, Orange, MTN, Moov, ou virement).
  * **Modes de règlement** : Espèces, Wave, Orange Money, MTN Money, Moov Money, Chèque, Virement bancaire.
* **Ségrégation Stricte des Flux Financiers** :
  * **Tiroir-Caisse Physique** : Les sessions journalières de caisse (`sessions_caisse`) ne comptabilisent **que** les flux `canal_paiement = 'guichet'`. Les règlements digitaux n'impactent pas le solde physique du caissier.
  * **Séparation Frais Annexes vs Scolarité** : Les encaissements de scolarité et les frais annexes sont strictement isolés dans les états comptables et les journaux.

### C. Structure Académique & Pédagogie (LMD)
* Établissements, Cycles (BTS, Licence, Master), Filières et Spécialités.
* Niveaux d'étude, Classes / Promotions, Semestres académiques.
* Unités d'Enseignement (UE), Matières, Coefficients, Enseignants affectés.
* Gestion des salles et emplois du temps hebdomadaires.

### D. Évaluations, Notes & Absences
* Saisie et validation des notes par classe et matière.
* Sessions de compositions et examens semestriels.
* Calcul automatisé des moyennes, crédits validés et rangs.
* Génération des bulletins officiels et relevés de notes.
* Registre des absences et justification d'assiduité.

### E. Documents Officiels & Éditions PDF (mPDF)
* **Registre Chronologique des Encaissements en Caisse** :
  * Journal officiel avec filtres croisés (Année, Niveau, Classe, Canal, Mode, Période).
  * Indicateurs financiers ventilés (Cumul, Part Guichet, Part En Ligne, Espèces, Mobiles & Banque, Volume).
  * Tableau détaillé avec badge de canal (`GUICHET` / `EN LIGNE`).
* **Reçus de Versement & Fiches d'Inscription** :
  * Impression sécurisée avec signature et métadonnées d'authenticité.
* **Listes d'Émargement, Fiches Étudiant & Offres Académiques**.

---

## 4. Sécurité, Rôles & Permissions (RBAC)

* **Architecture des Privilèges** :
  * Tables `users`, `roles`, `permissions`, `role_permissions`, `user_roles`.
  * Rôles principaux : `ROLE_SUPERADMIN`, `ROLE_ADMIN`, `ROLE_FINANCE`, `ROLE_SCOLARITE`, `ROLE_PEDAGOGIE`, `ROLE_ENSEIGNANT`.
* **Vérification Systématique** :
  * Côté Contrôleur : `$this->requirePermission('CODE_PERMISSION');` ou `$this->requireAnyPermission([...]);`.
  * Côté Vue : Masquage conditionnel des menus et boutons d'action selon `$canAccess('CODE_PERMISSION')`.
* **Contexte de Session Multi-Année** :
  * Sélecteur d'année académique actif dans la navbar.
  * Gestion des années clôturées en lecture seule (`isAnneeReadOnly()`), avec déverrouillage pour les utilisateurs autorisés (`VIEW_ANNEES_ANTERIEURES`, `EDIT_ANNEES_ANTERIEURES`).

---

## 5. Standards de Développement & UX/UI

1. **Stack Technique** :
   - PHP 8.x natif orienté objet MVC sans framework tiers.
   - MySQL 8.x / MariaDB avec transactions InnoDB et requêtes préparées PDO.
   - Frontend : HTML5 sémantique, CSS sur mesure (Palette Navy Profond `#1E3A5F`, Émeraude `#059669`, Indigo `#4338CA`), JavaScript modulaire Vanilla + jQuery pour DataTables.
2. **Bibliothèques Intégrées** :
   - **DataTables** : Pagination serveur/client avec traduction française standardisée.
   - **Lucide Icons** : Iconographie vectorielle moderne (`lucide.createIcons()`).
   - **Toast Notifications** : Système `notification.show(type, title, message)` avec son discret et protection anti-doublon.
   - **mPDF** : Moteur de rendu PDF haute précision avec mise en page A4 portrait et paysage.
