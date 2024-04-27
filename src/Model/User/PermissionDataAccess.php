 <?php

enum PermissionType: int {
    case None    = 0;
    case Read    = 1;
    case Write   = 3;

}

class PermissionDataAccess extends DataAccess 
{
    public function register()
    {
        $columnMappings = [
			new GTKColumnMapping($this, "id",  [
                "formLabel"       => "ID",
                "isPrimaryKey"    => true, 
                "isAutoIncrement" => true, 
                "hideOnForms"     => true, 
            ]), 
			new GTKColumnMapping($this, "name", [
                "formLabel"  => "Nombre",
                "isUnique"   => true,
                "isNullable" => false,
            ]),
			new GTKColumnMapping($this, "comments", [
                "formLabel" => "Comentarios",
            ]),
			new GTKColumnMapping($this, "is_active", [
                "formLabel" => "¿Esta Activo?"
            ]),
			new GTKColumnMapping($this, "date_created", [
                "formLabel" => "Fecha Creacion"
            ]),
			new GTKColumnMapping($this, "date_modified", [
                "formLabel" => "Fecha Modificado"
            ]),
		];
        
		$this->dataMapping 			= new GTKDataSetMapping($this, $columnMappings);
		$this->defaultOrderByColumn = "name";
		$this->defaultOrderByOrder  = "DESC";
    }

    function hasPermission(&$permission, &$user)
    {
        return DataAccessManager::get("persona")->hasPermission($permission, $user);
    }

    function hasPermissionOnItem(&$permission, &$user, $item)
    {
        return DataAccessManager::get("persona")->hasPermission($permission, $user);
    }

    function permissionsForRole($role)
    {
        return DataAccessManager::get("role_permission_relationships")->permissionsForRole($role);
    }
}
