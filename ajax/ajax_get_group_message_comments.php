<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$messageID = mysql_escape_string($_GET['messageID']);

	$result = getGroupMessageComments($mysqli,$messageID);

	echo json_encode($result);

	$mysqli->close();
?>

