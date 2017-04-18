<?php

	include('../databasehelper/databasehelper.php');

	$mysqli = getDB();

	$email = mysql_escape_string($_POST['email']);
	$password = mysql_escape_string($_POST['password']);

	$result = $mysqli->query('SELECT users.UserID,users.Password FROM users WHERE Email = "'.$email.'"');
	$userID = -1;

	$pw_hashed = "";

	while($row = $result->fetch_row()){
		$userID = $row[0];
		$pw_hashed = $row[1];
	}

	if(password_verify($password,$pw_hashed)){
		$encryptedID = insertEncryptedUserID($mysqli,$userID);
		$mysqli->close();

		if($encryptedID != -1){//this way if user changes after logged in next time back at site it wont match so no session variable and LoginCheck() function will be false
			session_start();
			$_SESSION["UserID"] = $userID;
			setcookie("UserID",$encryptedID,0,'/',".fondobike.com");
			header ('Location: ../profile');
		}else{
			header ('Location: ../login/?invalid=true');//if $encryptID == -1 or something else there is a databse error...
		}
	}else{
		$mysqli->close();
		header ('Location: ../login/?invalid=true');
	}

?>