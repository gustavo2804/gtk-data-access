<?php

class DataAccessAction
{
    public $canEchoResult;
	public $dataSource;
    public $key;
	public $label;
    public $allowedFor;
	public $hideOnListsForUserItemObject; 
    public $hideOnListsForUserItemFunction;
	public $hideOnEditForUserItemObject;
    public $hideOnEditForUserItemFunction;
	public $doObjectForUserItemDelegateOptions;
	public $doFunctionForUserItemDelegateOptions;
    public $isInvalidForUserItemDelegateOptionsObject;
    public $isInvalidForUserItemDelegateOptionsFunction;

    public function __construct($dataSource, $key, $label)
    {
        $this->dataSource = $dataSource;
        $this->key        = $key;
        $this->label      = $label;
    }

	public function labelForUserItem($user, $item)
	{
		$label = $this->label;
		if (is_callable($label))
		{
			return $label($user, $item);
		}
		else
		{
			return $label;
		}
	}
	public function hideOnListsForUserItem($user, $item)
	{
		$debug = false;
		
		if ($this->hideOnListsForUserItemObject)
        {
			$hideOnListsForUserItemFunction = $this->hideOnListsForUserItemFunction;
			return $this->hideOnListsForUserItemObject->$hideOnListsForUserItemFunction($user, $item);
        }
        else if ($this->hideOnListsForUserItemFunction)
		{
			$hideOnListsForUserItemFunction = $this->hideOnListsForUserItemFunction;
			return $hideOnListsForUserItemFunction($user, $item);
		}
    
        return $this->isInvalidForUserOnItem($user, $item);
	}

	public function hideOnEditForUserItem($user, $item)
	{
		$debug = false; 

        if ($this->hideOnEditForUserItemObject)
        {
			if ($debug)
			{
				error_log("hideOnEditForUserItem - hasCustomIsInvalidLogic");
			}
			$hideOnEditForUserItemFunction = $this->hideOnEditForUserItemFunction;
			return $this->hideOnEditForUserItemObject->$hideOnEditForUserItemFunction($user, $item);
        }
        else if ($this->hideOnListsForUserItemFunction)
		{
			$hideOnEditForUserItemFunction = $this->hideOnEditForUserItemFunction;
			return $hideOnEditForUserItemFunction($user, $item);
		}

        return $this->isInvalidForUserOnItem($user, $item);
	}
    /* 
     * Should return an array of messages.
     * Or interact with the delegate.anchorLinkForItem
     * 
     */
    public function hasCustomIsInvalidLogic()
    {
        if ($this->isInvalidForUserItemDelegateOptionsFunction)
        {
            return true;
        }
        return false;
    }
    public function isInvalidForUserOnItem(&$user, &$item, &$delegate = null, &$options = null)
    {
		$debug = false;

		if ($debug)
		{
			error_log("`isInvalidForUserOnItem` - trying...");
		}

        if ($this->isInvalidForUserItemDelegateOptionsObject)
        {
			if ($debug)
			{
				error_log("Trying object with function option");
			}
			$isInvalidForUserItemDelegateOptionsFunction = $this->isInvalidForUserItemDelegateOptionsFunction;
			return $this->isInvalidForUserItemDelegateOptionsObject->$isInvalidForUserItemDelegateOptionsFunction($user, $item, $delegate, $options);
        }
        else if ($this->isInvalidForUserItemDelegateOptionsFunction)
		{
			if ($debug)
			{
				error_log("Trying object with function option");
			}
			$isInvalidForUserItemDelegateOptionsFunction = $this->isInvalidForUserItemDelegateOptionsFunction;
			return $isInvalidForUserItemDelegateOptionsFunction($user, $item, $delegate, $options);
		}
        else if (is_array($this->allowedFor))
        {
            $userHasPermission = DataAccessManager::get('roles')->isUserInAnyOfTheseRolesNamed($user, $this->allowedFor);
			if ($debug)
			{
				error_log("User has permission: $userHasPermission on ".print_r($this->allowedFor, true));
			}
			return !$userHasPermission;
		}

		return true;
    }
	public function doActionForUserItem(&$user, &$item, &$delegate = null, &$options = null)
	{
		if ($this->doObjectForUserItemDelegateOptions)
		{
			$doFunctionForUserItemDelegateOptions = $this->doFunctionForUserItemDelegateOptions;
			return $this->doObjectForUserItemDelegateOptions->$doFunctionForUserItemDelegateOptions($user, $item, $delegate, $options);
		}
		else if ($this->doFunctionForUserItemDelegateOptions)
		{
			if (is_callable($this->doFunctionForUserItemDelegateOptions))
			{
				$doFunctionForUserItemDelegateOptions = $this->doFunctionForUserItemDelegateOptions;
				return $doFunctionForUserItemDelegateOptions($user, $item, $delegate, $options);
			}
		}
		return null;
	}
    public function shouldEchoResultForUserItem($user, $item, $delegate = null, $options = null)
    {
        return true;
    }
    public function anchorLinkForItem($user, $item, $options = null)
    {
        $label = $options["label"] ?? $this->label;

		if (is_callable($label))
		{
			$label = $label($user, $item);
		}
        
        $toReturn  = "";
        $toReturn .= "<a class='button' href=\"{$this->linkForItem($item)}\">{$label}</a>";
        $toReturn .= "<br/>";
        $toReturn .= "<br/>";

        return $toReturn;
    }
    public function linkForItem($item)
    {
        $actionURL  = "/action.php";

        $queryArguments = [
            "actionName"     => $this->key,
            "dataSourceName" => $this->dataSource->dataAccessorName,
            "identifier"     => $this->dataSource->valueForIdentifier($item),
        ];

        return $actionURL.'?'.http_build_query($queryArguments);
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
