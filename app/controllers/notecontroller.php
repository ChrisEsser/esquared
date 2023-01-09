<?php

class NoteController extends BaseController
{

    public function beforeAction()
    {
        if (!Auth::isAdmin()) {
            HTML::addAlert('Unauthorized access');
            HTTP::redirect('/');
        }
    }


    public function note($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $noteId = ($params['noteId']) ?? 0;

        $note = ($noteId)
            ? Note::findOne(['note_id' => $noteId])
            : new Note();

        $this->view->setVar('note', $note);
    }

    public function edit($params)
    {
        HTTP::removePageFromHistory();
        $this->render_header = false;

        $noteId = ($params['noteId']) ?? 0;
        $propertyId = ($params['propertyId']) ?? 0;

        $note = ($noteId)
            ? Note::findOne(['note_id' => $noteId])
            : new Note();

        if ($noteId) $propertyId = $note->property_id;

        $property = ($propertyId)
            ? Property::findOne(['property_id' => $propertyId])
            : new Property();

        $this->view->setVar('note', $note);
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

            $noteId = ($_POST['noteId']) ?? 0;
            $propertyId = ($_POST['property']) ?? 0;

            $note = ($noteId)
                ? Note::findOne(['note_id' => $noteId])
                : new Note();

            if (empty($_POST['note'])) $missing[] = 'note';

            if (!empty($missing)) throw new Exception('Some required fields were missing');

            $note->note = $_POST['note'];
            $note->property_id = $propertyId;
            $note->status = $_POST['status'];
            $note->type = $_POST['type'];
            $note->created_by = Auth::loggedInUser();
            $note->save();

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

        $noteId = ($params['noteId']) ?? 0;

        $note = Note::findOne(['note_id' => $noteId]);
        $note->delete();

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
            $layout = new PlainLayout();
            $layout->action = $this->_action;
            $layout->addTemplate($this->view);
            $layout->display();
        }
    }

}