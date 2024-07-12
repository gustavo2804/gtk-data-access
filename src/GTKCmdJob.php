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

class UNEDIFACTGateInGateOutFile
{
    public $nLines;
    public $ediString ='';
    public $SEGMENT_TERMINATOR = "'";

    public $interchangeControlReference;
    public $senderID; 
    public $recipientID;
    public $lineaID;
    public $dateTimePreparation;
    public $bookingReferenceNumber;
    public $documentMessageName      = "36"; // References Gate In/Out - 
    public $messageReferenceNumber   = "0031"; // Gate In/Out -
    public $containerNumber;
    public $containerISOType;
    public $equipmentStatus;
    public $locationIdentifier;
    public $movementType;
    public $transportStageQualifier;
    public $transportReferenceNumber;
    public $transportName;
    public $dateAndTime;

    public $fullEmptyIndicator;
    public $containerSupplierLeseeIndicator;
    public $positionOfEquipment;

    public $currentSegments;

    public function beginMessage()
    {
        $this->currentSegments = [];
    }

    public function addSegment($line)
    {
        $this->currentSegments[] = $line;
    }

    public function endMessage()
    {
        foreach ($this->currentSegments as $segment) 
        {
            $this->writeLine($segment);
        }

        $nSegments = count($this->currentSegments) + 1;

        $this->writeLine("UNT+".$nSegments.":".$this->messageReferenceNumber);

        $this->currentSegments = null;
    }

    public function writeLine($line)
    {
        if (is_string($line))
        {
            $this->ediString .= $line.$this->SEGMENT_TERMINATOR."\n";
        }
    }

    public function writeEDIFile()
    {
        /*
        The basic structure of an EDIFACT interchange typically looks like this:
        
        UNB (Interchange Header) - Not a segment
        UNH (Message Header)
        [Message segments...]
        UNT (Message Trailer)
        UNH (Message Header)
        [Message segments...]
        UNT (Message Trailer)
        UNZ (Interchange Trailer) - Not a segment

        The UNT (Message Trailer) segment does indeed include a count of segments, but it counts:

        All segments within the message, starting from and including the UNH (Message Header).
        The UNT segment itself.

        */
        /*
        a plus sign    (+)    for an addition
        an asterisk    (*)    for an amendment to structure
        a hash sign    (#)    for changes to names
        a vertical bar (|)    for changes to text for descriptions and notes
        a minus sign   (-)    for marked for deletion (within either batch and interactive messages)
        a X sign       (X)    for marked for deletion (within both batch and interactive messages)
        */

        

        /* UNB - Interchange Header            */ $this->writeLine("UNB+UNOA:2+{$this->senderID}+{$this->recipientID}+{$this->dateTimePreparation}+{$this->interchangeControlReference}");
        /* UNH - Message Header                */ $this->writeLine("UNH+{$this->messageReferenceNumber}+CODECO:D:95B:UN:ITG13");
        /* BGM - Beginning of Message          */ $this->writeLine("BGM+{$this->documentMessageName}++9");
        /* TDT - Transport Details             */ $this->writeLine("TDT+{$this->transportStageQualifier}++1++:172:ZZZ+++146");
        /* NAD - Name and Address              */ $this->writeLine("NAD+CF+".$this->lineaID.":160:166");
        /* EQD - Equipment Details             */ $this->writeLine("EQD+CN+{$this->containerNumber}+{$this->containerISOType}:102:5+{$this->fullEmptyIndicator}+{$this->containerSupplierLeseeIndicator}+{$this->equipmentStatus}");
        /* RFF - Reference                     */ $this->writeLine("RFF+BN:{$this->bookingReferenceNumber}");
        /* TMD - Transport Movement Details    */ $this->writeLine("TMD+{$this->movementType}");
        /* DTM - Date/Time/Period              */ $this->writeLine("DTM+".UNDateTimePeriodFunctionCodeQualifier::EffectiveFromDatetime->value.":".$this->dateAndTime->format('YmdHi').":".UNDateTimeFormatCode::CCYYMMDDHHMM->value);
        /* MEA - Measurements                  */ $this->writeLine("MEA+AAE+T+KGM:1900");
        /* TDT - Transport Details (Repeated)  */ $this->writeLine("TDT+1++3++{$this->transportReferenceNumber}:172:ZZZ+++{$this->transportReferenceNumber}:146::{$this->transportName}");
    
        // LOC Segment - Location
        // ----------- - LOC: Location segment.
        // ----------- - 165: The location qualifier indicating the type of location, likely an "Operational Port Location" or "Place of Delivery."
        // ----------- - STW: The specific location code, indicating the port or depot.
        // ----------- - 139: The code list responsible agency.
        // ----------- - 6: The specific sub-location or area within the location.
        // ----------- - STW: Reinforcement or additional context for the location code.
        // ----------- - TER: Another qualifier, likely indicating a terminal or specific area.
        // ----------- - ZZZ: A mutually defined code indicating an agreed-upon location or terminal code.
        $this->writeLine("LOC+165+{$this->locationIdentifier}:139:6:STW+TER:ZZZ");
    
        $numberOfLineItemQuanities = 16;
        // CNT Segment - Control Total
        $this->writeLine("CNT+".$numberOfLineItemQuanities.":".UNControlTotalQualifier::TOTAL_NUMBER_OF_LINE_ITEM_QUANTITIES->value);
    
        $numberOfSegments        = 12;
        $numberOfSegementsPadded = str_pad($numberOfSegments, 6, "0", STR_PAD_LEFT);
        // UNT Segment - Message Trailer
        $this->writeLine("UNT+".$numberOfSegementsPadded."+{$this->messageReferenceNumber}");
    
        // UNZ Segment - Interchange Trailer
        $this->writeLine("UNZ+1+{$this->interchangeControlReference}");


        return $this->ediString;
    }
    
}
class GTKCmdJob
{
    public $logFileResolution   = GTKLogFileResolution::DAILY;
    public $runWithLock         = true;
    public $runWithTimeOut      = false;
    public $lockTimeoutTime     = 10;
    public $commandTimeoutTime  = 30;
    public $onLockTimeout       = null;
    public $onCommandTimeout    = null;


    public function getOnLockTimeoutFunction() 
    {
        if ($this->onLockTimeout)
        {
            if (is_callable($this->onLockTimeout))
            {
                $onLockTimeout = $this->onLockTimeout;
                return $onLockTimeout();
            }
            else
            {
                return $this->onLockTimeout;
            }
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
            
            $logDirectory = $logBaseDirectory."/{$this->className}/";

            if (!file_exists($logDirectory)) 
            {
                mkdir($logDirectory, 0777, true);
            }

            $logFilePath = $logDirectory . "{$this->className}_{$this->date}.log";
        }

        ini_set('error_log', $logFilePath);
        if ($this->runWithLock)
        {
            if ($this->runWithTimeOut)
            {
                GTKTimeOutLock::withLockDo(get_class($this), function() {
                    $this->main();
                }, $this->lockTimeoutTime, $this->getOnLockTimeoutFunction());
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
