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

        $paymentId = ($params['paymentId']) ?? '';
        $propertyId = ($params['propertyId']) ?? '';

        $payment = ($paymentId)
            ? PaymentHistory::findOne(['payment_id' => $paymentId])
            : new PaymentHistory();

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $units = ($property->property_id)
            ? $property->getUnit()
            : Unit::find([], ['name' => 'ASC']);

        $this->view->setVar('payment', $payment);
        $this->view->setVar('units', $units);
    }

    public function save($params)
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $paymentId = ($_POST['payment']) ?? 0;
            $payment = ($paymentId)
                ? PaymentHistory::findOne(['payment_id' => $paymentId])
                : new PaymentHistory();

            $missing = [];
            if (empty($_POST['method'])) $missing[] = 'method';
            if (empty($_POST['type'])) $missing[] = 'type';
            if (empty($_POST['payment_date'])) $missing[] = 'payment_date';
            if (empty($_POST['amount'])) $missing[] = 'amount';

            if (!emptY($missing)) {
                throw new Exception('Required fields were missing');
            }


            if (!$payment->unit_id) $payment->unit_id = intval($_POST['unit_id']);
            $payment->method = $_POST['method'];
            $payment->type = $_POST['type'];
            $payment->payment_date = gmdate('Y-m-d H:i:s', strtotime($_POST['payment_date']));
            $payment->amount = number_format($_POST['amount'], 2, '.', '');
            $payment->user_id = Auth::loggedInUser();
            $payment->fee = 0;
            $payment->description = $_POST['type'] . ' Payment - ' . date('Y-m-d H:i:s', strtotime($_POST['payment_date']));
            $payment->save();

            HTML::addAlert('The rent payment was saved', 'success');

        }  catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
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