<?php

class OrderBy implements SQLTextInterface
{
    public $column;
    public $order;

    public function __construct($column, $order = 'ASC')
    {
        $this->column = $column;
        $this->order  = $order;
    }

    public function getSQLForSelectQuery($selectQuery, &$params) 
    {
        $columnName = $selectQuery->dbColumnNameForKey($this->column);
        return "{$columnName} {$this->order}";
    }
}
