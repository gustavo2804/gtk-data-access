<?php

class GTKCmdJob
{
    public function run()
    {
        $oldErrorLog = ini_get('error_log');

        $className    = get_class($this);
        $date         = date('Y-m-d');

        global $_GLOBALS;

        $logBaseDirectory = $_GLOBALS['LOG_BASE_DIRECTORY'] ?? findRootLevel()."/logs/";
        
        $logDirectory = $logBaseDirectory."/{$className}/";

        if (!file_exists($logDirectory)) 
        {
            mkdir($logDirectory, 0777, true);
        }

        $logFile = $logDirectory . "{$className}_{$date}.log";

        ini_set('error_log', $logFile);
        $this->main();
        ini_set('error_log', $oldErrorLog);
    }

    public function main()
    {
        error_log("Running GTKCmdJob from Command Job.");
    }
}
