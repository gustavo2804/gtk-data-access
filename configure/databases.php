<?php

global $_GLOBALS;
if (!$_GLOBALS)
{
	$_GLOBALS = [];
}

$defaultPermissions = [
	"delete" => [
		"DEV",
	],
	"create" => [
		"DISPATCHERS",
	],
	"update" => [
		"SOFTWARE_ADMIN",
		"DISPATCHERS",
	],
	"read" => [
		"ACCOUNTANTS",
	],
	"none" => [
	],
];

$adminExclusive = [
	"create" => [
		"SOFTWARE_ADMIN",
	],
	"delete" => [
		"DEV",
	],
];

$defaultWithDispatcherWrite = [
	"delete" => [
		"DEV",
	],
	"update" => [
		"SOFTWARE_ADMIN",
		"DISPATCHERS",
	],
	"read" => [
		"ACCOUNTANTS",
	],
	"none" => [
	],
];

$devControlPermissions = [
	"delete" => [
		"DEV",
	],
	"update" => [
		
	],
	"read" => [
		"SOFTWARE_ADMIN",
	],
	"none" => [
		"ACCOUNTANTS",
		"DISPATCHERS",
	],
];

$_GLOBALS["GTK_DATA_ACCESS_CONSTRUCTIONS"] = [
	"phpinfo" => [
		"class" => "GTKPHPInfoDataAccess",
		"permissions" => [
			"type"   => "inherited",
			"delete" => "DEVS",
		],
	],
	"dynamo" => [
		"class" => "GTKPHPInfoDataAccess",
		"permissions" => [
			"type"   => "inherited",
			"delete" => "DEVS",
		],
	],
	/*
	*
	*
	*/
	"ShowDataSourceRenderer" => [
        "class" => "ShowDataSourceRenderer",
		"db" => "appDB",
		"permissions" => $devControlPermissions,
	],
	"EditDataSourceRenderer" => [
        "class" => "EditDataSourceRenderer",
		"db" => "appDB",
		"permissions" => $devControlPermissions,
	],
	"AllDataSourceRenderer" => [
        "class" => "AllDataSourceRenderer",
		"db" => "appDB",
		"permissions" => $devControlPermissions,
	],
	//////////////////////////////////////////
	// -
	// - Roles
	// -
	//////////////////////////////////////////
	//////////////////////////////////////////
	"roles" => [
		"class"       => "RoleDataAccess", 
		"db"          => "appDB",
		"permissions" => $adminExclusive,
	],
	"flat_roles" => [
		"class"       => "FlatRoleDataAccess", 
		"db"          => "appDB",
		"permissions" => $adminExclusive,
	],
	"role_person_relationships" => [
		"class" => "RolePersonRelationshipsDataAccess",                  
		"db" => "appDB",
		"permissions" => $adminExclusive,
	],
	"role_permission_relationships" => [
		"class" => "RolePermissionRelationshipsDataAccess",              
		"db" => "appDB",
		"permissions" => $adminExclusive,
	],
	//////////////////////////////////////////
	// - 
	// - Usuario
	// - 
	//////////////////////////////////////////
	//////////////////////////////////////////
	"request_password_reset" => [
		"class" => "RequestPasswordResetController",
		"permissions" => ["type" => "strict",],
	],
	"persona" => [
		"class" => "PersonaDataAccess",                     			   
		"db" => "appDB",
		"permissions" => $adminExclusive,
	],
	"email_queue" => [
		"class"       => "EmailQueueManager", 
		"db"          => "appDB",
		"permissions" => $devControlPermissions,
	],
	"mail_list_manager" => [
		"class"       => "MailListManager",
		"db"          => "appDB",
		"permissions" => $devControlPermissions,
	],
	"permissions" => [
		"class" => "PermissionDataAccess",                               
		"db" => "appDB",
		"permissions" => $adminExclusive,
	],
    "SetPasswordTokenDataAccess" => [
		"class"       => "SetPasswordTokenDataAccess",
		"db"          => "appDB",
		"permissions" => $defaultPermissions,
	],
	"session" => [
		"class"       => "SessionDataAccess",
		"db"          => "appDB",
		"permissions" => $defaultPermissions,
	],
	"solicitud_usuario" => [
        "class"       => "SolicitudUsuarioDataAccess",
        "db"          => "appDB",
        "permissions" => [
			"type" => "strict",
			"delete" => [
				"DEV",
				"SOFTWARE_ADMIN",
			],
			"create" => [
				"ANONYMOUS_USER",
			],
			"update" => [
			],
			"read" => [
				"SOFTWARE_ADMIN",
				"ADMIN_USER",
			],
			"none" => [
			],
	    ],
	],
];
