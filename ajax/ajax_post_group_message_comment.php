<?php
	include_once("../databasehelper/databasehelper.php");
	   
	$mysqli = getDB();

	$messageID = mysql_escape_string($_GET['messageID']);
	$userID = mysql_escape_string($_GET['userID']);
	$comment = mysql_escape_string($_GET['comment']);

	date_default_timezone_set("UTC"); 
	$timestamp = time();//get time from server (more secure)

	$result = postMessageCommentGroup($mysqli, $comment, $userID, $messageID, $timestamp);

	echo ($result) ? 1 : 0;

	$mysqli->close();
?>

