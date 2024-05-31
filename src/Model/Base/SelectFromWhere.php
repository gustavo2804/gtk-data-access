<?php


class OrderBy
{
    public $column;
    public $order;

    public function __construct($column, $order = 'ASC')
    {
        $this->column = $column;
        $this->order  = $order;
    }

    public function getSQLForDataAccess($dataAccess, &$params) 
    {
        $columnName = $dataAccess->dbColumnNameForKey($this->column);
        return "{$columnName} {$this->order}";
    }
}
class SelectQuery
{
    public $isCountQuery = false;
    public $dataSource;
    public $columns;
    public $queryOptions = [];
    public $whereGroup;
    public $orderBy;
    public $limit;
    public $offset;

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function __construct($dataSource, $columns = null, $whereClauses = null, $queryOptions = [])
    {
        $this->dataSource = $dataSource;
        $this->columns    = $columns;

        if ($whereClauses)
        {
            $this->addWhereClauses($whereClauses);    
        }
        

        $this->queryOptions = $queryOptions;
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;
    }



    public function addClause($whereClause)
    {
        return $this->addWhereClause($whereClause);
    }

    public function addWhereClause($whereClause) 
    {
        if (!$this->whereGroup) 
        {
            $this->whereGroup = new WhereGroup();
        }
        $this->whereGroup->addClause($whereClause);
        return $this;
    }

    public function addWhereClauses($whereClauses) 
    {
        if ($whereClauses instanceof WhereGroup)
        {
            $this->whereGroup = $whereClauses;
        }
        else if ($whereClauses) 
        {
            $this->whereGroup = new WhereGroup();
            foreach ($whereClauses as $whereClause) {
                $this->whereGroup->addClause($whereClause);
            }
        }
        return $this;
    }

    public function where($column, $operator = null, ...$values) 
    {
        $whereClause = null;

        if ($column instanceof WhereClause)
        {
            $whereClause = $column;
        }
        else if ($column instanceof WhereGroup)
        {
            $whereClause = $column;
        }
        else
        {
            $whereClause = new WhereClause($column, $operator, ...$values);
        }
        
        if (!$this->whereGroup) {
            $this->whereGroup = new WhereGroup();
        }
        $this->whereGroup->addClause($whereClause);

        return $this;
    }

    public function whereGroup($logicalOperator = 'AND') 
    {
        if (!$this->whereGroup) 
        {
            $this->whereGroup = new WhereGroup();
        }
        $whereGroup = new WhereGroup($logicalOperator);
        $this->whereGroup->addGroup($whereGroup);
        return $whereGroup;
    }

