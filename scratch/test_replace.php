<?php
$content = file_get_contents('controllers/filieres/FiliereController.php');
$target = "header('Location: ' . RACINE . 'filiere/list'); exit();";
$pos = strpos($content, $target);
var_dump($pos);
if ($pos !== false) {
    $content = str_replace($target, "redirect('filiere/list');", $content);
    file_put_contents('controllers/filieres/FiliereController.php', $content);
    echo "REPLACED SUCCESSFULLY\n";
}

