<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID = mysql_escape_string($_GET['userID']);

	$friends = getFriends($mysqli,$userID);

	echo json_encode($friends);

	$mysqli->close();
?>

