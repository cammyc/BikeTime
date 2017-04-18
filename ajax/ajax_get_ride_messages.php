<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$rideID = mysql_escape_string($_GET['rideID']);
	$intervals = mysql_escape_string($_GET['intervals']);

	echo json_encode(getRideMessages($mysqli,$rideID,$intervals));

	$mysqli->close();
?>