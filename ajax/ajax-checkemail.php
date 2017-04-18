<?php
 	include "../databasehelper/databasehelper.php";

	$mysqli = getDB();

	$rideCriteria = array();

	$email = mysql_escape_string($_GET['email']);

	$result = $mysqli->query("SELECT * FROM users WHERE Email = '" .$email. "'");

	$numRows = $result->num_rows;

	if($numRows > 0){
		echo "true";
	}else{
		echo "false";
	}


?>