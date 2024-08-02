<?php


class BetweenClause implements SQLTextInterface
{
    public $column;
    public $start;
    public $end;
    public $inclusive;

    public function __construct($column, $start, $end, $inclusive = true) 
    {
        $this->column = $column;
        $this->start = $start;
        $this->end = $end;
    }

    public function getSQLForSelectQuery($selectQuery, &$params) 
    {
        $columnName = null;

        if ($this->column instanceof GTKColumnBase)
        {
            $columnName = $this->column->dbColumnName();
        }
        else
        {
            $columnName = $selectQuery->dbColumnNameForKey($this->column);
        }

        $params[] = $this->start;
        $params[] = $this->end;

        if ($this->inclusive)
        {
            return "{$columnName} BETWEEN ? AND ?";
        }
        else
        {
            return "{$columnName} >= ? AND {$columnName} < ?";
        }
    }
}
