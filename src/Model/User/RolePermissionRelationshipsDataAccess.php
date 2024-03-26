<?php

class RolePermissionRelationshipsDataAccess extends DataAccess
{
    public $cache;

    public function register()
    {
            $columnMappings = [
			new GTKColumnMapping($this, "role_permission_relationship_id", [
                "formLabel" => "ID",
                "isPrimaryKey" => true, 
                "isAutoIncrement" => true, 
                "hideOnForms" => true,
            ]), 
            new GTKColumnMapping($this, "permission_id"),
            new GTKColumnMapping($this, "role_id"),
			new GTKColumnMapping($this, "comments"),
			new GTKColumnMapping($this, "is_active"),
			new GTKColumnMapping($this, "date_created"),
			new GTKColumnMapping($this, "date_modified"),
		];

		$this->dataMapping 			= new GTKDataSetMapping($this, $columnMappings);
		$this->defaultOrderByColumn = "name";
		$this->defaultOrderByOrder  = "DESC";
    }

    public function migrate()
    {
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS {$this->tableName()} 
        (role_permission_relationship_id INTEGER PRIMARY KEY,
         role_id,
         permission_id, 
         comments,
         is_active,
         date_created,
         date_modified,
        UNIQUE(role_permission_relationship_id))");
    }

    public function permissionsForRole($role)
    {
        $roleID = null;

        if (is_string($role) || is_numeric($role))
        {
            $roleID = $role;
        }
        else
        {
            $roleID = DataAccessManager::get('roles')->identifierForItem($role);
        }

        if (isset($this->cache[$roleID]))
        {
            return $this->cache[$roleID];
        }

        $permissionIDS = [];

        $query = new SelectQuery($this);

        $query->where(new WhereClause(
            "role_id", "=", $roleID
        ));
        $query->where(new WhereClause(
            "is_active", "=",  true
        ));

        $result = $query->executeAndReturnAll();

        foreach ($result as $row)
        {
            $permissionIDS[] = $row["permission_id"];
        }

        $permissions = DataAccessManager::get('permissions')->getByIdentifier($permissionIDS);    

        $permissionsLib = [];

        foreach ($permissions as $permission)
        {
            $permissionLib[] = $permission["name"];
        }

        $this->cache[$roleID] = $permissionsLib;

        return $permissionsLib;
    }


    public function selectForRole($role)
    {
        $roleID = null;

        if (is_string($role) || is_numeric($role))
        {
            $roleID = $role;
        }
        else
        {
            $roleID = DataAccessManager::get('roles')->identifierForItem($role);
        }

        $permissions = $this->findByParameter("role_id", $roleID);

        return $permissions;
    }

    public function permissionDictionaryForRole($role)
    {
        $selected = $this->selectForRole($role);
        $toReturn = [];

        foreach ($selected as $grantedPermission)
        {
            $permissionID = $grantedPermission["permission_id"];

            $toReturn[$permissionID] = $grantedPermission;
        }

        return $toReturn;
    }
}
