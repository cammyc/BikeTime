<?php

	include_once("../databasehelper/databasehelper.php");

	$mysqli = getDB();

	$groupID = $mysqli->real_escape_string($_POST["groupID"]);
	$userID = $mysqli->real_escape_string($_POST["userID"]);
	$isAccepted = $mysqli->real_escape_string($_POST["isAccepted"]);

    addGroupMember($mysqli, $groupID, $userID,0,$isAccepted);

	$mysqli->close();

    header("Location: ../group/?id=".$groupID."");

?>