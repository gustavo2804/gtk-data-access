<?php

function startsWith($lookFor, $string)
{
	return strpos($string, $lookFor) === 0;
}


function snakeToSpaceCase($string) {
    // Replace underscores with spaces
    $stringWithSpaces = str_replace('_', ' ', $string);

    // Capitalize the first letter of each word (optional)
    $spaceCaseString = ucwords($stringWithSpaces);

    return $spaceCaseString;
}

function LinkToAllPermissionIfExists($permission, $options = null)
{
	$currentUser = DataAccessManager::get("session")->getCurrentUser();

	if (DataAccessManager::get("persona")->hasPermission($permission, $currentUser))
	{
		$dataSourceName = explode(".", $permission)[0];
		return AllLinkTo($dataSourceName, $options);
	}
	else
	{
		return "";
	}  
}

function LinkToEditItemPermissionIfExists($permission, $item, $options = null)
{
	$currentUser = DataAccessManager::get("session")->getCurrentUser();

	if (DataAccessManager::get("persona")->hasPermission($permission, $currentUser))
	{
		return editLinkTo($permission, $item, $options);
	}
	else
	{
		return "";
	}  
}

function linkTo($maybeHref, $options)
{
	$href = null;

	if (startsWith("/", $maybeHref))
	{
		$href = $maybeHref;
	}
	else
	{
		$href = $maybeHref;
	}

	$id      = $options['class'] ?? '';
	$class   = $options['class'] ?? '';
	$style   = $options['style'] ?? '';
	$label   = $options['label'] ?? $href;

	$toReturn  = "";
	$toReturn .= "<a id='$id' href='$href' class='$class' style='$style'>";
	$toReturn .= $label;
	$toReturn .= "</a>";
	
	return $toReturn;	
}

function ShowURLTo($dataSourceName, $identifier, $options = null)
{
	return DataAccessManager::showURLTo($dataSourceName, $identifier, $options);
}

function AllURLTo($dataSourceName, $options = null)
{
	return DataAccessManager::allURLTo($dataSourceName, $options);
}

function AllLinkTo($dataSourceName, $options = null)
{
	return DataAccessManager::allLinkTo($dataSourceName, $options);
}