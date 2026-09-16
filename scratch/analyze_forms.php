<?php
$viewsDir = __DIR__ . '/../views';
$dirs = scandir($viewsDir);
$results = [];

foreach ($dirs as $dir) {
    if ($dir === '.' || $dir === '..' || !is_dir("$viewsDir/$dir")) continue;
    $files = scandir("$viewsDir/$dir");
    
    $hasList = in_array('list.php', $files);
    $hasEdit = in_array('edit.php', $files);
    $hasAdd = in_array('add.php', $files);
    $hasForm = in_array('formulaire.php', $files);
    
    $targetFormFile = null;
    if ($hasEdit) $targetFormFile = "$viewsDir/$dir/edit.php";
    elseif ($hasAdd) $targetFormFile = "$viewsDir/$dir/add.php";
    elseif ($hasForm) $targetFormFile = "$viewsDir/$dir/formulaire.php";
    
    if ($hasList && $targetFormFile) {
        $content = file_get_contents($targetFormFile);
        
        // Count form fields (input, select, textarea) excluding hidden/submit/button/csrf
        preg_match_all('/<input\b(?![^>]*type=["\'](hidden|submit|button|reset)["\'])[^>]*>/i', $content, $inputs);
        preg_match_all('/<select\b[^>]*>/i', $content, $selects);
        preg_match_all('/<textarea\b[^>]*>/i', $content, $textareas);
        
        $inputCount = count($inputs[0]);
        $selectCount = count($selects[0]);
        $textareaCount = count($textareas[0]);
        $totalFields = $inputCount + $selectCount + $textareaCount;
        
        $listContent = file_get_contents("$viewsDir/$dir/list.php");
        $hasModalInList = (stripos($listContent, 'modal') !== false);
        $hasSeparatePageNav = (
            preg_match('/(formulaire|edition|edit|add)/i', $listContent) === 1
        );
        
        // Extract field names/labels for context
        preg_match_all('/name=["\']([^"\']+)["\']/i', $content, $nameMatches);
        $fieldNames = array_unique($nameMatches[1] ?? []);
        // filter out csrf, id, tokens
        $filteredNames = array_filter($fieldNames, function($n) {
            return !in_array($n, ['csrf_token', 'id', 'user_code', 'etablissement_code', 'action']);
        });
        
        $results[] = [
            'module' => $dir,
            'formFile' => basename($targetFormFile),
            'totalFields' => $totalFields,
            'inputs' => $inputCount,
            'selects' => $selectCount,
            'textareas' => $textareaCount,
            'listHasModal' => $hasModalInList,
            'fieldNames' => array_values($filteredNames)
        ];
    }
}

usort($results, function($a, $b) { return $a['totalFields'] <=> $b['totalFields']; });

echo sprintf("%-20s | %-12s | %-15s | %-5s | %s\n", "Module", "Form File", "Fields", "Modal", "Field Names");
echo str_repeat("-", 100) . "\n";
foreach ($results as $r) {
    echo sprintf("%-20s | %-12s | %-2d (in:%d,sel:%d,tx:%d) | %-5s | %s\n",
        $r['module'],
        $r['formFile'],
        $r['totalFields'],
        $r['inputs'],
        $r['selects'],
        $r['textareas'],
        $r['listHasModal'] ? 'OUI' : 'NON',
        implode(', ', $r['fieldNames'])
    );
}
