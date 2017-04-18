<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$userID1 = mysql_escape_string($_GET['userID1']);
	$userID2 = mysql_escape_string($_GET['userID2']);

	echo removeFriend($mysqli, $userID1, $userID2); //1 means removed, 0 means error

	$mysqli->close();
?>
