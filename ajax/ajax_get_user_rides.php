<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	$rideCriteria = array();

	$startDate = mysql_escape_string($_GET['startDate']);
	$endDate = mysql_escape_string($_GET['endDate']);
	$userID = mysql_escape_string($_GET['userID']);
	$timezone = mysql_escape_string($_GET['timezone']);
	$joinedRides = (mysql_escape_string($_GET['joinedRides']) == 'true') ? true : false;
	$createdRides = (mysql_escape_string($_GET['createdRides']) == 'true') ? true : false;

	$timezone = $userID != -1 ? getUserProfileFromDB($mysqli,$userID)->timezone : $timezone;

	$startTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";
	$endTimeAdjust = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";


	date_default_timezone_set('UTC');

	$startDate = strtotime($startDate);
	$endDate = strtotime($endDate);

	$startDateTemp = $startDate;//need to use temp variable because startDate is used for the joinedRidesQuery variable.

	$days = array();

	while($endDate >= $startDateTemp){
		$days[] = ' ('.$startTimeAdjust.' = '.$startDateTemp.' OR ((( '.$startDateTemp.' - '.$startTimeAdjust.' ) % rr.repeat_interval = 0 )
				 AND '.$endTimeAdjust.' >= '.$startDateTemp.'))';
		$startDateTemp += 86400;
	}

	$query = 'LEFT JOIN `joined_rides` jr ON jr.RideID = rides.ID WHERE ('.implode(" OR ", $days).')';

	function addParam($column,$param){

		if ($param == -1) {
			$param = null;
		}

		if($param != null){
			return " AND ".$column." = '".$param."'";
		}

		return "";
	}

	$joinedRidesQuery = ($joinedRides) ? '(jr.UserID = '.$userID.' AND ((jr.Intervals*rr.repeat_interval)+rr.repeat_start) >= '.$startDate.')' : '';
	$createdRidesQuery = ($createdRides) ? 'rides.CreatorID = '.$userID.'' : '';

	if($joinedRides && $createdRides){
		$query .= ' AND ('.$joinedRidesQuery.' OR '.$createdRidesQuery.')';
	}else{
		$query.= ' AND '.$joinedRidesQuery.''.$createdRidesQuery.'';//can do both because with this being in the else statement, one of the varibales will be ''
	}

	$rides = (!$joinedRides && !$createdRides) ? '' : getUserRidesWithDateRangeFromDB($mysqli,$query,$timezone, $startDate, $endDate, null, null, null, null);

	$mysqli->close();

	echo json_encode($rides);
?>