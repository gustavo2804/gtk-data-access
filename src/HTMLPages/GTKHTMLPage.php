<?php

use function Deployer\desc;

class GTKHTMLPage
{
	public $get; 
	public $post; 
	public $server;
	public $cookie;
	public $phpSession;
	public $files;
	public $env;
	
	public $didSearchForCurrentUser;
	public $didSearchForCurrentSession;

	//----------------------------------------------------------
	public $messages        = [];
	public $user;
	public $session;

	public $headerPath;
	public $footerPath;

	public bool    $authenticationRequired = false; // ovveride in construct
	public ?string $permissionRequired 	   = null;  // override in construct

	public function __construct($options = [])
	{
	}

	public function setAuthenticate($toSet)
	{
		$this->authenticate = $toSet;
	}

	public function setAuthorize($toSet)
	{
		$this->authorize = $toSet;
	}

	public function currentPath()
	{
	    if (isset($_SERVER["PATH_INFO"])) {
			return $_SERVER["PATH_INFO"];
		} elseif (isset($_SERVER["REQUEST_URI"])) {
			return parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
		}
		return null; // Or throw an exception if preferred
	}

	public function getClientIP() 
	{
		$ipAddress = null;
		
		if (!empty($this->server['HTTP_CLIENT_IP'])) 
		{
			$ipAddress = $this->server['HTTP_CLIENT_IP'];
		} 
		elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) 
		{
			$ipAddress = $this->server['HTTP_X_FORWARDED_FOR'];
		} 
		else 
		{
			$ipAddress = $this->server['REMOTE_ADDR'];
		}
		
