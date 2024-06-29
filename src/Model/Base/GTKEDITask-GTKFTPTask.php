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
	public $connection;

	public function getConnection()
	{
		if (!$this->connection)
		{
        	$this->connection = ftp_connect($this->host);

        	if (!$this->connection)
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
		}

		return $this->connection;
	}

	public function send()
	{
		$ftp_conn = $this->getConnection();

		if (!$ftp_conn)
		{
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
		gtk_log(print_r($meta_data, true));
        $tempFilePath = $meta_data['uri'];

        $success = ftp_put($ftp_conn, $this->remoteFileName, $tempFilePath, FTP_BINARY);

		if (!$success)
		{
			$error = error_get_last();

			gtk_log("Error uploading file: ".$this->remoteFileName." to host: ".$this->host." with user: ".$this->user." error: ".print_r($error, true));
			gtk_log(print_r($error, true));
		
			if ($this->attemptNumber > $this->attemptErrorThreshold)
			{
				$subject = "Error subiendo archivo a host: ".$this->host." con usuario: ".$this->user." para enviar archivo: ".$this->remoteFileName;
				$body    = $subject."\n\n\n\Sending...\n\n\n".$this->content."\n\nError details: ".print_r($error, true);
			
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
