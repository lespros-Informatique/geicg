<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_eicg;charset=utf8', 'root', '');
$cols = $pdo->query('DESCRIBE compositions')->fetchAll(PDO::FETCH_ASSOC);
echo "=== COMPOSITIONS COLUMNS ===\n";
foreach ($cols as $c) {
    echo "- {$c['Field']} ({$c['Type']})\n";
}
