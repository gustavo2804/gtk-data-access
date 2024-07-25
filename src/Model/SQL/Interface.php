<?php

interface SQLTextInterface 
{
    public function getSQLForSelectQuery($selectQuery, &$params);
}


interface SQLWhereInterface
{
    public function where($column, $operator = null, ...$values);
}


interface URLQueryParameterInterface
{
    public function serializeToURLQueryParamaters(&$params);
    public static function fromSerializedURLQueryParameter($params);
}
