<?php

class HTML
{
    public static function includeHtml($fileNamespace)
    {
        if (file_exists(ROOT . DS . 'app' . DS . 'views' . DS . $fileNamespace)) {
            include (ROOT . DS . 'app' . DS . 'views' . DS . $fileNamespace);
        }
    }

    public static function addScriptToHead($source)
    {
        if (!isset($GLOBALS['framework']['html_head']['scripts'])) {
            $GLOBALS['framework']['html_head']['scripts'] = [];
        }

        if (!in_array($source, $GLOBALS['framework']['html_head']['scripts'])) {
            $GLOBALS['framework']['html_head']['scripts'][] = $source;
        }

    }

    public static function addStyleToHead($source)
    {
        if (!isset($GLOBALS['framework']['html_head']['style'])) {
            $GLOBALS['framework']['html_head']['style'] = [];
        }

        if (!in_array($source, $GLOBALS['framework']['html_head']['style'])) {
            $GLOBALS['framework']['html_head']['style'][] = $source;
        }
    }

    public static function addAlert($message, $type = 'primary')
    {
        if (!isset($_SESSION['framework']['html']['alerts'][$type])) {
            $_SESSION['framework']['html']['alerts'][$type] = [];
        }

        if (!in_array($message, $_SESSION['framework']['html']['alerts'][$type])) {
            $_SESSION['framework']['html']['alerts'][$type][] = $message;
        }
    }

    public static function displayAlerts()
    {
        $html = '';

        if (!empty($_SESSION['framework']['html']['alerts'])) {

            foreach ($_SESSION['framework']['html']['alerts'] as $type => $typeAlerts) {
                foreach ($typeAlerts as $message) {
                    $html .= '<div class="alert alert-' . $type . '" role="alert">';
                    $html .= $message;
                    $html .= '</div>';
                }
            }

            unset($_SESSION['framework']['html']['alerts']);
        }

        return $html;

    }

    public static function displayHead()
    {
        $html = '';

        if (!empty($GLOBALS['framework']['html_head']['scripts'])) {
            foreach ($GLOBALS['framework']['html_head']['scripts'] as $source) {
                $html .= '<script src="' . $source . '"></script>';
            }
        }

        if (!empty($GLOBALS['framework']['html_head']['style'])) {
            foreach ($GLOBALS['framework']['html_head']['style'] as $source) {
                $html .= '<link rel="stylesheet" href="' . $source . '">';
            }
        }

        return $html;
    }

}