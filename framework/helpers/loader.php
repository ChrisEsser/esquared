<?php

spl_autoload_register(function($className) {

    $tmpClassName = $className;
    $className = strtolower($className);

    $classPaths = [
        'framePath' => ROOT . DS . 'framework' . DS . 'classes' . DS . $className . '.class.php',
        'appClassesPath' => ROOT . DS . 'app' . DS . 'classes' . DS . $className . '.class.php',
        'appInterfacePath' => ROOT . DS . 'app' . DS . 'interfaces' . DS . $className . '.interface.php',
        'controllersPath' => ROOT . DS . 'app' . DS . 'controllers' . DS . $className . '.php',
        'componentsPath' => ROOT . DS . 'app' . DS . 'components' . DS . $className . '.php',
        'modelsPath' => ROOT . DS . 'app' . DS . 'models' . DS . $className . '.php',
        'layoutPath' => ROOT . DS . 'app' . DS . 'layouts' . DS . $className . '.class.php',
    ];

    $gotit = false;
    foreach ($classPaths as $name => $classPath) {
        if (file_exists($classPath)) {
            require $classPath;
            $gotit = true;
            break;
        }
    }

    if (!$gotit) throw new Exception('Class not found: ' . $tmpClassName);

});

register_shutdown_function(function() {
    // display dump data
    Debug::dump_shutdown();
});

// include composer autoloader
require ROOT . DS . 'vendor' . DS . 'autoload.php';

// load the environment variables from the main .env file
$dotenv = Dotenv\Dotenv::createImmutable(ROOT);
$dotenv->load();

require_once(ROOT . DS . 'config' . DS . 'config.php');
require_once(ROOT . DS . 'config' . DS . 'routing.php');

if ($_ENV['DEVELOPMENT_ENVIRONMENT'] == 'true') {

    error_reporting(E_ALL);
    ini_set('log_errors', 1);

    set_error_handler(function($errNo, $errStr, $errFile, $errLine) {
        switch ($errNo) {
            case E_WARNING:
            case E_NOTICE:
                $errFile = strtolower($errFile);
                $tmpFile = explode("framework\\",$errFile);
                $errFile = count($tmpFile) > 1 ? $tmpFile[1] : $tmpFile[0];
                Debug::dump("Error: {$errNo} {$errStr} in {$errFile} line {$errLine}\n");
                break;
            default:
                // nothing to do here
        }
    });
}

// this contains the main hook call
require_once(ROOT . DS . 'framework' . DS . 'helpers' . DS . 'hook.php');

