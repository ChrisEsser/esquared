<?php

class PlainLayout extends BaseLayout
{

    /** @var string */
    public $action = '';

    public function __construct()
    {
        $this->_template = new Template(ROOT . DS . 'app' . DS . 'layouts' . DS . 'views' . DS . 'plain.layout.php');
    }

    public function fetch()
    {
        $this->_template->setVar('body', $this->getBody());
        $this->_template->setVar('action', $this->action);
        return $this->_template->fetch();
    }

}