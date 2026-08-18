<?php

class HomeController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelHome();
    }

    public function index()
    {
        if (Validator::isConnected()) {
            $isSuperAdmin = $this->isSuperAdmin();
            $isPressing = $this->isPressing();
            $isLivreur = $this->isLivreur();
            $model = $this->resolveModel();

            if ($isLivreur) {
                $livreurCode = $this->getCurrentLivreurCode();
                $stats = $model->getLivreurStats($livreurCode);
                $missions = $model->getLivreurMissions($livreurCode, 20);

                $this->loadView('../views/home/livreur_dashboard.php', [
                    'isSuperAdmin' => false,
                    'isPressing' => false,
                    'isLivreur' => true,
                    'livreurCode' => $livreurCode,
                    'stats' => $stats,
                    'missions' => $missions
                ]);
                return;
            }

            $this->loadView('../views/home/index.php', [
                'isSuperAdmin' => $isSuperAdmin,
                'isPressing' => $isPressing,
                'isLivreur' => false
            ]);
        } else {
            $this->loadView('../views/users/connexion.php');
        }
    }

    public function dashboardData()
    {
        $this->requireAuth();
        $model = $this->resolveModel();

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            $stats = $model->getLivreurStats($livreurCode);
            $missions = $model->getLivreurMissions($livreurCode, 20);

            $this->json([
                'is_livreur' => true,
                'stats' => $stats,
                'missions' => $missions
            ]);
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        $stats = $model->getStats($pressingCode);
        $recentOrders = $model->getRecentOrders(10, $pressingCode);

        $this->json([
            'is_livreur' => false,
            'stats' => $stats,
            'salesByDay' => [],
            'topProducts' => [],
            'recentOrders' => $recentOrders
        ]);
    }
}
