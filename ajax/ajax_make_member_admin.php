<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);
	$userID = mysql_escape_string($_GET['userID']);

	$result = makeMemberAdmin($mysqli,$groupID,$userID);

	echo $result; //1 means successful, 0 means something went wrong

	$mysqli->close();
?>

