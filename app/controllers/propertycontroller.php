<?php

class PropertyController extends BaseController
{

    /** @var User $loggedInUser */
//    public $loggedInUser;

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }

    public function property($params)
    {
        $propertyId = ($params['propertyId']) ?? 0;

        $property = Property::findOne(['property_id' => $propertyId]);

        if (!$property) {
            HTML::addAlert('Invalid property', 'danger');
            HTTP::rewind();
        }

        $images = $documents = [];
        foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $propertyId . DS . 'images' . DS . '*.*') as $file) {
            $images[] = basename($file);
        }
        foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $propertyId . DS . 'documents' . DS . '*.*') as $file) {
            $documents[] = basename($file);
        }

        $this->view->setVar('property', $property);
        $this->view->setVar('images', $images);
        $this->view->setVar('documents', $documents);
    }


    public function properties()
    {
        $properties = Property::find();
        $this->view->setVar('properties', $properties);
    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $propertyId = ($params['propertyId']) ?? 0;

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

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

            $propertyId = ($_POST['property']) ?? 0;

            $property = ($propertyId)
                ? Property::findOne(['property_id' => $propertyId])
                : new Property();

            if (empty($_POST['name'])) $missing[] = 'name';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $property->name = $_POST['name'];
            $property->description = $_POST['description'];
            $property->purchase_price = $_POST['purchase_price'];
            $property->purchase_date = gmdate('Y-m-d', strtotime($_POST['purchase_date']));
            $property->save();

            // setup all the file directories even if we are not uploading files
            $propertyFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $property->property_id;
            if (!is_dir($propertyFilePath)) mkdir($propertyFilePath);

            $tmpBasePath = $propertyFilePath;

            // make sure there is a documents path for this property so we have it later
            $propertyFilePath = $tmpBasePath . DS . 'documents';
            if (!is_dir($propertyFilePath)) mkdir($propertyFilePath);

            // get the image path for this property so we can drop the image in
            $propertyFilePath = $tmpBasePath . DS . 'images';
            if (!is_dir($propertyFilePath)) mkdir($propertyFilePath);

            if (!empty($_POST['filepond'])) {

                $tmpPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp';

                /** @var \File $dbFile */
                $dbFile = File::findOne(['uid' => $_POST['filepond']]);
                if (!empty($dbFile->file_id)) {

                    $ext = strtolower(pathinfo($dbFile->original_name, PATHINFO_EXTENSION));
                    $tmpFile = $tmpPath . DS . $dbFile->uid . '.' . $ext;
                    $newFile = $propertyFilePath . DS . $dbFile->original_name;
                    rename($tmpFile, $newFile);
                }
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

        $propertyId = ($params['propertyId']) ?? 0;

        $property = Property::findOne(['property_id' => $propertyId]);
        $property->delete();

        HTTP::rewindQuick();
    }

    public function addDocument($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $propertyId = ($params['propertyId']) ?? 0;

        /** @var \Property $property */
        $property = Property::findOne(['property_id' => $propertyId]);

        if (empty($property->property_id)) {
            throw new Exception404();
        }

        $this->view->setVar('property', $property);

    }

    public function saveDocument($params)
    {
        $this->render = false;

        $return = [
            'result' => 'success',
            'message' => '',
        ];

        try {

            $propertyId = ($params['propertyId']) ?? 0;

            /** @var \Property $property */
            $property = Property::findOne(['property_id' => $propertyId]);

            if (empty($property->property_id)) throw new Exception('Invalid Property');

            if (!empty($_POST['filepond'])) {

                $propertyFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $property->property_id . DS . 'documents';
                $tmpPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp';

                /** @var \File $dbFile */
                $dbFile = File::findOne(['uid' => $_POST['filepond']]);

                if (!empty($dbFile->file_id)) {
                    $ext = strtolower(pathinfo($dbFile->original_name, PATHINFO_EXTENSION));
                    $tmpFile = $tmpPath . DS . $dbFile->uid . '.' . $ext;
                    $newFile = $propertyFilePath . DS . $dbFile->original_name;
                    rename($tmpFile, $newFile);
                }

                // save the document now record now
                $document = new Document();
                $document->name = $dbFile->original_name;
                $document->description = $_POST['description'];
                $document->type = $_POST['type'];
                $document->amount = $_POST['amount'];
                $document->property_id = $property->property_id;
                $document->owner = 0;
                $document->user_id = Auth::loggedInUser();
                $document->document_date = gmdate('Y-m-d H:i:s', time());
                $document->amount = 0;
                $document->save();

            } else throw new Exception('No file key' . print_r($_POST, true));

        } catch (Exception $e) {
            $return = [
                'result' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        echo json_encode($return);
    }

    public function deleteDocument($params)
    {
        $this->render = false;

        $propertyId = ($params['propertyId']) ?? 0;

        /** @var \Property $property */
        $property = Property::findOne(['property_id' => $propertyId]);

        if (empty($property->property_id)) {
            throw new Exception404();
        }

        $document = $_GET['document'];
        $file = ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $property->property_id . DS . 'documents' . DS . $document;
        unlink($file);

        HTTP::rewind();

    }

    public function addNote($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $propertyId = ($params['propertyId']) ?? 0;

        /** @var \Property $property */
        $property = Property::findOne(['property_id' => $propertyId]);

        if (empty($property->property_id)) {
            throw new Exception404();
        }

        $this->view->setVar('property', $property);

    }

    public function editPayment($params)
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

        $this->view->setVar('payment', $payment);
        $this->view->setVar('property', $property);
    }

    public function savePayment()
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

     public function deletePayment($params)
     {
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
            $layout = new PlainLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}