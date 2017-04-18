<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID = mysql_escape_string($_GET['userID']);

	$friendRequests = getFriendRequests($mysqli,$userID);

	echo json_encode($friendRequests);

	$mysqli->close();
?>

