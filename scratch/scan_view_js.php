<?php
$viewsDir = __DIR__ . '/../views';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$results = [];
foreach ($rii as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    if (preg_match_all('/<script(?![^>]*\b(?:src\s*=|type\s*=\s*["\']application\/json["\']))[^>]*>(.*?)<\/script>/is', $content, $matches)) {
        $relPath = str_replace('\\', '/', substr($file->getPathname(), strlen($viewsDir) + 1));
        $totalLines = 0;
        $snippets = [];
        foreach ($matches[1] as $idx => $js) {
            $lines = substr_count(trim($js), "\n") + 1;
            $totalLines += $lines;
            $snippets[] = [
                'lines' => $lines,
                'preview' => substr(preg_replace('/\s+/', ' ', trim($js)), 0, 80)
            ];
        }
        $results[$relPath] = [
            'count' => count($matches[0]),
            'lines' => $totalLines,
            'snippets' => $snippets
        ];
    }
}
ksort($results);
echo "Total views with executable inline JS: " . count($results) . "\n\n";
foreach ($results as $path => $info) {
    echo "[$path] : {$info['count']} script(s), ~{$info['lines']} lines\n";
    foreach ($info['snippets'] as $s) {
        echo "    - ({$s['lines']} lines): {$s['preview']}\n";
    }
}
