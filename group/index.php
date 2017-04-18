<?php

    include_once("../databasehelper/databasehelper.php");
    loginCheck();

    $memberProfile = null;
   
	if(empty($_GET['id'])){
		header("Location: groupnotfound.php");
	}

	$groupID = $_GET['id'];

	$mysqli = getDB();

	    $memberProfile = getGroupMember($mysqli,$groupID,$_SESSION['UserID']);    

		$group = getGroupFromDB($mysqli,$groupID);

	$mysqli->close();

?>
<!DOCTYPE html>
<html>
	<title><?php echo $group->name; ?></title>
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="../css/stylegroup.css" rel="stylesheet" type="text/css" >	
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/materialize.min.js"></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmLcM0z1weNroKQhtoe4VFurx3zljP_3s"></script>
	<script src="../bin/oms.min.js"></script>
	<script src="../datetimepicker/jquery.datetimepicker.js"></script>
    <script type="text/javascript" src="../js/displayrides.js"></script>

	<?php
		include '../menu.php';
	?>

	</head>

<?php

	if($group->private && ($memberProfile->isMember == 2 || $memberProfile->isMember == 0)){
		include('pending.php'); //name should be pending.php
	}else{
		include('accepted.php'); //name should be accepted.php
	}
?>

	<script type="text/javascript">
          $(".button-collapse").sideNav();
          $(".dropdown-button").dropdown({hover:true});
	</script>

</html>