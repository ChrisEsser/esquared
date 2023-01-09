<?php

class Debug
{
    public static function dump($var, $comment = '', $asText = false)
    {
        if (substr($_SERVER['REMOTE_ADDR'], 0, 8) == '192.168.' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {

            if ($asText) {
                ob_start();
                var_dump($var);
                $var = ob_get_contents();
                ob_end_clean();
            }

            if (empty($comment)) {
                if (function_exists('xdebug_get_function_stack')) {
                    $trace = xdebug_get_function_stack();
                    $info = $trace[count($trace) - 1];
                } else {
                    $trace = @debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                    $info = $trace[0];
                }
            } else {
                $info = ['file' => $comment, 'line' => ""];
            }

            $_SESSION['framework']['dump'][] = [$info, $var];
        }
    }

    public static function dump_shutdown()
    {

        if (substr($_SERVER['REMOTE_ADDR'], 0, 8) == '192.168.' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {


            if (isset($_SESSION['framework']['dump'])) {

                if (function_exists('xdebug_get_function_stack')) {
                    ini_set('xdebug.var_display_max_children', 200);
                    ini_set('xdebug.var_display_max_depth', 12);
                    ini_set('xdebug.var_display_max_data', 1536);
                }

                echo '<br />';

                $dumpData = $_SESSION['framework']['dump'];
                unset($_SESSION['framework']['dump']);

                foreach ($dumpData as $var) {

                    $tmpLine = empty($var[0]['line']) ? "" : '&nbsp;&nbsp;&nbsp;Line: ' . $var[0]['line'];
                    echo '<div style="background-color:#e1e1ed;border-top:1px solid #000;font-family:Arial,Helvetica,sans-serif;';
                    echo 'font-size:10pt;padding:8px;text-align:left;">', $var[0]['file'], $tmpLine, '</div>', "\n";
                    echo '<div style="background-color:#fff;font-family:Courier New,Courier,monospace;font-size:10pt;margin-top:2px;margin-bottom: 25px;padding:8px;text-align:left;">', "\n";

                    if (is_string($var[1])) {
                        echo '<small>string</small> <span style="color:#c00">\'', nl2br(htmlentities($var[1])), "'</span> ";
                        echo '<i>(length=', strlen($var[1]), ')</i>';
                    } else if (is_int($var[1])) {
                        echo '<small>int</small> <span style="color:#4e9a06">', $var[1], '</span>';
                    } else if (is_float($var[1])) {
                        echo '<small>float</small> <span style="color:#f57900">', $var[1], '</span>';
                    } else if (is_bool($var[1])) {
                        $var[1] = ($var[1]) ? 'true' : 'false';
                        echo '<small>boolean</small> <span style="color:#75507b">', $var[1], '</span>';
                    } else if (function_exists('xdebug_get_function_stack')) {
                        var_dump($var[1]);
                    } else if (is_object($var[1])) {
                        echo '<pre>';
                        var_dump($var[1]);
                        echo '</pre>';
                    } else if (function_exists('krumo')) {
                        krumo($var[1]);
                    } else {
                        echo '<pre>';
                        var_dump($var[1]);
                        echo '</pre>';
                    }
                    echo '</div>';
                }
            }
        }
    }

}