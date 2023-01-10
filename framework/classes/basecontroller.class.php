<?php

class BaseController
{

    protected $_controller;
    protected $_action;
    protected $_template;

    public $render = true;
    public $render_header = true;
    public $addToRewind = true;

    /** @var Template */
    protected $view;

    /**
     * BaseController constructor.
     * @param $controller
     * @param $action
     */
    public function __construct($controller = null, $action = null)
    {
        global $inflect;

        $this->_controller = $controller;
        $this->_action = $action;

        if (class_exists('Auth', false)) {
            Auth::loggedIn();
        }

        // set the view file base of the action and controller
        $this->view = new Template(ROOT . DS . 'app' . DS . 'views' . DS . strtolower($this->_controller) . DS . strtolower($this->_action) . '.php');

    }

    public function __destruct()
    {
        // add this request to the rewind queue if we rendered
        // @TODO: maybe I need to move this? it will run even if I forget to set render to false. that may or may not be good behavior
//        if ($this->render) HTTP::addToRewindQueue();
    }

}