<?php

use function Deployer\error;

function startsWith($lookFor, $string)
{
	return strpos($string, $lookFor) === 0;
}


function snakeToSpaceCase($string) {
    // Replace underscores with spaces
    $stringWithSpaces = str_replace('_', ' ', $string);

    // Capitalize the first letter of each word (optional)
    $spaceCaseString = ucwords($stringWithSpaces);

    return $spaceCaseString;
}

function LinkToAllPermissionIfExists($permission, $options = null)
{
	$currentUser = DataAccessManager::get("session")->getCurrentUser();

	if (DataAccessManager::get("persona")->hasPermission($permission, $currentUser))
	{
		$dataSourceName = explode(".", $permission)[0];
		return AllLinkTo($dataSourceName, $options);
	}
	else
	{
		return "";
	}  
}

function LinkToEditItemPermissionIfExists($permission, $item, $options = null)
{
	$currentUser = DataAccessManager::get("session")->getCurrentUser();

	if (DataAccessManager::get("persona")->hasPermission($permission, $currentUser))
	{
		return editLinkTo($permission, $item, $options);
	}
	else
	{
		return "";
	}  
}

function linkTo($maybeHref, $options)
{
	$href = null;

	if (startsWith("/", $maybeHref))
	{
		$href = $maybeHref;
	}
	else
	{
		$href = $maybeHref;
	}

	$id      = $options['class'] ?? '';
	$class   = $options['class'] ?? '';
	$style   = $options['style'] ?? '';
	$label   = $options['label'] ?? $href;

	$toReturn  = "";
	$toReturn .= "<a id='$id' href='$href' class='$class' style='$style'>";
	$toReturn .= $label;
	$toReturn .= "</a>";
	
	return $toReturn;	
}

function ShowURLTo($dataSourceName, $identifier, $options = null)
{
	return DataAccessManager::showURLTo($dataSourceName, $identifier, $options);
}

function AllURLTo($dataSourceName, $options = null)
{
	return DataAccessManager::allURLTo($dataSourceName, $options);
}

function AllLinkTo($dataSourceName, $options = null)
{
	return DataAccessManager::allLinkTo($dataSourceName, $options);
}

if (!function_exists("gtk_log"))
{

	if (strpos($_SERVER['SCRIPT_NAME'], 'phpunit.phar') !== false) {
		// Code is being executed under PHPUnit
		// echo "Running under PHPUnit: ".$_SERVER['SCRIPT_NAME']."\n";
		// echo "\n\n\n";
		function gtk_log($toLog)
		{
			echo $toLog."\n";
		}
	} 
	else 
	{
		// Code is being executed outside of PHPUnit (e.g., by Apache CGI)
		// echo "Not running under PHPUnit: ".$_SERVER['SCRIPT_NAME']."\n";
		// echo "\n\n\n";
		function gtk_log($toLog)
		{
			error_log($toLog);
		}
	}

}

class DataAccessManager 
{
    private $dataAccessors             = [];
    private $dataAccessorConstructions = [];
    private $databaseConfigurations    = [];
    private $databases                 = [];

	public function getDefaultOptionsForSelectForUser($user)
    {
        return [
            "columnValue" => "id",
            "columnName"  => "name",
        ];
    }

	public function generateSelectForUserColumnValueName(
        $user,
		$dataAccessor,
		$objectID,
		$foreignColumnName,
		$foreignColumnValue,
		$options = []
	){
		$debug = false;

    	$language = isset($options['language']) ? $options['language'] : 'spanish';

    	$select = '<select name="' . $foreignColumnName . '">';

		$addNullCase = true;

		if ($addNullCase)
		{
        	$select .= '<option';
			$select .= ' value=""';
			$select .= '>';

			switch ($language)
			{
				case "english":
					$select .= "N / A";
					break;
				case "spanish":
				default:
					$select .= "No aplica";
					break;
			}

			$select .= '</option>';
		}

		$constructions = $this->getConstructions();
		$keys = array_keys($constructions);

		usort($keys, function ($a, $b){
			return strcasecmp($a, $b);
		});

    	foreach ($keys as $key)
		{
            if ($debug)
            {
                gtk_log("Working with key ($key) - construction: ".print_r($constructions[$key], true));
            }

            $label = $key;
            $value = $key;

            if ($debug)
            {
                gtk_log("Got label: ".$label);
                gtk_log("Got value: ".$value);
            }

        	$select .= '<option';
			$select .= ' value="'.$value .'"';
			if ($value === $foreignColumnValue)
			{
				$select .= ' selected ';
			}
			$select .= '>';
			$select .= ucwords($label);
			$select .= '</option>';
    	}
 
    	$select .= '</select>';

        if ($debug)
        {
            gtk_log("Made select: ".$select);
        }

    	return $select;


	}

