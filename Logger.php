<?php

namespace Lifyzer\Api;

class Logger
{
    public function __construct()
    {
        ini_set('log_errors', 'On');
        ini_set('error_log', __DIR__ . '/logs/php_error.log');
        ini_set('ignore_repeated_errors', 'On');
    }

    public function showErrors()
    {
        error_reporting(E_ALL); // Since PHP 5.4 E_STRICT became part of E_ALL
        ini_set('display_errors', 'On');
        ini_set('display_startup_errors', 'On');
        ini_set('track_errors', 'On');
        ini_set('html_errors', 'On');
    }

    public function hideErrors()
    {
        error_reporting(0);
        ini_set('display_errors', 'Off');
        ini_set('display_startup_errors', 'Off');
        ini_set('track_errors', 'Off');
        ini_set('html_errors', 'Off');
    }

    public function log($identifer, $content): void
    {
        echo '<pre>';
        echo $identifer;
        echo "<br>";
        print_r($content);
        echo "<br>";
        echo '<pre>';
    }

    public function writeToFile($identifer, $content, $filename): void
    {
        $logtime = date('m/d/Y h:i:s a', time());

        // The new person to add to the file
        $person = $logtime . "\n\n $identifer" . serialize($content);// Write the contents to the file,

        // using the FILE_APPEND flag to append the content to the end of the file
        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        file_put_contents($filename, $person, FILE_APPEND | LOCK_EX);
    }
}
