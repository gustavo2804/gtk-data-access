<?php



class WhereClause implements SQLTextInterface
{
    public $column;
    public $operator;
    public $values;

    public function __construct($column, $operator, ...$values) 
    {
        $debug = false;

        if ($debug)
        {
            error_log("Constructing column: ".$column);
        }

        $this->column   = $column;
        $this->operator = $operator;
        $this->values   = $values;
    }

    public function serializeToQueryParameters(&$queryParameters)
    {
        $queryParameters['clauses'][]= serialize($this);
    }
    public function getSQLForSelectQuery($selectQuery, &$params) 
    {
        $debug = false;

        // $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $columnName = null;

        if ($this->column instanceof GTKColumnBase)
        {
            $columnName = $this->column->dbColumnName();
        }
        else
        {
            $columnName = $selectQuery->dbColumnNameForKey($this->column);
        }

        if ($debug)
        {
            error_log("SQL...");
            error_log("Operator..: ".$this->operator);
            error_log("VALUES....: ".print_r($this->values, true));
            error_log("...END SQL");
        }

        $pdoType = $selectQuery->getDriverName();

        return $this->clauseForColumnNameParamsPDOType($columnName, $params, $pdoType);
    }

    public static function isOperator($operator)
    {
        $whereClause = new WhereClause("for_method", $operator, "value");
        $params = [];
        $clause = $whereClause->clauseForColumnNameParamsPDOType("column_name", $params, "mysql", false);
        if ($clause)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function serializeToURLQueryParamaters(&$params)
    {
        $key = "where_clause_".uniqid();

        $params[$key] = serialize($this);
    }

    public static function fromSerializedURLQueryParameter($params)
    {
        return unserialize($params);
    }

    public function clauseForColumnNameParamsPDOType($columnName, &$params, $pdoType, $throwException = true)
    {
        $debug = false;

        switch ($this->operator) 
        {
            case "NOT EMPTY":
                switch ($pdoType)
                {
                    case 'mysql':
                        return "LENGTH({$columnName})";
                    
                    case 'pgsql':
                        return "OCTET_LENGTH({$columnName}) > 0";
                    
                    case 'sqlite':
                        return "LENGTH({$columnName}) > 0";
                    
                    case 'sqlsrv':
                    case 'dblib':  // Sybase or MS SQL Server
                    case 'mssql':
                        return "DATALENGTH({$columnName}) > 0 ";
                    
                    case 'oci':    // Oracle
                        return "LENGTH({$columnName}) > 0";
                    
                    case 'firebird':
                        return "OCTET_LENGTH({$columnName}) > 0";
        
                }
            case 'IN':
            case 'NOT IN':
                if ($debug)
                {
                    error_log("PRINTING IN");
                }

                
                $flatValues = array_reduce($this->values, function ($carry, $item) {
                    return array_merge($carry, is_array($item) ? array_values($item) : [$item]);
                }, []);

                $placeholders = implode(',', array_fill(0, count($flatValues), '?'));

                $params = array_merge($params, $flatValues);

                if ($debug)
                {
                    error_log("SERIALIZED: ".serialize($this->values));
                    error_log("FLAT VALUES: ".serialize($flatValues));
                    error_log("PLACEHOLDERS: ".$placeholders);
                    error_log("PARAMS: ".print_r($params, true));
                }

                return "{$columnName} {$this->operator} ({$placeholders})";
            case 'IS NULL':
            case 'IS NOT NULL':
                return "{$columnName} {$this->operator}";
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $params[] = $this->values[0];
                $params[] = $this->values[1];
                return "{$columnName} {$this->operator} ? AND ?";
            case '!=':
                $this->operator = "<>";
            case '=':
            case '<>':
            case '<':
            case '>':
            case '<=':
            case '>=':
                $params[] = $this->values[0];
                return "{$columnName} {$this->operator} ?";
            case 'LIKE':
            case 'CONTAINS':
                $params[] = '%'.$this->values[0].'%';
                // return "{$columnName} {$this->operator} ?";
                return "{$columnName} LIKE ?";
            case 'NOT LIKE':
            case 'NOT CONTAINS':
                $params[] = $this->values[0];
                // return "{$columnName} {$this->operator} ?";
                return "{$columnName} NOT LIKE ?";
            case 'HAS PREFIX':
                $params[] = $this->values[0].'%'; 
                // return "{$columnName} {$this->operator} ?";
                return "{$columnName} LIKE ?";
            case 'HAS SUFFIX':
                $params[] = '%'.$this->values[0];
                // return "{$columnName} {$this->operator} ?";
                return "{$columnName} LIKE ?";
            default:
                if ($throwException)
                {
                    throw new Exception("Invalid operator: {$this->operator}");
                }
                else
                {
                    return null;
                
                }
        }
    }
}
