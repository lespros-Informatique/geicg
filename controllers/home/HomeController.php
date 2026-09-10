<?php

class HomeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelHome();
    }

    public function index()
    {
        if (Validator::isConnected()) {
            $model = $this->resolveModel();
            $auth = $_SESSION[USERS_AUTH] ?? [];
            $roleCode = $auth['role_code'] ?? '';
            $userCode = $auth['code_user'] ?? '';
            $anneeCode = $_SESSION['annee_active_code'] ?? null;

            $stats = $model->getStats($anneeCode, $userCode, $roleCode);
            $recentInscriptions = $model->getRecentInscriptions(5, $stats['annee_code']);
            $recentPaiements = $model->getRecentPaiements(5, $stats['annee_code']);
            $recentDepenses = $model->getRecentDepenses(5, $stats['annee_code']);
            $teacherCourses = $model->getTeacherCourses($userCode, 6);

            $this->loadView('../views/home/index.php', [
                'stats' => $stats,
                'roleCode' => $roleCode,
                'userCode' => $userCode,
                'auth' => $auth,
                'recentInscriptions' => $recentInscriptions,
                'recentPaiements' => $recentPaiements,
                'recentDepenses' => $recentDepenses,
                'teacherCourses' => $teacherCourses
            ]);
        } else {
            $this->loadView('../views/users/connexion.php');
        }
    }

    public function dashboardData()
    {
        $this->requireAuth();
        $model = $this->resolveModel();
        $auth = $_SESSION[USERS_AUTH] ?? [];
        $roleCode = $auth['role_code'] ?? '';
        $userCode = $auth['code_user'] ?? '';
        $anneeCode = $_SESSION['annee_active_code'] ?? null;

        $stats = $model->getStats($anneeCode, $userCode, $roleCode);

        $this->json([
            'status' => 1,
            'stats' => $stats
        ]);
    }
}
