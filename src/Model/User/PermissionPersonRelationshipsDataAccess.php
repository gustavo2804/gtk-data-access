<?php

class PermissionPersonRelationshipDataAccess extends DataAccess
{
    public function register()
    {
        $columnMappings = [
			new GTKColumnMapping($this, "permission_person_relationship_id", [
                "formLabel"    => true,
                "isPrimaryKey" => true, 
                "hideOnForms"  => true, 
            ]), 
            new GTKColumnMapping($this, "permission_id"),
            new GTKColumnMapping($this, "persona_id"),
			new GTKColumnMapping($this, "comments"),
			new GTKColumnMapping($this, "is_active"),
			new GTKColumnMapping($this, "date_created"),
			new GTKColumnMapping($this, "date_modified"),
		];

		$this->dataMapping 			= new GTKDataSetMapping($this, $columnMappings);
		$this->defaultOrderByColumnKey = "date_modified";
		$this->defaultOrderByOrder  = "DESC";
    }

    public function migrate()
    {
        $this->getDB()->query("CREATE TABLE IF NOT EXISTS {$this->tableName()} 
        (persona_permission_id INTEGER PRIMARY KEY,
         persona_id,
         permission_id, 
         comments,
         is_active,
         date_created,
         date_modified,
        UNIQUE(persona_permission_id))");
    }


}
