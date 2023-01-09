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

//        HTML::addAlert('This is a test alert', 'danger');
//        HTML::addAlert('This is a test notification', 'info');

    }

    public function afterAction()
    {
        if ($this->render) {
            $layout = new PlainLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}