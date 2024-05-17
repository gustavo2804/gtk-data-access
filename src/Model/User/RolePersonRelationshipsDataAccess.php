<?php

class RolePersonRelationshipsDataAccess extends DataAccess
{
    public function register()
    {
        $columnMappings = [
			new GTKColumnMapping($this, "role_person_relationship_id", [ "formLabel" => "ID", "isPrimaryKey" => true, "isAutoIncrement" => true, "hideOnForms" => true, ]), 
            new GTKColumnMapping($this, "role_id", [ 
                "formLabel" => "Rol",
                "customInputFunctionClass"  => null,
                "customInputFunctionScope"  => "object", //--- instance?? ---
                "customInputFunctionObject" => DataAccessManager::get('roles'),
                "customInputFunction"       => "generateSelectForUserColumnValueName",
            ]),
            new GTKColumnMapping($this, "persona_id", [ 
                "formLabel" => "Persona",
                "transformValueOnLists" => function ($item, $value) {
                    $persona = DataAccessManager::get("persona")->getByIdentifier($value);
                    return $value." - ".DataAccessManager::get("persona")->fullNameForItem($persona);
                },
            ]),
			new GTKColumnMapping($this, "comments",	                   [ "formLabel" => "Comentarios"]),
			new GTKColumnMapping($this, "can_grant",                   [ "formLabel" => "¿Puede asignar?"]),
			new GTKColumnMapping($this, "can_remove",                  [ "formLabel" => "¿Puede quitar?"]),
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

    public function getRoleAssignmentForUser($roleToGrant, $user)
    {
        $roleID = null;

        if (is_numeric($roleToGrant))
        {
            $roleID = $roleToGrant;
        }
        else if (is_string($roleToGrant))
        {
            $role = DataAccessManager::get("roles")->getOne("name", $roleToGrant);

            if ($role)
            {
                $roleID = $role["id"];
            }
        }
        else if (is_array($roleToGrant))
        {
            $roleID = $roleToGrant["id"];
        }

        $userID = null;

        if (is_numeric($user))
        {
            $userID = $user;
        }
        else if (is_string($user))
        {
            $user = DataAccessManager::get("persona")->getOne("email", $user);

            if ($user)
            {
                $userID = $user["id"];
            }
        }
        else if (is_array($user))
        {
            $userID = $user["id"];
        }

        $query = new SelectQuery($this);

        $query->addWhereClause(new WhereClause(
            "role_id", "=", $roleID
        ));

        $query->addWhereClause(new WhereClause(
            "persona_id", "=", $userID
        ));

        $query->addWhereClause($this->getIsActiveClause());

        $results = $query->executeAndReturnAll();

        return $results;
    }


    public function isInvalidInsertionForUser($formItem, $user)
    {
        $roleToGrant = $formItem["role_id"];

        $canGrant = $this->canUserGrantRole($user, $roleToGrant);

        if (!$canGrant)
        {
            return "No tienes permisos para asignar este rol";
        }

        $grantorRoleAssignment = $this->getRoleAssignmentForUser($roleToGrant, $user);

        $grantorQualifier = $grantorRoleAssignment["qualifier"];

        if ($grantorQualifier)
        {
            $qualifier = $formItem["qualifier"];

            if ($qualifier != $grantorQualifier)
            {
                return "No puedes asignar este rol a otra persona";
            }
        }

        return null;
    }

    public function isIlegalUpdateForUserOnItem($user, $currentItem, $maybeItem)
    {
        if ($currentItem["role_id"] != $maybeItem["role_id"])
        {
            return "No puedes cambiar el rol asignado";
        }

        if ($currentItem["persona_id"] != $maybeItem["persona_id"])
        {
            return "No puedes cambiar la persona asignada";
        }

        if ($currentItem["qualifier"] != $maybeItem["qualifier"])
        {
            $isDevOrSuperAdmin = DataAccessManager::get("roles")->isUserInAnyOfTheseRoles(["DEV", "SUPER_ADMIN"], $user);

            if (!$isDevOrSuperAdmin)
            {
                return "No puedes cambiar el calificador";
            }
        }

        return null;
    }

    public function grantRoleToUser($grantor, $role, $user, $comments = null)
    {
        $roleID = null;
        $userID = null;

        if (is_numeric($role))
        {
            $roleID = $role;
        }
        else if (is_string($role))
        {
            $role = DataAccessManager::get("roles")->getOne("name", $role);

            if ($role)
            {
                $roleID = $role["id"];
            }
        }
        else if (is_array($role))
        {
            $roleID = $role["id"];
        }

        if (is_numeric($user))
        {
            $userID = $user;
        }
        else if (is_string($user))
        {
            $user = DataAccessManager::get("persona")->getOne("email", $user);

            if ($user)
            {
                $userID = $user["id"];
            }
        }
        else if (is_array($user))
        {
            $userID = $user["id"];
        }

        if ($roleID && $userID)
        {
            $existingRole = $this->whereDict(1, [
                "role_id" => $roleID,
                "persona_id" => $userID,
            ]);

            if (!$existingRole)
            {
                $roleRelation = [
                    "role_id" => $roleID,
                    "persona_id" => $userID,
                    "comments" => $comments,
                    "is_active" => 1,
                    "date_created" => date("Y-m-d H:i:s"),
                    "date_modified" => date("Y-m-d H:i:s"),
                ];

                $this->insert($roleRelation);
            }
        }
    }

    public function getIsActiveClause()
    {
        $whereGroup = new WhereGroup("OR");
        $whereGroup->addWhereClause(new WhereClause(
            "is_active", "=", 1
        ));
        $whereGroup->addWhereClause(new WhereClause(
            "is_active", "=", "TRUE"
        ));
        return $whereGroup;
    }


    public function getQualifiedRolesForUser($user)
    {
        $query = new SelectQuery($this);

        $query->addWhereClause(new WhereClause(
            "persona_id", "=", DataAccessManager::get("persona")->valueForIdentifier($user)
        ));

        $query->addWhereClause($this->getIsActiveClause());

        $query->addWhereClause(new WhereClause(
            "qualifier", "IS NOT NULL"
        ));

        return $qualifiedRoles;
    }

    public function addWhereClauseForUser($user, &$selectQuery)
    {
        $orQuery = new SelectQuery($this);

        $orQuery->addWhereClause(new WhereClause(
            "persona_id", "=", DataAccessManager::get("persona")->valueForIdentifier($user)
        ));

        $WhereGroup = new WhereGroup("AND");


    }

    public function rolesGrantableByUser($currentUser = null)
    {
        $query = new SelectQuery($this);

        $query->addWhereClause(new WhereClause(
            "persona_id", "=", DataAccessManager::get("persona")->valueForIdentifier($user)
        ));

        
        $whereGroup = new WhereGroup("OR");
        $whereGroup->addWhereClause(new WhereClause(
            "can_grant", "=", 1
        ));
        $whereGroup->addWhereClause(new WhereClause(
            "can_grant", "=", "TRUE"
        ));
        $query->addClause($whereGroup);

        $results = $query->executeAndReturnAll();

        return $results;
    }

    public function rolesRemovableByUser($currentUser = null)
    {
        $query = new SelectQuery($this);

        $query->addWhereClause(new WhereClause(
            "persona_id", "=", DataAccessManager::get("persona")->valueForIdentifier($user)
        ));

        
        $whereGroup = new WhereGroup("OR");
        $whereGroup->addWhereClause(new WhereClause(
            "can_remove", "=", 1
        ));
        $whereGroup->addWhereClause(new WhereClause(
            "can_remove", "=", "TRUE"
        ));
        $query->addClause($whereGroup);

        $results = $query->executeAndReturnAll();

        return $results;
    }

    public function isUserInRoleNamed($roleName, $user)
    {
        if (!$user)
        {
            return false;
        }

        $role = DataAccessManager::get("roles")->getOne("name", $roleName);

        if ($role)
        {
            $existingRole = $this->whereDict(1, [
                "role_id" => $role["id"],
                "persona_id" => DataAccessManager::get('persona')->valueForKey("id", $user),
            ]);
    
            return $existingRole;
        }
        else
        {
            return null;
        }
    }

    
    public function isUserInAnyOfTheseRoles($toAllowFrom, $user)
    {
        $debug = false;

        if (!$user)
        {
            return false;
        }


        $roleRelations = $this->roleRelationsForUser($user);


        $allowedRolesIDS = [];

        foreach ($toAllowFrom as $maybeRoleStringNumber)
        {
            if (is_numeric($maybeRoleStringNumber))
            {
                $allowedRolesIDS[] = $maybeRoleStringNumber;
            }
            else if (is_string($maybeRoleStringNumber))
            {
                $role = DataAccessManager::get("roles")->getOne("name", $maybeRoleStringNumber);

                if ($role)
                {
                    $allowedRolesIDS[] = $role["id"];
                }
            }
            else if (is_array($maybeRoleStringNumber))
            {
                $allowedRolesIDS[] = $maybeRoleStringNumber["id"];
            }
        }

        if ($debug)
        {
            error_log("Roles relations for user: ".print_r($roles, true));
        }

        $roleIDS = [];
        foreach ($roleRelations as $roleRelation)
        {
            $roleIDS[] = $roleRelation["role_id"];
        }

        if ($debug)
        {
            error_log("Role IDs Name: ".print_r($roleIDS, true));
        }

        $intersection = array_intersect($roleIDS, $allowedRolesIDS);

        if ($debug)
        {
            error_log("Allowed Roles: ".print_r($allowedRolesIDS, true));
            error_log("Intersection: ".print_r($intersection, true));
        }

        return count($intersection) > 0;


    }

    public function roleRelationsForUser($user = null)
    {

        $debug = false;

        if ($debug)
        {
            error_log("Role Relations for user: $user");
        }

        $userID = null;

        if (is_array($user))
        {
            $userID = DataAccessManager::get("persona")->valueForKey("id", $user);
        }
        else if (is_numeric($user))
        {
            $userID = $user;
        }
        else
        {
            return [];
        }


        $query = new SelectQuery($this, null, [
            new WhereClause("persona_id", "=",  DataAccessManager::get("persona")->valueForKey("id", $user)),
        ]);

        $results = $query->executeAndReturnAll();

        return $results;
    }
    /*
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
    */
    
    public function rolesForUser($currentUser = null)
    {
        return null;
    }

    public function isActive($item)
    {
        return isTruthy($this->valueForKey("is_active", $item));
    }
}
