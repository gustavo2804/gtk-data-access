<?php 

// class GTKPHPInfoDataAccess implements DataAccessInterface
class GTKPHPInfoDataAccess
{
    public function render($get, $post, $server, $cookie, $session, $files, $env)
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
