<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);
	$userID = mysql_escape_string($_GET['userID']);

	$result = removeGroupMember($mysqli,$groupID,$userID);

	echo $result;

	$mysqli->close();
?>

