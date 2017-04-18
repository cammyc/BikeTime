<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID1 = mysql_escape_string($_GET['userID1']);
	$userID2 = mysql_escape_string($_GET['userID2']);

	$status = getFriendshipStatus($mysqli, $userID1, $userID2);

	echo $status; //1 means added, 0 means request is pending, 2 means error

	$mysqli->close();
?>
