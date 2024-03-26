<?php

class RolePersonRelationshipsDataAccess extends DataAccess
{
    public function register()
    {
        $columnMappings = [
			new GTKColumnMapping($this, "role_person_relationship_id", [ "formLabel" => "ID", "isPrimaryKey" => true, "isAutoIncrement" => true, "hideOnForms" => true, ]), 
            new GTKColumnMapping($this, "role_id",                     [ "formLabel" => "ID Rol"]),
            new GTKColumnMapping($this, "persona_id",                  [ "formLabel" => "ID Persona"]),
			new GTKColumnMapping($this, "comments",	                [ "formLabel" => "Comentarios"]),
			new GTKColumnMapping($this, "is_active",                   [ "formLabel" => "¿Esta Activo?"]),
			new GTKColumnMapping($this, "date_created",                [ "formLabel" => "Fecha Creacion"]),
			new GTKColumnMapping($this, "date_modified",               [ "formLabel" => "Fecha Modificado"]),
		];
		$this->dataMapping 			= new GTKDataSetMapping($this, $columnMappings);
		$this->defaultOrderByColumn = "date_modified";
		$this->defaultOrderByOrder  = "DESC";
		$this->singleItemName	    = "Relacion Rol con Persona";
		$this->pluralItemName	    = "Relaciones Rol con Persona";


        
    }

    public function migrate()
    {
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS {$this->tableName()} 
        (role_person_relationship_id,
         person_id,
         role_id, 
         comments,
         is_active,
         date_created,
         date_modified,
        UNIQUE(role_person_relationship_id))");
    }
    
    public function rolesForUser($currentUser = null)
    {
        return null;
    }
}
