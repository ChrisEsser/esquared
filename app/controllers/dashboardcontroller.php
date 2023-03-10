<?php

class DashboardController extends BaseController
{

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function dashboard()
    {
        HTML::addScriptToHead('https://cdn.jsdelivr.net/npm/chart.js');

        $totals = DashboardHelper::getTotals();
        $topProperties = DashboardHelper::topCashFlowProperties(5);
        $monthlyBreakdown = DashboardHelper::monthBreakdown(6);

        $this->view->setVar('totals', $totals);
        $this->view->setVar('topProperties', $topProperties);
        $this->view->setVar('monthlyBreakdown', $monthlyBreakdown);
    }

    public function afterAction()
    {
        if ($this->render) {
            $layout = new AdminLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}