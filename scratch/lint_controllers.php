<?php
$dir = new RecursiveDirectoryIterator('controllers');
$ite = new RecursiveIteratorIterator($dir);
$phpCli = 'C:\\wamp64\\bin\\php\\php8.2.29\\php.exe';
$errorCount = 0;
$totalChecked = 0;

foreach ($ite as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $totalChecked++;
        $path = $f->getPathname();
        $output = [];
        $exitCode = 0;
        exec("\"$phpCli\" -l \"$path\"", $output, $exitCode);
        if ($exitCode !== 0) {
            echo "SYNTAX ERROR in $path:\n" . implode("\n", $output) . "\n";
            $errorCount++;
        }
    }
}

echo "TOTAL CONTROLLER FILES CHECKED: $totalChecked\n";
echo "ERRORS: $errorCount\n";
if ($errorCount === 0) {
    echo "ALL CONTROLLERS PASSED SYNTAX LINTING!\n";
}

