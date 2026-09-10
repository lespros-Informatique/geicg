<?php
$db = new PDO("mysql:host=localhost;dbname=db_eicg;charset=utf8", "root", "");

echo "=== VIEW v_dash_synthese_annuelle CREATION SQL ===\n";
$stmt = $db->query("SHOW CREATE VIEW v_dash_synthese_annuelle");
print_r($stmt->fetch(PDO::FETCH_ASSOC));

echo "=== CONTENT OF v_dash_synthese_annuelle ===\n";
$stmt2 = $db->query("SELECT * FROM v_dash_synthese_annuelle");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
