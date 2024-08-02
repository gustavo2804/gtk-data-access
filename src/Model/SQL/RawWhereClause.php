<?php


class RawWhereClause implements SQLTextInterface
{
    public $sql;
    public $params;

    public function __construct($sql, ...$params)
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    public function getSQLForSelectQuery($selectQuery, &$params) 
    {
        $params = array_merge($params, $this->params);
        return $this->sql;
    }
}
