<?php

	include('../databasehelper/databasehelper.php');

	if (session_status() == PHP_SESSION_NONE) {
    	session_start();
    }

	$userID = $_SESSION['UserID'];

	$mysqli = getDB();

  	$account = getAccountFromDB($mysqli, $userID);

  	$account->email =  $mysqli->real_escape_string(!empty($_POST['email']) ? $_POST['email'] : $account->email); //if value has been changed update it, if not keep old value so that this file can be used for all updates
  	$account->password = (!empty($_POST['Password'])) ? password_hash($mysqli->real_escape_string($_POST['Password']), PASSWORD_DEFAULT) : $account->password; 
  	$account->receiveUpdates =  $mysqli->real_escape_string(!empty($_POST['receiveUpdates']) ? $_POST['receiveUpdates'] : "0"); 

	updateAccount($mysqli, $account);


	$mysqli->close();

	header("Location: ../profile?id=".$userID."");
?>