<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	$userID = getEncryptedUserIDCookie($mysqli,$mysqli->escape_string($_COOKIE['UserID'])); //don't kill page if -1 because don't need to be logged in here


	$rideCriteria = array();

	$startDate = mysql_escape_string($_GET['startDate']);
	$endDate = mysql_escape_string($_GET['endDate']);
	$rideType = mysql_escape_string($_GET['rideType']);
	$startingAfter = mysql_escape_string($_GET['startTime']);

	$startingBefore = mysql_escape_string($_GET['endTime']);
	$startingBefore = ($startingBefore != "Not Sure") ? $startingBefore : '24:00:00';


	$level = mysql_escape_string($_GET['level']);
	//$userID = mysql_escape_string($_GET['userID']);//get logged in user from session/cookie variable, this is succeptible to a hack rn. If invalid kill page
	$groupRides = mysql_escape_string($_GET['groupRides']);
	$friendRides = mysql_escape_string($_GET['friendRides']);
	$publicRides = !empty(mysql_escape_string($_GET['publicRides']));
	$timezone = mysql_escape_string($_GET['timezone']);
	$showFriends = !empty($friendRides);

	$friendRidesQuery = "";
	$groupRidesQuery = "";

	if(!empty($groupRides)){
		$groupRidesQuery .= (!empty($friendRides)) ? " OR " : "";

		$groups = getGroupRideIDsForUser($mysqli, $userID);

		if(sizeof($groups) > 0){
			$groupRidesQuery .= '(CreatorType = 1 AND ('.implode(" OR ", $groups).'))';
		}
	}

	//**********Time Range*******

	$timezone = ($userID != -1) ? getUserProfileFromDB($mysqli,$userID)->timezone : $timezone;

	$timeColumn = "DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%H:%i:%s')";
	$range = "BETWEEN ? AND ?";

	$timeRangeQuery = " AND (".$timeColumn." ".$range." )";

	//****************Time Range End ********


	//********friends,groups,public,type,level options - NON TIME RELATED OPTIONS **********

	if($showFriends && $userID != -1){
		$friends = getFriendIDs($mysqli, $userID);
		$friendRidesQuery = '(CreatorType = 0 AND ('.implode(" OR ", $friends).'))';
	}

	$and = "";

	if($userID != -1){
			$and = (!$publicRides && empty($groupRides) && empty($friendRides)) ? '' : "AND";
	}else{
		$and = "AND";
	}


	$showANDOR = (!empty($groupRides) || $showFriends) ? "OR " : "";
	$showPublic = ($publicRides) ? " ".$showANDOR."Visibility = 2 " : "";

	$groupFriendOrPublic =  ($userID != -1) ? '('.$friendRidesQuery.$groupRidesQuery.$showPublic.')' : " Visibility = 2 "; //public rides (visibility = 2) don't show - safe from injection

	$query = 'WHERE ('.$groupFriendOrPublic.''.addParam('RideType', $rideType ,$and).''.addParam('Level', $level,'AND').''.$timeRangeQuery.') '; //fix empty ()

	//need to have same number on bindParams this will allow value to be passed on
	$rideType = ($rideType == "-1") ? "0" : $rideType;  //ride type is int type so 0 = false
	$level = ($level == "-1") ? "false" : $level; //level is string so use false


	//*************END NON TIME RELATED OPTIONS **************

	date_default_timezone_set('UTC');

	$startDate = strtotime($startDate); //dont need to escape because will be false if invalid string
	$endDate = strtotime($endDate);

	$query .= ' AND ((rr.repeat_start >= '.$startDate.' AND rr.repeat_end = 0) OR (getStartDate(rr.repeat_start,'.$startDate.', rr.repeat_interval) <= '.$endDate.' AND rr.repeat_interval > 0)) ORDER BY realStartDate ASC, startTimeAdjust ASC'; //realStartDate is defined in the query from the function getUserRidesWithDateRangeFromDB( in databasehelper.php


	$rides = getUserRidesWithDateRangeFromDB($mysqli,$query,$timezone, $startDate, $endDate, $startingAfter, $startingBefore, $rideType, $level);
	
	echo json_encode($rides);
	
	// echo $query;
	$mysqli->close();


	function addParam($column,$param, $and){

		if ($param == '-1') {//EX: is ride type is any it would be -1
			return " ".$and." ISNULL(".$column.") = ?"; //wont be null, so will select as long as there is a value
		}

		if($param != null){
			return " ".$and." ".$column." = ?";
		}

		return "";
	}


	// $startTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";
	// $endTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";

	
	// $days = array();

	// while($endDate >= $startDate){
	// 	$days[] = ' ('.$startTimeAdjust.' = '.$startDate.' OR ((( '.$startDate.' - '.$startTimeAdjust.' ) % rr.repeat_interval = 0 )
	// 			 AND '.$endTimeAdjust.' >= '.$startDate.'))';
	// 	$startDate += 86400;
	// }

	// $query .= ' AND ('.implode(" OR ", $days).') ORDER BY startDateAdjust ASC, dayOfWeek ASC';
	//shows own users rides
?>