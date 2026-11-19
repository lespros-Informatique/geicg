<?php

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../models/audit/ModelAuditLog.php';
require_once __DIR__ . '/../../models/users/ModelUser.php';

class AuditController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAuditLog();
    }

    /**
     * Page principale de consultation des logs
     */
    public function list()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_LOGS);

        $modules = $this->model->getDistinctModules();
        $actions = $this->model->getDistinctActions();
        $users = (new ModelUser())->getUsersWithRolesAndFonctions();

        $this->loadView('../views/audit/list.php', [
            'modules' => $modules,
            'actions' => $actions,
            'users' => $users
        ]);
    }

    /**
     * API JSON pour DataTables avec filtres
     */
    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_LOGS);

        $filters = [
            'action' => trim($_GET['action'] ?? ''),
            'module' => trim($_GET['module'] ?? ''),
            'user_id' => trim($_GET['user_id'] ?? ''),
            'date_debut' => trim($_GET['date_debut'] ?? ''),
            'date_fin' => trim($_GET['date_fin'] ?? ''),
            'search' => trim($_GET['search'] ?? '')
        ];

        $logs = $this->model->getLogs($filters, 1000, 0);

        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                'id' => $log['id_log'],
                'date' => !empty($log['created_at']) ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-',
                'user_nom' => $log['user_nom'] ?: 'Système',
                'user_code' => $log['user_code'] ?: '-',
                'user_id' => $log['user_id'],
                'etablissement_code' => $log['etablissement_code'] ?: '-',
                'action' => $log['action'],
                'module' => strtoupper($log['module']),
                'entite_id' => $log['entite_id'] ?: '-',
                'description' => $log['description'],
                'details' => $log['details_json'],
                'has_details' => !empty($log['details_json']),
                'ip' => $log['ip_address'] ?: '127.0.0.1',
                'user_agent' => $log['user_agent'] ?: '-'
            ];
        }

        $this->json(['data' => $data]);
    }

    /**
     * Génère et imprime le journal d'audit en PDF
     */
    public function imprimerPdf()
    {
        $this->requireAuth();
        $this->requirePermission(PERMISSIONS::VIEW_LOGS);
        require_once __DIR__ . '/../../core/PdfService.php';

        $filters = [
            'action' => trim($_GET['action'] ?? ''),
            'module' => trim($_GET['module'] ?? ''),
            'user_id' => trim($_GET['user_id'] ?? ''),
            'date_debut' => trim($_GET['date_debut'] ?? ''),
            'date_fin' => trim($_GET['date_fin'] ?? ''),
            'search' => trim($_GET['search'] ?? '')
        ];

        $logs = $this->model->getLogs($filters, 2000, 0);

        $targetUserLabel = '';
        if (!empty($filters['user_id'])) {
            $uRow = (new ModelUser())->getById((int)$filters['user_id']);
            if ($uRow) {
                $targetUserLabel = trim($uRow['nom_user'] . ' ' . ($uRow['prenom_user'] ?? '')) . ' (' . ($uRow['code_user'] ?? '') . ')';
            }
        }

        // Journaliser cette impression
        $this->logAction(
            AuditLogger::ACTION_PRINT,
            'audit',
            null,
            "Impression du journal des logs d'audit (" . count($logs) . " entrées)"
        );

        $dataView = [
            'logs' => $logs,
            'filters' => $filters,
            'targetUserLabel' => $targetUserLabel,
            'etablissement' => $this->getEtablissementConfig()
        ];

        $html = PdfService::renderTemplate('journal_audit.php', $dataView);
        if (isset($_GET['html'])) {
            echo $html;
            return;
        }

        $filename = 'Journal_Audit_Logs_' . date('Ymd_His') . '.pdf';

        PdfService::generate($html, $filename, [
            'orientation' => 'L',
            'format' => 'A4',
            'title' => 'Journal d\'Audit & Logs - GEICG',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);
    }
}

