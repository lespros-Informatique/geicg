# Journal des Anomalies de Calcul : Association Indue entre Scolarité et Frais Annexes

> **Référence Système** : Conforme à la *Directive 4 des Standards d'Ingénierie du Projet GEICG* (`.agents/rules/live_inspection_rule.md`).  
> **Règle Fondamentale** : Séparation étanche et stricte entre les **Frais de Scolarité** (Caisse Principale, tranches, grille tarifaire, échéanciers) et les **Frais Annexes** (Caisse Annexe, uniformes, kits, macarons, attestations). Les deux flux ne doivent **jamais** être cumulés, amalgamés ou imputés l'un dans l'autre.

---

## 1. Tableau Récapitulatif des Anomalies Identifiées

| N° | Module / Fichier | Emplacement | Type | Impact Métier / Dysfonctionnement |
| :--- | :--- | :--- | :--- | :--- |
| **1** | [`models/impayes/ModelImpayes.php`](file:///c:/wamp64/www/geicg/models/impayes/ModelImpayes.php#L108-L113) | L. 108-113 | Requête SQL | Cumul de tous les paiements (y compris Caisse Annexe) sans filtre, réduisant artificiellement les impayés de scolarité des étudiants. |
| **2** | [`controllers/impayes/ImpayesController.php`](file:///c:/wamp64/www/geicg/controllers/impayes/ImpayesController.php#L286-L301) | L. 286-301 | Calcul PHP & SQL | Modal de relance : additionne `$scolariteDue + $fraisAnnexesDus` face à un `$totalPaye` global non filtré. |
| **3** | [`controllers/impayes/ImpayesController.php`](file:///c:/wamp64/www/geicg/controllers/impayes/ImpayesController.php#L462-L474) | L. 462-474 | Calcul PHP & PDF | Impression en lot des lettres de rappel : injecte la somme scolarité + frais annexes dans la colonne `TOTAL SCOLARITE`. |
| **4** | [`models/arrieres/ModelArriere.php`](file:///c:/wamp64/www/geicg/models/arrieres/ModelArriere.php#L65-L70) | L. 65-70 | Requête SQL | `getEtudiantsAvecArrieres` : déduit les versements de frais annexes du solde d'arriéré de scolarité des années antérieures. |
| **5** | [`models/arrieres/ModelArriere.php`](file:///c:/wamp64/www/geicg/models/arrieres/ModelArriere.php#L128-L133) | L. 128-133 | Requête SQL | `getKpisArrieres` : KPIs globaux de recouvrement des arriérés faussés par l'inclusion des paiements de Caisse Annexe. |
| **6** | [`models/arrieres/ModelArriere.php`](file:///c:/wamp64/www/geicg/models/arrieres/ModelArriere.php#L193-L198) | L. 193-198 | Requête SQL | `getArrieresEtudiantDetail` : calcule le `solde_restant` d'arriéré d'un étudiant en y intégrant les frais annexes. |
| **7** | [`controllers/arrieres/ArriereController.php`](file:///c:/wamp64/www/geicg/controllers/arrieres/ArriereController.php#L119-L124) | L. 119-124 | Calcul PHP & SQL | Vérification du montant lors du règlement d'un arriéré : compare `montant_scolarite_inscription` à la somme brute des paiements. |
| **8** | [`controllers/arrieres/ArriereController.php`](file:///c:/wamp64/www/geicg/controllers/arrieres/ArriereController.php#L214-L220) | L. 214-220 | Calcul PHP & PDF | Reçu d'arriéré PDF : évalue `$totalPaye` et `$restePayer` sans exclure les frais annexes. |
| **9** | [`controllers/inscriptions/InscriptionController.php`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php#L268-L276) | L. 268-276 | Calcul PHP & SQL | Wizard de réinscription : évaluation du quitus financier de l'année précédente faussée par l'absence de filtre de catégorie. |
| **10** | [`controllers/inscriptions/InscriptionController.php`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php#L746-L750) | L. 746-750 | Calcul PHP & SQL | Enregistrement de réinscription : contrôle d'arriéré bloquant sans filtrer les paiements d'accessoires. |
| **11** | [`controllers/inscriptions/InscriptionController.php`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php#L1017-L1033) | L. 1017-1033 | Calcul PHP | Fiche détail inscription (`details`) : calcule le solde de scolarité en déduisant les frais annexes. |
| **12** | [`controllers/inscriptions/InscriptionController.php`](file:///c:/wamp64/www/geicg/controllers/inscriptions/InscriptionController.php#L1200) | L. 1200 | Calcul PHP & PDF | Reçu d'inscription PDF : `$totalVerse = $fraisAnnexesVerse + $scolariteVerse` affiché dans le tableau face au montant de scolarité seul. |
| **13** | [`controllers/etudiants/EtudiantController.php`](file:///c:/wamp64/www/geicg/controllers/etudiants/EtudiantController.php#L446-L454) | L. 446-454 | Calcul PHP | Dossier étudiant (`details`) : section financière déduisant tous les versements de la scolarité due. |
| **14** | [`controllers/annees/AnneeController.php`](file:///c:/wamp64/www/geicg/controllers/annees/AnneeController.php#L243) | L. 243 | Requête SQL | Fiche année académique : KPI `total_recouvrement` amalgamant scolarité et caisse annexe. |
| **15** | [`controllers/paiements/PaiementController.php`](file:///c:/wamp64/www/geicg/controllers/paiements/PaiementController.php#L1114-L1127) | L. 1114-1127 | Logique PHP & SQL | `findActivePaymentTranche` : les règlements de frais annexes non affectés à une tranche scolarité sont utilisés pour solder les tranches de scolarité. |
| **16** | [`controllers/paiements/PaiementController.php`](file:///c:/wamp64/www/geicg/controllers/paiements/PaiementController.php#L1560) | L. 1560 | Requête SQL | Annulation de paiement : le recalcul pour basculer le statut d'inscription entre `solde` et `valide` prend en compte les frais annexes. |

---

## 2. Détail Technique par Module et Correctifs Préconisés

### 2.1. Module Impayés & Relances de Scolarité

#### A. `models/impayes/ModelImpayes.php` (L. 108-113)
* **Code fautif :**
  ```sql
  SELECT inscription_code, SUM(montant_paiement) as total_paye
  FROM paiements
  WHERE statut_paiement != 'annule'
  GROUP BY inscription_code
  ```
* **Correction requise :**
  ```sql
  SELECT inscription_code, SUM(montant_paiement) as total_paye
  FROM paiements
  WHERE statut_paiement != 'annule'
    AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES')
    AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')
    AND (type_paiement IS NULL OR type_paiement NOT LIKE '%Frais Annexe%')
  GROUP BY inscription_code
  ```

#### B. `controllers/impayes/ImpayesController.php` (L. 286-301 et L. 462-474)
* **Code fautif :**
  ```php
  $stmtPay = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
  $stmtPay->execute([$ins['code_inscription']]);
  $totalPaye = (float)($stmtPay->fetchColumn() ?: 0);
  ...
  $totalAttendu = $scolariteDue + $fraisAnnexesDus;
  $restePayer = max(0, $totalAttendu - $totalPaye);
  ```
* **Correction requise :**
  - Filtrer la requête `$stmtPay` pour n'obtenir que les versements de scolarité.
  - Dissocier strictement les deux flux :
    - `total_scolarite_due` = `$scolariteDue`
    - `total_scolarite_paye` = `$scolaritePaye`
    - `reste_scolarite_payer` = `max(0, $scolariteDue - $scolaritePaye)`
  - Ne plus fusionner `$fraisAnnexesDus` dans le calcul d'impayé de scolarité.

---

### 2.2. Module Gestion des Arriérés

#### A. `models/arrieres/ModelArriere.php` (L. 65-70, L. 128-133, L. 193-198)
* **Code fautif :**
  ```sql
  LEFT JOIN (
      SELECT inscription_code, SUM(montant_paiement) as total_paye
      FROM paiements
      WHERE statut_paiement = 'confirme'
      GROUP BY inscription_code
  ) p_sub ON p_sub.inscription_code = i_orig.code_inscription
  ```
* **Correction requise :**
  Ajouter dans chaque sous-requête :
  ```sql
  AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES')
  AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')
  AND (type_paiement IS NULL OR type_paiement NOT LIKE '%Frais Annexe%')
  ```

#### B. `controllers/arrieres/ArriereController.php` (L. 119 et L. 214)
* **Code fautif :**
  ```php
  $stmtP = $db->prepare("SELECT COALESCE(SUM(montant_paiement), 0) as paye FROM paiements WHERE inscription_code = ? AND statut_paiement = 'confirme'");
  ```
* **Correction requise :**
  Restreindre la requête exclusivement aux versements de scolarité afin que le solde d'arriéré corresponde à la réalité comptable de la scolarité.

---

### 2.3. Module Inscriptions & Réinscriptions

#### A. `controllers/inscriptions/InscriptionController.php` (L. 268-276 et L. 746-750)
* **Code fautif :**
  ```php
  $stmtP = $db->prepare("SELECT SUM(montant_paiement) as total_paye FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
  $stmtP->execute([$prevIns['code_inscription']]);
  $prevPaye = (float)($stmtP->fetch(PDO::FETCH_ASSOC)['total_paye'] ?? 0);
  $prevSolde = max(0, $prevDue - $prevPaye);
  ```
* **Correction requise :**
  Ajouter le filtre `categorie_paiement != 'FRAIS_ANNEXES'` et `tranche_code != 'FRAIS_ANNEXES'` pour que le quitus de scolarité de l'année précédente ne soit pas faussé.

#### B. `controllers/inscriptions/InscriptionController.php` (L. 1017-1033)
* **Code fautif :**
  ```php
  $scolarite = (float)($item['montant_scolarite_inscription'] ?? 0);
  $totalPaye = 0;
  foreach ($paiements as $p) {
      if (($p['statut_paiement'] ?? '') !== 'annule') {
          $totalPaye += (float)($p['montant_paiement'] ?? 0);
      }
  }
  $solde = max(0, $scolarite - $totalPaye);
  ```
* **Correction requise :**
  Ignorer les paiements dont la catégorie ou la tranche vaut `FRAIS_ANNEXES` lors du calcul de `$totalPaye` affecté à la scolarité.

#### C. `controllers/inscriptions/InscriptionController.php` (L. 1200) & `recu_inscription.php`
* **Code fautif :**
  ```php
  $totalVerse = $fraisAnnexesVerse + $scolariteVerse;
  ```
* **Correction requise :**
  Sur le reçu, si la ligne `TOTAL` affiche face à face le total à payer et le total versé, `$totalVerse` doit correspondre strictement à `$scolariteVerse` (ou afficher une ligne dédiée pour chaque caisse).

---

### 2.4. Fiche Étudiant & Année Académique

#### A. `controllers/etudiants/EtudiantController.php` (L. 446-454)
* **Code fautif :**
  ```php
  $scolariteTotale = (float)($inscription['montant_scolarite_inscription'] ?? 0);
  $totalPaye = 0;
  foreach ($paiements as $p) {
      if (($p['statut_paiement'] ?? '') !== 'annule') {
          $totalPaye += (float)($p['montant_paiement'] ?? ($p['montant_paye'] ?? 0));
      }
  }
  $soldeRestant = max(0, $scolariteTotale - $totalPaye);
  ```
* **Correction requise :**
  Séparer le calcul en deux blocs distincts :
  1. **Scolarité :** `$scolaritePayee` et `$soldeScolariteRestant`.
  2. **Frais Annexes :** `$fraisAnnexesPayes` (affiché séparément dans sa propre métrique).

#### B. `controllers/annees/AnneeController.php` (L. 243)
* **Code fautif :**
  ```sql
  (SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE annee_code = ? AND (statut_paiement != 'annule' OR statut_paiement IS NULL)) as total_recouvrement
  ```
* **Correction requise :**
  Scinder en :
  - `total_recouvrement_scolarite` (excluant `FRAIS_ANNEXES`).
  - `total_recouvrement_annexe` (filtrant exclusivement `FRAIS_ANNEXES`).

---

### 2.5. Contrôleur Paiements (`PaiementController.php`)

#### A. `controllers/paiements/PaiementController.php` (L. 1114-1127) - `findActivePaymentTranche`
* **Code fautif :**
  ```php
  $stmtPay = $db->prepare("SELECT * FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
  ```
* **Correction requise :**
  Ajouter `AND (categorie_paiement IS NULL OR categorie_paiement != 'FRAIS_ANNEXES') AND (tranche_code IS NULL OR tranche_code != 'FRAIS_ANNEXES')` pour éviter que des paiements d'accessoires orphelins de tranche ne viennent solder les tranches de scolarité.

#### B. `controllers/paiements/PaiementController.php` (L. 1560) - `annulerPaiement`
* **Code fautif :**
  ```php
  $stmtTot = $db->prepare("SELECT SUM(montant_paiement) FROM paiements WHERE inscription_code = ? AND statut_paiement != 'annule'");
  ```
* **Correction requise :**
  Ajouter le filtre d'exclusion des frais annexes, identique à celui utilisé lors de la création d'un versement (L. 1337-1338 et L. 1431-1432).

---

## 3. Clause SQL Standard de Référence

Pour garantir la conformité avec la directive de séparation stricte, toute requête évaluant la **Scolarité** doit utiliser ce filtre standard :

```sql
AND (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES')
AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')
AND (p.type_paiement IS NULL OR p.type_paiement NOT LIKE '%Frais Annexe%')
```
Et inversement, toute requête ciblant la **Caisse Annexe** doit utiliser :
```sql
AND (p.categorie_paiement = 'FRAIS_ANNEXES' OR p.tranche_code = 'FRAIS_ANNEXES' OR p.type_paiement LIKE '%Frais Annexe%')
```
