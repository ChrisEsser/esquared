<?php

session_start();

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));

// framework loader
require_once(ROOT . DS . 'framework' . DS . 'helpers' . DS . 'loader.php');
