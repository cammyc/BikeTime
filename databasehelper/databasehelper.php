<?php

class userProfile
	{
		public $userID;
		public $email;
		public $password;
		public $facebookID;
		public $timezone;
    public $receiveUpdates;
		public $dateCreated;
		public $firstName;
		public $lastName;
		public $gender;
		public $birthday;
		public $weight;
		public $height;
		public $bio;
    public $groups;
    public $profPicURL;
    public $tempVar;
	}

  class ride
  {
    public $rideID;
    public $creatorID;
    public $routeID;
    public $creatorType;
    public $rideType;
    public $startTime;
    public $endTime;
    public $UTCStartTime;
    public $UTCEndTime;
    public $title;
    public $description;
    public $level;
    public $visibility;
    public $startLat;
    public $startLon;
    public $startDate;
    public $endDate;
    public $repeatInterval;
    public $routeCoords;
    public $creatorProfile;
    public $facebookID;
    public $group;
  }

  class singleRide{
    public $rideID;
    public $creatorID;
    public $routeID;
    public $creatorType;
    public $rideType;
    public $startTime;
    public $endTime;
    public $title;
    public $description;
    public $level;
    public $visibility;
    public $startLat;
    public $startLon;
    public $startDate;
    public $endDate;
    public $repeatInterval;
    public $route;
    public $attendees;
    public $creatorProfile;
    public $group;
  }

class group
  {
    public $groupID;
    public $name;
    public $description;
    public $website;
    public $private;
    public $type;
    public $sport;
    public $userIsAdmin;
    public $groupMembers;
    public $pendingMembers;
    public $groupMessages;//only for getGroupFromDB()
    public $logoURL;
    public $city;
    // public $members;
    // public $rides;
    // public $logoLink;
    // public $photo_url;
  }

class groupMember{
    public $groupID;
    public $profile;
    public $isAdmin;
    public $isMember;//0 mean not member, 1 means member, 2 means request pending
}

class groupMessage{
    public $messageID;
    public $message;
    public $user;
    public $groupID;
    public $timestamp;
    public $userIsAdmin;
}

class groupMsgComment{
    public $commentID;
    public $messageID;
    public $comment;
    public $user;
    public $timestamp;
    public $userIsAdmin;
}


class rideMessage{
    public $messageID;
    public $message;
    public $user;
    public $rideID;
    public $timestamp;
    public $userCreatedRide;
}

class route{
    public $routeID;
    public $creatorID;
    public $name;
    public $description;
    public $distance;
    public $climb;
    public $time;
    public $isPublic;
    public $deleted;
    public $routeCoords;
    public $waypointCoords;
}

function getDB(){//replace all other uses so that when site goes live I only have to change this function from root,root
  //return new mysqli("localhost", "root", "root", "biketime");
  return new mysqli("173.194.225.113", "root", "helloworld", "biketime");

}

function getGroupFromDB($mysqli,$groupID){
    $g = new group();

      $query = "SELECT ID, Name, Description, Website, Private, Type, Sport, LogoURL, City FROM groups WHERE groups.ID = ?";


      $statement = $mysqli->prepare($query);

      $statement->bind_param("i", $groupID);
      $statement->execute();
      $result = $statement->get_result();

      $row = $result->fetch_array(MYSQLI_NUM);

        $g->groupID = $row[0];
        $g->name = $row[1];
        $g->description = $row[2];
        $g->website = $row[3];
        $g->private = $row[4];
        $g->type = $row[5];
        $g->sport = $row[6];
        $g->logoURL = $row[7];
        $g->city = $row[8];
        $g->groupMembers = getGroupMembers($mysqli,$groupID,true);
        $g->pendingMembers = getGroupMembers($mysqli,$groupID,0);
        $g->groupMessages = getGroupMessages($mysqli,$groupID);

      
      return $g;
}

function getMultipleGroups($mysqli){

   $query = "SELECT ID, Name, Description, Website, Private, Type, Sport, LogoURL, City FROM groups";


    $statement = $mysqli->prepare($query);
    $statement->execute();
    $result = $statement->get_result();

    $groups = array();
    $i = 0;
      while($row = $result->fetch_array(MYSQLI_NUM)){
        $g = new group();
          $g->groupID = $row[0];
          $g->name = $row[1];
          $g->description = $row[2];
          $g->website = $row[3];
          $g->private = $row[4];
          $g->type = $row[5];
          $g->sport = $row[6];
          $g->logoURL = $row[7];
          $g->city = $row[8];
        $groups[$i] = $g;
        $i++;
      }
    return $groups;
}

function creategroup($mysqli,$name,$description,$website,$private,$type,$sport,$photo_url,$city){
  $query = 'INSERT INTO `groups` (Name, Description, Website, Private, Type, Sport, LogoURL, City) VALUES (?,?,?,?,?,?,?,?)';

  $statement = $mysqli->prepare($query);
  $statement->bind_param("sssissss", $name, $description, $website, $private, $type, $sport, $photo_url, $city);
  $statement->execute();

  if($statement){
    return $statement->insert_id;;
  }else{
    return -1;
  }
}

function updateGroupLogo($mysqli, $groupID, $url){
  $query = 'UPDATE groups SET LogoURL = ? WHERE ID = ?';

  $statement = $mysqli->prepare($query);
  $statement->bind_param("si", $url, $groupID);
  $statement->execute();

  if($statement){
    return 1;
  }else{
    return 0;
  }
}

