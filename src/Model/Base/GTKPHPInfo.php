<?php 

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
}
