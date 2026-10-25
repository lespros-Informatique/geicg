# Journal d'Audit, Nettoyage & Évolutions d'Architecture — GEICG

**Statut Global** : ✅ **100% ASSAINI, CONFORME & OPÉRATIONNEL**

---

## 1. 🧹 Purgation des Dépendances & Fichiers Inutiles (Nettoyage Final)

L'intégralité des fichiers résiduels, scripts de test temporaires et modules tiers obsolètes a été supprimée du projet :

### A. Scripts & Fichiers Scratch à la Racine

* `check_classes.php`, `check_regimes.php`, `check_tuition.php` : supprimés.
* `debug_get_tuition.php`, `test_tuition_endpoint.php` : supprimés.
* `scratch_migration.php`, `scratch_schema.json` : supprimés.
* Dossier racine `scratch/` (84 fichiers temporaires : tests, aperçus PDF, rendus PNG, dumps) : intégralement supprimé.

### B. Scripts Scratch dans `public/` (24 fichiers supprimés)

* `public/apply_perm_classe.php`, `public/check_assigned.php`, `public/check_perm_classe.php`
* `public/check_solde_details.php`, `public/check_solde_fix.php`, `public/check_syntax.php`
* `public/find_annexe_lines.php`, `public/get_etudiant_cols.php`, `public/get_last_ids.php`
* `public/get_swal.php`, `public/get_toastr.php`
* `public/migrate_annuler_inscription.php`, `public/migrate_cancel_paiement_roles.php`
* `public/run_permission_migration.php`
* `public/scratch_check_cancel_perm.php`, `public/scratch_check_max_perm.php`, `public/scratch_check_perms.php`, `public/scratch_list_roles.php`
* `public/test_bilan_exec.php`, `public/test_check.php`
* `public/test_check_bilan_v2.php`, `public/test_check_bilan_v3.php`, `public/test_check_bilan_v4.php`, `public/test_compta_check.php`

### C. Contrôleurs, Services & Vues Résiduels Hors-Périmètre

* `controllers/settings/` & `views/settings/` : supprimés (anciens paramètres d'un module tiers).
* `controllers/webhooks/` : supprimé (`GeniusPayWebhookController.php`).
* `core/GeniusPayService.php` : supprimé.
* `core/NotificationService.php` : supprimé et retiré de `core/PrincipalRoute.php`.
* `public/inc/header.php` : nettoyage des variables inutilisées (`$isPressing`, `$isLivreur`, etc.).
* `config/const.php` : nettoyage intégral des constantes obsolètes (`PRESSING`, `LIVREUR`, `COMMANDES`, `ARTICLES`, etc.) et alignement strict sur les tables, rôles et statuts réels du GROUPE EICG.

---

## 2. 🏛️ Évolutions d'Architecture & Ségrégation Multi-Canal

| Date | Entité / Module | Modifications Réalisées | Impact & Sécurité |
| --- | --- | --- | --- |
| **Oct. 2026** | **Inscriptions (`inscriptions`)** | Ajout de `canal_inscription` ENUM('guichet', 'en_ligne') et mise à jour de `statut_inscription` ENUM('en_attente', 'valide', 'solde', 'annule'). | Permet de distinguer les dossiers physiques des pré-inscriptions distantes. |
| **Oct. 2026** | **Paiements (`paiements`)** | Ajout de `canal_paiement` ENUM('guichet', 'en_ligne'). | Traçabilité de l'origine des règlements (guichet physique vs paiement digital). |
| **Oct. 2026** | **Caisses (`sessions_caisse`)** | Ségrégation stricte des caisses : la caisse journalière ne prend en compte que `canal_paiement = 'guichet'`. | Aucun impact des versements en ligne sur le tiroir-caisse physique du caissier. |
| **Oct. 2026** | **Ségrégation Comptable** | Isolation stricte des frais annexes (`FRAIS_ANNEXES`) des calculs de scolarité. | Garantie d'exactitude des totaux comptables scolarité sans pollution des frais annexes. |
| **Oct. 2026** | **RBAC & Permissions** | Création de la permission `VALIDER_INSCRIPTION_EN_LIGNE` assignée à `ROLE_SUPERADMIN`. | Contrôle strict sur la validation administrative des dossiers étudiants en attente. |
| **Oct. 2026** | **Registre PDF Encaissements** | Mise à jour de `imprimerRegistreEncaissements` et `views/templates/pdf/registre_encaissements.php`. | Intégration du filtre Canal, ventilation des KPIs (Guichet vs En Ligne) et colonne `CANAL` avec badges. |

---

## 3. 🛡️ Source de Vérité & Conformité

* Le fichier `database/db_eicg.sql` est synchronisé en continu avec la base de données active MySQL.
* Règle de zéro hardcoding strictement appliquée sur les contrôleurs, modèles et templates d'impression.
* L'ensemble des répertoires du projet (`controllers/`, `models/`, `views/`, `core/`, `public/`) ne contient désormais plus que du code directement lié au fonctionnement de l'établissement **GROUPE EICG**.
