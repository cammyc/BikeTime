<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$rideID = mysql_escape_string($_GET['rideID']);
	$userID = mysql_escape_string($_GET['userID']);
	$message = mysql_escape_string($_GET['message']);
	$intervals = mysql_escape_string($_GET['intervals']);

	date_default_timezone_set("UTC"); 
	$timestamp = time();//get time from server (more secure)

	$result = $mysqli->query('INSERT INTO `ride_message_board` (`ID`, `Message`, `UserID`, `rideID`,`Intervals`, `Timestamp`) VALUES (NULL, "'.$message.'", '.$userID.', '.$rideID.','.$intervals.', '.$timestamp.')');

	if($result){
		echo 1;
	}else{
		echo 0;
	}

	$mysqli->close();
?>