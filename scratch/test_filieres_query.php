<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = (new Database())->getCon();
    $sql = "SELECT f.*,
                   GROUP_CONCAT(DISTINCT c.code_cycle ORDER BY c.code_cycle SEPARATOR ', ') as cycles_codes,
                   GROUP_CONCAT(DISTINCT c.libelle_cycle ORDER BY c.libelle_cycle SEPARATOR ' | ') as cycles_libelles
            FROM filieres f
            LEFT JOIN filiere_cycles fc ON fc.filiere_code = f.code_filiere AND (fc.statut_filiere_cycle = 'actif' OR fc.statut_filiere_cycle IS NULL)
            LEFT JOIN cycles c ON c.code_cycle = fc.cycle_code AND (c.statut_cycle = 'actif' OR c.statut_cycle IS NULL)
            WHERE f.statut_filiere = 'actif' OR f.statut_filiere IS NULL
            GROUP BY f.id_filiere
            ORDER BY f.type_filiere ASC, f.libelle_filiere ASC";
    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
