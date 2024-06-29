<?php

enum GTKLogFileResolution : string
{
    case EVERYTIME = "everytime";
    case HOURLY    = "hourly";
    case DAILY     = "daily";
    case WEEKLY    = "weekly";
    case BIWEEKLY  = "biweekly";
    case MONTHLY   = "monthly";
    case YEARLY    = "yearly";
}
class GTKCmdJob
{
    public $logFileResolution   = GTKLogFileResolution::DAILY;
    public $runWithLock         = true;
    public $runWithTimeOut      = false;
    public $timeoutTime         = 10;
    public $onTimeout           = null;


    public function getOnTimeoutFunction() 
    {
        if ($this->onTimeout)
        {
            return $this->onTimeout;
        }
        else
        {
            return function() {
                error_log("Timeout while acquiring lock on file:".get_class($this));
                echo "Timeout while acquiring lock on file: ".get_class($this)."\n";
            };
        }
    }

    public function logFileName()
    {
        switch ($this->logFileResolution->value)
        {
            case GTKLogFileResolution::EVERYTIME:
                return date('Y-m-d_H-i-s').".log";
            case GTKLogFileResolution::HOURLY:
                return date('Y-m-d_H').".log";

            case GTKLogFileResolution::WEEKLY:
                return date('Y-W').".log";
            case GTKLogFileResolution::BIWEEKLY:
                return date('Y-W').".log";
            case GTKLogFileResolution::MONTHLY:
                return date('Y-m').".log";
            case GTKLogFileResolution::YEARLY:
                return date('Y').".log";

            case GTKLogFileResolution::DAILY:
            default:
                return date('Y-m-d').".log";
        
        }
    }
    public function run()
    {
        $oldErrorLog = ini_get('error_log');

        $className    = get_class($this);
        $date         = date('Y-m-d');

        global $_GLOBALS;

        $logDirectory = null;
        $logFilePath  = null;

        if (isset($_GLOBALS['LOG_BASE_DIRECTORY_PATH_ARRAY']) && count($_GLOBALS['LOG_BASE_DIRECTORY_PATH_ARRAY'])) 
        {
            $logPathComponents = $_GLOBALS['LOG_BASE_DIRECTORY_PATH_ARRAY'];

            $logPathComponents[] = get_class($this);

            if (!file_exists(implode(DIRECTORY_SEPARATOR, $logPathComponents))) 
            {
                mkdir(implode(DIRECTORY_SEPARATOR, $logPathComponents), 0777, true);
            }

            $logPathComponents[] = $this->logFileName();

            $logFilePath = implode(DIRECTORY_SEPARATOR, $logPathComponents);
        }
        else
        {
            $logBaseDirectory = findRootLevel()."/logs/";
            
            $logDirectory = $logBaseDirectory."/{$className}/";

            if (!file_exists($logDirectory)) 
            {
                mkdir($logDirectory, 0777, true);
            }

            $logFilePath = $logDirectory . "{$className}_{$date}.log";
        }

        ini_set('error_log', $logFilePath);
        if ($this->runWithLock)
        {
            if ($this->runWithTimeOut)
            {
                GTKTimeOutLock::withLockDo(get_class($this), function() {
                    $this->main();
                }, $this->timeoutTime, $this->getOnTimeoutFunction());
            }
            else
            {
                GTKLockManager::withLockDo(get_class($this), function() {
                    $this->main();
                });
            }
        }
        else
        {
            $this->main();
        }
        ini_set('error_log', $oldErrorLog);
    }

    public function main()
    {
        error_log("Running GTKCmdJob from Command Job.");
    }
}
