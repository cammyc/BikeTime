<?php
include('../databasehelper/databasehelper.php');

$mysqli = getDB();

$rideID = $mysqli->real_escape_string($_GET["rideID"]);

$coords =  json_encode(getRouteForRide($mysqli,$rideID));

$mysqli->close();

echo $coords;

?>