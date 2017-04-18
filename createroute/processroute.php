<?php
include('../databasehelper/databasehelper.php');

if (session_status() == PHP_SESSION_NONE) {
	session_start();
}


 $mysqli = getDB();
  if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
  }

$userID = $_SESSION['UserID'];
$name = $mysqli->real_escape_string($_POST["name"]);
$name = $mysqli->real_escape_string($_POST["name"]);
$description = $mysqli->real_escape_string($_POST["description"]);
$isPublic = true;
$coordinates = json_decode($_POST["coordinates"]);
$waypoints = json_decode($_POST["waypoints"]);
$distance = $mysqli->real_escape_string($_POST["distance"]);
$climb = $mysqli->real_escape_string($_POST["climb"]);
$time = $mysqli->real_escape_string($_POST["time"]);
$elevationData = json_decode($_POST["elevationData"]);

if(isset($_POST["private"])){
	$isPublic = false;
}


$routeID = insertRoute($mysqli,$userID,$name,$description,$distance,$climb,$time,$isPublic);


if(sizeof($coordinates) > 0){
	insertRouteCoords($mysqli, $routeID, $coordinates);
}

if(sizeof($waypoints) > 0){
	insertRouteWaypoints($mysqli, $routeID, $waypoints);
}

if(sizeof($elevationData) > 0){
	insertRouteElevationData($mysqli, $routeID, $elevationData);
}

$mysqli->close();

header("Location: ../profile/index.php?id=".$_SESSION['UserID']."");

?>