    public function getSQLAndUpdateParams(&$params, $pdo = null) 
    {
        $debug = false;

        $sql = "";
        $sql .= "SELECT ";

        if ($this->isCountQuery)
        {
             $sql .= " COUNT(*) as COUNT";
        }
        else if ($this->columns && count($this->columns) > 0) 
        {
            $toQueryColumns = [];

            foreach ($this->columns as $column) 
            {
                if (is_string($column))
                {
                    $toQueryColumns[] = $this->dataSource->dbColumnNameForKey($column);
                }
                else 
                {
                    /* Ignore — likely a virutal column. */
                }
                
            }

            $sql .= implode(',', $toQueryColumns);
        } 
        else 
        {
            $sql .= '*';
        }

        $sql .= " FROM ".$this->dataSource->tableName();
        
        if ($this->whereGroup && (count($this->whereGroup->clauses) > 0))
        {
            $sql .= ' WHERE ' . $this->whereGroup->getSQLForDataAccess($this->dataSource, $params);
        }          
        
        if (!$this->isCountQuery)
        {
            $didSetOrderBy = false;

            if (is_array($this->orderBy) && (count($this->orderBy) > 0))
            {
                $sql .= ' ORDER BY ';
                $isFirst = true;
                $isEven  = false;
                foreach ($this->orderBy as $orderBy) 
                {
                    if ($debug)
                    {
                        error_log("Handling order by case: ".print_r($orderBy,true));
                    }
                    if (!$isFirst)
                    {
                        $sql .= ', ';
                    }
                    $isFirst = false;
    
                    if (is_string($orderBy))
                    {
                        $sql .= $this->dataSource->dbColumnNameForKey($orderBy)." ASC";
                    }
                    else if (is_array($orderBy) && (count($orderBy) == 2))
                    {
    
                        $sql .= $this->dataSource->dbColumnNameForKey($orderBy[0])." ".$orderBy[1];
                    }
                    else if ($orderBy instanceof OrderBy)
                    {
                        $sql .= $this->dataSource->dbColumnNameForKey($orderBy->column)." ".$orderBy->order;
                    }
                    else
                    {
                        throw new Exception("Don't know how to handle ORDER BY object of gtype: ".get_class($orderBy));
                    }
                    
    
                    $isEven = !$isEven;
                }
    
                $didSetOrderBy = true;
            }
            else if (is_string($this->orderBy))
            {
    
            }

            if ($this->limit || $this->offset)
            {
                $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

                if (!$didSetOrderBy && ($driverName == "sqlsrv"))
                {
                    $orderByColumn = $this->dataSource->defaultOrderByColumn;
                    $orderByOrder  = $this->dataSource->defaultOrderByOrder ?? "DESC";
                
                    if (!$orderByColumn)
                    {
                        throw new Exception("Cannot do limit/offset without an ORDER BY clause - SQL: ".$sql);
                    }

                    $sql .= " ORDER BY ".$orderByColumn." ".$orderByOrder;
                }
                
                $sql .= $this->sqlForLimitOffset($this->limit, $this->offset, $pdo);
            }

        }

        if ($debug)
        {
            error_log("SQL@SelectFromWhere - DataSource(".get_class($this->dataSource).": ".$sql);
        }

        return $sql;
    }

    public function sqlForLimitOffset($limit, $offset, $pdo)
    {
        $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        $sql = "";

        switch ($driverName)
        {
            case 'mysql':
            case 'sqlite':
                if ($limit > 0)
                {
                    $sql .= " LIMIT {$limit}";
                    if ($offset)
                    {
                        $sql .= " OFFSET {$offset}";    
                    } 
                    
                }
                break;
            case 'pgsql':
            case 'sqlsrv':
                if ($limit > 0)
                {
                   $offsetToUse = $offset ?? 0;                    
                    $sql .= " OFFSET ".$offsetToUse." ROWS";
                    $sql .= " FETCH NEXT {$limit} ROWS ONLY";
                }
                break;
            case 'oci': // Oracle
                // $sql = "SELECT * FROM (
                //     SELECT *, ROW_NUMBER() OVER (ORDER BY {$orderByColumn} {$orderByOrder}) AS row_num
                //     FROM {$this->tableName()}
                // ) WHERE row_num BETWEEN {$offset} + 1 AND {$offset} + {$limit}";
                // break;
            default:
                gtk_log("Connected to a database with unsupported driver: " . $driverName);
                die();
        }

        return $sql;
    }

    public function getPDOStatement(&$params)
    {
        $debug = false;

        $pdo = $this->dataSource->getPDO();

        $sql = $this->getSQLAndUpdateParams($params, $pdo);
        
        if ($debug)
        {
            error_log("SQL (".get_class($this->dataSource)."): ".$sql);
        }

        try
        {
            $pdoStatement = $pdo->prepare($sql); 
        }
        catch (Exception $e)
        {
            if ($this->isCountQuery)
            {
                throw $e;
            }
            else
            {
                return QueryExceptionManager::manageQueryExceptionForDataSource($this->dataSource, $e, $sql, $params, $outError);
            }
        }

        return $pdoStatement;
        // return $this->dataSource->execute($sql, $params);
    }

    public function getCount()
    {
        return $this->count();
    }