		return $ipAddress;
	}



	//--- Process GET, POST, PUT, PATCH, renderBODY - Overidable Methods
	//----------------------------------------------------------
	//----------------------------------------------------------

	public function processGet($getObject)
	{
		$debug = false;

		if ($debug)
		{
			error_log("Process GET Basic: ".print_r($getObject, true));
		}
		// Does nothing..

		
	}

	public function processPost($postObject, $files)
	{
		$debug = false;

		if ($debug)
		{
			error_log("Process POST Basic: ".print_r($postObject, true));
		}
		// Does nothing..
	}


	public function gtk_renderBody($get, $post, $server, $cookie, $session, $files, $env)
	{
		$toReturn = $this->renderMessages($get, $post, $server, $cookie, $session, $files, $env);

		if (method_exists($this, "renderBody"))
		{
			$toReturn .= $this->renderBody($get, $post, $server, $cookie, $session, $files, $env);
		}
		else
		{
			$toReturn .= "<h1 class='font-bold'>";
			$toReturn .= "No `renderBody` method found";
			$toReturn .= "</h1>";
		}
		
		return $toReturn;
	}
	
	public function renderMessages($get, $post, $server, $cookie, $session, $files, $env)
	{
		$toReturn = "";

		if (count($this->messages) > 0)
		{
			$toReturn .= "<h1 class='font-bold'>";
			$toReturn .= "Mensajes";
			$toReturn .= "</h1>";
			$toReturn .= "<div>";
			foreach ($this->messages as $message)
			{
				$toReturn .= "<div>";
				if (is_string($message))
				{
					$toReturn .= $message;
				}
				else
				{
					$toReturn .= print_r($message, true);
				}
				$toReturn .= "</div>";
			}
			$toReturn .= "</div>";
		}

		return $toReturn;
	}

	public function processPUT(){}
	public function processPatch(){}


	//--- Header, Footer methods
	//---------------------------------------------------------
	//---------------------------------------------------------
	
	public function gtk_renderHeader($get, $post, $server, $cookie, $session, $files, $env)
	{
		$contentType = $this->server["CONTENT_TYPE"];

		switch($contentType)
		{
			case "application/json":
				return "";
			case "application/xml":
				return "";
			case "application/x-www-form-urlencoded":
			case "multipart/form-data":
			case "text/plain":
			case "text/html":
			default:
				$headerPath = $this->headerPath;

				if (!$headerPath)
				{
					$headerPath = dirname($_SERVER['DOCUMENT_ROOT'])."/templates/header.php";
				
				}
			
				require_once($headerPath);
				break;
		}
	}

	public function gtk_renderFooter($get, $post, $server, $cookie, $session, $files, $env)
	{


		$contentType = $this->server["CONTENT_TYPE"];

		switch($contentType)
		{
			case "application/json":
				return "";
			case "application/xml":
				return "";
			case "application/x-www-form-urlencoded":
			case "multipart/form-data":
			case "text/plain":
			case "text/html":
			default:
				$footerPath = $this->footerPath;

				if (!$footerPath)
				{
					$footerPath = dirname($_SERVER['DOCUMENT_ROOT'])."/templates/footer.php";
				
				}
			
				require_once($footerPath);
				break;
		}
	}

	public function handleNotAuthenticated($maybeCurrentUser, $maybeCurrentSession)
	{
		redirectToURL("/auth/login.php", null, [
		]);
	}

	public function handleNotAuthorized($maybeCurrentUser, $maybeCurrentSession)
	{
		die("No autorizado.");
	}

	public function currentSession()
	{
		if (!$this->didSearchForCurrentSession)
		{
			$this->didSearchForCurrentSession = true;
			$this->session = DataAccessManager::get("session")->getCurrentApacheSession([
				"requireValid" => true,
			]);
		}

		return $this->session;
	}

	public function currentUser()
	{
		if (!$this->didSearchForCurrentUser)
		{
			$this->didSearchForCurrentUser = true;
			$this->user = DataAccessManager::get("session")->getCurrentUser();
		}

		return $this->user;
	}

	public function dataSourceFor($name)
	{
		return DataAccessManager::get($name);
	}

	public function getDataSource($name)
	{
		return DataAccessManager::get($name);
	}

	public function isAuthorized()
	{
		$debug = true;

		$isAuthorized = true;

		if ($this->permissionRequired)
		{
			if (!$this->user)
			{
				if ($debug)
				{
					gtk_log("No current user, will redirect...");
				}
				return $this->handleNotAuthenticated($this->user, $this->session);
			}

			$userDataAccess = DataAccessManager::get("persona");

			$isAuthorized = $userDataAccess->hasPermission($this->permissionRequired, $this->user);
		}

		if ($debug)
		{
			$message = "`render` : Is authorized: ".print_r($isAuthorized, true)." for permission: ".$this->permissionRequired;

			error_log($message);
		}

		return $isAuthorized;
	}


	public function render($get, $post, $server, $cookie, $session, $files, $env)
	{
		$debug = false;

		$this->get 		  = $get;
		$this->post 	  = $post;
		$this->server 	  = $server;
		$this->cookie 	  = $cookie;
		$this->phpSession = $session;
		$this->files      = $files;
		$this->env 		  = $env;

		$maybeCurrentUser    = DataAccessManager::get("session")->getCurrentUser();
		$maybeCurrentSession = DataAccessManager::get("session")->getCurrentApacheSession([
			"requireValid" => true,
		]);

		$this->user    = $maybeCurrentUser;
		$this->session = $maybeCurrentSession;
		
		if ($debug)
		{
			error_log("`render` : Got current user: ".print_r($maybeCurrentUser, true));
			error_log("`render` : Got current session: ".print_r($maybeCurrentSession, true));
		}


		if ($this->authenticationRequired && !$this->session)
		{
			if ($debug)
			{
				gtk_log("Authentication required, No user, no session, will redirect...");
			}
			return $this->handleNotAuthenticated($this->user, $this->session);
		}



		if (!$this->isAuthorized())
		{
			return $this->handleNotAuthorized($maybeCurrentUser, $maybeCurrentSession);
		}

		$this->processGet($get);

		switch ($this->server["REQUEST_METHOD"])
		{
			case "POST":
				$toReturn = $this->processPost($post, $files);
				if ($toReturn)
				{
					return $toReturn;
				}
				break;
			case "PATCH":
				$this->processPatch($post);
				break;
			case "PUT":
				$this->processPut($post);
				break;
		}

		$text = "";
		$text .= $this->gtk_renderHeader($get, $post, $server, $cookie, $session, $files, $env);
		$text .= $this->gtk_renderBody($get, $post, $server, $cookie, $session, $files, $env);
		$text .= $this->gtk_renderFooter($get, $post, $server, $cookie, $session, $files, $env);
		return $text;
	}

	function htmlEscape($string)
	{
    	if ($string)
    	{
        	return htmlspecialchars($string);
    	}
	}

}

class GTKEditPage extends GTKHTMLPage
{

}



class GTKEntityPage extends GTKHTMLPage
{
	public $entity;
	public $entityDataSourceName;

	public function entityValue($key)
    {
        
        return $this->getDataSource($this->entityDataSourceName)->valueForKey($key, $this->entity);
    }

}
