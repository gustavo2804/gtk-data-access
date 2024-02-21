<?php

class GTKEditDynamo extends EditDataSourceRenderer
{
    public $dataSourceName;
    public function processGet($getObject)
    {
        if (!isset($_GET["data_source"]))
        {
            $this->dataSourceName = "persona";
        }
        else
        {
            $this->dataSourceName = $_GET["data_source"];
        }
        
        $this->dataSource = DataAccessManager::get($this->dataSourceName);
        parent::processGet($getObject);
    }
}