    public function count($debug = false)
    {
        $this->isCountQuery = true;
        $params = [];
        $pdoStatement = $this->getPDOStatement($params);
        if ($debug)
        {
            error_log("COUNT Query: ".$pdoStatement->queryString);
        }
        $pdoStatement->execute($params);
        $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        if ($debug)
        {
            error_log("COUNT: ".print_r($result, true));
        }
        $this->isCountQuery = false;
        return $result['COUNT'];
    }

    public function sql()
    {
        return $this->getSQL();
    }

    public function getSQL()
    {
        $params = [];
        return $this->getSQLAndUpdateParams($params, $this->dataSource->getPDO());
    }

    public function executeAndReturnStatement()
    {
        $params = [];
        $statement = $this->getPDOStatement($params);
        try
        {
            $statement->execute($params);
        }
        catch (Exception $e)
        {
            return QueryExceptionManager::manageQueryExceptionForDataSource($this->dataSource, $e, $statement->queryString, $params, $outError);
        }
        // $statement->execute($params);
        return $statement;
    }

    public function executeAndYield()
    {
        $statement = $this->executeAndReturnStatement();
    
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) 
        {
            yield $row;
        }
    }

    public function executeAndReturnAll()
    {
        $statement = $this->executeAndReturnStatement();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function executeAndReturnOne()
    {
        $results = $this->executeAndReturnAll();
        if (count($results) > 0)
        {
            return $results[0];
        }
        else
        {
            return [];
        }
    }
}

class RawWhereClause
{
    public $sql;
    public $params;

    public function __construct($sql, ...$params)
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    public function getSQLForDataAccess($dataAccess, &$params) 
    {
        $params = array_merge($params, $this->params);
        return $this->sql;
    }
}

class WhereClause 
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

    public function getSQLForDataAccess($dataAccess, &$params) 
    {
        $debug = false;

        // $dbType = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $columnName = $dataAccess->dbColumnNameForKey($this->column);

        if ($debug)
        {
            error_log("SQL...");
            error_log("Operator..: ".$this->operator);
            error_log("VALUES....: ".print_r($this->values, true));
            error_log("...END SQL");
        }

        switch ($this->operator) {
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
                throw new Exception("Invalid operator: {$this->operator}");
        }
    }
}

class AndClause extends WhereClause 
{
    public function __construct($column, $operator, ...$values) 
    {
        parent::__construct($column, $operator, ...$values);
    }
}

class OrClause extends WhereClause 
{
    public function __construct($column, $operator, ...$values) 
    {
        parent::__construct($column, $operator, ...$values);
    }
}


class WhereGroup 
{
    public $clauses = [];
    public $logicalOperator;

    public function __construct($logicalOperator = 'AND', $clauses = [])
    {
        $this->logicalOperator = $logicalOperator;
        $this->clauses         = $clauses;


    }

    public function addWhereClause($clause) { 
        return $this->addClause($clause);
    }

    public function addClause(/* WhereClause|WhereGroup */ $clause) 
    {
        $this->clauses[] = $clause;
    }

    public function addGroup(/* WhereGroup|WhereGroup */ $group) {
        $this->clauses[] = $group;
    }

    public function getSQLForDataAccess($dataAccess, &$params) 
    {
        if (!is_array($this->clauses))
        {
            throw new Exception("Clauses must be an array on WhereGroup");
        }

        $sqlParts = [];
        foreach ($this->clauses as $clause) 
        {
            $sqlParts[] = ($clause instanceof WhereGroup) ? '(' . $clause->getSQLForDataAccess($dataAccess, $params) . ')' : $clause->getSQLForDataAccess($dataAccess, $params);
        }
        return implode(" {$this->logicalOperator} ", $sqlParts);
    }

    public function where($column, $operator = null, ...$values) 
    {
        $whereClause = null;

        if ($column instanceof WhereClause)
        {
            $whereClause = $column;
        }
        else if ($column instanceof WhereGroup)
        {
            $whereClause = $column;
        }
        else
        {
            $whereClause = new WhereClause($column, $operator, ...$values);
        }

        $this->clauses[] = $whereClause;

        return $this;
    }
}
