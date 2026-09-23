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
    echo "Render template SUCCESS! HTML length: " . strlen($html) . "\n";
    
    // Check if GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION is in the HTML:
    if (strpos($html, 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION') !== false) {
        echo "Found 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION' in rendered HTML!\n";
    } else {
        echo "NOT FOUND 'GROUPE ECOLE INTERNATIONALE DE COMMERCE ET DE GESTION' in rendered HTML!\n";
    }

    if (strpos($html, 'OFFICIELLE') === false) {
        echo "'OFFICIELLE' is successfully absent.\n";
    } else {
        echo "WARNING: 'OFFICIELLE' found!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
