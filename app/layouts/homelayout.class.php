<?php

class HomeLayout extends BaseLayout
{

    /** @var string */
    public $action = '';
    /** @var string  */
    public $user = '';

    public function __construct()
    {
        $this->_template = new Template(ROOT . DS . 'app' . DS . 'layouts' . DS . 'views' . DS . 'home.layout.php');
    }

    public function fetch()
    {
        if (empty($this->user)) $this->user = new User();

        $this->_template->setVar('body', $this->getBody());
        $this->_template->setVar('action', $this->action);
        $this->_template->setVar('user', $this->user);
        return $this->_template->fetch();
    }

}