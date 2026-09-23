<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/PdfService.php';

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
    $stmt = $db->query($sql);
    $filieres = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $etablissement = $db->query("SELECT * FROM etablissements LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];

    $data = [
        'etablissement' => $etablissement,
        'annee_libelle' => '2025-2026',
        'editeur_nom' => 'Direction des Études & Scolarité',
        'filieres' => $filieres
    ];

    $html = PdfService::renderTemplate('catalogue_filieres.php', $data);
    $outputPdf = __DIR__ . '/output_filieres_test.pdf';

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 12,
        'tempDir' => sys_get_temp_dir() . '/mpdf'
    ]);
    $mpdf->WriteHTML($html);
    $mpdf->Output($outputPdf, \Mpdf\Output\Destination::FILE);

    echo "Filières PDF generated successfully: $outputPdf (Pages: {$mpdf->page}, Size: " . filesize($outputPdf) . " bytes)\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
