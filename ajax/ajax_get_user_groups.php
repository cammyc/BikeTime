<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID = mysql_escape_string($_GET['userID']);

	$userGroups = getUsersGroups($mysqli,$userID);

	echo json_encode($userGroups);

	$mysqli->close();
?>

