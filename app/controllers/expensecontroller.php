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

        $expenseId = ($params['expenseId']) ?? '';
        $propertyId = ($params['propertyId']) ?? '';

        $expense = ($expenseId)
            ? Expense::findOne(['expense_id' => $expenseId])
            : new Expense();

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $properties = Property::find([], ['name' => 'ASC']);

        $tmpUnits = ($property->property_id)
            ? $property->getUnit()
            : Unit::find([], ['name' => 'ASC']);

        // We want to group the units by property, so we can better handle the javascript on the front end
        // for example when a property is changed I want to show a dropdown of all units for said property only
        $units = [];
        foreach ($tmpUnits as $tmpUnit) {
            $units[$tmpUnit->property_id][] = [
                'unit_id' => $tmpUnit->unit_id,
                'name' => $tmpUnit->name,
            ];
        }

        $this->view->setVar('expense', $expense);
        $this->view->setVar('units', $units);
        $this->view->setVar('properties', $properties);
        $this->view->setVar('property', $property);
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