# Guide des Commandes Utiles — Projet GEICG

Ce fichier regroupe les commandes CLI essentielles pour alimenter, vider, sauvegarder et maintenir la base de données du projet **GEICG**.

---

## 1. Seeder de Données (`database/DatabaseSeeder.php`)

### Remplir la base de données (2023-2024, 2024-2025, 2025-2026)

```bash
# Remplissage propre (vide les anciennes données métier puis injecte les 3 années)
php database/DatabaseSeeder.php --years=2023-2024,2024-2025,2025-2026 --students=10 --teachers=8 --depenses=10 --fresh
```

### Exemples avec paramètres personnalisés

```bash
# Remplissage avec les valeurs par défaut (2023-2024, 2024-2025, 2025-2026 ; 10 étudiants/classe)
php database/DatabaseSeeder.php

# Plus grand volume (20 étudiants/classe, 10 enseignants, 12 dépenses/an, autre graine aléatoire)
php database/DatabaseSeeder.php --years=2023-2024,2024-2025,2025-2026 --students=20 --teachers=10 --depenses=12 --sessions=4 --seed=42 --fresh

# Une seule année spécifique en mode silencieux
php database/DatabaseSeeder.php --years=2025-2026 --students=15 --fresh --quiet
```

### Vider la base de données (sans ré-injecter)

```bash
# Vide uniquement les données métier/transactionnelles (préserve la structure DB, établissement, configurations, RBAC, admins, cycles, filières, niveaux, salles, types de dépenses et années)
php database/DatabaseSeeder.php --clear

# Vide les données métier + la table annees
php database/DatabaseSeeder.php --clear --clear-years
```

### Afficher l'aide du seeder

```bash
php database/DatabaseSeeder.php --help
```

### Tableau récapitulatif des options

| Option | Défaut | Description |
| :--- | :--- | :--- |
| `--years=...` | `2023-2024,2024-2025,2025-2026` | Liste des années académiques séparées par des virgules |
| `--students=N` | `10` | Nombre d'étudiants par classe |
| `--teachers=N` | `8` | Nombre d'enseignants à créer (max `10`) |
| `--depenses=N` | `10` | Nombre de dépenses par année |
| `--sessions=N` | `4` | Nombre de sessions de caisse par année |
| `--seed=N` | `2026` | Graine aléatoire déterministe |
| `--fresh` | `false` | Vide les données métier puis ré-injecte |
| `--clear` | `false` | Vide uniquement les données métier sans ré-injecter |
| `--clear-years` | `false` | À combiner avec `--clear` pour vider aussi `annees` |
| `--quiet` | `false` | Désactive les logs dans le terminal |
| `--help` / `-h` | — | Affiche la liste des options |

### Utilisation en PHP (programmatique)

```php
require_once __DIR__ . '/database/DatabaseSeeder.php';

$seeder = new DatabaseSeeder($pdo, [
    'years'              => ['2023-2024', '2024-2025', '2025-2026'],
    'students_per_class' => 10,
    'teachers'           => 8,
    'depenses_per_year'  => 10,
    'sessions_per_year'  => 4,
    'fresh'              => true,
    'seed'               => 2026,
    'verbose'            => false,
]);

// Remplir
$stats = $seeder->run();

// Vider uniquement
$deleted = $seeder->clear();      // Conserve la table annees
$deleted = $seeder->clear(true);  // Vide aussi la table annees
```

### Comptes créés par le seeder (mot de passe : `password123`)

| Email | Code utilisateur | Rôle |
| :--- | :--- | :--- |
| `caissier@eicg.ci` | `SEED-CAIS001` | `ROLE_CAISSIER` |
| `scolarite@eicg.ci` | `SEED-SCOL001` | `ROLE_SCOLARITE` |
| `comptable@eicg.ci` | `SEED-COMP001` | `ROLE_COMPTABLE` |
| `prof1@eicg.ci` → `prof8@eicg.ci` | `SEED-ENS001` → `SEED-ENS008` | `ROLE_ENSEIGNANT` |

---

## 2. Base de Données MySQL (`c2833857c_eicg`)

### Importer le schéma complet de référence

```bash
mysql -u admin -padmin c2833857c_eicg < database/db_eicg.sql
```

### Appliquer les vues SQL & index d'optimisation

```bash
# Créer / mettre à jour toutes les vues SQL métier
mysql -u admin -padmin c2833857c_eicg < database/optimize_views.sql

# Appliquer les index de performance
mysql -u admin -padmin c2833857c_eicg < database/optimize_indexes.sql

# Réimporter uniquement les rôles et permissions RBAC
mysql -u admin -padmin c2833857c_eicg < database/rbac.sql
```

### Sauvegarder la base actuelle dans `database/db_eicg.sql`

```bash
mysqldump -u admin -padmin --single-transaction --routines --triggers --default-character-set=utf8mb4 c2833857c_eicg > database/db_eicg.sql
```

---

## 3. Validation Syntaxique & Git

### Vérifier la syntaxe PHP d'un fichier ou de tout le projet

```bash
# Un fichier précis
php -l controllers/maquettes/MaquetteController.php

# Tous les fichiers PHP (hors vendor/)
find config core controllers models views -name "*.php" -exec php -l {} +
```

### Commandes Git courantes

```bash
# Vérifier l'état et la branche active
git status -s
git branch -vv

# Commiter et pousser sur starter-branch
git add .
git commit -m "chore: mise à jour"
git push origin starter-branch
```
