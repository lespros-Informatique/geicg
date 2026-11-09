<?php
$dir = new RecursiveDirectoryIterator('controllers');
$ite = new RecursiveIteratorIterator($dir);
$matches = [];

foreach ($ite as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $lines = file($f->getPathname());
        foreach ($lines as $num => $line) {
            if (strpos($line, 'RACINE') !== false) {
                $matches[$path][] = [
                    'line' => $num + 1,
                    'code' => trim($line)
                ];
            }
        }
    }
}

ksort($matches);
file_put_contents('scratch/controllers_to_update.json', json_encode($matches, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "SAVED: " . count($matches) . " files.\n";
foreach (array_keys($matches) as $f) {
    echo $f . "\n";
}
