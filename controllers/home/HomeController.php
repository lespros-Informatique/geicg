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
            $this->loadView('../views/home/index.php', [
                'isSuperAdmin' => $isSuperAdmin,
                'isPressing' => $isPressing,
                'isLivreur' => $isLivreur
            ]);
        } else {
            $this->loadView('../views/users/connexion.php');
        }
    }

    public function dashboardData()
    {
        $this->requireAuth();
        $model = $this->resolveModel();

        $pressingCode = $this->getCurrentPressingCode();
        $stats = $model->getStats($pressingCode);
        $recentOrders = $model->getRecentOrders(10, $pressingCode);

        $this->json([
            'stats' => $stats,
            'salesByDay' => [],
            'topProducts' => [],
            'recentOrders' => $recentOrders
        ]);
    }
}
