<?php
try{
include('databasehelper/databasehelper.php');

$mysqli = getDB();

if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

//$group = getGroupFromDB($mysqli,1);

//var_dump($group);


$mysqli->close();

echo "strings";
}catch(Exception $err){
	echo $err->getMessage();
}


?>
