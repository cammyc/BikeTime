<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID1 = mysql_escape_string($_GET['userID1']);
	$userID2 = mysql_escape_string($_GET['userID2']);
	$requesterID = mysql_escape_string($_GET['requesterID']);

	$result = addFriend($mysqli, $userID1, $userID2, $requesterID, time());

	echo $result; //1 means added, 0 means request is pending, 2 means error

	$mysqli->close();
?>

