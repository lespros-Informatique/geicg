<?php
require_once __DIR__ . '/../core/PdfService.php';

try {
    $html = PdfService::renderTemplate('fiche_inscription.php', []);
    echo "SUCCESS: Template rendered, HTML length = " . strlen($html) . " bytes\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
