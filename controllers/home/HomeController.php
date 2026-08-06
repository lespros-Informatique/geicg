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
            $this->loadView('../views/home/index.php');
        } else {
            $this->loadView('../views/users/connexion.php');
        }
    }

    public function dashboardData()
    {
        $this->requireAuth();
        $model = $this->resolveModel();

        $stats = $model->getStats();
        $recentOrders = $model->getRecentOrders(10);

        $this->json([
            'stats' => $stats,
            'salesByDay' => [],
            'topProducts' => [],
            'recentOrders' => $recentOrders
        ]);
    }
}
