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
            $auth = Auth::user() ?? [];
            $roleCode = Auth::role() ?? '';
            $userCode = Auth::code() ?? '';

            $annees = $this->getAccessibleAnnees();
            $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);

            if ($anneeCode === null) {
                $stats = [
                    'annee_code' => '',
                    'total_etudiants' => 0,
                    'total_enseignants' => 0,
                    'total_classes' => 0,
                    'total_filieres' => 0,
                    'total_recettes' => 0,
                    'total_depenses' => 0,
                    'solde_caisse' => 0,
                    'inscriptions_validees' => 0,
                    'inscriptions_en_attente' => 0
                ];
                $recentInscriptions = [];
                $recentPaiements = [];
                $recentDepenses = [];
                $teacherCourses = $model->getTeacherCourses($userCode, 6);
            } else {
                $stats = $model->getStats($anneeCode, $userCode, $roleCode);
                $recentInscriptions = $model->getRecentInscriptions(5, $stats['annee_code'] ?? $anneeCode);
                $recentPaiements = $model->getRecentPaiements(5, $stats['annee_code'] ?? $anneeCode);
                $recentDepenses = $model->getRecentDepenses(5, $stats['annee_code'] ?? $anneeCode);
                $teacherCourses = $model->getTeacherCourses($userCode, 6);
            }

            $this->loadView('../views/home/index.php', [
                'stats' => $stats,
                'annees' => $annees,
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
        $roleCode = Auth::role() ?? '';
        $userCode = Auth::code() ?? '';
        $anneeCode = $this->resolveTargetAcademicYear($_GET['annee_code'] ?? null);

        if ($anneeCode === null) {
            $this->json([
                'status' => 1,
                'stats' => [
                    'annee_code' => '',
                    'total_etudiants' => 0,
                    'total_enseignants' => 0,
                    'total_classes' => 0,
                    'total_filieres' => 0,
                    'total_recettes' => 0,
                    'total_depenses' => 0,
                    'solde_caisse' => 0,
                    'inscriptions_validees' => 0,
                    'inscriptions_en_attente' => 0
                ]
            ]);
            return;
        }

        $stats = $model->getStats($anneeCode, $userCode, $roleCode);

        $this->json([
            'status' => 1,
            'stats' => $stats
        ]);
    }
}
