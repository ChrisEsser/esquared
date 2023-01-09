<?php

class Template
{
    /** @var string */
     private $_template;
     /** @var array */
     private $_data;

    public function __construct($templatePath)
    {
        $this->_template = $templatePath;
    }

    public function fetch()
    {
        if (!is_file($this->_template)) throw new Exception404();
        ob_start();
        include $this->_template;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    public function display()
    {
        echo $this->fetch();
    }

    public function setVar($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function getVar($name, $default = '')
    {
        if (isset($this->_data[$name])) return $this->_data[$name];
        else throw new Exception('Error: Invalid template variable. ' . $name);
    }

}
