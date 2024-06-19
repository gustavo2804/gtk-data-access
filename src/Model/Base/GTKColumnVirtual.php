<?php

class GTKColumnReference extends GTKColumnBase
{
    public $referencedDataSource;
    public $referencedColumn;
    public $originMatchOnColumn;
    public $referencedMatchOnColumn;
    public $isMany = false;

    public function __construct($dataSource, $phpKey, $referencedDataSource, $referencedColumn, $options = [])
    {
        parent::__construct($dataSource, $phpKey, $options);
        $this->referencedDataSource = $referencedDataSource;
        $this->referencedColumn     = $referencedColumn;

        $this->originMatchOnColumn     = $options["originMatchOnColumn"]     ?? $phpKey."_id";
        $this->referencedMatchOnColumn = $options["referencedMatchOnColumn"] ?? $referencedDataSource->primaryKeyMapping();
    }

    public function isUpdateKey()
    {
        return false;
    }

    public function valueForItem($item)
    {
        return $this->getValueFromArray($item);
    }

    public function isVirtual()
    {
        return true;
    }


    public function getValueFromArray($item)
    {
        $originValue = null;

        if ($this->originMatchOnColumn instanceof GTKColumnBase)
        {
            $originValue = $this->originMatchOnColumn->getValueFromArray($item);
        }
        else
        {
            $originValue = $this->dataSource->valueForKey($this->originMatchOnColumn, $item);
        }

        $query = new SelectQuery($this->referencedDataSource);
        
        $query->where($this->referencedMatchOnColumn, "=", $originValue);

        $results = $query->executeAndReturnAll();
        
        if (!count($results))
        {
            return null;
        }

        if ($this->isMany)
        {
            return $results;
        }
        
        $referencedItem = $results[0];

        if ($referencedItem == null)
        {
            return null;
        }

        // die(print_r($referencedItem, true));

        if ($this->referencedColumn instanceof GTKColumnBase)
        {
            return $this->referencedColumn->getValueFromArray($referencedItem);
        }
        else
        {
            return $this->referencedDataSource->valueForKey($this->referencedColumn, $referencedItem);
        }

    }



    public function isReference()
    {
        return true;
    }

    public function doesColumnExist()
    {
        return true;
    }

    public function addColumnIfNotExists()
    {
    }

    public function getSqlColumnName()
    {
    }
}

class GTKColumnVirtual extends GTKColumnBase 
{
    public function isVirtual()
    {
        return true;
    }

    public function doesColumnExist()
    {
        return true;
    }

    public function addColumnIfNotExists()
    {
    }

    public function getSqlColumnName()
    {
    }
}
