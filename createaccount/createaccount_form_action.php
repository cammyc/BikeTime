<?php
include "../databasehelper/databasehelper.php";

$mysqli = getDB();

$firstName =  $mysqli->real_escape_string($_POST['firstName']);
$lastName =  $mysqli->real_escape_string($_POST['lastName']);
$email =  $mysqli->real_escape_string($_POST['firstEmail']);
$password =  $mysqli->real_escape_string($_POST['createPassword']);
$receiveUpdates =  $mysqli->real_escape_string(isset($_POST["sendUpdates"])) ? $_POST['sendUpdates'] : "false";
$timezoneOffset = $mysqli->real_escape_string($_POST['timezoneOffset']);

$password = password_hash($password, PASSWORD_DEFAULT);

$userQuery = 'INSERT INTO users VALUES (NULL,"'.$email.'","'.$password.'",NULL,"'.$timezoneOffset.'",'.$receiveUpdates.',NULL)';

//echo $userQuery;

$mysqli->query($userQuery);

$userID = $mysqli->insert_id;

$profileQuery = 'INSERT INTO profiles VALUES ("'.$userID.'","'.$firstName.'","'.$lastName.'",NULL,NULL,NULL,NULL,NULL,"user.png")';

$mysqli->query($profileQuery);
$encryptedID = insertEncryptedUserID($mysqli,$userID);
$mysqli->close();

session_destroy();
session_start();
$_SESSION["UserID"] = $userID;
setcookie("UserID",$encryptedID,0,'/',".fondobike.com");

header ('Location: ../profile');
?>