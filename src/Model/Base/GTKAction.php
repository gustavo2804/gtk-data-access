<?php

class GTKEDITask
{
	public $host;
	public $user;
	public $password;
	public $remoteFileName;
	public $content;
	public $attemptNumber;
	public $attemptErrorThreshold = 5;

	public function send()
	{
		throw new Exception(get_class($this)." - Not implemented");
	}
}

class GTKSFTPTask extends GTKEDITask
{
	public function send()
	{
		// Sends with phpseclib
		$sftp = new \phpseclib\Net\SFTP($this->host);

		if (!$sftp->login($this->user, $this->password)) 
		{
			$subject = "Error de login. Clave denegada";
			$body    = $subject."\n\n\n\Sending...n\n\n".$this->content;
			DataAccessManager::get("email_queue")->reportError($subject, $body);
			return;
		}

		$success = $sftp->put($this->remoteFileName, $this->content);
		
		if (!$success) 
		{
			$subject = "Error subiendo archivo a host: ".$this->host." con usuario: ".$this->user;
			$body    = $subject."\n\n\n\Sending...n\n\n".$this->content;
			DataAccessManager::get("email_queue")->reportError($subject, $body);
		}

		return $success;
	}
}


class GTKFTPTask extends GTKEDITask
{
	public function send()
	{

        $ftp_conn = ftp_connect($this->host);

        if (!$ftp_conn)
        {
			$subject = "Error al conectar con el servidor FTP: ".$this->host." para enviar archivo: ".$this->remoteFileName;
			$body    = $subject."\n\n\n\Sending...n\n\n".$this->content;

			gtk_log($subject.$body);
			
			if ($this->attemptNumber > $this->attemptErrorThreshold)
			{
            	DataAccessManager::get("email_queue")->reportError($subject, $body);
			}

            return false;
        }


        if (!ftp_login($ftp_conn, $this->user, $this->password)) 
        {
			$subject = "Error de login. Clave denegada para enviar archivo: ".$this->remoteFileName;
			gtk_log($subject);
			if ($this->attemptNumber > $this->attemptErrorThreshold)
			{
            	$body    = $subject."\n\n\n\Sending...n\n\n".$this->content;
            	DataAccessManager::get("email_queue")->reportError($subject, $body);
			}

            ftp_close($ftp_conn);

            return false;
        }
        
        $tempFile = tmpfile();
        fwrite($tempFile, $this->content);
        rewind($tempFile);
        $meta_data = stream_get_meta_data($tempFile);
        $tempFilePath = $meta_data['uri'];

        $success = ftp_put($ftp_conn, $this->remoteFileName, $tempFilePath, FTP_ASCII);

        if (!$success)
        {
			if ($this->attemptNumber > $this->attemptErrorThreshold)
			{
				$subject = "Error subiendo archivo a host: ".$this->host." con usuario: ".$this->user." para enviar archivo: ".$this->remoteFileName;
				$body    = $subject."\n\n\n\Sending...n\n\n".$this->content;
	
				DataAccessManager::get("email_queue")->reportError($subject, $body);
			}

			return false;
        }

        ftp_close($ftp_conn);
        fclose($tempFile);

        return $success;
	}

}

class GTKHTTPTask extends GTKEDITask
{
	public function send()
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->host);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close ($ch);

		return $server_output;
	}
}

class GTKAction extends GTKDataAccessLink
{
	public $doObjectForUserItemDelegateOptions;
	public $doFunctionForUserItemDelegateOptions;

    public function __construct($dataSource, $permission, $function, $options)
    {
		$options["key"] = "/action.php"." ".$permission."_".$function;

		parent::__construct($dataSource, $permission, "/action.php", $options);
		
		$this->doFunctionForUserItemDelegateOptions = $function;
    }


    
    //--------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------
    //--------------------------------------------------------------------------------

    /* 
     * Should return an array of messages.
     * Or interact with the delegate.anchorLinkForItem
     * 
     */


	public function doActionForUserItem(&$user, &$item, &$delegate = null, &$options = null)
	{
		$debug = false;

		if ($debug)
		{
			error_log("`doActionForUserItem` - trying...");
			error_log("Object ".is_null($this->doObjectForUserItemDelegateOptions));
			error_log("Function ".print_r($this->doFunctionForUserItemDelegateOptions, true));
		}

		if ($this->doObjectForUserItemDelegateOptions)
		{
			if ($debug)
			{
				error_log("Trying object `doObjectForUserItemDelegateOptions` with function option");
			}
			$doFunctionForUserItemDelegateOptions = $this->doFunctionForUserItemDelegateOptions;
			return $this->doObjectForUserItemDelegateOptions->$doFunctionForUserItemDelegateOptions($user, $item, $delegate, $options);
		}
		else if ($this->doFunctionForUserItemDelegateOptions)
		{
			if ($debug)
			{
				error_log("Trying function `doFunctionForUserItemDelegateOptions` with function option");
			}
			$isCallable = is_callable($this->doFunctionForUserItemDelegateOptions);

			if ($debug)
			{
				error_log("Is callable: $isCallable");
			}

			if ($isCallable)
			{
				if ($debug)
				{
					error_log("Going from callable");
				}
				$doFunctionForUserItemDelegateOptions = $this->doFunctionForUserItemDelegateOptions;
				return $doFunctionForUserItemDelegateOptions($user, $item, $delegate, $options);
			}

			$methodExists = method_exists($this->dataSource, $this->doFunctionForUserItemDelegateOptions);
			
			if ($debug)
			{
				error_log("Method exists: $methodExists");
			}


			if ($methodExists)
			{
				return $this->dataSource->{$this->doFunctionForUserItemDelegateOptions}($user, $item);
			}

			throw new Exception("No action defined for this action: ".$this->doFunctionForUserItemDelegateOptions." on ".get_class($this->dataSource)." for ".$this->permission);
		}
		else if (method_exists($this->dataSource, $this->permission))
		{
			return $this->dataSource->{$this->permission}($user, $item);
		}
		

		throw new Exception("No action defined for this action: ".$this->permission);
	}



    public function shouldEchoResultForUserItem($user, $item, $delegate = null, $options = null)
    {
        return true;
    }



    public function defaultActionForUserItemDelegateOptions($user, $item, $delegate, $options)
	{
		$toPublish = "";

		try
		{
			$this->doActionForUserItem($user, $item);

            $toPublish .= "Su acción: (".$this->labelForUserItem($user, $item).") fue ejecutada exitosamente.";
		}
		catch (Exception $e)
		{
			$toPublish .= "Hubo un problema ejecutando esta acción: (".$this->doActionForUserItem($user, $item).") Intente nuevamente o reporte al administrador.";
		}

		$toPublish .= "<br/>";
		// $toPublish .= '<a href="'.AllLinkTo("persona").'">Volver a lista</a>';
		$toPublish .= "<br/>";
		$toPublish .= '<a href="/">Ir a inicio</a>';

		die();
	}
}
