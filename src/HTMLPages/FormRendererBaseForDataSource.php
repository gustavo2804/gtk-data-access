<?php

class FormRendererBaseForDataSource extends ShowDataSourceRenderer
{
	
	public function primaryKeyMapping()
	{
		return $this->dataSource->dataMapping->primaryKeyMapping;
	}

	public function handleIsInvalid($isInvalid)
	{
		$debug = false;

		if ($debug)
		{
			error_log("`handleIsInvalid` - parameter: ".print_r($isInvalid, true));
		}
		if ($isInvalid)
		{
			foreach ($isInvalid as $message)
			{
				echo $message;
				echo "<br/>";
				echo "<br/>";
			}
			die();
		}
	}

	public function didUpdateSuccessfully($message)
	{
		$debug = true;

		$redirectTo = null;		
					
		$linkToAll = AllURLTo($this->dataSource);

		$linkToItem = $this->dataSource->editURLForItem($this->item);

		$EOL = "<br/>";
		$redirectText = "";

		$redirectText .= $message.$EOL.$EOL;
		
		if (DataAccessManager::get("persona")->getCurrentUser())
		{
			if (false)
			{
				$redirectText .= "En breve lo volvemos a la lista".$EOL;
				$redirectTo = $linkToAll;
			}
			else
			{
				$redirectTo = $linkToItem;		
				$redirectText .= "Lo vamos a redigir al registo automaticamente.".$EOL;
			}
		}
		else
		{
			$redirectTo = "/";
			$redirectText .= "Lo vamos a redirigir a inicio automaticamente".$EOL;
		}

		
		$redirectText .= "<a href='".$redirectTo."'>Ir a registro</a>".$EOL.$EOL;

		$redirectText .= "<a href='".$linkToAll."'>Ir a lista</a>".$EOL.$EOL;

		$redirectText .= "<a href='".$redirectTo."'>Ir a inicio</a>".$EOL.$EOL;


		if ($debug)
		{
			error_log("Will redirect to: ".$redirectTo);
		}

		header("Refresh:3; url=".$redirectTo);
		echo $redirectText;
		die();
	}
}
