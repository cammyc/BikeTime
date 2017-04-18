<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	date_default_timezone_set("UTC");

	$rideID = mysql_escape_string($_GET['rideID']);
	$isAllRides = mysql_escape_string($_GET['allRides']);
	$skipDateTemp = mysql_escape_string($_GET['dateOfRide']);//-1 if not repeating ride
	$skipDate = ($skipDateTemp == -1) ? -1 : strtotime($skipDateTemp);

	if($isAllRides == 0){
		echo skipRide($mysqli,$rideID,$skipDate,-1);
	}else{
		echo skipRide($mysqli,$rideID,-1,-1);

	}

	$mysqli->close();

?>