<?php

class NewDataSourceRenderer extends FormRendererBaseForDataSource
{
	public function processPost()
	{
		$debug = true;

		$user =  DataAccessManager::get("persona")->getCurrentUser();

		try
		{					
			$isInvalid  = null;
			$didSucceed = false;

			$this->itemIdentifier = $this->dataSource->insertFromFormForUser($_POST, $user, $isInvalid);

			if ($isInvalid)
			{
				return $this->handleIsInvalid($isInvalid);
			}

			if (!$this->itemIdentifier)
			{
				throw new Exception("Have an issue inserting. Please check DataAccess inset.");
			}

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
			$this->item = $this->dataSource->getOne($this->primaryKeyMapping()->phpKey, $this->itemIdentifier);
			if (method_exists($this->dataSource, "didCreateNewOnFormWith"))
			{
				$this->dataSource->didCreateNewOnFormWith($this->post, $this->item, $user);
			}

			return $this->didUpdateSuccessfully($message);
		}
		catch (Exception $e)
		{
			error_log("Error: " . $e->getMessage()."Trace: ".$e->getTraceAsString());
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
    	$debug = false;
		
		$user = DataAccessManager::get("persona")->getCurrentUser();
    	
		$this->itemIdentifier = $this->dataSource->dataMapping->valueForIdentifier($this->item);
		
		$this->columnMappings = $this->dataSource->dataMapping->ordered;
		if ($debug)
		{
			error_log("Data Source : ".get_class($this->dataSource));
			error_log("Data Set Mapping: ".get_class($this->dataSource->dataMapping));
			error_log("Ordered: ".count($this->dataSource->dataMapping->ordered));
		}
		if (is_string($this->header))
		{
			echo $this->header;
		}
		else if (is_callable($this->header))
		{
			$header = $this->header;
			echo $header($this);
		}
    	ob_start(); ?>
		
		<?php $inputOptions = [
			"identifier"     => $this->itemIdentifier,
			"dataSourceName" => get_class($this->dataSource),
		]; ?>

		<h2 class="ml-12 text-2xl font-bold my-4 center">
				<?php echo $this->dataSource->singleItemName()." Nuevo"; ?>
		</h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<table>
			<?php foreach ($this->columnMappings as $columnMapping): ?>
				<?php $phpKey = $columnMapping->phpKey; ?>
				<?php if ($columnMapping->isPrimaryKey()): ?>
					<tr>
						<th><?php echo $columnMapping->getFormLabel($this->dataSource); ?></th>
						<td>
							Nuevo
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
				<?php elseif ($columnMapping->hideOnNewForUser($user)): ?>
					<input type="hidden"
						   value="<?php echo $this->post[$columnMapping->phpKey] ?? null; ?>"
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
			if (is_string($this->footer))
			{
				echo $this->footer;
			}
			else if (is_callable($this->footer))
			{
				$footer = $this->footer;
				echo $footer($this);
			}
		

			$submitButtonValue = Glang::get("EditDataSourceRenderer/SubmitButtonValue/IsNew");

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
