<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	$rideCriteria = array();

	$startDate = mysql_escape_string($_GET['startDate']);
	$endDate = mysql_escape_string($_GET['endDate']);
	$rideType = mysql_escape_string($_GET['rideType']);
	$startingAfter = mysql_escape_string($_GET['startTime']);
	$startingBefore = mysql_escape_string($_GET['endTime']);
	$level = mysql_escape_string($_GET['level']);
	$userID = mysql_escape_string($_GET['userID']);
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

	$timezone = $userID != -1 ? getUserProfileFromDB($mysqli,$userID)->timezone : $timezone;

	$timeColumn = "DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%H:%i:%s')";
	
	$startTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";
	$endTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";

	$range = "";

	if($startingBefore != "Not Sure"){

			$range = "BETWEEN '".$startingAfter."' AND '".$startingBefore."'";
		             
	}else{
			$range = "BETWEEN '".$startingAfter."' AND '24:00:00'";
	}


	date_default_timezone_set('UTC');

	$startDate = strtotime($startDate);
	$endDate = strtotime($endDate);

	$days = array();

	function addParam($column,$param, $and){

		if ($param == '-1') {
			$param = null;
		}

		if($param != null){
			return " ".$and." ".$column." = '".$param."'";
		}

		return "";
	}

	if($showFriends){
		$friends = getFriendIDs($mysqli, $userID);
		$friendRidesQuery = '(CreatorType = 0 AND ('.implode(" OR ", $friends).'))';
	}

	$and = ($publicRides || (empty($groupRides) && empty($friendRides))) ? '' : "AND";

	$timeRangeQuery = " AND (".$timeColumn." ".$range." )";

	$showANDOR = (!empty($groupRides) || $showFriends) ? "OR " : "";
	$showPublic = ($publicRides) ? " ".$showANDOR."Visibility = 2 " : "";


	$groupFriendOrPublic =  '('.$friendRidesQuery.$groupRidesQuery.$showPublic.')'; //public rides (visibility = 2) don't show

	$query = 'WHERE ('.$groupFriendOrPublic.''.addParam('RideType',$rideType,$and).''.addParam('Level',$level,'AND').''.$timeRangeQuery.') '; //fix empty ()

	// while($endDate >= $startDate){
	// 	$days[] = ' ('.$startTimeAdjust.' = '.$startDate.' OR ((( '.$startDate.' - '.$startTimeAdjust.' ) % rr.repeat_interval = 0 )
	// 			 AND '.$endTimeAdjust.' >= '.$startDate.'))';
	// 	$startDate += 86400;
	// }

	// $query .= ' AND ('.implode(" OR ", $days).') ORDER BY startDateAdjust ASC, dayOfWeek ASC';
	//shows own users rides

	$query .= ' AND ((rr.repeat_start >= '.$startDate.' AND rr.repeat_end = 0) OR (getStartDate(rr.repeat_start,'.$startDate.', rr.repeat_interval) <= '.$endDate.' AND rr.repeat_interval > 0)) ORDER BY realStartDate ASC, startTimeAdjust ASC';


	$rides = getUserRidesWithDateRangeFromDB($mysqli,$query,$timezone, $startDate, $endDate);
	
	echo json_encode($rides);
	//echo $query;
	$mysqli->close();
?>