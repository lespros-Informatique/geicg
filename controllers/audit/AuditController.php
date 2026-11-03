<?php

require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../models/audit/ModelAuditLog.php';

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
        $this->requirePermission([PERMISSIONS::CONFIG_SYSTEM, PERMISSIONS::MANAGE_USERS, PERMISSIONS::VIEW_USERS]);

        $modules = $this->model->getDistinctModules();
        $actions = $this->model->getDistinctActions();

        $this->loadView('../views/audit/list.php', [
            'modules' => $modules,
            'actions' => $actions
        ]);
    }

    /**
     * API JSON pour DataTables avec filtres
     */
    public function apiList()
    {
        $this->requireAuth();
        $this->requirePermission([PERMISSIONS::CONFIG_SYSTEM, PERMISSIONS::MANAGE_USERS, PERMISSIONS::VIEW_USERS]);

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
}

