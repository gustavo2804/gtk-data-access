<?php 

class DataSetView
{
    public $name;
    public $rawColumns;
    public $compiledColumns;
    public $dataAccessor;


    public function __construct($name, $rawColumns, $dataAccessor)
    {
        $this->name         = $name;
        $this->rawColumns   = $rawColumns;
        $this->dataAccessor = $dataAccessor;
    }

    public function getColumns()
    {
        if (!$this->compiledColumns)
        {
            $this->compiledColumns = [];
    
            foreach ($this->rawColumns as $maybeColumn)
            {
                if (is_string($maybeColumn))
                {
                    $columnMapping = $this->dataAccessor->columnMappingForKey($maybeColumn);
                    if ($columnMapping)
                    {
                        array_push($this->compiledColumns, $columnMapping);
                    }
                    else
                    {
                        die("No column mapping on columnView: ".$this->name.", column: $maybeColumn");
                    }
                }
                else if ($maybeColumn instanceof GTKColumnBase) // GTKColumnMapping, StdColumnVirtual
                {
                    array_push($this->compiledColumns, $maybeColumn);
                }
                else if ($maybeColumn instanceof ColumnView)
                {
                    array_push($this->compiledColumns, $maybeColumn);
                }
                else
                {
                    die("DataSetView: $this->name - Don't know how to handle column: ".serialize($maybeColumn));
                }
            }
        }

        return $this->compiledColumns;
    }


    public function renderForUser($user)
    {
        return "<h1>Renders for user: $user</h1>";
        /*
        $columns = $this->getColumns();
        $data = $this->dataAccessor->getData();
        $rows = $this->dataAccessor->getRows();

        $html = "<table class='table table-striped table-bordered table-hover'>";
        $html .= "<thead><tr>";
        foreach ($columns as $column)
        {
            $html .= "<th>".$column->name."</th>";
        }
        $html .= "</tr></thead>";

        $html .= "<tbody>";
        foreach ($rows as $row)
        {
            $html .= "<tr>";
            foreach ($columns as $column)
            {
                $html .= "<td>".$column->render($row)."</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>";

        $html .= "</table>";

        return $html;
        */
    }
}
