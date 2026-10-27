<?php
$viewsDir = 'c:/wamp64/www/geicg/views';

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
$viewsWithJs = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        if (preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $content, $matches)) {
            $scripts = [];
            foreach ($matches[1] as $idx => $scriptBody) {
                $trimmed = trim($scriptBody);
                if (!empty($trimmed)) {
                    $scripts[] = [
                        'lines' => count(explode("\n", $trimmed)),
                        'preview' => substr($trimmed, 0, 150)
                    ];
                }
            }
            if (!empty($scripts)) {
                $relPath = str_replace('\\', '/', str_replace($viewsDir . DIRECTORY_SEPARATOR, '', $path));
                $viewsWithJs[$relPath] = $scripts;
            }
        }
    }
}

echo "Total views with inline JS: " . count($viewsWithJs) . "\n\n";

foreach ($viewsWithJs as $view => $scripts) {
    $totalLines = array_sum(array_column($scripts, 'lines'));
    echo "[$view] : " . count($scripts) . " script(s), ~$totalLines lines\n";
    foreach ($scripts as $s) {
        $oneLinePreview = str_replace(["\r", "\n", "  "], ' ', substr($s['preview'], 0, 80));
        echo "    - ({$s['lines']} lines): $oneLinePreview\n";
    }
}
