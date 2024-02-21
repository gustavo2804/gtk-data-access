<?php

require_once(dirname($_SERVER["DOCUMENT_ROOT"])."/vendor/autoload.php");
require_once "tablePlusHelpers.php";

$debug = false;

if (!isset($_GET["data_source"])) 
{
    die("<h1>Add Filter: No data source provided </h1><h3>GET</h3><pre>" . print_r($_GET, true) . "</pre><h3>POST</h3><pre>" . print_r($_POST, true) . "</pre>");
}


$dataSourceName = $_GET["data_source"];
$dataSource     = DataAccessManager::get($dataSourceName);

if ($debug)
{
    error_log("Running `addFilter.php`: request URI: ".$_SERVER['REQUEST_URI']);
    error_log("<pre>GET</pre>");
    error_log("<pre>");
    error_log(print_r($_GET, true));
    error_log("</pre>");
    error_log("<pre>POST</pre>");
    error_log("<pre>");
    error_log(print_r($_POST, true));
    error_log("</pre>");
}

echo createSingleFilterLineForDataSource($dataSource);
