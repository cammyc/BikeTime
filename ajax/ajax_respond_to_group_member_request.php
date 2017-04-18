<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$groupID = mysql_escape_string($_GET['groupID']);
	$userID = mysql_escape_string($_GET['userID']);
	$isAccepted = (mysql_escape_string($_GET['isAccepted']) == 1) ? 1 : 0;

	$result = 0;

	if($isAccepted == 1){
		acceptGroupMember($mysqli,$groupID,$userID);
		$result = 1;
	}else{
		removeGroupMember($mysqli,$groupID,$userID);
		$result = 2;
	}

	echo $result; //1 means accepted, 2 means declined, 0 is error

	$mysqli->close();
?>

