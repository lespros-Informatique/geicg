<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../core/PdfService.php';

try {
    $db = (new Database())->getCon();
    $etablissement = $db->query("SELECT * FROM etablissements LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];
    
    $data = [
        'etablissement' => $etablissement,
        'annee_libelle' => '2025-2026',
        'editeur_nom' => 'Administrateur Test',
        'cycles' => [
            ['id_cycle' => 1, 'code_cycle' => 'BTS', 'libelle_cycle' => 'Brevet de Technicien Supérieur']
        ],
        'filieres' => [
            ['id_filiere' => 1, 'code_filiere' => 'IDA', 'libelle_filiere' => 'Informatique Développeur d\'Application', 'type_filiere' => 'TERTIAIRE']
        ],
        'niveaux' => [
            ['id_niveau' => 1, 'libelle_niveau' => '1ère Année']
        ],
        'parcours' => [
            [
                'id_filiere_cycle' => 1,
                'cycle_code' => 'BTS',
                'libelle_cycle' => 'Brevet de Technicien Supérieur',
                'filiere_code' => 'IDA',
                'libelle_filiere' => 'Informatique Développeur d\'Application',
                'type_filiere' => 'TERTIAIRE',
                'libelle_niveau' => '1ère Année'
            ]
        ],
        'synthese_cycles' => [
            'BTS' => [
                'code_cycle' => 'BTS',
                'libelle_cycle' => 'Brevet de Technicien Supérieur',
                'filieres' => [
                    'IDA' => ['code' => 'IDA', 'libelle' => 'Informatique Développeur d\'Application', 'type' => 'TERTIAIRE']
                ],
                'niveaux' => ['1ère Année', '2ème Année'],
                'parcours_items' => []
            ]
        ],
        'filtre_cycle_libelle' => null
    ];

    $html = PdfService::renderTemplate('offre_academique.php', $data);
    $outputFile = __DIR__ . '/output_test.pdf';
    
    // Test mPDF instantiation and PDF rendering
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
    $mpdf->Output($outputFile, \Mpdf\Output\Destination::FILE);
    
    echo "PDF generated successfully at: " . $outputFile . " (Size: " . filesize($outputFile) . " bytes, Pages: " . $mpdf->page . ")\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
