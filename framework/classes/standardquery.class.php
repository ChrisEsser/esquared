<?php

class StandardQuery extends \Dcblogdev\PdoWrapper\Database
{
    public function __construct()
    {
        $args = [
            'username' => $_ENV['DB_USER'],
            'database' => $_ENV['DB_NAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'type' => 'mysql',
            'host' => $_ENV['DB_HOST'],
        ];

        parent::__construct($args);
    }

    public function quote($string)
    {

    }

}