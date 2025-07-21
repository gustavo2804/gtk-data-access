<?php

global $_GTK_DATA_ACCESS;

if (!isset($_GTK_DATA_ACCESS)) {
    $_GTK_DATA_ACCESS = [];
}

$_GTK_DATA_ACCESS["PERMISSIONS"] = [
    "persona.change_password_for_user",
    //-----------------------------------
    "persona.createAndDisplayNewPassword",
    "persona.createManualPassword",
    //-----------------------------------
    "reset_password",
    "solicitud_usuario.approve",
    "solicitud_usuario.deny",
    "roles.assign",
    "roles.revoke",
    "phpinfo.view",
    "dynamo.use",
];


/*
"robot",
"anonymous",
"logged_in",
"roles",
*/
