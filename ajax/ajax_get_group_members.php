<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);
	$accepted = (mysql_escape_string($_GET['accepted']) == 1) ? 1 : 0;

	$result = getGroupMembers($mysqli,$groupID,$accepted);

	echo json_encode($result); //1 means added, 0 means request is pending, 2 means error

	$mysqli->close();
?>

