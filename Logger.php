<?php

namespace Lifyzer\Api;

class Logger
{
    public function log($identifer, $content): void
    {
        echo '<pre>';
        echo $identifer;
        echo "<br>";
        print_r($content);
        echo "<br>";
        echo '<pre>';
    }

    public function writetoFile($identifer, $content, $filename): void
    {
        $logtime = date('m/d/Y h:i:s a', time());

        // The new person to add to the file
        $person = $logtime . "\n\n $identifer" . serialize($content);// Write the contents to the file,

        // using the FILE_APPEND flag to append the content to the end of the file
        // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
        file_put_contents($filename, $person, FILE_APPEND | LOCK_EX);
    }
}
