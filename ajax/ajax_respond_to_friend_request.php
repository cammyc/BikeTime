<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$friendshipID = mysql_escape_string($_GET['friendshipID']);
	$acceptDecline = mysql_escape_string($_GET['acceptDecline']);

	$result = ($acceptDecline == 1) ? acceptFriendRequest($mysqli,$friendshipID) : declineFriendRequest($mysqli,$friendshipID);

	echo $result; //1 means added, 0 means request is pending, 2 means error

	$mysqli->close();
?>

