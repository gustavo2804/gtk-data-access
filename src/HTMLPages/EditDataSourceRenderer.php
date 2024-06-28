<?php



class EditDataSourceRenderer extends FormRendererBaseForDataSource
{
    public $isNew;
	public $didSucceed;

    public function processPost()
    {
		$debug = false;
		try
		{
			$user =  DataAccessManager::get("persona")->getCurrentUser();
			
			$isInvalid = null;

			$didSucceed = $this->dataSource->updateFromFormForUser(
				$this->post, 
				$user, 
				$isInvalid);

			if ($isInvalid)
			{
				return $this->handleIsInvalid($isInvalid);
			}

			if (!$didSucceed)
			{
				$this->messages[] = 'Problema actualizando registro.';
				return;
			}

			$this->messages[] = 'Item editado exitosamente.';
			$this->itemIdentifier = $this->get[$this->primaryKeyMapping()->phpKey];
			$this->item 	      = $this->dataSource->getOne($this->primaryKeyMapping()->phpKey, $this->itemIdentifier);
				
			if (method_exists($this->dataSource, "didUpdateOnFormWith"))
			{
				$this->dataSource->didUpdateOnFormWith($this->post, $this->item, $user);
			}

			if (method_exists($this->dataSource, "renderEditSuccess"))
			{
				$this->dataSource->renderEditSuccess($this);
			}

			if ($debug)
			{
				error_log("Item (Succes:$didSucceed): ".serialize($_POST));
			}
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

        ob_start(); ?>
        <h2 class="ml-12 text-2xl font-bold my-4 center">
			Edita <?php echo $this->dataSource->singleItemName();?> 
		</h2>
        <?php
			if (is_string($this->header))
			{
				echo $this->header;
			}
			else if (is_callable($this->header))
			{
				$header = $this->header;
				echo $header($this);
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
        					<?php echo $columnMapping->valueFromDatabase($this->item); ?>
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
        		<?php elseif ($columnMapping->hideOnEditForUser($user)): ?>
        			<input type="hidden"                   
        				   value="<?php echo $columnMapping->valueFromDatabase($this->item); ?>"
        				   name="<?php echo $columnMapping->phpKey; ?>">
        		<?php else: ?>
        			<tr>
        				<th><?php echo $columnMapping->getFormLabel($this->dataSource); ?></th>
        				<td><?php echo $columnMapping->htmlInputForUserItem($user, $this->item, $inputOptions); ?></td>
        			</tr>
        		<?php endif; ?>
        	<?php endforeach; ?>
        	</table>
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

			$submitButtonValue = Glang::get("EditDataSourceRenderer/SubmitButtonValue");

			?>
        	<input type="submit" 
				   class="px-8 mt-8 ml-8 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 rounded"
				   value="<?php echo $submitButtonValue; ?>">
        
        	<div class="button-group">
        	</div>
        </form>

		<?php 
			echo $this->dataSource->displayActionsForUserItem($user, $this->item);
		?>

        <?php return ob_get_clean();
    }

}
