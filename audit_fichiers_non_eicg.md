# Audit & Nettoyage du projet GEICG

**Statut du nettoyage** : ✅ **EFFECTUÉ** (Tous les fichiers et dossiers résiduels hors `db_eicg` ont été supprimés avec succès).

---

## 📊 Résumé des opérations de nettoyage
- **Base de données de référence** : `db_eicg` (48 tables et vues scolaires)
- **Modèles non scolaires supprimés** : 26 modèles (Pressing, Article, Livreur, Panier, Commande, Retrait, Promotion, etc.)
- **Contrôleurs non scolaires supprimés** : 22 contrôleurs
- **Vues non scolaires supprimées** : 57 vues
- **Core / Routeur** : Mis à jour ([PrincipalRoute.php](file:///C:/wamp64/www/geicg/core/PrincipalRoute.php)) pour retirer tous les chargements des modèles/contrôleurs supprimés et la classe `PressingAware.php`.

Toutes les dépendances résiduelles ont été purgées et le système se charge parfaitement.