	public static function validateDataSourceName($maybeDataSourceName)
	{
		$debug = false;

		$dataSourceName = null;

		if (is_object($maybeDataSourceName))
		{
			$dataSourceName = get_class($maybeDataSourceName);
		}
		else if (is_string($maybeDataSourceName))
		{
			$dataSourceName = $maybeDataSourceName;
		}
		
		$theValidName = self::getSingleton()->keyForDataAccessorConstructions($dataSourceName);

		if (!$theValidName)
		{
			throw new Exception("Providing invalid key for data source. Invalid key: $dataSourceName");
		}

		return $theValidName;
	}

	public static function editURLTo($maybeDataSourceName, $identifier, $options = null)
	{
		$dataSourceName = self::validateDataSourceName($maybeDataSourceName);
		$queryParameters = [
			"id" => $identifier,
		];
		return "/$dataSourceName/edit?".http_build_query($queryParameters);
	}

	public static function showURLTo($maybeDataSourceName, $identifier, $options = null)
	{
		$dataSourceName = self::validateDataSourceName($maybeDataSourceName);
		$queryParameters = [
			"id" => $identifier,
		];
		return "/$dataSourceName/show?".http_build_query($queryParameters);
	}

	public static function allURLTo($maybeDataSourceName, $options = null)
	{
		$debug = false;

		if ($debug)
		{
			$toPrint = is_string($maybeDataSourceName) ? $maybeDataSourceName : get_class($maybeDataSourceName);
			error_log("`allURLTo`::Got maybe data source name: ".$toPrint);
			error_log("`allURLTo`::Got options: ".print_r($options, true));
		}

		$dataSourceName = self::validateDataSourceName($maybeDataSourceName);

		if ($debug)
		{
			error_log("`allURLTo`::Got data source name: ".$dataSourceName);
		}

		return "/$dataSourceName/all";
	}


	public static function allLinkTo($dataSourceName, $options = null)
	{
		$debug = false;

		$dataSource = self::get($dataSourceName);
		$href       = self::allURLTo($dataSourceName, $options);

		if ($debug)
		{
			error_log("Got HREF: ".$href);
		}

		$id      = $options['id']    ?? '';
		$class   = $options['class'] ?? '';
		$style   = $options['style'] ?? '';
		$label   = $options['label'] ?? $dataSource->getPluralItemName(); // snakeToSpaceCase($dataSourceName);
		

		$toReturn  = "";
		$toReturn .= "<a";
		$toReturn .= ' id="'.$id.'"';
		$toReturn .= ' href="'.$href.'"';
		$toReturn .= ' class="'.$class.'"';
		$toReturn .= ' style="'.$style.'"';
		$toReturn .= '>';
		$toReturn .= $label;
		$toReturn .= "</a>";

		/*
		if (!$class)
		{
			$toReturn .= "<br/>";
		}
		*/
		
		return $toReturn;
	}

	public static function editLinkTo($dataSourceName, $options)
	{
		$href = "/$dataSourceName/edit";

		$id      = $options['class'] ?? '';
		$class   = $options['class'] ?? '';
		$style   = $options['style'] ?? '';
		$label   = $options['label'] ?? snakeToSpaceCase($dataSourceName);

		$toReturn  = "";
		$toReturn .= "<a id='$id' href='$href' class='$class' style='$style'>";
		$toReturn .= $label;
		$toReturn .= "</a>";
		
		return $toReturn;
	}

	public function getConstructions()
	{
		return $this->dataAccessorConstructions;
	}

	public static function maybeGet($name)
	{
		return self::getAccessor($name, false);
	}

    public static function get($name)
    {
        return self::getAccessor($name);
    }

