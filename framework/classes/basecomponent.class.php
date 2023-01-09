<?php

class BaseComponent extends Template
{

    public function __construct($templatePath)
    {
        parent::__construct($templatePath);
    }

    public function display()
    {
        echo $this->fetch();
    }

}
