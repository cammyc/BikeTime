<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$rideID = mysql_escape_string($_GET['rideID']);
	$userID = mysql_escape_string($_GET['userID']);
	$intervals = mysql_escape_string($_GET['intervals']);

	//store the UTC date of the ride;

	$result = $mysqli->query('SELECT * FROM `joined_rides` WHERE RideID = '.$rideID.' AND UserID = '.$userID.' AND Intervals = '.$intervals.'');//dont need timezone because we have users timezone

	if($result){//0 is error,2 is joined, 1 is didn't join
		$row_cnt = $result->num_rows;

		$hasJoined = ($row_cnt > 0) ? 2 : 1;

		echo $hasJoined;

	}else{
		echo 0;
	}



	$mysqli->close();
?>