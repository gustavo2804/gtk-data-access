<?php

class GTKProfilePage extends GTKHTMLPage
{
    public $user;

    public function __construct()
    {
        parent::__construct();
        $this->authenticationRequired = true;
    }

    public function getUserKey($key)
    {
        return $this->getDataSource("persona")->valueForKey($key, $this->user);
    }

    public function renderBody()
    {
        if (!$this->get["id"])
        {
            $this->user = DataAccessManager::get("session")->getCurrentUser();
        }
        else
        {
            $this->user = DataAccessManager::get("persona")->getByIdentifier($this->get["id"]);
        }


        $toReturn = "<h1>Profile</h1>";
        $toReturn .= "<h2>Email: ".$this->getUserKey("email")."</h2>";

        $canSeeUserProfileDetails = false;

        if ($this->currentUser("id") == $this->getDataSource("persona")->valueForKey("id", $this->user))
        {
            $canSeeUserProfileDetails = true;
        }
        else if (DataAccessManager::get("session")->currentUserIsInGroups([
            "SOCIOS",
            "DEV",
        ]))
        {
            $canSeeUserProfileDetails = true;
        }

        if ($canSeeUserProfileDetails)
        {
            $toReturn .= "<h2>Roles:</h2>";

            $roles = $this->getDataSource("roles")->getRolesForUser($this->user);

            foreach ($roles as $role)
            {
                $toReturn .= "<p>".$this->getDataSource("roles")->valueForKey("name", $role)."</p>";
                // $toReturn .= "<p>".$roles->getDescription()."</p>";
            }
            $toReturn .= "<h2>Permissions:</h2>";

            $permissions = $this->getDataSource("permissions")->getPermissionsForUser($this->user);

            sort($permissions);

            foreach ($permissions as $permission)
            {
                $toReturn .= "<p>".$permission."</p>";
                // $toReturn .= "<h3>".$this->getDataSource("permissions")->valueForKey("name", $permission)."</h3>";
                // $toReturn .= "<p>".$permissions->getDescription()."</p>";
            }
        }

        return $toReturn;

    }
}
