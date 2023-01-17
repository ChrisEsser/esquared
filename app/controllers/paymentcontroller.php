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

        $paymentId = $params['paymentId'] ?? 0;

        if ($payment = PaymentHistory::findOne(['payment_id' => $paymentId])) {
            $payment->delete();
        }

        HTTP::rewindQuick();

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