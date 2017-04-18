<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$routeID = mysql_escape_string($_GET['routeID']);

	$result = deleteRoute($mysqli, $routeID);

	echo $result;

	$mysqli->close();
?>