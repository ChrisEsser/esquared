<?php

class PaymentController extends BaseController
{

    public function beforeAction()
    {

    }

    public function payments($params)
    {

    }

    public function payment($params)
    {

    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;
    }

    public function save($params)
    {
        $this->render = false;
    }

    public function delete($params) {
        $this->render = false;
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
            $layout = new AdminLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}