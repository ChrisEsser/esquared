<?php

class IndexController extends BaseController
{
    private $user;

    public function beforeAction()
    {
        if (!Auth::loggedInUser()) HTTP::redirect('/login');

        /** @var User $user */
        $user = User::findOne(['user_id' => Auth::loggedInUser()]);
        if (!$user) {
            HTML::addAlert('Invalid User');
            HTTP::redirect('/login');
        }

        $this->user = $user;

        $this->view->setVar('user', $user);
    }

    public function index()
    {
        HTTP::removePageFromHistory();
        HTTP::redirect('/account');
    }

    public function afterAction()
    {
        if (!$this->render_header) {
            $layout = new AjaxLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
        else if ($this->render) {
            $layout = new HomeLayout();
            $layout->action = $this->_action;
            $layout->user = $this->user;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}
