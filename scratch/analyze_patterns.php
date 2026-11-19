<?php
$data = json_decode(file_get_contents('scratch/controllers_to_update.json'), true);

$patterns = [
    'header_redirect' => 0,
    'ajax_redirect' => 0,
    'other' => 0
];

foreach ($data as $file => $occurrences) {
    echo "=== $file ===\n";
    foreach ($occurrences as $occ) {
        $code = $occ['code'];
        echo "  [L{$occ['line']}] $code\n";
    }
}

