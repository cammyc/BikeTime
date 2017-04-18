<?php

	include_once("../databasehelper/databasehelper.php");

	if (session_status() == PHP_SESSION_NONE) {
    	session_start();
    }

    $mysqli = getDB();

	$name = $_POST["name"];
	$description = $mysqli->real_escape_string($_POST["description"]);
	$website = $mysqli->real_escape_string($_POST["website"]);
	$private = $mysqli->real_escape_string(isset($_POST['private']) ? 1 : 0); //true/false
	$type = $mysqli->real_escape_string($_POST["type"]);
	$sport = $mysqli->real_escape_string($_POST["sport"]);

	// echo $name;
	// echo $description;
	// echo $website;
	// echo $private;
	// echo $type;
	// echo $sport;

	$groupID = creategroup($mysqli,$name,$description,$website,$private,$type,$sport,"group.png","");

	addGroupMember($mysqli, $groupID, $_SESSION['UserID'],true,true);

	$mysqli->close();

    header("Location: ../group/?id=".$groupID."");

?>