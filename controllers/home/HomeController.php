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
            $anneeCode = $_SESSION['annee_active_code'] ?? null;
            $stats = $model->getStats($anneeCode);
            $recentInscriptions = $model->getRecentInscriptions(5, $stats['annee_code']);
            $recentPaiements = $model->getRecentPaiements(5, $stats['annee_code']);

            $this->loadView('../views/home/index.php', [
                'stats' => $stats,
                'recentInscriptions' => $recentInscriptions,
                'recentPaiements' => $recentPaiements
            ]);
        } else {
            $this->loadView('../views/users/connexion.php');
        }
    }

    public function dashboardData()
    {
        $this->requireAuth();
        $model = $this->resolveModel();
        $anneeCode = $_SESSION['annee_active_code'] ?? null;
        $stats = $model->getStats($anneeCode);

        $this->json([
            'status' => 1,
            'stats' => $stats
        ]);
    }
}
