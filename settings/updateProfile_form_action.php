<?php

	include('../databasehelper/databasehelper.php');

  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }

  $userID = $_SESSION['UserID'];

	$mysqli = getDB();

  	$profile = getUserProfileFromDB($mysqli, $userID);

  	$profile->email =  $mysqli->real_escape_string(!empty($_POST['email']) ? $_POST['email'] : $profile->email); //if value has been changed update it, if not keep old value so that this file can be used for all updates
  	$profile->password =  $mysqli->real_escape_string(!empty($_POST['password']) ? $_POST['password'] : $profile->password); 
  	$profile->receiveUpdates =  $mysqli->real_escape_string(!empty($_POST['receiveUpdates']) ? $_POST['receiveUpdates'] : $profile->receiveUpdates); 
  	$profile->firstName =  $mysqli->real_escape_string(!empty($_POST['firstName']) ? $_POST['firstName'] : $profile->firstName); 
  	$profile->lastName =  $mysqli->real_escape_string(!empty($_POST['lastName']) ? $_POST['lastName'] : $profile->lastName); 
  	$profile->gender =  $mysqli->real_escape_string(!empty($_POST['gender']) ? $_POST['gender'] : $profile->gender); 
  	$profile->birthday =  $mysqli->real_escape_string(!empty($_POST['birthday']) ? $_POST['birthday'] : $profile->birthday); 
  	$profile->weight =  $mysqli->real_escape_string(!empty($_POST['weight']) ? $_POST['weight'] : $profile->weight); 
  	$profile->height =  $mysqli->real_escape_string(!empty($_POST['height']) ? $_POST['height'] : $profile->height); 
  	$profile->bio =  $mysqli->real_escape_string(isset($_POST['bio']) ? $_POST['bio'] : $profile->bio); //isset because not required field

	updateProfile($mysqli, $profile);

	$mysqli->close();

  header("Location: ../profile?id=".$userID."");

?>