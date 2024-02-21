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
}
