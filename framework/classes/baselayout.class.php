<?php

class BaseLayout
{
    /** @var array */
    private $_components;
    /** @var string */
    protected $_template;

    public function __construct()
    {

    }

    public function addTemplate($template)
    {
        $this->_components[] = $template;
        return $this->_components[count($this->_components) - 1];
    }

    /**
     * This relies on a fetch method to always be in the called layout
     */
    public function display()
    {
        $output = $this->fetch();
        echo $output;
    }

    public function getBody()
    {
        $content = '';
        if (!empty($this->_components)) {
            foreach ($this->_components as $components) {
                $content .= $components->fetch();
            }
        }
        return $content;
    }

}