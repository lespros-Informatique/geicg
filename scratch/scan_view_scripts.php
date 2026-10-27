<?php
$viewsDir = __DIR__ . '/../views';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$results = [];

foreach ($rii as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    $relPath = str_replace('\\', '/', substr($path, strlen(realpath($viewsDir)) + 1));
    $content = file_get_contents($path);

    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $content, $matches);
    $scriptCount = count($matches[0]);
    $totalScriptLines = 0;
    $details = [];

    if ($scriptCount > 0) {
        foreach ($matches[1] as $idx => $scriptBody) {
            $trimmed = trim($scriptBody);
            $lines = substr_count($trimmed, "\n") + 1;
            $totalScriptLines += $lines;
            
            // Check characteristics
            $hasLucideOnly = preg_match('/^\s*\$\(document\)\.ready\(function\s*\(\)\s*\{\s*(if\s*\(window\.lucide\)\s*)?lucide\.createIcons\(\);\s*\}\);?\s*$/is', $trimmed);
            $hasLucideSelect2Only = preg_match('/^\s*\$\(document\)\.ready\(function\s*\(\)\s*\{\s*(if\s*\(window\.lucide\)\s*)?lucide\.createIcons\(\);\s*\$\([\'"]\.select2[\'"]\)\.select2\([^)]*\);\s*\}\);?\s*$/is', $trimmed);
            $hasDataTable = stripos($trimmed, 'DataTable') !== false;
            $hasAjax = stripos($trimmed, '$.ajax') !== false;

            $details[] = [
                'lines' => $lines,
                'hasLucideOnly' => (bool)$hasLucideOnly,
                'hasLucideSelect2Only' => (bool)$hasLucideSelect2Only,
                'hasDataTable' => $hasDataTable,
                'hasAjax' => $hasAjax,
                'snippet' => substr($trimmed, 0, 80)
            ];
        }

        $results[$relPath] = [
            'scripts' => $scriptCount,
            'lines' => $totalScriptLines,
            'details' => $details
        ];
    }
}

echo "Total view files with <script>: " . count($results) . "\n\n";

// Let's categorize:
$trivial = [];
$crudList = [];
$complex = [];

foreach ($results as $file => $info) {
    $isTrivial = true;
    foreach ($info['details'] as $d) {
        if (!$d['hasLucideOnly'] && !$d['hasLucideSelect2Only'] && $d['lines'] > 6) {
            $isTrivial = false;
            break;
        }
    }
    if ($isTrivial) {
        $trivial[] = $file;
    } elseif ($info['lines'] <= 60) {
        $crudList[] = $file;
    } else {
        $complex[] = $file;
    }
}

echo "--- CATEGORY 1: TRIVIAL BOILERPLATES (" . count($trivial) . " files) ---\n";
foreach ($trivial as $f) echo "  - $f (" . $results[$f]['lines'] . " lines)\n";

echo "\n--- CATEGORY 2: MODERATE / CRUD (" . count($crudList) . " files) ---\n";
foreach ($crudList as $f) echo "  - $f (" . $results[$f]['lines'] . " lines)\n";

echo "\n--- CATEGORY 3: COMPLEX / EXTENSIVE (" . count($complex) . " files) ---\n";
foreach ($complex as $f) echo "  - $f (" . $results[$f]['lines'] . " lines)\n";
