<?php

class Exception404 extends Exception {}


/** GZip Output **/
function gzipOutput()
{
//    $ua = $_SERVER['HTTP_USER_AGENT'];
//
//    if (strpos($ua, 'Mozilla/4.0 (compatible; MSIE ') !== false || strpos($ua, 'Opera') !== false) {
//        return false;
//    }
//
//    echo '<pre>';
//    $browser = get_browser(null, true);
//
////    $version = (float)substr($ua, 30);
//    var_dump($browser);
////    var_dump($version);
//    die;
//    return (
//        $version < 6 || ($version == 6 && strpos($ua, 'SV1') === false)
//    );

    return true;
}


/** Check register globals and remove them **/
function unregisterGlobals()
{
    if (ini_get('register_globals')) {

        $array = ['_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES'];

        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}

/** Check if environment is development and display errors **/
function setReporting()
{
    if (DEVELOPMENT_ENVIRONMENT == true) {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', ROOT . DS . 'tmp' . DS . 'logs' . DS . 'error.log');
    }
}

function hook()
{

    $routes = [];
    $routesFileName = ROOT . DS . 'app' . DS . 'routes.php';
    if (file_exists($routesFileName)) {
        $routes = include($routesFileName);
    }

    $router = new Router();
    $router->setBasePath(BASE_PATH);
    $router->addRoutes($routes);

    // get the request
    $request = BASE_PATH . '/' . str_replace('_url=', '', $_SERVER['QUERY_STRING']);
    if(substr($request, -1) == '/') {
        $request = substr($request, 0, -1);
    }

    $request = preg_replace('/\&.*/', '', $request);

    $match = $router->match($request, $_SERVER['REQUEST_METHOD']);

    try {

        if ($match === false) {

            throw new Exception404();

        } else {

            list($controller, $action) = explode('#', $match['target']);

            $controllerName = $controller;
            $controller = str_replace('Controller', '', $controller);

            if (class_exists($controllerName)) {

                if (method_exists($controllerName, $action)) {

                    $dispatch = new $controllerName($controller, $action);
                    HTTP::addToRewindQueue();

                    if (method_exists($controllerName, 'beforeAction')) {
                        $dispatch->beforeAction($match['params']);
                    }

                    $dispatch->$action($match['params']);

                    if (method_exists($controllerName, 'afterAction')) {
                        $dispatch->afterAction($match['params']);
                    }

                    if (!$dispatch->render) HTTP::removePageFromHistory();

                } else throw new Exception('action method does not exist');

            } else throw new Exception('class ' . $controllerName . ' does not exist');

        }

    } catch (Exception404 $e) {

        header('HTTP/1.1 404 Not Found');
        exit;

    } catch (Exception $e) {

        if ($_ENV['DEVELOPMENT_ENVIRONMENT'] == 'true') {
            var_dump($e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            exit;
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            exit;
        }
    }
}

gzipOutput() || ob_start('ob_gzhandler');
$inflect = new Inflection();
setReporting();
unregisterGlobals();
hook();
