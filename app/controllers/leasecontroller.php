<?php

class LeaseController extends BaseController
{

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function leases()
    {

    }

    public function lease($params)
    {
        $leaseId = $params['leaseId'] ?? 0;
        if (empty($leaseId)) {
            throw new Exception404();
        }

        $lease = Lease::findOne(['lease_id' => $leaseId]);

        $this->view->setVar('lease', $lease);
    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $leaseId = ($params['leaseId']) ?? 0;
        $propertyId = ($params['propertyId']) ?? 0;
        $unitId = ($params['unitId']) ?? 0;

        $lease = ($leaseId)
            ? Lease::findOne(['lease_id' => $leaseId])
            : new Lease();

        if ($lease->unit_id) {
            $unit = Unit::findOne(['unit_id' => $lease->unit_id]);
            $propertyId = $unit->property_id;
        } else if ($unitId) {
            $unit = Unit::findOne(['unit_id' => $unitId]);
            $propertyId = $unit->property_id;
        } else {
            $unit = new Unit();
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

        $this->view->setVar('lease', $lease);
        $this->view->setVar('unit', $unit);
        $this->view->setVar('properties', $properties);
        $this->view->setVar('propertyId', $propertyId);
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

            $leaseId = ($_POST['lease']) ?? 0;
            $unitId = ($_POST['unit_id']) ?? 0;

            $lease = ($leaseId)
                ? Lease::findOne(['lease_id' => $leaseId])
                : new Lease();

            if (empty($_POST['start_date'])) $missing[] = 'start_date';
            if (empty($_POST['end_date'])) $missing[] = 'end_date';
            if (empty($_POST['unit_id'])) $missing[] = 'unit_id';
            if (empty($_POST['rent'])) $missing[] = 'rent';

            if (!empty($missing)) throw new Exception('Some required fields were missing ' . print_r($missing, true));

            $lease->unit_id = intval($_POST['unit_id']);
            $lease->start_date = gmdate('Y-m-d 00:00:00', strtotime($_POST['start_date']));
            $lease->end_date = gmdate('Y-m-d 23:59:59', strtotime($_POST['end_date']));
            $lease->rent = $_POST['rent'];
            $lease->rent_frequency = $_POST['rent_frequency'];
            $lease->save();

            // setup all the file directories even if we are not uploading files
            if (!is_dir(ROOT . DS . 'app' . DS . 'files' . DS . 'leases')) mkdir(ROOT . DS . 'app' . DS . 'files' . DS . 'leases');
            $leaseFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'leases' . DS . $lease->lease_id;
            if (!is_dir($leaseFilePath)) mkdir($leaseFilePath);

            if (!empty($_POST['filepond'])) {

                $tmpPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp';

                /** @var \File $dbFile */
                $dbFile = File::findOne(['uid' => $_POST['filepond']]);
                if (!empty($dbFile->file_id)) {

                    $ext = strtolower(pathinfo($dbFile->original_name, PATHINFO_EXTENSION));
                    $tmpFile = $tmpPath . DS . $dbFile->uid . '.' . $ext;
                    $newFile = $leaseFilePath . DS . $dbFile->original_name;
                    rename($tmpFile, $newFile);
                }
            }

            // add or remove users as needed
            $users = [];
            $sql = '';
            foreach ($_POST['users'] as $tmpUserId) {
                if (!empty($tmpUserId)) {
                    $users[] = $tmpUserId;
                    $sql .= 'INSERT IGNORE INTO user_leases SET lease_id = ' . $lease->lease_id . ', user_id = ' . intval($tmpUserId) . ";";
                }
            }

            $db = new StandardQuery();

            if (!empty($sql)) {
                $db->run($sql);
            }

            if (!empty($users)) {
                $sql = 'DELETE FROM user_leases WHERE lease_id = ' . $lease->lease_id . ' AND user_id NOT IN (' . implode(',', $users) . ')';
                $db->run($sql);
            }

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

        $leaseId = ($params['leaseId']) ?? 0;

        $lease = Lease::findOne(['lease_id' => $leaseId]);
        $lease->delete();

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