<?php

class EditDataSourceRenderer extends ShowDataSourceRenderer
{
    public $isNew;
	public $didSucceed;

	public function primaryKeyMapping()
	{
		return $this->dataSource->dataMapping->primaryKeyMapping;
	}

	public function handleIsInvalid($isInvalid)
	{
		$debug = true;

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

    public function processPost($postObject)
    {
		$debug = true;

		$this->isNew       = $_POST["isNew"];
		// $identifier  = $_GET[$this->primaryKeyMapping->phpKey];
	
		try
		{
			unset($_POST["isNew"]); // array_remove($_POST, "isNew");

			$user =  DataAccessManager::get("persona")->getCurrentUser();
			$didSucceed = false;

			if ($this->isNew)
			{
				
				$isInvalid = null;
				
				$this->itemIdentifier = $this->dataSource->insertFromFormForUser($_POST, $user, $isInvalid);

				if ($isInvalid)
				{
					return $this->handleIsInvalid($isInvalid);
				}

				if ($this->itemIdentifier)
				{
					$message   = "Item registrado exitosamente.";

					$queryParameters = [
						"data_source" => $this->dataSource->dataAccessorName,
						$this->dataSource->primaryKeyMapping()->phpKey => $this->itemIdentifier,
					];

					$href      = "edit.php"."?".http_build_query($queryParameters);
					$linkStart = "<a href=\"$href\">";
					$linkText  = "link to $this->itemIdentifier";
					$linkEnd   = '</a>';
					$this->messages[] = $message.$linkStart.$linkText.$linkEnd;
					$didSucceed = true;
					$this->isNew = false;
					$this->item = $this->dataSource->getOne($this->primaryKeyMapping()->phpKey, $this->itemIdentifier);

					if (method_exists($this->dataSource, "didCreateNewOnFormWith"))
					{
						$this->dataSource->didCreateNewOnFormWith($_POST, $this->item, $user);
					}

				}
				else
				{
					$didSucceed = false;
				}
				
			}
			else
			{
				$isInvalid = null;

				$didSucceed = $this->dataSource->updateFromFormForUser($_POST, $user, $isInvalid);

				if ($isInvalid)
				{
					return $this->handleIsInvalid($isInvalid);
				}


				$this->messages[] = 'Item editado exitosamente.';
				$this->itemIdentifier = $_GET[$this->primaryKeyMapping()->phpKey];
				$this->item = $this->dataSource->getOne($this->primaryKeyMapping()->phpKey, $this->itemIdentifier);
				
				if (method_exists($this->dataSource, "didUpdateOnFormWith"))
				{
					$this->dataSource->didUpdateOnFormWith($_POST, $this->item, $user);
				}
			}

			if ($didSucceed)
			{
				
				$redirectTo = AllURLTo($this->dataSource);
				$EOL = "<br/>";

				if ($debug)
				{
					error_log("Will redirect to: ".$redirectTo);
				}

				header("Refresh:3; url=".$redirectTo);

				$redirectText = "";

				if ($this->isNew)
				{
					$redirectText .= "Ha actualizado el registro existosamente.".$EOL.$EOL;

					if (DataAccessManager::get("persona")->getCurrentUser())
					{
						$redirectText .= "En breve lo volvemos a la lista".$EOL;
						$redirectText .= "<a href='".$redirectTo."'>Ir a lista</a>".$EOL.$EOL;
					}
					else
					{
						$redirectTo .= "/";
					}


					$redirectText .= "<a href='/'>Ir a inicio</a>".$EOL;
				}
				else
				{
					$redirectText .= "Ha actualizado el registro existosamente.".$EOL.$EOL;
					$redirectText .= "En breve lo volvemos a la lista".$EOL;
					$redirectText .= "<a href='".$redirectTo."'>Ir a lista</a>".$EOL.$EOL;
					$redirectText .= "<br/>";
					$redirectText .= "<a href='/'>Ir a inicio</a>".$EOL;

				}

				echo $redirectText;
				die();
			}

			

			if ($debug)
			{
				error_log("Item (isNew:$this->isNew) (Succes:$didSucceed): ".serialize($_POST));
			}
		}
		catch (Exception $e)
		{
			error_log("Error: " . $e->getMessage().print_r($e, true));
			if (DataAccessManager::get('persona')->isDeveloper())
			{
				die("Error procesando el formulario: ".$e->getMessage()."Trace: ".$e->getTraceAsString());
			}
			else
			{
				die("Error procesando el formulario: ".$e->getMessage());
			}
		}
    }

    public function renderBody()
    {
        $debug = true;

		$user = DataAccessManager::get("persona")->getCurrentUser();

        if (!$this->isNew)
        {
            $this->itemIdentifier = $this->dataSource->dataMapping->valueForIdentifier($this->item);
        }

		$this->columnMappings = $this->dataSource->dataMapping->ordered;

		if ($debug)
		{
			error_log("Data Source : ".get_class($this->dataSource));
			error_log("Data Set Mapping: ".get_class($this->dataSource->dataMapping));
			error_log("Ordered: ".count($this->dataSource->dataMapping->ordered));
		}

        ob_start(); ?>
        <h2 class="ml-12 text-2xl font-bold my-4 center">
			<?php if ($this->isNew): ?>
				<?php echo $this->dataSource->singleItemName()." Nuevo"; ?>
			<?php else: ?>
				Edita <?php echo $this->dataSource->singleItemName();?> 
			<?php endif; ?>
		</h2>
        <?php
        	$file = dirname($_SERVER["DOCUMENT_ROOT"])."/templates/".get_class($this->dataSource)."/edit/preTitleHeader.php";
        	if ($debug)
        	{
        		error_log("Looking for: $file");
        	}
        	if (file_exists($file))
        	{
        		if ($debug)
        		{
        			error_log("Found: $file. Require-ing...");
        		}
        		require_once($file);
        	}
        	else
        	{
        		if ($debug)
        		{
        			error_log("File not found: $file");
        		}
        	}
        ?>
        
        <?php $inputOptions = [
        	"identifier"     => $this->itemIdentifier,
        	"dataSourceName" => get_class($this->dataSource),
        ]; ?>
        
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
        	<table>
        	<?php foreach ($this->columnMappings as $columnMapping): ?>
				<?php $phpKey = $columnMapping->phpKey; ?>
        		<?php if ($columnMapping->isPrimaryKey()): ?>
        			<tr>
        				<th><?php echo $columnMapping->getFormLabel($this->dataSource); ?></th>
        				<td>
        					<?php if ($this->isNew): ?>
        						Nuevo
        					<?php else: ?>
        					<?php echo $columnMapping->valueFromDatabase($this->item); ?>
        					<input type="hidden" 
        						   name="<?php echo $columnMapping->phpKey; ?>"
        						   value="<?php echo $columnMapping->valueFromDatabase($this->item); ?>">
        					<?php endif; ?>
        				</td>
        			</tr>
				<?php elseif ($columnMapping->onlyDisplayOnFormsForUser($user)): ?>
					<tr>
        				<th><?php echo $columnMapping->getFormLabel($this->dataSource); ?></th>
        				<td>
        				    <?php echo $columnMapping->valueFromDatabase($this->item); ?>
        				</td>
        			</tr>
        		<?php elseif ($columnMapping->removeOnFormsForUser($user)): ?>
        			<?php continue; ?>
        		<?php elseif ($columnMapping->hideOnFormsForUser($user)): ?>
        			<input type="hidden"                   
        					<?php if (!$this->isNew): ?>
        						value="<?php echo $columnMapping->valueFromDatabase($this->item); ?>"
        					<?php endif; ?>
        				   name="<?php echo $columnMapping->phpKey; ?>">
        		<?php else: ?>
        			<tr>
        				<th><?php echo $columnMapping->getFormLabel($this->dataSource); ?></th>
        				<td><?php echo $columnMapping->htmlInputForUserItem($user, $this->item, $inputOptions); ?></td>
        			</tr>
        		<?php endif; ?>
        	<?php endforeach; ?>


        	</table>
        	<input type="hidden" name="isNew" value="<?php echo $this->isNew; ?>">
        
        <?php
            $file = dirname($_SERVER["DOCUMENT_ROOT"])."/templates/".get_class($this->dataSource)."/edit/footer.php";
        	
        	if ($debug)
        	{
        		error_log("Looking for: $file");
        	}
        	if (file_exists($file))
        	{
        		if ($debug)
        		{
        			error_log("Found: $file. Require-ing...");
        		}
        		require_once($file);
        	}
        	else
        	{
        		if ($debug)
        		{
        			error_log("File not found: $file");
        		}
        	}
        
			$submitButtonValue = null;

			if ($this->isNew)
			{
				$submitButtonValue = Glang::get("EditDataSourceRenderer/SubmitButtonValue/IsNew");
			}
			else
			{
				$submitButtonValue = Glang::get("EditDataSourceRenderer/SubmitButtonValue");
			}

			?>
        	<input type="submit" 
				   class="px-8 mt-8 ml-8 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded"
				   value="<?php echo $submitButtonValue; ?>">
        
        	<div class="button-group">
        	</div>
        </form>

        <?php return ob_get_clean();
    }

}
