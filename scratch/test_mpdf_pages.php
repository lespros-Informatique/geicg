<?php
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Validator.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../core/PdfService.php';

$_GET['pdf'] = '1';
$html = PdfService::renderTemplate('fiche_inscription.php', ['is_pdf' => true]);

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 8,
    'margin_bottom' => 8,
    'tempDir' => sys_get_temp_dir()
]);

$mpdf->WriteHTML($html);
echo "FINAL MPDF TOTAL PAGES: " . $mpdf->page . "\n";
