<?php


class LimitClause
{
    public $limit;
    public $offset;

    public function __construct($limit, $offset = null)
    {
        $this->limit  = $limit;
        $this->offset = $offset;
    }

    public function getSQLForSelectQuery($selectQuery, &$params) 
    {
        return $selectQuery->sqlForLimitOffset($this->limit, $this->offset);
    }
}