function getAccountFromDB($mysqli, $userID){
      $query = "SELECT UserID, Email, Password, FacebookID, Timezone, receiveUpdates, DateCreated FROM users WHERE UserID = ?";

      $statement = $mysqli->prepare($query);

      $statement->bind_param("i", $userID);
      $statement->execute();
      $result = $statement->get_result();

      $p = new userProfile();


      $row = $result->fetch_array(MYSQLI_NUM);
             $p->userID = $row[0];
             $p->email = $row[1];
             $p->password = $row[2]; 
             $p->facebookID = $row[3];
             $p->timezone = $row[4];
             $p->receiveUpdates = $row[5];
             $p->dateCreated = $row[6];
          
      $result->close();
      return $p;
}

function getUserProfileFromDB($mysqli, $userID){
    $userColumns = "users.UserID, users.Email, users.FacebookID, users.Timezone, users.receiveUpdates, users.DateCreated";
    $profileColumns = "profiles.FirstName, profiles.LastName, profiles.Gender, profiles.Birthday, profiles.Weight, profiles.Height, profiles.Bio, profiles.Prof_Pic_URL";

    $query = "SELECT ".$userColumns.", ".$profileColumns." FROM users LEFT JOIN profiles ON users.userID = profiles.userID WHERE users.UserID = ?";

    $statement = $mysqli->prepare($query);

      $statement->bind_param("i", $userID);
      $statement->execute();
      $result = $statement->get_result();

      $p = new userProfile();

      $row = $result->fetch_array(MYSQLI_NUM);
       			 $p->userID = $row[0];
       			 $p->email = $row[1];
       			 $p->facebookID = $row[2];
       			 $p->timezone = $row[3];
             $p->receiveUpdates = $row[4];
       			 $p->dateCreated = $row[5];
       			 $p->firstName = $row[6];
       			 $p->lastName = $row[7];
       			 $p->gender = $row[8];
       			 $p->birthday = $row[9];
       			 $p->weight = $row[10];
       			 $p->height = $row[11];
       			 $p->bio = $row[12];
             $p->profPicURL = $row[13];
             $p->groups = getUsersGroups($mysqli,$userID);
      		
    	$result->close();
    	return $p;
}

  function getUserProfileShort($mysqli, $userID){//dont include groups
    $userColumns = "users.UserID, users.Email, users.FacebookID, users.Timezone, users.receiveUpdates, users.DateCreated";
    $profileColumns = "profiles.FirstName, profiles.LastName, profiles.Gender, profiles.Birthday, profiles.Weight, profiles.Height, profiles.Bio, profiles.Prof_Pic_URL";

    $query = "SELECT ".$userColumns.", ".$profileColumns." FROM users LEFT JOIN profiles ON users.userID = profiles.userID WHERE users.UserID = ?";

    $statement = $mysqli->prepare($query);

      $statement->bind_param("i", $userID);
      $statement->execute();
      $result = $statement->get_result();

      $p = new userProfile();

      $row = $result->fetch_array(MYSQLI_NUM);
             $p->userID = $row[0];
             $p->email = $row[1];
             $p->facebookID = $row[2];
             $p->timezone = $row[3];
             $p->receiveUpdates = $row[4];
             $p->dateCreated = $row[5];
             $p->firstName = $row[6];
             $p->lastName = $row[7];
             $p->gender = $row[8];
             $p->birthday = $row[9];
             $p->weight = $row[10];
             $p->height = $row[11];
             $p->bio = $row[12];
             $p->profPicURL = $row[13];
          
      $result->close();
      return $p;
  }

  //HALF EDITED THIS FUNCTION THEN REALIZED IT ISNT USED, PROBABLY WONT WORK IF USED SINCE I MADE CHANGES AND DIDN'T TEST

  // function getUserRidesFromDB($mysqli,$query,$timezone){
  //     $rides = array();

  //     $ridesColumns = "rides.ID, rides.CreatorID, rides.RouteID, rides.CreatorType, rides.RideType, rides.Title, rides.Description, rides.Level, rides.Visibility, rides.StartLat, rides.StartLon";
  //     $ridesRepeatColumns = "rr.repeat_interval";


  //     $result = $mysqli->query("SELECT ".$ridesColumns.", rr.repeat_interval, mrr.GroupID, 
  //                               DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, 'UTC'),'%H:%i:%s') AS UTCStartTime, 
  //                               DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.endTime)), rides.tzName, 'UTC'),'%H:%i:%s') AS UTCEndTime ,
  //                               DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS startTimeAdjust,
  //                               DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.endTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS endTimeAdjust,
  //                               UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_start_adjust,
  //                               UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_end_adjust ,
  //                               DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%y-%m-%d') AS startDateAdjust,
  //                               FROM `rides` LEFT JOIN `rides_repeat` rr ON rr.`ride_id` = rides.`ID` LEFT JOIN `multi_rider_rides` mrr ON mrr.RideID = rides.ID ".$query.''); //the two UTC for startDay Adjust are to make it so that the UNIX time is in UTC Before CONVERT_TZ is executed, or else the from_tz param would be incorrect
    
  //     if (!$result) {
  //          return $mysqli->error;
  //       }

  //       $i = 0;

  //         while ($row = $result->fetch_row()) {
  //           $r = new ride();
  //             $r->rideID = $row[0];
  //             $r->creatorID = $row[1];
  //             $r->routeID = $row[2];
  //             $r->creatorType = $row[3];
  //             $r->rideType = $row[4];
  //             $r->title = $row[5];
  //             $r->description = $row[6];
  //             $r->level = $row[7];
  //             $r->visibility = $row[8];
  //             $r->startLat = $row[9];
  //             $r->startLon = $row[10];
  //             $r->repeatInterval = $row[11];

  //             $r->UTCStartTime = $row[13];
  //             $r->UTCEndTime = $row[14];

  //             $r->startTime = $row[15]; //all times are in GMT
  //             $r->endTime = $row[16];
              

  //             $r->startDate = $row[17];
  //             $r->endDate = $row[18];

  //             $r->creatorProfile = getUserProfileFromDB($mysqli,$row[1]);

  //             if($row[12] != null){
  //             $r->group = getGroupFromDB($mysqli,$row[12]);
  //             }

  //           $rides[$i] = $r;
  //           $i++;
  //         }
  //     return $rides;
  // }


  //this function is used for the profile page too and luckily already isn't succeptible to sql injection. Though need to update in future if there are more filter options for profile
  function getUserRidesWithDateRangeFromDB($mysqli,$query,$timezone, $startDate, $endDate, $startingAfterTime, $startingBeforeTime, $rideType, $rideLevel){
     $rides = array();
     $ridesColumns = "rides.ID, rides.CreatorID, rides.RouteID, rides.CreatorType, rides.RideType, rides.Title, rides.Description, rides.Level, rides.Visibility, rides.StartLat, rides.StartLon";

     $query = "SELECT ".$ridesColumns.", rr.repeat_interval, mrr.GroupID,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, 'UTC'),'%H:%i:%s') AS UTCStartTime, 
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.endTime)), rides.tzName, 'UTC'),'%H:%i:%s') AS UTCEndTime ,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS startTimeAdjust,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.endTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS endTimeAdjust,
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_start_adjust,
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_end_adjust ,
                                getStartDate(rr.repeat_start, ".$startDate.", rr.repeat_interval) AS realStartDate
                                FROM `rides` LEFT JOIN `rides_repeat` rr ON rr.`ride_id` = rides.`ID` LEFT JOIN `multi_rider_rides` mrr ON mrr.RideID = rides.ID ".$query.''; 

      $statement = $mysqli->prepare($query);

      $statement->bind_param("isss", $rideType, $rideLevel, $startingAfterTime, $startingBeforeTime);
      $statement->execute();
      $result = $statement->get_result();

      $i = 0;

      while($row = $result->fetch_array(MYSQLI_NUM)){
            $r = new ride();
              $r->rideID = $row[0];
              $r->creatorID = $row[1];
              $r->routeID = $row[2];
              $r->creatorType = $row[3];
              $r->rideType = $row[4];
              $r->title = $row[5];
              $r->description = $row[6];
              $r->level = $row[7];
              $r->visibility = $row[8];
              $r->startLat = $row[9];
              $r->startLon = $row[10];
              $r->repeatInterval = $row[11];

              $r->UTCStartTime = $row[13];
              $r->UTCEndTime = $row[14];

              $r->startTime = $row[15]; //all times are in GMT
              $r->endTime = $row[16];
              

              $r->startDate = $row[19];
              $r->endDate = $row[18];

              $r->creatorProfile = getUserProfileFromDB($mysqli,$row[1]);

              if($row[12] != null){
              $r->group = getGroupFromDB($mysqli,$row[12]);
              }

            $rides[$i] = $r;
            $i++;
          }
      return $rides;

          //return $query;
  }

 

  function getSingleGroupRidesFromDB($mysqli, $query, $timezone){//UP NEXT, this function and find all SQL interactive functions not in dbHelper and bring them here and update them
    $rides = array();
      $result = $mysqli->query("SELECT r.*, rr.*, p.firstName, p.lastName, mrr.GroupID,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(r.startTime)), r.tzName, '".$timezone."'),'%H:%i:%s') AS startTimeAdjust,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(r.endTime)), r.tzName, '".$timezone."'),'%H:%i:%s') AS endTimeAdjust,
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), r.startTime), '%Y-%m-%d %T'), r.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_start_adjust,
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(r.endTime,'')), '%Y-%m-%d %T'), r.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_end_adjust,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(r.startTime)), r.tzName, '".$timezone."'),'%y-%m-%d') AS startDateAdjust
                                FROM `multi_rider_rides` mrr LEFT JOIN `rides` r ON r.`ID` = mrr.RideID LEFT JOIN `rides_repeat` rr ON rr.`ride_id` = mrr.`RideID` LEFT JOIN `profiles` p ON r.creatorID = p.userID ".$query."");
       
      // echo $query;
