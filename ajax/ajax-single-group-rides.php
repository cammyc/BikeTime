<?php
	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	$userID = mysql_escape_string($_GET['userID']);
	$startDate = mysql_escape_string($_GET['startDate']);
	$endDate = mysql_escape_string($_GET['endDate']);
	$groupID = mysql_escape_string($_GET['groupID']);
	$timezone = mysql_escape_string($_GET['timezone']);
	$groupRides = (mysql_escape_string($_GET['groupRides']) == 'true') ? true : false;
	$memberRides = (mysql_escape_string($_GET['memberRides']) == 'true') ? true : false;

	$timezone = $userID != -1 ? getUserProfileFromDB($mysqli,$userID)->timezone : $timezone;

	$startTimeAdjustGroup = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), r.startTime), '%Y-%m-%d %T'), r.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";
	$endTimeAdjustGroup = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(r.endTime,'')), '%Y-%m-%d %T'), r.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";

	$startTimeAdjustMember = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";
	$endTimeAdjustMember = "UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone))";

	//need to different variables above because for group rides the SQL is r.* and for member its rides.*

	date_default_timezone_set('UTC');

	$startDate = strtotime($startDate);
	$endDate = strtotime($endDate);

	$daysGroup = array();
	$daysMember = array();

	while($endDate >= $startDate){
		$daysGroup[] = ' ('.$startTimeAdjustGroup.' = '.$startDate.' OR ((( '.$startDate.' - '.$startTimeAdjustGroup.' ) % rr.repeat_interval = 0 )
				 AND '.$endTimeAdjustGroup.' >= '.$startDate.'))';

		$daysMember[] = ' ('.$startTimeAdjustMember.' = '.$startDate.' OR ((( '.$startDate.' - '.$startTimeAdjustMember.' ) % rr.repeat_interval = 0 )
				 AND '.$endTimeAdjustMember.' >= '.$startDate.'))';
		$startDate += 86400;
	}

	$query = ' WHERE ('.implode(" OR ", $daysGroup).')';

	$memberRideQuery = ' WHERE ('.implode(" OR ", $daysMember).')';

	function addParam($column,$param){

		if ($param == -1) {
			$param = null;
		}

		if($param != null){
			return " AND ".$column." = '".$param."'";
		}

		return "";
	}

	if ($memberRides) {
		$groupMembers =  getGroupMembers($mysqli, $groupID, 1);
		$memberIDs = array();
		foreach ($groupMembers as $gm) {
			$memberIDs[] = 'rides.creatorID = '.$gm->profile->userID;
		}
		$memberRideQuery .= ' AND ('.implode(" OR ", $memberIDs).') AND rides.creatorType = 0';
	}

	if($groupRides){
		$query .= addParam('GroupID',$groupID); 
	}

	$rides = null;

	if($memberRides && $groupRides){
		$groupRidesArray = getSingleGroupRidesFromDB($mysqli,$query,$timezone);
		$memberRidesArray = getUserRidesWithDateRangeFromDB($mysqli,$memberRideQuery,$timezone, $startDate, $endDate);


		$rides = array_merge($groupRidesArray,$memberRidesArray);
	}else if ($memberRides){
		$rides = getUserRidesWithDateRangeFromDB($mysqli,$memberRideQuery,$timezone, $startDate, $endDate);
	}else if ($groupRides){
		$rides = getSingleGroupRidesFromDB($mysqli,$query,$timezone);
	}

	$mysqli->close();

	echo json_encode($rides);

	//echo $rides;
?>