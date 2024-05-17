<?php


function HasPermission($permission) : bool {
    return DataAccessManager::get("session")->currentUserHasPermission($permission);
}

//       IfHasPermission
function IfHasPermission($permission, $closure) {
    if (HasPermission($permission)) {
        $closure();
    }
}