// if (!$result) {
//            return $mysqli->error;
//         }
       $i = 0;
        while ($row = $result->fetch_row()) {
          $r = new ride();
            $r->rideID = $row[0];
            $r->creatorID = $row[1];
            $r->routeID = $row[2];
            $r->creatorType = $row[3];
            $r->rideType = $row[4];
            $r->startTime = $row[22]; //all times are in GMT
            $r->endTime = $row[23];
            $r->title = $row[8];
            $r->description = $row[9];
            $r->level = $row[10];
            $r->visibility = $row[11];
            $r->startLat = $row[12];
            $r->startLon = $row[13];
            $r->startDate = $row[24];
            $r->endDate = $row[25];
            $r->repeatInterval = $row[18];
            $r->creatorName = "".$row[19]." ".$row[20]."";
            $r->group = getGroupFromDB($mysqli,$row[21]);
          $rides[$i] = $r;
          $i++;
        }
      return $rides;
  }

  function getRideDetails($mysqli,$rideID,$timezone){
    $r = new singleRide();
      $result = $mysqli->query("SELECT rides.*,rr.repeat_start,rr.repeat_end,rr.repeat_interval,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS startTimeAdjust,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.endTime)), rides.tzName, '".$timezone."'),'%H:%i:%s') AS endTimeAdjust, mrr.groupID, 
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_start, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), rides.startTime), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_start_adjust,
                                UNIX_TIMESTAMP(CONVERT_TZ(DATE_FORMAT(CONVERT_TZ(STR_TO_DATE(CONCAT(DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(rr.repeat_end, '%y-%m-%d %T'), @@session.time_zone,'UTC'), '%y-%m-%d'), IFNULL(rides.endTime,'')), '%Y-%m-%d %T'), rides.tzName, '".$timezone."'), '%y-%m-%d'),'UTC',@@session.time_zone)) AS repeat_end_adjust,
                                DATE_FORMAT(CONVERT_TZ( ADDTIME(DATE(DATE_ADD('1970-01-01', INTERVAL rr.repeat_start MINUTE_SECOND)), TIME(rides.startTime)), rides.tzName, '".$timezone."'),'%y-%m-%d') AS startDateAdjust
                                FROM rides LEFT JOIN `rides_repeat` rr ON rr.`ride_id` = rides.ID LEFT JOIN `multi_rider_rides` mrr ON mrr.RideID = rides.ID WHERE rides.ID = ".$rideID."");
      while ($row = $result->fetch_row()) {
        $r->rideID = $row[0];
        $r->creatorID = $row[1];
        $r->routeID = $row[2];
        $r->creatorType = $row[3];
        $r->rideType = $row[4];
        $r->startTime = $row[17]; //all times are in GMT
        $r->endTime = $row[18];
        $r->title = $row[8];
        $r->description = $row[9];
        $r->level = $row[10];
        $r->visibility = $row[11];
        $r->startLat = $row[12];
        $r->startLon = $row[13];
        $r->startDate = $row[20];
        $r->endDate = $row[21];
        $r->repeatInterval = $row[16];
        $r->route = getRoute($mysqli,$row[2]);
        if($r->creatorType == 0){
          $r->creatorProfile = getUserProfileFromDB($mysqli,$r->creatorID);
        }else if($r->creatorType == 1){
          $r->group = getGroupFromDB($mysqli,$row[19]);
        }
      }
    return $r;
  }

  function getRouteForRide($mysqli, $rideID){
    $result = $mysqli->query('SELECT rides.RouteID FROM rides WHERE ID = '.$rideID.' LIMIT 1');
    $routeID = $result->fetch_row()[0];

    $result->free();

    return getRouteCoords($mysqli, $routeID);
  }

  function getUsersRoutes($mysqli,$userID,$publicQuery){
    $routes = array();
      $result = $mysqli->query('SELECT * FROM routes WHERE CreatorID = '.$userID.' '.$publicQuery.'');
      $i = 0;
        while ($row = $result->fetch_row()) {
            $route = new route();
            $route->routeID = $row[0];
            $route->creatorID = $row[1];
            $route->name = $row[2];
            $route->description = $row[3];
            $route->distance = $row[4];
            $route->climb = $row[5];
            $route->time = $row[6];
            $route->public = $row[7];
            $route->deleted = $row[8];
            $route->routeCoords = getRouteCoords($mysqli,$row[0]);
            //$route->waypointCoords
            $routes[$i] = $route;
            $i++;
        }
      return $routes;
  }

  function getRoute($mysqli,$routeID){
     $route = new route();
      $result = $mysqli->query('SELECT * FROM routes WHERE ID = '.$routeID.'');
        while ($row = $result->fetch_row()) {
            $route->routeID = $row[0];
            $route->creatorID = $row[1];
            $route->name = $row[2];
            $route->description = $row[3];
            $route->distance = $row[4];
            $route->climb = $row[5];
            $route->time = $row[6];
            $route->public = $row[7];
            $route->deleted = $row[8];
            $route->routeCoords = getRouteCoords($mysqli,$routeID);
            //call for elevation data separately, takes up a lot of time and data.
        }
      return $route;
  }

  function getRouteCoords($mysqli,$routeID){
    $result = $mysqli->query('SELECT route_coordinates.Latitude, route_coordinates.Longitude FROM `route_coordinates` WHERE routeID = '.$routeID.'');
    $routeCoords = array();
    $i=0;


    while($row = $result->fetch_row()){
      $routeCoords[$i] = $row;
      $i++;
    }

    return $routeCoords;
  }

  function insertRoute($mysqli, $userID, $name, $description, $distance, $climb, $time, $isPublic){
    $mysqli->query('INSERT INTO routes VALUES ("NULL",'.$userID.',"'.$name.'","'.$description.'","'.$distance.'","'.$climb.'","'.$time.'","'.$isPublic.'","false")');
    return $mysqli->insert_id;
  }

  function insertRouteCoords($mysqli,$routeID,$coordinates){
    $sql = array();

    foreach ($coordinates as $row) {
      $sql[] = '('.$routeID.','.$row->lat.', '.$row->lon.')';
    }

    $coordQuery = 'INSERT INTO route_coordinates (`RouteID`,`Latitude`,`Longitude`) VALUES '.implode(',', $sql);
    $result = $mysqli->query($coordQuery);

    return ($result) ? 1 : 0;
  }

  function insertRouteWaypoints($mysqli, $routeID, $waypoints){
    $sql = array();

    foreach ($waypoints as $row) {
       $sql[] = '('.$routeID.','.$row->lat.', '.$row->lon.')';
    }

    $waypointQuery = 'INSERT INTO waypoint_coordinates (`RouteID`,`Latitude`,`Longitude`) VALUES '.implode(',', $sql);
    $mysqli->query($waypointQuery);
  }

  function insertRouteElevationData($mysqli, $routeID, $elevationData){
    $sql = array();

    foreach ($elevationData as $row) {
       $sql[] = '('.$routeID.','.$row.')';
    }

    $elevationQuery = 'INSERT INTO route_elevation (`RouteID`,`Elevation`) VALUES '.implode(',', $sql);
    $mysqli->query($elevationQuery);
  }

  function getRouteElevationData($mysqli,$routeID){
    $elevationData = array();

    $result = $mysqli->query('SELECT * FROM route_elevation WHERE RouteID ='.$routeID.'');
    $i=0;
      while($row = $result->fetch_row()){
        $elevationData[$i] = $row[2];
        $i++;
      }
    return $elevationData;
  }

  function deleteRoute($mysqli,$routeID){
    $result = $mysqli->query('DELETE FROM routes WHERE ID = '.$routeID.'');

    if($result){
      $mysqli->query('DELETE FROM route_coordinates WHERE RouteID = '.$routeID.'');
      $mysqli->query('DELETE FROM route_elevation WHERE RouteID = '.$routeID.'');
      $mysqli->query('DELETE FROM waypoint_coordinates WHERE RouteID = '.$routeID.'');
      $updateResult = $mysqli->query('UPDATE rides SET RouteID = -1 WHERE RouteID = '.$routeID.'');
      $isSuccess = ($updateResult) ? 1 : 0;
      return $isSuccess;
    }else{
      return 0;
    }
  }

   function getUsersGroups($mysqli,$userID){//this function is for profile and plan rides
    $result = $mysqli->query("SELECT groups.*, group_members.isAdmin FROM `group_members` RIGHT JOIN groups ON group_members.group_ID = groups.ID WHERE Member_ID = ".$userID."");
    $groups = array();

      $i=0;
      while($row = $result->fetch_row()){
        $g = new group();
          $g->groupID = $row[0];
          $g->name = $row[1];
          $g->description = $row[2];
          $g->website = $row[3];
          $g->private = $row[4];
          $g->type = $row[5];
          $g->sport = $row[6];
          $g->logoURL = $row[7];
          $g->city = $row[8];
          $g->userIsAdmin = $row[9];
        $groups[$i] = $g;
        $i++;
      }
    return $groups;
  }

 
  function getGroupRideIDsForUser($mysqli,$userID){//this function is for profile and plan rides
    $result = $mysqli->query("SELECT Group_ID FROM group_members WHERE isAccepted = 1 AND Member_ID = ".$userID."");
    $groupIDs = array();

      $k=0;
      while($row = $result->fetch_row()){
        $groupIDs[$k] = " GroupID = ".$row[0];
        $k++;
      }



    $result->close();

    $groups = array();

    if(sizeof($groupIDs) > 0){
          $result = $mysqli->query("SELECT RideID FROM multi_rider_rides WHERE (".implode(" OR ", $groupIDs).")");
    }else{
          return $groups;
    }

      $i=0;
      while($row = $result->fetch_row()){
        $groups[$i] = "(RideID = ".$row[0].")";
        $i++;
      }
    return $groups;
  }

  function getGroupMembers($mysqli, $groupID, $isAccepted){//have to use 0 instead of false 
    $result = $mysqli->query('SELECT profiles.*,group_members.isAdmin FROM group_members LEFT JOIN profiles ON profiles.UserID = group_members.Member_ID WHERE group_members.isAccepted = '.$isAccepted.' AND Group_ID = '.$groupID.'');
    $members = array();

//     if(!$result) {
//     echo 'SELECT profiles.*,group_members.isAdmin FROM group_members LEFT JOIN profiles ON profiles.UserID = group_members.Member_ID WHERE group_members.isAccepted = '.$isAccepted.' AND Group_ID = '.$groupID.'';
// }
   
    $i = 0;
    while($row = $result->fetch_row()){
      $m = new groupMember();
        $m->profile = getUserProfileShort($mysqli,$row[0]);//save some data by not including groups
        $m->isAdmin = $row[9];
        $m->isMember = $isAccepted;
      $members[$i] = $m;
      $i++;
    }
    return $members;
  }

   function getGroupMember($mysqli,$groupID,$userID){
    $result = $mysqli->query("SELECT * FROM group_members WHERE Group_ID = ".$groupID." AND Member_ID = ".$userID."");
    $member = new groupMember();

    $member->profile = getUserProfileFromDB($mysqli,$userID);
    $member->isAdmin = false;
    $member->isMember = 0;//not member

    while($row = $result->fetch_row()){
      $member->groupID = $row[0];
      $member->isAdmin = ($row[2] == 1);
      if($row[3] == 1){
        $member->isMember = 1;//member
      }else{
        $member->isMember = 2;//membership pending
      }
    }

    return $member;
  }


  function addGroupMember($mysqli, $groupID, $userID, $isAdmin, $isAccepted){//NEED TO TEST THIS
    $mysqli->query('INSERT INTO `group_members` VALUES ('.$groupID.','.$userID.','.$isAdmin.','.$isAccepted.')');
  }

  function acceptGroupMember($mysqli, $groupID, $userID){
    $result = $mysqli->query('UPDATE group_members SET isAccepted = 1 WHERE Group_ID = '.$groupID.' AND Member_ID = '.$userID.'');
    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function removeGroupMember($mysqli,$groupID,$userID){
    $result = $mysqli->query('DELETE FROM `group_members` WHERE Group_ID = '.$groupID.' AND Member_ID = '.$userID.'');
    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function makeMemberAdmin($mysqli,$groupID,$userID){
    $result = $mysqli->query('UPDATE `group_members` SET isAdmin = 1 WHERE Group_ID = '.$groupID.' AND Member_ID = '.$userID.'');
    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function getGroupMessages($mysqli,$groupID){
    $result = $mysqli->query("SELECT group_message_board.*,gm.IsAdmin FROM group_message_board LEFT JOIN group_members gm ON group_message_board.UserID = gm.Member_ID AND gm.Group_ID = group_message_board.GroupID WHERE GroupID = ".$groupID." ORDER BY Timestamp DESC");
    $messages = array();
    $i = 0;
    while ($row = $result->fetch_row()){
        $m = new groupMessage();

          $m->messageID = $row[0];
          $m->message = $row[1];
          $m->user = getUserProfileShort($mysqli,$row[2]);
          $m->groupID = $row[3];//not sure if this is needed
          $m->timestamp = $row[4];
          $m->userIsAdmin = $row[5];
          $m->numComments =  sizeOf(getGroupMessageComments($mysqli,$row[0]));
          //$m->numLikes = $row[7];

        $messages[$i] = $m;

        $i++;
    }
    return $messages;
  }

  function getGroupMessageComments($mysqli,$messageID){
    $result = $mysqli->query("SELECT * FROM group_message_comment WHERE MessageID = ".$messageID." ORDER BY Timestamp ASC");
    $comments = array();
    $i = 0;
    while ($row = $result->fetch_row()){
        $c = new groupMsgComment();

          $c->commentID = $row[0];
          $c->messageID = $row[1];
          $c->user = getUserProfileShort($mysqli,$row[2]);
          $c->comment = $row[3];//not sure if this is needed
          $c->timestamp = $row[4];

        $comments[$i] = $c;

        $i++;
    }
    return $comments;
  }

  function getRideMessages($mysqli,$rideID,$intervals){
    $result = $mysqli->query('SELECT rmm.*, CASE WHEN rmm.UserID = r.CreatorID THEN "true" ELSE "false" END AS CreatedRide FROM ride_message_board rmm JOIN rides r ON rmm.rideID = r.ID WHERE rmm.RideID = '.$rideID.' AND rmm.Intervals = '.$intervals.' ORDER BY Timestamp DESC');
    $messages = array();
    $i = 0;
    while ($row = $result->fetch_row()){
        $m = new rideMessage();

          $m->messageID = $row[0];
          $m->message = $row[1];
          $m->user = getUserProfileShort($mysqli,$row[2]);
          $m->userID = $row[3];
          $m->timestamp = $row[5];
          $m->userCreatedRide = $row[6];

        $messages[$i] = $m;

        $i++;
    }
    return $messages;
  }

  function sendMessageGroup($mysqli, $message, $userID, $groupID, $timestamp){
    return $mysqli->query('INSERT INTO `group_message_board` (`ID`, `Message`, `UserID`, `GroupID`, `Timestamp`) VALUES (NULL, "'.$message.'", '.$userID.', '.$groupID.', '.$timestamp.')');
  }

  function postMessageCommentGroup($mysqli, $comment, $userID, $messageID, $timestamp){
    return $mysqli->query('INSERT INTO `group_message_comment` VALUES (NULL, '.$messageID.', '.$userID.', "'.$comment.'", '.$timestamp.')');
  }

  function getRideMembers($mysqli,$rideID,$intervals){
    $result = $mysqli->query('SELECT * FROM `joined_rides` WHERE RideID = '.$rideID.' AND Intervals = '.$intervals.'');
    $profiles = array();
    $i = 0;
    while($row = $result->fetch_row()){
        $profiles[$i] = getUserProfileShort($mysqli,$row[1]);
        $i++;
    }
    return $profiles;
  }

  function hasRiderJoinedRide($mysqli,$rideID,$intervals,$userID){
    $result = $mysqli->query('SELECT * FROM `joined_rides` WHERE RideID = '.$rideID.' AND Intervals = '.$intervals.' AND UserID = '.$userID.'');
    
    $hasJoined = ($result->num_rows > 0) ? 'true' : 'false';

    return $hasJoined;
  }

  function addUserToRide($mysqli,$rideID,$intervals,$userID){
    $result = $mysqli->query('INSERT INTO `joined_rides` VALUES ('.$rideID.','.$userID.','.$intervals.')');
    return $result;
  }

  function removeUserFromRide($mysqli,$rideID,$intervals,$userID){
    $result = $mysqli->query('DELETE FROM `joined_rides` WHERE RideID = '.$rideID.' AND UserID = '.$userID.' AND Intervals = '.$intervals.'');
    return $result;
  }

  function addMultiRiderRide($mysqli, $groupID, $rideID, $groupType, $creatorID){
    $mysqli->query('INSERT INTO `multi_rider_rides` VALUES (NULL,'.$groupID.','.$rideID.','.$groupType.','.$creatorID.')');
  }

  function updateProfile($mysqli,$profile){ //this is different then account
    $query = "UPDATE `profiles` SET FirstName = '".$profile->firstName."', LastName = '".$profile->lastName."', Gender = '".$profile->gender."', Birthday = '".$profile->birthday."', Weight = '".$profile->weight."', 
              Height = '".$profile->height."', Bio = '".$profile->bio."', Prof_Pic_URL = '".$profile->profPicURL."' WHERE UserID = ".$profile->userID;
    $mysqli->query($query);
  }

  function updateAccount($mysqli,$account){ 
    $query = "UPDATE `users` SET Email = '".$account->email."', Password = '".$account->password."', receiveUpdates = '".$account->receiveUpdates."' WHERE UserID = ".$account->userID;
    $mysqli->query($query);
  }

        function hasEmailCheck($userID){
          $mysqli = getDB();

          $account = getUserProfileFromDB($mysqli, $userID);

          if(empty($account->email)){
            header("Location: http://localhost/projects/biketime/settings/account");
          }
        }

  function indexLoginCheck(){
    if(isset($_COOKIE['UserID'])){
        $mysqli = getDB();
        $userID = getEncryptedUserIDCookie($mysqli,$mysqli->escape_string($_COOKIE['UserID']));
        $mysqli->close();
        if($userID != -1){
          if(session_status() == PHP_SESSION_NONE) {
            session_start();
          }
          $_SESSION['UserID'] = $userID;
        }

        return $userID;
    }else{
      if(isset($_SESSION['UserID'])){
        $mysqli = getDB();
        $encryptedID = insertEncryptedUserID($mysqli,$_SESSION['UserID']);
        $mysqli->close();
        setcookie("UserID",$encryptedID,0,'/',".fondobike.com");
        return $_SESSION['UserID'];
      }
      return -1;
    }
  }

  function loginCheck(){
    //COOKIE[UserID] just remembers the user, they are only logged in if they have the session variable, when session is expired
    if (session_status() == PHP_SESSION_NONE) {
    session_start();
    }

    if(!isset($_COOKIE['UserID'])){//may need to change this
      header("Location: ../logout.php");//log user out even if session has user ID in case they have been tampering with cookie data, this will destroy session
      return false;
    }else{
      //reset session variable since user has left and it has been deleted
        $mysqli = getDB();
        $userID = getEncryptedUserIDCookie($mysqli,$_COOKIE['UserID']);
        $mysqli->close();

        if(!isset($_SESSION['UserID'])){
          //here is where session variable will be reinitialized
          /*

          1.check DB for encrypted cookie, if found set session variable to corresponing userID

          2.if cookie has been changed or for some other reason there is no match unset the cookie and make the user login again.

          if session has expired 1. will take effect if cookie has been altered 2 will happen

          */

          if($userID == -1){
            header("Location: ../logout.php");
          }else{
            $_SESSION['UserID'] = $userID;
          }
        }else{
          if($userID == -1){
            header("Location: ../logout.php");
            //if session is active but cookie doesnt match that of database it means user has tampered with it. Log them out.
          }
        }



      hasEmailCheck($_SESSION['UserID']);

      return true;
    }
  }

  function accountExists($mysqli,$userID){
    $result = $mysqli->query("SELECT * FROM users WHERE UserID =".$userID."");

    $row_cnt = $result->num_rows;

    if($row_cnt > 0){
      return true;
    }else{
      return false;
    }
  }

  function addFriend($mysqli, $userID1, $userID2, $requesterID, $timestamp){
    //user with lower ID will be user1, this will make queries easier since it is always one way
    $user1 = ($userID1 < $userID2) ? $userID1 : $userID2;
    $user2 = ($userID1 < $userID2) ? $userID2 : $userID1;

    $result = $mysqli->query('SELECT * FROM `friends` WHERE userID1 = '.$userID1.' AND userID2 = '.$userID2.'');

    if($result){
        if($result->num_rows == 0){
          $mysqli->query('INSERT INTO `friends` VALUES (NULL,'.$userID1.','.$userID2.','.$requesterID.',0,'.$timestamp.')');
          return 1;//dont need to return result for this method just boolean because result isn't used when this method is called in ajax_add_friend.php
        }else{
          return 0;//this else block should never be triggered because button will be disabled if pending, but just incase
        }
    }else{
      return 2;//this means there was an error
    }
  }

  function getFriendshipStatus($mysqli, $userID1, $userID2){
    //user with lower ID will be user1, this will make queries easier since it is always one way
    $_user1 = ($userID1 < $userID2) ? $userID1 : $userID2;
    $_user2 = ($userID1 < $userID2) ? $userID2 : $userID1;

    $status = 2; //0 means not confirmed but requested, 1 means confirmed, 2 means no request

    $result = $mysqli->query('SELECT * FROM `friends` WHERE userID1 = '.$_user1.' AND userID2 = '.$_user2.'');

     while ($row = $result->fetch_row()){
        $status = $row[4];
    }

    return $status;
  }

  function removeFriend($mysqli, $userID1, $userID2){
    //user with lower ID will be user1, this will make queries easier since it is always one way
    $user1 = ($userID1 < $userID2) ? $userID1 : $userID2;
    $user2 = ($userID1 < $userID2) ? $userID2 : $userID1;

    $result = $mysqli->query('DELETE FROM `friends` WHERE userID1 = '.$userID1.' AND userID2 = '.$userID2.'');//can probably use fID instead

    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function getFriendRequests($mysqli, $userID1){
    $result = $mysqli->query('SELECT * FROM `friends` WHERE (userID1 = '.$userID1.' OR userID2 = '.$userID1.') AND confirmed = 0 AND requesterID != '.$userID1.'');

    $users = array();

    $i = 0;
     while ($row = $result->fetch_row()){
        $users[$i] = getUserProfileShort($mysqli,$row[3]);
        $users[$i]->tempVar = $row[0];//friendshipID
        $i++;
    }

    return $users;
  }

   function acceptFriendRequest($mysqli, $fID){
      $result = $mysqli->query('UPDATE `friends` SET confirmed = 1 WHERE fID = '.$fID.'');

      if($result){
        return 1;
      }else{
        return 0;
      }
  }

  function declineFriendRequest($mysqli, $fID){
    $result = $mysqli->query('DELETE FROM `friends` WHERE fID = '.$fID.'');

    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function getFriends($mysqli, $_userID){
    $result = $mysqli->query('SELECT userID1, userID2 FROM `friends` WHERE (userID1 = '.$_userID.' OR userID2 = '.$_userID.') AND confirmed = 1');

    $users = array();

    $i = 0;
     while ($row = $result->fetch_row()){
        $userID = ($_userID == $row[1]) ? $row[2] : $row[1]; //want to show the friend not the user requesting friends
        $users[$i] = getUserProfileShort($mysqli,$userID);
        $users[$i]->tempVar = $row[4];//tempVar is friendship status
        $i++;
    }

    return $users;
  }

  function getFriendIDs($mysqli, $userID){
    $result = $mysqli->query('SELECT CASE WHEN userID1 = '.$userID.' THEN userID2 ELSE userID1 END AS userID FROM `friends` WHERE (userID1 = '.$userID.' OR userID2 = '.$userID.') AND confirmed = 1');
    $idArray = array();
    $i = 0;
     while ($row = $result->fetch_row()){
        $idArray[$i] = "(rides.CreatorID = ".$row[0].") ";
        $i++;
    }
    $idArray[$i] = "(rides.CreatorID = ".$userID.") ";
    return $idArray;
  }

  function insertEncryptedUserID($mysqli, $userID){
    $encryptedID = password_hash($userID, PASSWORD_DEFAULT);

    $check = $mysqli->query('SELECT * FROM userid_cookie WHERE UserID = '.$userID.'');

    $insertQuery = 'INSERT INTO userid_cookie VALUES ('.$userID.',"'.$encryptedID.'")';
    $updateQuery = 'UPDATE userid_cookie SET encryptedID = "'.$encryptedID.'" WHERE UserID = '.$userID.'';

    $query = ($check->num_rows > 0) ? $updateQuery : $insertQuery;

    $result = $mysqli->query($query);
   

    if($result){
      return $encryptedID;
    }else{
      return -1;
    }
  }

  function getEncryptedUserIDCookie($mysqli, $encryptedCookie){
      $result = $mysqli->query('SELECT * FROM userid_cookie WHERE encryptedID = "'.$encryptedCookie.'"');
      $userID = -1;

      while($row = $result->fetch_row()){
        $userID = $row[0];
      }

      return $userID;
  }

  function updateRideFields($mysqli, $ride){
    $endTime = (strcasecmp($ride->endTime, "NULL") == 0) ? "NULL" : '"'.$ride->endTime.'"';
    $result = $mysqli->query('UPDATE rides SET Title = "'.$ride->title.'", RideType = "'.$ride->rideType.'", Level = "'.$ride->level.'", StartTime = "'.$ride->startTime.'", EndTime = '.$endTime.' WHERE ID = '.$ride->rideID.'');
    
    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  function updateRideDate($mysqli, $ride){
    $result = $mysqli->query('UPDATE rides_repeat SET repeat_start = '.$ride->startDate.' WHERE ride_id = '.$ride->rideID.''); 
    if($result){
      return 1;
    }else{
      return 0;
    }
  }

  //function cancelRide
    //rides,ride_repeat,ride_message_board,multi_rider_rides,joined_rides
  //}

  function skipRide($mysqli, $rideID, $skipDate, $newRideID){
    $insertQuery = 'INSERT INTO skipped_rides VALUES (NULL,'.$rideID.',"'.$skipDate.'",'.$newRideID.')';
    $results = $mysqli->query($insertQuery);

    return ($results) ? 1 : 0;
  }

  function createSingleRideFromRepeat($mysqli, $ride, $timezone, $skipDate){
    $insertQuery = 'INSERT INTO `rides` (ID,CreatorID,RouteID,CreatorType, RideType, StartTime, tzName, Title, Description,Level,Visibility,StartLat, StartLon)VALUES ("NULL",'.$ride->creatorID.','.$ride->routeID.','.$ride->creatorType.','.$ride->rideType.',"'.$ride->startTime.'"
          ,"'.$timezone.'","'.$ride->title.'","'.$ride->description.'","'.$ride->level.'",'.$ride->visibility.','.$ride->startLat.','.$ride->startLon.')';
    $result = $mysqli->query($insertQuery);

    if($result){
      $result = null;
      $rideID = $mysqli->insert_id;
      $result = $mysqli->query('INSERT INTO `rides_repeat` VALUES ("NULL",'.$rideID.',"'.$ride->startDate.'",0,NULL)');
      if($result){
        $result = null;
        $oldRideID = $ride->rideID;
        $result = $mysqli->query('UPDATE ride_message_board SET RideID = '.$rideID.', Intervals = 0 WHERE RideID = '.$oldRideID.'');
        if($result){
          $result = null;
          $result = $mysqli->query('UPDATE joined_rides SET RideID = '.$rideID.', Intervals = 0 WHERE RideID = '.$oldRideID.'');
          if($result){
            return skipRide($mysqli,$oldRideID,$skipDate,$rideID);
          }
        }
      }
    }

    return $mysqli->error;

  }

  function getRideSkipDate($mysqli, $rideID){
    $result = $mysqli->query('SELECT * FROM skipped_rides WHERE RideID = '.$rideID.'');
    $dates = array();

    $i = 0;
    while ($row = $result->fetch_row()){
        $dates[$i]['skipDate'] = $row[2];
        $dates[$i]['newRideID'] = $row[3];
        $i++;
    }

    return ($i==0) ? -2 : $dates; //-2 because -1 in the db means its not a repeating ride, too confusing
  }

?>