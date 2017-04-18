<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);

	echo json_encode(getGroupMessages($mysqli,$groupID));

	$mysqli->close();
?>