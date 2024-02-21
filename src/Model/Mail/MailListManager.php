<?php


class ReportSender
{
    public static function sendReport($reportName, $title, $body, $options = null)
    {
        $emailQueue = DataAccessManager::get('email_queue');

        $sendTo        = DataAccessManager::get('mail_list_manager')->getSendToForReport($reportName);

        $emailOptions = [];

        $emailOptions["ccRecipients"]  = DataAccessManager::get('mail_list_manager')->getCCForReport($reportName);
        $emailOptions["bccRecipients"] = DataAccessManager::get('mail_list_manager')->getBCCForReport($reportName);   
        
        if (!$sendTo)
        {
            throw new Exception("No send to for report: ".$reportName);
        }
    
        $emailQueue->addToQueue(
            $sendTo,
            $title, 
            $body, 
            $emailOptions
        );
    }
}

function globalIfExists($toSearch)
{
    global $_GLOBALS;

    if (isset($_GLOBALS[$toSearch]))
    {
        return $_GLOBALS[$toSearch];
    }

    return null;
}

class MailListManager
{
    public $dataAccessorName = "MailListManager";

    public function getSendToForReport($reportName)
    {
        return globalIfExists("Report/".$reportName."/SendTo");
    }

    public function getCCForReport($reportName)
    {
        return globalIfExists("Report/".$reportName."/CC");
    }

    public function getBCCForReport($reportName)
    {
        return globalIfExists("Report/".$reportName."/BCC");
    }
}