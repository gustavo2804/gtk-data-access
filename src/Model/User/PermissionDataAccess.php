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
                "formLabel"    => "ID",
                "isPrimaryKey" => true, 
                "isAutoIncrement" => true, 
                "hideOnForms" => true, 
            ]), 
			new GTKColumnMapping($this, "name", [
                "formLabel" => "Nombre"
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

    public function migrate()
    {
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS {$this->tableName()} 
        (permission_id INTEGER PRIMARY KEY, 
         name, 
         comments,
         is_active,
         date_created,
         date_modified,
        UNIQUE(permission_id))");
    }

    function hasPermissionOnDataAccessor($dataAccessorName, $actionName, $item)
    {
        

        $dataSource = DataAccessManager::get($dataAccessorName);

    }

    function hasPermission($permission, $user, $options = [])
    {
        $debug = 0;

        if ($debug)
        {
            error_log("Checking for permission: $permission");
        }
        if ($permission === 'view_data_errors')
        {
            $cedula = presentCedula($user["cedula"]);

            error_log("Checking for cedula: $cedula");

            $allowedCedulas = 
            [
                '001-1859419-1', // Gustavo Tavares
            ];

            return in_array($cedula, $allowedCedulas);
        }
        return false;
    }


    public function userHasPermissionToOnDataAccessor($dataAccessor, $maybePermission, $user)
    {
        
    }
}
