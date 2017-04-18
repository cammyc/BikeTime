<?php
include('../databasehelper/databasehelper.php');

$mysqli = getDB();

$routeID = $mysqli->real_escape_string($_GET["routeID"]);

$elevationData =  json_encode(getRouteElevationData($mysqli,$routeID));

$mysqli->close();

echo $elevationData;

?>