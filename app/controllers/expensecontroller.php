<?php

class ExpenseController extends BaseController
{

    public function beforeAction()
    {

    }

    public function expenses($params)
    {

    }

    public function expense($params)
    {

    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $expenseId = ($params['expenseId']) ?? 0;
        $propertyId = ($params['propertyId']) ?? 0;
        $unitId = ($params['propertyId']) ?? 0;

        $expense = ($expenseId)
            ? Expense::findOne(['expense_id' => $expenseId])
            : new Expense();

        if ($expense->expense_id) {
            $unitId = ($expense->unit_id) ?? 0;
            $propertyId = ($expense->property_id) ?? 0;
        }

        if ($unitId) {
            /** @var Unit $tmpUnit */
            $tmpUnit = Unit::findOne(['unit_id' => $unitId]);
            $propertyId = ($tmpUnit->property_id) ?? 0;
        }

        /** @var Property[] $tmpProperties */
        $tmpProperties = Property::find([], ['name' => 'ASC']);

        $properties = [];
        foreach ($tmpProperties as $tmpProperty) {
            $tmp = [
                'property_id' => $tmpProperty->property_id,
                'property_name' => $tmpProperty->name,
                'units' => [],
            ];
            foreach ($tmpProperty->getUnit([], ['name' => 'ASC']) as $tmpUnit) {
                $tmp['units'][] = [
                    'unit_id' => $tmpUnit->unit_id,
                    'unit_name' => $tmpUnit->name,
                ];
            }
            $properties[] = $tmp;
        }

        $this->view->setVar('expense', $expense);
        $this->view->setVar('properties', $properties);
        $this->view->setVar('propertyId', $propertyId);
        $this->view->setVar('unitId', $unitId);
    }

    public function save($params)
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $expenseId = ($_POST['expense']) ?? 0;
            $expense = ($expenseId)
                ? Expense::findOne(['expense_id' => $expenseId])
                : new Expense();

            $missing = [];
            if (empty($_POST['amount'])) $missing[] = 'amount';
            if (empty($_POST['date'])) $missing[] = 'date';
            if (empty($_POST['property_id'])) $missing[] = 'property_id';

            if (!empty($missing)) {
                throw new Exception('Required fields were missing');
            }


            if (!$expense->unit_id) $expense->unit_id = intval($_POST['unit_id']);
            if (!$expense->property_id) $expense->property_id = intval($_POST['property_id']);
            $expense->date = gmdate('Y-m-d H:i:s', strtotime($_POST['date']));
            $expense->amount = number_format($_POST['amount'], 2, '.', '');
            $expense->description = $_POST['description'];
            $expense->save();

            HTML::addAlert('The expense was saved', 'success');

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