	public static function configureSystem()
	{
		$debug = false; 

		static $didConfigure;

		if (!$didConfigure)
		{
			$didConfigure = true;
			
			/*
			error_reporting(E_STRICT);
			
			function terminate_missing_variables($errno, $errstr, $errfile, $errline)
			{                               
			  if (($errno == E_NOTICE) and (strstr($errstr, "Undefined variable")))
			  {
				die ("$errstr in $errfile line $errline");
			  }
			
			
			  return false; // Let the PHP error handler handle all the rest  
			}
			*/
			
			// $old_error_handler = set_error_handler("terminate_missing_variables"); 
			
			if (!function_exists("gtk_log"))
			{
			
				if (strpos($_SERVER['SCRIPT_NAME'], 'phpunit.phar') !== false) {
					// Code is being executed under PHPUnit
					// echo "Running under PHPUnit: ".$_SERVER['SCRIPT_NAME']."\n";
					// echo "\n\n\n";
					function gtk_log($toLog)
					{
						echo $toLog."\n";
					}
				} 
				else 
				{
					// Code is being executed outside of PHPUnit (e.g., by Apache CGI)
					// echo "Not running under PHPUnit: ".$_SERVER['SCRIPT_NAME']."\n";
					// echo "\n\n\n";
					function gtk_log($toLog)
					{
						error_log($toLog);
					}
				}
			
			}
			
			date_default_timezone_set('America/Santo_Domingo');
			
			$envPath = null;
			global $_GLOBALS;
		
			if (isset($_GLOBALS["ENV_FILE_PATH"]))
			{
				$envPath = $_GLOBALS["ENV_FILE_PATH"];
			}
			
			if (!$envPath || !file_exists($envPath))
			{
				die(__CLASS__.": No se encontró el archivo de configuración de la red. Buscando en: ".$envPath);
			}
			
			if ($debug)
			{
				error_log("Reading `env.php` at: $envPath");
			}
			
			require_once($envPath);	
			
			if (class_exists('PHPUnit\Framework\TestCase')) 
			{
				$testFilePath = dirname($envPath)."test-env.php";
			
				if (file_exists($testFilePath))
				{
					require_once($testFilePath);
				}
			} 

		}

	}

	public static function getSingleton()
	{
	    $debug = false;

	    static $dataAccessManager = null;

	    if ($dataAccessManager === null) 
	    {
			self::configureSystem();

            global $_GLOBALS;
        
		    $dataAccessManager = new DataAccessManager(
                $_GLOBALS["DataAccessManager_DB_CONFIG"],
                $_GLOBALS["DataAccessManager_dataAccessorConstructions"]);
	    }

		return $dataAccessManager;
	}

    public static function getAccessor($name, $throwException = true)
    {
	    if ($name === "DataAccessManager")
	    {
	    	return self::getSingleton();
	    }
        else
        {
            return self::getSingleton()->getDataAccessor($name, $throwException);
        }
    }


    public function __construct($databaseConfigurations, $dataAccessorConfigurations) 
    {
        $this->databaseConfigurations = $databaseConfigurations;
        $this->dataAccessorConstructions = $dataAccessorConfigurations;
    }

    public function getDatabaseInstance($dbName) 
    {
		// $debug = false;
		/*
		if (!$dbName)
		{
			return null;
		}
		*/

        if (!isset($this->databases[$dbName])) 
        {
            if (!isset($this->databaseConfigurations[$dbName])) 
            {
                throw new Exception("Database configuration for '{$dbName}' not found.");
            }

            $config = $this->databaseConfigurations[$dbName];

            if (!isset($config["connectionString"]))
            {
                throw new Exception("Database connection string for '{$dbName}' not found.");
            }

            $connection_string = $config["connectionString"];
            $username          = $config["userName"];
            $password          = $config["password"];

            $this->databases[$dbName] = new PDO(
                $connection_string,
                $username, 
                $password
            );  

			if (strpos($connection_string, "sqlite") !== false)
			{
				try
				{
					$journalMode = $config["journal_mode"] ?? "WAL";
					$this->databases[$dbName]->exec("PRAGMA journal_mode = $journalMode");					
				}
				catch (Exception $e)
				{
					/*
					So...I needed to give the database and the containing folder write permissions.
					That's why I was getting the error.
					Need to make it 777 or 774 - because form some god-forsaken reason...nothing else worked

					*/
					if (false)
					{
						echo "Connection string: ".$connection_string;
						echo "Error setting journal mode: ".$e->getMessage();
						die();
					}
					else
					{
						throw $e;
					}
				}
			}
        }
        return $this->databases[$dbName];
    }

