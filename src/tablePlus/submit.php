<?php

require_once dirname($_SERVER["DOCUMENT_ROOT"]).'\vendor\autoload.php';
require_once 'tablePlusHelpers.php';

class TablePlusResultsFromSubmitPage extends TablePlusPage
{
    /*
    public $dataSourceName; 
    public $filters;        
    public $columns; 
    public $dataAccessor; 
    */    
    public $results = [];  
    public $columns = [];
    public $dataAccessor;

    public function render($get, $post, $server, $cookie, $session, $files, $env)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') 
        {
            die("Invalid request method");
        }

        ob_start(); ?>

            
        <?php if (count($this->results)): ?>
        <table>
            <tr>
            <?php foreach ($this->columns as $column): ?>
                <th>
                    <?php echo $this->dataAccessor->labelForColumnKey($column); ?>
                </th>
            <?php endforeach; ?>
            </tr>
            <?php foreach ($this->results as $result): ?>
            <tr>
                <?php foreach ($this->columns as $column): ?>
                    <td>
                        <?php echo $this->dataAccessor->valueForKey($column, $result); ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <h3>No hay resultados que mostrar.</h3>
        <?php endif; ?>

        <?php return ob_get_clean();
    }
    

    public function processPost()
    {
        $debug = false;

        $dataSourceName = $_POST['data_source']    ?? null;
        $filters        = $_POST['filters']        ?? [];
        $this->columns  = $_POST['columns']        ?? [];

        if ($debug) 
        {
            error_log("Debugging...");
            error_log("POST Data");
            error_log(print_r($_POST, true));
            error_log("Filters");
            error_log(print_r($filters, true));
        }
        $this->dataAccessor = DataAccessManager::get($dataSourceName);
        if (!$this->dataAccessor) 
        {
            // Error handling
            die("Invalid data source provided");
        }


        $mainGroup      = new WhereGroup();

        $filterCount = count($filters['active']);

        for ($i = 0; $i < $filterCount; $i++)
        {
            $active  = $filters['active'][$i] ?? null;
            $column  = $filters['column'][$i] ?? null;
            $logical = $filters['logical'][$i] ?? null;
            $type    = $filters['type'][$i]   ?? null;
            $value1  = $filters['value1'][$i] ?? null;
            $value2  = $filters['value2'][$i] ?? null;
        
            $filter = [
                'active'  => $active,
                'column'  => $column,
                'type'    => $type,
                'value1'  => $value1,
                'value2'  => $value2,
                'logical' => $logical,
            ];
        
            if ($debug) 
            {
                error_log("Filter $i");
                error_log(print_r($filter, true));
            }
        
            $isActive = in_array($filter['active'], [ 1, 'on', 'active']);
        
            if (!$active)
            {
                // Skip inactive filters
                error_log("Skipping inactive filter");
                continue;
            }
            if (empty($filter['column']) || empty($filter['type'])) 
            {
                // Skip incomplete filters
                error_log("Skipping incomplete filter");
                continue;
            }
        
            $textBoxCount = getTextBoxCount($filter['type']);
        
            if ($textBoxCount == 1 && empty($filter['value1'])) 
            {
                // Skip filters with no value
                continue;
            } 
            else if ($textBoxCount == 2)
            {
                if ((empty($filter['value1']) && empty($filter['value2'])))
                {
                    error_log("Skipping 2 box filter with no value");
                    continue;
                }
                else if (empty($filter['value1']) || empty($filter['value2']))
                {
                    die("Invalid filter: 2 box filter with only 1 value provided");
                }
            }
        
            if ($debug) 
            {
                error_log("Adding filter");
                error_log(print_r($filter, true));
            }


            $whereClause = new WhereClause(
                $filter['column'],
                $filter['type'],
                $filter['value1'] ?? null,
                $filter['value2'] ?? null // This may not be set for all filters
            );
        
        
            if ($filter['logical'] == 'OR' && count($mainGroup->clauses) > 0) {
                // If the logical operator is "OR", we need to start a new subgroup
                $lastGroup = end($mainGroup->clauses);
                if ($lastGroup instanceof WhereGroup && $lastGroup->logicalOperator == 'OR') {
                    // If the last group is already an OR group, add to it
                    $lastGroup->addClause($whereClause);
                } else {
                    // Otherwise, start a new OR group
                    $orGroup = new WhereGroup('OR');
                    $orGroup->addClause($whereClause);
                    $mainGroup->addGroup($orGroup);
                }
            } else {
                // Default to adding the clause with "AND"
                $mainGroup->addClause($whereClause);
            }
        }

        $selectQuery = new SelectQuery($dataAccessor, $columns, $mainGroup);
        $this->results = $selectQuery->executeAndReturnAll();

        if (!count($this->columns))
        {
            $this->columns = $dataAccessor->getOrderedColumnKeys();
        }
    }
}
