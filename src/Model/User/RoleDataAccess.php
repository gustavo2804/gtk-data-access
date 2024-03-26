<?php

class RoleDataAccess extends DataAccess
{

    public function getDefaultOptionsForSelectForUser($user)
    {
        return [
            "columnValue" => "id",
            "columnName"  => "name",
        ];
    }

    public function columnToCheckIfExists()
    {
        return "name";
    }

    public function isQualifiedRole($role)
    {
        $value =  $role["needs_qualifier"];

        switch ($value)
        {
            case "TRUE":
            case "true":
            case true:
            case 1:
                return true;
            default:
                return false;
        }
    }

    public function register()
    {
        $columnMappings = [
			new GTKColumnMapping($this, "id",	[
                "isPrimaryKey"    => true, 
                "isAutoIncrement" => true, 
                "hideOnForms"     => true, 
            ]), 
			new GTKColumnMapping($this, "name", [
                "isUnique" => true,
            ]),
			new GTKColumnMapping($this, "purpose"),
			new GTKColumnMapping($this, "is_active", [
                'formInputType' => 'select', 
				'possibleValues' => [ 
					true  => ['label'=>'TRUE'],
					false => ['label'=>'FALSE'],
				],
            ]),
            new GTKColumnMapping($this, "needs_qualifier", [
                'formInputType' => 'select', 
				'possibleValues' => [ 
					true  => ['label'=>'TRUE'],
					false => ['label'=>'FALSE'],
				],
            ]),
            new GTKColumnMapping($this, "qualifier_data_source", [
                "customInputFunctionClass"  => null,
                "customInputFunctionScope"  => "object", //--- instance?? ---
                "customInputFunctionObject" => DataAccessManager::get('DataAccessManager'),
                "customInputFunction"       => "generateSelectForUserColumnValueName",
            ]),
            new GTKColumnMapping($this, "qualifier_data_source_column"),
            new GTKColumnMapping($this, "qualifier_data_label_column"),
            new GTKColumnMapping($this, "permissionsArray", [
                "hideOnForms" => true, 
                "hideOnLists" => true,
            ]),
            new GTKColumnMapping($this, "is_root_role"),
			new GTKColumnMapping($this, "date_created"),
			new GTKColumnMapping($this, "date_modified"),
		];
		$this->dataMapping 			= new GTKDataSetMapping($this, $columnMappings);
		$this->defaultOrderByColumn = "name";
		$this->defaultOrderByOrder  = "DESC";
    }

    public function updateWithPHPKeys(&$item, $options = null, &$outError = null)
    {

        list($permissionsList, $roleData) = segregateArrayKeysWithPrefix('permission_granted_on_', $item);
        
        $roleReturn = parent::updateWithPHPKeys($roleData, $options);

        foreach ($permissionsList as $permission)
        {
            error_log("Permission: ".serialize($permission));
        }
        
        return $roleReturn;

    }

    public function isUserInRoleNamed($role, $user)
    {
        return DataAccessManager::get("roles")->isUserInRoleNamed($role, $user);
    }

    public function isUserInAnyOfTheseRoles($user, $toCheck)
    {
        $isAllowed = false;

        $roles = DataAccessManager::get('roles')->rolesForUser($user);

        foreach ($roles as $role)
        {
            if (in_array($role, $toCheck))
            {
                return true;
            }
        }

        return false;
    }

    public function isUserInAnyOfTheseRolesNamed($user, $toCheck)
    {
        $isAllowed = false;

        $roles = DataAccessManager::get('roles')->rolesForUser($user);

        foreach ($roles as $role)
        {
            if (in_array($role["name"], $toCheck))
            {
                return true;
            }
        }

        return false;
    }


    public function rolesForUser($user = null)
    {
        if (!$user)
        {
            return [];
        }

        $roleRelatiosnForUser = DataAccessManager::get("flat_roles")->roleRelationsForUser($user);

        $roleIDS = [];

        foreach ($roleRelatiosnForUser as $roleRelation)
        {
            $roleIDS[] = $roleRelation["role_id"];
        }

        $query = new SelectQuery($this);

        $query->addClause(new WhereClause(
            "id", "IN", $roleIDS
        ));

        return $query->executeAndReturnAll();
    }

    public function getPermissionsForRole(&$role)
    {
        $debug = true;

        if (isset($role["permissions"]))
        {
            return $role["permissions"];
        }

        $permissions = [];
    
        $rolePermissions = DataAccessManager::get("role_permission_relationships")->permissionsForRole($role);
        
        $permissions = [];

        foreach ($rolePermissions as $rolePermission)
        {
            $permissions[] = DataAccessManager::get("permissions")->getOne("id", $rolePermission["permission_id"]);
        }
    

        $role["permissions"] = $permissions;

        return $permissions;
    }

    public function addPermissionToRole(&$role, $permission)
    {
        $debug = true;

        $rolePermissions = DataAccessManager::get("role_permission_relationships")->permissionsForRole($role);
        
        $rolePermissions[] = [
            "role_id"       => $role["id"],
            "permission_id" => $permission["id"],
        ];
        
        DataAccessManager::get("role_permission_relationships")->create($rolePermissions);
    
    }

    public function removePermissionFromRole(&$role, $permissionToRemove)
    {
        $debug = true;

        $rolePermissions = DataAccessManager::get("role_permission_relationships")->permissionsForRole($role);
        
        foreach ($rolePermissions as $rolePermission)
        {
            if ($rolePermission["permission_id"] == $permission["id"])
            {
                DataAccessManager::get("role_permission_relationships")->delete($rolePermission);
            }
        }
    }

    public function createRole(&$role)
    {
        $debug = false;
        $didInsert = $this->insertAssociativeArray($role);

        if ($didInsert)
        {
            if ($debug)
            {
                gtk_log("Role Created: ".serialize($role));
            }

            $role = $this->getOne("name", $role["name"]);
        }
        else
        {
            if ($debug)
            {
                gtk_log("Role Not Created: ".serialize($role));
            }
        }
    }

    public function manageRole(&$existingRole, &$roleInConfig)
    {
        $debug = true;

        $permissions = $this->getPermissionsForRole($existingRole);

        if (isset($role["permissions_to_remove"])) 
        {
            $permissionsToRemove = $roleInConfig["permissions_to_remove"];

            foreach ($permissionsToRemove as $permission)
            {
                if ($debug)
                {
                    gtk_log("Removing Permission: ".serialize($permission));
                }
                if (in_array($permission, $permissions))
                {
                    $this->removePermissionFromRole($existingRole, $permission);
                }
            }
        }

        if (isset($role["permissions"])) 
        {
            $permissionsToAdd = $roleInConfig["permissions"];

            foreach ($permissionsToAdd as $permission)
            {
                if ($debug)
                {
                    gtk_log("Adding Permission: ".serialize($permission));
                }
                if (!in_array($permission, $permissions))
                {
                    $this->addPermissionToRole($existingRole, $permission);
                }
            }
        }
    }

    public function createOrManageRole(&$role)
    {
        $debug = false;

        if ($debug)
        {
            gtk_log("Role: ".serialize($role));
        }

        $roleName = $role["name"];

        $existingRole = $this->getOne("name", $roleName);

        if ($existingRole)
        {
            if ($debug)
            {
                gtk_log("Role Exists: ".serialize($role));
            }
            $this->manageRole($existingRole, $role);
        }
        else
        {
            if ($debug)
            {
                gtk_log("Role Does Not Exist: ($roleName) ".serialize($role));
            }
            $this->createRole($role);
        }
    }

}