    public function getDataAccessor($name, $throwException = true) 
    {
        $debug = false;
		
		if (!array_key_exists($name, $this->dataAccessors)) 
        {
			$key = $this->keyForDataAccessorConstructions($name);
			
			if($debug)
			{
				error_log("`getDataAccessor`: key: ".$key);
			}
            // if (!isset($this->dataAccessorConstructions[$name])) 
			if (!$key) 
            {
				if ($throwException)
				{
					throw new Exception("Data accessor configuration for '{$name}' not found.");
				}
				else
				{
                    return null;
				}  
            }

            $config     = $this->dataAccessorConstructions[$key];

			if (!isset($config["class"]))
			{
				throw new Exception("Must set class to start up object");
			}

			$className  = $config['class'];

			$instance = null;

			$initFromDataAccessManager = "initFromDataAccessManager";

			$config["dataAccessorName"] = $name;
			
			if (method_exists($className, $initFromDataAccessManager))
			{
				$instance = $className::$initFromDataAccessManager(
					$this,
					$key,
					$config);
			}
			else
			{
				$dbName = null;

				if (isset($config['db']))
				{
					$dbName = $config['db'];
				}
	
				$dbInstance = null;
				if ($dbName)
				{
					$dbInstance = $this->getDatabaseInstance($dbName);
				}
				$instanceOptions = null;
				if (isset($config['instanceOptions']))
				{
					$instanceOptions = $config['instanceOptions'];
				}
	
				$instance = new $className($dbInstance, $instanceOptions);
	
				if (isset($config["tableName"]))
				{
					$instance->setTableName($config["tableName"]);
				}
	
				if (isset($config["permissions"]))
				{
					if (method_exists($instance, "setPermissions"))
					{
						$instance->setPermissions($config["permissions"]);
					}
				}
			}



            $this->dataAccessors[$name] = $instance;

        }
        return $this->dataAccessors[$name];
    }

    public function keyForDataAccessorConstructions($name) 
    {
        $debug = false;

        if ($debug)
        {
			if (is_string($name))
			{
				error_log("Searching for data accessor key for name: ".$name);	
			}
        }

        
        if (array_key_exists($name, $this->dataAccessorConstructions)) 
        {
			if ($debug)
			{
				error_log("Found key for data accessor name: ".$name);
			}
            return $name;
        }

        foreach ($this->dataAccessorConstructions as $key => $details) 
        {
            if (isset($details["class"]) && $name === $details["class"])
			{
				if ($debug)
				{
					error_log("Matching on classs returning ".$key." for ".$name);
				}
                return $key;
            }
			if (isset($details["synonyms"]))
			{
				foreach ($details["synonyms"] as $synonym)
				{
					if ($synonym == $name)
					{
						if ($debug)
						{
							error_log("Matching on synonym returning ".$key." for ".$name);
						}
						return $key;
					}					
				}
			}
        }

		if ($debug)
		{
			error_log("No key found for data accessor name: ".$name);
		}

        return null;
    }

    public function notFoundPage($requestPath) {
        return "404 Not Found: " . htmlspecialchars($requestPath);
    }

   
	public function toRenderForPath($requestPath, $user)
	{
		$debug = false;

		$pathParts                = explode('/', trim($requestPath, '/'));
		$potentialDataAccessorKey = $pathParts[0];
		$toTryDataAccessorKey     = null;

		if ($debug)
		{
			gtk_log("Potential data accessor key: ".$potentialDataAccessorKey);
		}

		$dataSource = null;

		if (in_array($potentialDataAccessorKey, [
			"show", "edit", "list"
		]))
		{
			if (!isset($_GET["data_source"]))
			{
				die("Please set `data_source` parameter in URL.");
			}

			$toTryDataAccessorKey = $_GET["data_source"];
			$requestedPermission  = $potentialDataAccessorKey;
		}
		else
		{
			$relevantPathParts 	  = array_slice($pathParts, 1);
			$requestedPermission  = implode('/', $relevantPathParts);
			$toTryDataAccessorKey = $potentialDataAccessorKey;
		}

		if ($debug)
		{
			gtk_log("To try data accessor key: ".$toTryDataAccessorKey);
			gtk_log("Requested permission: ".$requestedPermission);
		}

		$dataSource = $this->getDataAccessor($toTryDataAccessorKey, false);

		if (!$dataSource)
		{
			return null;
		}

		$permissionURL = $toTryDataAccessorKey.".".$requestedPermission;

		if ($debug)
		{
			gtk_log("Permission URL: ".$permissionURL);
		}

		// $this->getDataAccessor("persona")->logAction($user, $permissionURL);
		// if ($dataSource->userHasPermissionTo($routeAsString, $user))

		$hasPermission = $this->getDataAccessor("persona")->hasPermission($permissionURL, $user);

		if ($hasPermission)
		{
			return $dataSource->renderObjectForRoute($requestedPermission, $user);
		}
	
		if (!$user)
		{
			echo Glang::get("DataAccessManager/RequiresRedirect");
			header("Refresh:3; url=/auth/login.php");
			echo "Requires redirect. No user.";
			exit();
		}
		else
		{
			die(Glang::get("NotAuthorizedToDoThis"));
		}
	}

}
