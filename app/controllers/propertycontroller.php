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

        $images = [];
        foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $propertyId . DS . 'images' . DS . '*.*') as $file) {
            $images[] = basename($file);
        }

        $this->view->setVar('property', $property);
        $this->view->setVar('images', $images);
    }


    public function properties()
    {

    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $propertyId = ($params['propertyId']) ?? 0;

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $images = [];
        foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $property->property_id . DS . 'images' . DS . '*.*') as $file) {
            $images[] = basename($file);
        }

        $this->view->setVar('property', $property);
        $this->view->setVar('images', $images);
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
            $property->type = intval($_POST['type']);
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

            // get the image path for this property. so we can drop the image in
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

    public function deleteImage($params)
    {
        HTTP::removePageFromHistory();

        $propertyId = ($params['propertyId']) ?? 0;
        $image = ($_GET['image']) ?? '';

        foreach (glob(ROOT . DS . 'app' . DS . 'files' . DS . 'properties' . DS . $propertyId . DS . 'images' . DS . $image) as $file) {
            unlink($file);
        }

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
                } else throw new Exception('No file to move');

                $originalName = $dbFile->original_name;

                // ok now that we moved the doc, lets see if we need to convert from image to pdf
                if (isset($_POST['convert']) && $_POST['convert'] == 1) {
                    $pathInfo = pathinfo($newFile);
                    $dir = $pathInfo['dirname'];
                    $filename = $pathInfo['filename'];
                    $pdfFile = $dir . DS . $filename . '.pdf';

                    try {
                        $command = 'convert "' . $newFile . '" -quality 100 ' . $pdfFile;
                        if (exec($command)) {
                            unlink($newFile);
                        }
                        $originalName = str_replace('.' . $ext, '.pdf', $originalName);
                    } catch (Exception $e) {
                        var_dump($e->getMessage());
                    }
                }

                // save the document record to the db
                $document = new Document();
                $document->name = $originalName;
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

    public function test()
    {
        $this->render = false;

        $client = new GuzzleHttp\Client();

        $db = new StandardQuery();

        $sql = 'SELECT l.lead_id, l.url
                FROM scraper_leads l
                LEFT JOIN lead_addresses a ON a.lead_id = l.lead_id            
                GROUP BY l.lead_id
                HAVING COUNT(a.address_id) = 0';

        $leads = $db->rows($sql);

        foreach ($leads as $lead) {

            $plainSTring = ScraperHelper::getPdfString($lead->url);
            if (!empty($plainSTring)) {

                $addresses = ScraperHelper::pullAddressesFromString($plainSTring);

                var_dump($addresses);
                continue;

                if ($addresses) {
                    $addresses = ScraperHelper::parseAddressPartsFromGoogle($addresses);
                    $addresses = ScraperHelper::removeQuarantinedAddressFromArray($addresses);
                }
            }

            foreach($addresses as $address) {
                $addr = new ScraperLeadAddress();
                $addr->lead_id = $lead->lead_id;
                $addr->street = $address['streetNumber'] . ' ' . $address['streetName'];
                $addr->city = $address['city'];
                $addr->state = $address['state'];
                $addr->zip = $address['zip'];
                $addr->lat = $address['lat'];
                $addr->lon = $address['lon'];
                $addr->type = 0;
                $addr->save();
            }

        }

        exit;

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