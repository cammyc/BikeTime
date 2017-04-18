<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	date_default_timezone_set("UTC");

	$userID = mysql_escape_string($_GET['userID']);
	$rideID = mysql_escape_string($_GET['rideID']);
	$title = mysql_escape_string($_GET['rideTitle']);
	$rideType = mysql_escape_string($_GET['rideType']);
	$startDate = strtotime(mysql_escape_string($_GET['startDate']));//this is the date from the date input field, user can change
	$originalStartDate = strtotime(mysql_escape_string($_GET['originalStartDate'])); //this is the current date of ride, same as above if no change
	$rideLevel = mysql_escape_string($_GET['rideLevel']);
	$startTime = mysql_escape_string($_GET['startTime']);
	$endTime = mysql_escape_string($_GET['endTime']);
	$isAllRides = mysql_escape_string($_GET['isAllRides']);
	$dayDifference = mysql_escape_string($_GET['dayDifference']);
	$profile = getUserProfileFromDB($mysqli,$userID);

	$ride = getRideDetails($mysqli, $rideID, $profile->timezone);

	if($ride->creatorID == $userID){//making sure creator of ride is logged in, prevent hacker. need to change so that if ride is for group and admin wants to edit it they can even if they werent original creator

		$ride->title = $title;
		$ride->rideType = $rideType;
		$ride->level = $rideLevel;
		$ride->startTime = to24Hour($startTime);
		$ride->endTime = ($endTime == null || strcasecmp($endTime, "Not Sure") == 0) ? "NULL" : to24Hour($endTime);


		$updatedFields = updateRideFields($mysqli,$ride);

		if($ride->repeatInterval == null){
			$updatedDate = updateRideDate($mysqli,$ride);
		}else{
			if($isAllRides == 'true' && $dayDifference != 0){
				$days = $dayDifference*86400;
				$ride->startDate = $ride->startDate + $days;
				updateRideDate($mysqli,$ride);//THIS IS MAKING THE INTERVAL OFF BY 1
				//echo $ride->startDate;
			}else if($originalStartDate != $startDate){
				$ride->startDate = $startDate;
				createSingleRideFromRepeat($mysqli,$ride,$profile->timezone,$originalStartDate);
			}else{
				echo "no change";
			}
		}


	}else{
		echo 0;
	}

	$mysqli->close();

	function to24Hour($time){
		$unixtime = strtotime($time);
		return date("H:i:s", $unixtime);
	}

?>