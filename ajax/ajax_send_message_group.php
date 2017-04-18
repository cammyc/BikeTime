<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);
	$userID = mysql_escape_string($_GET['userID']);
	$message = mysql_escape_string($_GET['message']);

	date_default_timezone_set("UTC"); 
	$timestamp = time();//get time from server (more secure)

	$result = sendMessageGroup($mysqli, $message, $userID, $groupID, $timestamp);

	if($result){
		echo 1;
	}else{
		echo 0;
	}

	$mysqli->close();
?>