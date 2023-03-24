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

    public static function downForMaintenance()
    {
        echo '<!doctype html>
                <title>Site Maintenance</title>
                <style>
                  body { text-align: center; padding: 150px; }
                  h1 { font-size: 50px; }
                  body { font: 20px Helvetica, sans-serif; color: #333; }
                  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
                  a { color: #dc8100; text-decoration: none; }
                  a:hover { color: #333; text-decoration: none; }
                </style>
                
                <article>
                    <h1>We&rsquo;ll be back soon!</h1>
                    <div>
                        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. If you need to you can always <a href="mailto:info@esquaredholdings.com">contact us</a>, otherwise we&rsquo;ll be back online shortly!</p>
                        <p>&mdash; The E<sup>2</sup> Team</p>
                    </div>
                </article>';
        exit;
    }

}