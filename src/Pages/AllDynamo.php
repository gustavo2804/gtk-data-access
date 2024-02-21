<?php

class GTKAllDynamo extends AllDataSourceRenderer
{
    public $dataSourceName;
    public $dataAccessorConstructions;

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

        parent::processGet();
    }

    public function render()
    {
        ob_start();

        $this->dataAccessorConstructions = DataAccessManager::get('DataAccessManager')->getConstructions();

        ?>

        <h3>Seleccionar Data Source</h3>

        <form method="GET">
            <select name="data_source">
            <?php
                foreach ($this->dataAccessorConstructions as $key => $dataAccessorMap) {
                    echo '<option value="' . $key . '"'.($key == $this->dataSourceName ? ' selected="selected"' : '') .'>';
                    echo $dataAccessorMap["class"];
                    echo '</option>';
                }
            ?>
            </select>
            <input type="submit" value="Usar" />
        </form>
            
        <?php
            
        echo parent::renderBody();

        return ob_get_clean();
    }
}
