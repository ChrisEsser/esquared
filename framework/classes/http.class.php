<?php

class HTTP
{
    public static function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public static function rewind($stepsBack = 0)
    {

        $tmpRewind = &$_SESSION['framework']['http']['rewind'];
        if (empty($tmpRewind)) self::noHistoryRewind();

        if ($stepsBack < 1) $stepsBack = 1;
        if ($stepsBack > count($tmpRewind)) $stepsBack = count($tmpRewind);

        // pop the specified number of pages off the history stack
        for ($i = 0; $i <= $stepsBack; $i++) {
            $tmpQueryString = array_pop($tmpRewind);
            $tmpQueryString = (isset($tmpQueryString['url'])) ? $tmpQueryString['url'] : '';
        }

        if (empty($tmpQueryString) && empty($tmpRewind)) {

            $tmpParams = [];
            foreach ($_GET as $key => $value) {
                $tmpParams[] = urlencode($key) . '=' . urlencode($value);
            }
            $tmpQueryString = implode('&', $tmpParams);

        } else if (!empty($tmpQueryString)) {

            parse_str($tmpQueryString, $params);
            $tmpQueryString = http_build_query($params);

        }

        $tmpQueryString = str_replace('_url=', '', $tmpQueryString);

        $url = '/';
        $url .= (!empty($tmpQueryString)) ? $tmpQueryString : '';

        self::redirect($url);
    }

    public static function rewindQuick()
    {
        $tmpRewind = &$_SESSION['framework']['http']['rewind'];
        if (empty($tmpRewind)) self::noHistoryRewind();

        $tmpQueryString = array_pop($tmpRewind);
        $tmpQueryString = $tmpQueryString['url'];

        $tmpQueryString = str_replace('_url=', '', $tmpQueryString);

        $url = '/';
        $url .= (!empty($tmpQueryString)) ? $tmpQueryString : '';

        self::redirect($url);
    }

    private static function noHistoryRewind()
    {
        $tmpParams = [];
        foreach ($_GET as $key => $value) {
            if ($key != '_url') {
                $tmpParams[] = urlencode($key) . '=' . urlencode($value);
            }
        }

        $tmpQueryString = implode('&', $tmpParams);
        $url = '/';
        $url .= (!empty($tmpQueryString)) ? '?' . $tmpQueryString : '';
        self::redirect($url);
    }

    /**
     * method to add page to the rewind queue.
     *
     * add this page to the history if:
     *  - no POST variables have been passed
     *  - removePageFromHistory() hasn't been called in the controller
     *  - we will not even get here if $this->render has been set to false in the controller
     */
    public static function addToRewindQueue()
    {
        if (!isset($_SESSION['framework']['http']['rewind']) || !is_array($_SESSION['framework']['http']['rewind'])) {
            $_SESSION['framework']['http']['rewind'] = [];
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_GET['neverrewind'])) {

            $tmpQueryString = str_replace('_url=', '' , $_SERVER['QUERY_STRING']);

            $tmpCount = count($_SESSION['framework']['http']['rewind']);

            // pull the second to last URL to see if maybe the user hit the back button
            $tmpUrl = isset($_SESSION['framework']['http']['rewind'][$tmpCount - 2]['url']) ? $_SESSION['framework']['http']['rewind'][$tmpCount - 2]['url'] : "";

            if ($tmpQueryString == $tmpUrl) {
                array_pop($_SESSION['framework']['http']['rewind']);
                array_pop($_SESSION['framework']['http']['rewind']);
                $tmpCount = count($_SESSION['framework']['http']['rewind']);
            }

            // pull the last URL to make sure it's not a duplicate
            $tmpUrl = (isset($_SESSION['framework']['http']['rewind'][$tmpCount - 1]['url']))
                ? $_SESSION['framework']['http']['rewind'][$tmpCount - 1]['url']
                : '';

            if ($tmpQueryString != $tmpUrl) {

                if ($tmpCount > 9) { // Limit the rewind queue to 10 pages
                    $_SESSION['framework']['http']['rewind'] = array_slice($_SESSION['framework']['http']['rewind'], -9);
                    $tmpCount = count($_SESSION['framework']['http']['rewind']);
                }

                // otherwise, add this entry to the stack
                $_SESSION['framework']['http']['rewind'][$tmpCount]['url'] = $_SERVER['QUERY_STRING'];
            }
        }

    }

    public static function removePageFromHistory()
    {
        if (isset($_SESSION['framework']['http']['rewind'])) {

            $last = end($_SESSION['framework']['http']['rewind']);
            if (!empty($last['url']) && $last['url'] == $_SERVER['QUERY_STRING']) {

                array_pop($_SESSION['framework']['http']['rewind']);

            }
            reset($_SESSION['framework']['http']['rewind']);

        }

        return;
    }

}