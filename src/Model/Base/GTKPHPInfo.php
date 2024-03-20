<?php 

// class GTKPHPInfoDataAccess implements DataAccessInterface
class GTKPHPInfoDataAccess
{
    public function render()
    {
        return phpinfo();
    }
    public function renderObjectForRoute($routeAsString, $user)
    {
		  return $this;
    }
    public function getPluralItemName()
    {
        return "PHPInfo";
    }
}
