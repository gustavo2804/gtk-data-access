<?php

class GTKAction extends GTKDataAccessLink
{
	public $doObjectForUserItemDelegateOptions;
	public $doFunctionForUserItemDelegateOptions;

    public function __construct($dataSource, $permission, $function, $options)
    {
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
