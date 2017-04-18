
<?php
session_start();

include('databasehelper/databasehelper.php');
require_once('Facebook/autoload.php');

$fb = new Facebook\Facebook([
  'app_id' => '301357286684630',
  'app_secret' => 'd5a8ec8544c1db365735c12074c04d35',
  'default_graph_version' => 'v2.5',
]);

$mysqli = getDB();

$userID = -1;
$session = null;

echo $mysqli->error;
if(!isset($_COOKIE['UserID'])){
   $helper = $fb->getRedirectLoginHelper();
    try {
      $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      // When Graph returns an error
      echo 'Graph returned an error: ' . $e->getMessage();
      exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      // When validation fails or other local issues
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
    }

    if (isset($accessToken)) {
      try {
        // Returns a `Facebook\FacebookResponse` object
        $response = $fb->get('/me?fields=id,name,email,timezone,first_name,last_name,gender,birthday', $accessToken);
      } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
      } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
      }

      $user = $response->getGraphUser();

      $email = $user['email'];
      $facebookID = $user['id'];
      $timezone = $user['timezone'];

      $firstName = $user['first_name'];
      $lastName = $user['last_name'];
      $gender = $user['gender'];
      $birthday = date('Y-m-d', strtotime($user['birthday']));

      $result = $mysqli->query("SELECT * FROM users WHERE FacebookID =".$user['id'].""); //check if user has account


      if($result->num_rows == 0){//create user account and profile if they havet logged in before
        $mysqli->query("INSERT INTO users (`UserID`,`Email`,`Password`,`FacebookID`,`Timezone`,`receiveUpdates`,`DateCreated`)
          VALUES (NULL,'".$email."',NULL,'".$facebookID."','".tz_offset_to_name($timezone)."',true,NULL)");

        $userID = $mysqli->insert_id;

        $profPicURL = 'https://graph.facebook.com/'.$facebookID.'/picture?height=200&type=large&width=200&redirect=true';

        $fileName = 'user'.$userID.'.jpg';

        file_put_contents('profilepics/'.$fileName.'', file_get_contents($profPicURL));

        $mysqli->query("INSERT INTO profiles (`UserID`,`FirstName`,`LastName`,`Gender`,`Birthday`,`Prof_Pic_URL`)
        VALUES (".$userID.",'".$firstName."','".$lastName."','".$gender."','".$birthday."','".$fileName."')");

        //setLoginCookie so that if they come back to profile they are logged in

        $encryptedID = insertEncryptedUserID($mysqli,$userID); //encrypt userID
        $_SESSION["UserID"] = $userID; //set session variable to userID, no need for encryption unless server is hacked :p
        setcookie("UserID",$encryptedID,0,'/',".fondobike.com"); //set encrypted cookie so that hackers cant change userID
        $result -> close();
      }else{
        while($row = $result->fetch_row()){
          $userID = $row[0];
          break;
        }
        $result -> close();

        $encryptedID = insertEncryptedUserID($mysqli,$userID); //encrypt userID
        $_SESSION["UserID"] = $userID; //set session variable to userID, no need for encryption unless server is hacked :p
        setcookie("UserID",$encryptedID,0,'/', ".fondobike.com"); //set encrypted cookie so that hackers cant change userID
        echo $encryptedID;
      }
    }
  }else{
      $userID = getEncryptedUserIDCookie($mysqli,$mysqli->real_escape_string($_COOKIE['UserID']));
      $_SESSION["UserID"] = $userID;
      //if cookie is set and somehow they got to login page this will get the userID, if it doesnt exist it returns -1;
  }

  if($userID == -1){
      $mysqli->close();
      setcookie("UserID", null, 0, "/", ".fondobike.com");
      setcookie("UserID", null, 0,"/");
      setcookie("UserID", null, 0);//doing both, only thing that works...
      session_destroy();
      $helper = $fb->getRedirectLoginHelper();
      $permissions = ['email', 'user_likes', 'user_birthday']; 
      $loginUrl = $helper->getLoginUrl('http://www.fondobike.com/redirect.php', $permissions);
      header("Location: ".$loginUrl."");

  }else{
    $profile = getUserProfileFromDB($mysqli,$userID);
    $mysqli->close();
    if(empty($profile->email)){
      header("Location: settings/account");
    }else{
      header("Location: profile/index.php?id=".$userID."");
    }

  }

  /* Takes a GMT offset (in hours) and returns a timezone name */
function tz_offset_to_name($offset)
{
        $offset *= 3600; // convert hour offset to seconds
        $abbrarray = timezone_abbreviations_list();
        foreach ($abbrarray as $abbr)
        {
                foreach ($abbr as $city)
                {
                        if ($city['offset'] == $offset)
                        {
                                return $city['timezone_id'];
                        }
                }
        }

        return FALSE;
}



    ?>