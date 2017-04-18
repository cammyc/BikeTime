<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$rideID = mysql_escape_string($_GET['rideID']);
	$userID = mysql_escape_string($_GET['userID']);
	$intervals = mysql_escape_string($_GET['intervals']);

	//store the UTC date of the ride;

	echo json_encode(getRideMembers($mysqli,$rideID,$intervals));
	
	$mysqli->close();
?>