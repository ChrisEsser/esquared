<?php

class UnitController extends BaseController
{

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function units($params)
    {
        $propertyId = ($params['propertyId']) ?? 0;

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $units = ($propertyId)
            ? Unit::find(['property_id' => $propertyId])
            : Unit::find();

        $properties = $property->find();

        $this->view->setVar('property', $property);
        $this->view->setVar('units', $units);
        $this->view->setVar('properties', $properties);
    }


    public function unit($params)
    {
        $unitId = ($params['unitId']) ?? 0;

        $unit = ($unitId)
            ? Unit::findOne(['unit_id' => $unitId])
            : new Unit();

        $this->view->setVar('unit', $unit);
    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $unitId = ($params['unitId']) ?? 0;
        $propertyId = ($params['propertyId']) ?? 0;

        $unit = ($unitId)
            ? Unit::findOne(['unit_id' => $unitId])
            : new Unit();

        if ($unitId) $propertyId = $unit->property_id;

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $this->view->setVar('unit', $unit);
        $this->view->setVar('property', $property);
    }

    public function save()
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        $missing = [];

        try {

            $unitId = ($_POST['unit']) ?? 0;
            $propertyId = ($_POST['property']) ?? 0;

            $unit = ($unitId)
                ? Unit::findOne(['unit_id' => $unitId])
                : new Unit();

            if (empty($_POST['name'])) $missing[] = 'name';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $unit->name = $_POST['name'];
            $unit->description = $_POST['description'];
            $unit->property_id = $propertyId;
            $unit->status = $_POST['status'];
            $unit->rent = $_POST['rent'];
            $unit->rent_frequency = $_POST['rent_frequency'];
            $unit->save();

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function delete($params)
    {
        $this->render = false;

        $unitId = ($params['unitId']) ?? 0;

        $unit = Unit::findOne(['unit_id' => $unitId]);
        $unit->delete();

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