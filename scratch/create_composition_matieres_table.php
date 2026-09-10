<?php
$db = new PDO('mysql:host=localhost;dbname=db_eicg;charset=utf8', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "
CREATE TABLE IF NOT EXISTS composition_matieres (
  id_composition_matiere INT AUTO_INCREMENT PRIMARY KEY,
  composition_code VARCHAR(50) NOT NULL,
  classe_code VARCHAR(50) NOT NULL,
  matiere_code VARCHAR(50) NOT NULL,
  created_at_composition_matiere DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_comp_class_mat (composition_code, classe_code, matiere_code),
  KEY idx_composition_code (composition_code),
  KEY idx_classe_code (classe_code),
  KEY idx_matiere_code (matiere_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$db->exec($sql);
echo "Table composition_matieres créée avec succès !\n";

$cols = $db->query("DESCRIBE composition_matieres")->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
