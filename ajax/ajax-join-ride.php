<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$rideID = mysql_escape_string($_GET['rideID']);
	$userID = mysql_escape_string($_GET['userID']);
	$intervals = mysql_escape_string($_GET['intervals']);

	//store the UTC date of the ride;

	$hasRiderJoined = hasRiderJoinedRide($mysqli,$rideID,$intervals,$userID);

	$whichQuery = ($hasRiderJoined == 'true') ? 2 : 1;

	$result = ($hasRiderJoined == 'true') ?  removeUserFromRide($mysqli,$rideID,$intervals,$userID) : addUserToRide($mysqli,$rideID,$intervals,$userID);

	if($result){//0 is error,1 is joined, 2 is left
		echo $whichQuery;
	}else{
		echo 0;
	}

	$mysqli->close();
?>