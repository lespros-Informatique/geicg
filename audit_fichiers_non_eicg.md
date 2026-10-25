# Journal d'Audit, Nettoyage & Évolutions d'Architecture — GEICG

**Statut Global** : ✅ **100% CONFORME & OPÉRATIONNEL**

---

## 1. 🧹 Purgation des Dépendances Résiduelles (Legacy)
* **Base de données de référence** : `db_eicg` (tables et vues académiques et financières).
* **Modèles non scolaires supprimés** : 26 modèles hérités (Pressing, Article, Livreur, Panier, Commande, Retrait, Promotion, etc.).
* **Contrôleurs non scolaires supprimés** : 22 contrôleurs.
* **Vues non scolaires supprimées** : 57 vues obsolètes.
* **Core / Routeur** : Mise à jour de `core/PrincipalRoute.php` pour assainir l'ensemble des inclusions et retirer les classes tierces.

---

## 2. 🏛️ Évolutions d'Architecture & Ségrégation Multi-Canal

| Date | Entité / Module | Modifications Réalisées | Impact & Sécurité |
|---|---|---|---|
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
