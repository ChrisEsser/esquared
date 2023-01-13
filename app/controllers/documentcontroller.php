<?php

class DocumentController extends BaseController
{
    public $loggedInUser;

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }

        $this->loggedInUser = Auth::loggedInUser();
        $this->view->setVar('loggedInUser', $this->loggedInUser);
    }

    public function documents()
    {

    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $documentId = $params['documentId'] ?? 0;

        $document = ($documentId)
            ? Document::findOne(['document_id', $documentId])
            : new Document();

        $this->view->setVar('document', $document);
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

            $documentId = $_POST['documentId'] ?? 0;

            if (empty($_POST['filepond'])) $missing[] = 'file';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $document = ($documentId)
                ? Document::findOne(['document_id', $documentId])
                : new Document();

            $tmpPath = ROOT . DS . 'app' . DS . 'files' . DS . 'tmp';

            $documentFilePath = ROOT . DS . 'app' . DS . 'files' . DS . 'documents';
            if (!is_dir($documentFilePath)) mkdir($documentFilePath);
            $documentFilePath .= DS . $this->loggedInUser;
            if (!is_dir($documentFilePath)) mkdir($documentFilePath);

            $dbFile = File::findOne(['uid' => $_POST['filepond']]);
            if (!empty($dbFile->file_id)) {
                $ext = strtolower(pathinfo($dbFile->original_name, PATHINFO_EXTENSION));
                $tmpFile = $tmpPath . DS . $dbFile->uid . '.' . $ext;
                $newFile = $documentFilePath . DS . $dbFile->original_name;
                rename($tmpFile, $newFile);
            }

            $document->name = $dbFile->original_name;
            $document->owner_id = $this->loggedInUser;
            $document->user_id = $this->loggedInUser;
            $document->description = '';
            $document->property_id = 0;
            $document->type = 0;
            $document->document_date = gmdate('Y-m-d H:i:s', time());
            $document->amount = 0;
            $document->save();

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

        $documentId = $params['documentId'] ?? 0;
        $document = Document::findOne(['document_id' => $documentId]);

        $file = ROOT . DS . 'app' . DS . 'files' . DS . 'documents' . DS . $document->owner_id . DS . $document->name;

        $document->delete();

        unlink($file);

        HTTP::rewind();
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