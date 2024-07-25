<?php

interface SQLTextInterface 
{
    public function getSQLForSelectQuery($selectQuery, &$params);
}


interface SQLWhereInterface
{
    public function where($column, $operator = null, ...$values);
}
