<?php
$output = [];
try {
    require_once __DIR__ . '/../config/Database.php';
    require_once __DIR__ . '/../core/BaseModel.php';
    require_once __DIR__ . '/../models/caisse_annexe/ModelCaisseAnnexe.php';

    $model = new ModelCaisseAnnexe();
    $stats = $model->getFinancialStats();
    $all = $model->getAll();

    $output['status'] = 'SUCCESS';
    $output['stats'] = $stats;
    $output['payments_count'] = count($all);
    $output['sample'] = !empty($all) ? $all[0] : null;
} catch (Throwable $t) {
    $output['status'] = 'EXCEPTION';
    $output['message'] = $t->getMessage();
    $output['file'] = $t->getFile() . ':' . $t->getLine();
}

file_put_contents(__DIR__ . '/test_output.txt', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "DONE";
