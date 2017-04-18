<html>
 
<?php
	include_once("../databasehelper/databasehelper.php");
    loginCheck();

	if(empty($_GET['id'])){
		header("Location: groupnotfound.php");
	}

	$groupID = $_GET['id'];

	$mysqli = getDB();

    $memberProfile = getGroupMember($mysqli,$groupID,$_SESSION['UserID']);    

	$group = getGroupFromDB($mysqli,$groupID);

	$isMember = ($memberProfile->isMember == 2 || $memberProfile->isMember == 0) ? 0 : 1;


    if($isMember == 0 && $group->private == 1){
    	header('Location: ../group/?id='.$groupID.'');
    }

	$groupMembers = getGroupMembers($mysqli,$groupID,true);

	$mysqli->close();
?>

<title><?php  echo $group->name." - Members"; ?> </title>


<head>
	<link href="../css/stylegroupmembers.css" rel="stylesheet" type="text/css" >	
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/materialize.min.js"></script>
	<script type="text/javascript" src="../js/groupmemberhelper.js"></script>
	<script src="../bin/oms.min.js"></script>

	<?php
		include '../menu.php';
	?>

</head>


<body>

		<div id="main">

			<div class="row card" id="tabs">
			    <div class="col s12 center-align">
			      <ul>
			        <li class="tab col s3"><a class="blue-text" href="../group/?id=<?php echo $groupID; ?>">Upcoming Rides</a></li><!-- black text is active -->
			        <li class="tab col s3"><a class="black-text" href="#">Members</a></li>
			        <li class="tab col s3"><a class="blue-text" href="#">Events</a></li>
			        <li class="tab col s3"><a class="blue-text" href="#">About</a></li>
			      </ul>
			    </div>
		    </div>

			<div id="basicInfo" >
				<div id="infoText" class="card">
					<?php
						echo "<p><b>".$group->name."</b></p>";
						echo "<p><b>Sport:</b> ".ucfirst($group->sport)."</p>";
						echo "<p><b>Members:</b> <span id='numMembers'>".sizeof($groupMembers)."</span></p>";
					?>
				</div>


			</div>

			<div id="groupStream" class="card">

				<?php
					if($memberProfile->isAdmin && $group->private){
						echo "<h5 style='margin:10px;'>Pending Membership Requests</h5>";
						echo "<ul id='pendingMemberContainer'></ul>";
					}
				?>
				
				<h5 style='margin:10px;'>Members</h5>
					<ul id="memContainer">
					
					</ul>
				
			</div>
			
		</div>

		<script type="text/javascript">
		var acceptedGroupMembers;
		var pendingGroupMembers;
		//is it bad all this can be seen?

			$(document).ready(function() {
				<?php echo "getMembers(".$groupID.",0,$('#pendingMemberContainer'),true)"; ?>;

	            $(".button-collapse").sideNav();
	            $(".dropdown-button").dropdown({hover:true});

	            getMembers(<?php echo $groupID; ?>,1,$('#memContainer'),<?php echo ($memberProfile->isAdmin == true) ? 'true' : 'false'; ?>);

	            $('#memContainer').on('click',"a.removeMember", function(){
			        var userID = acceptedGroupMembers[$(this).parent().index()]['profile']['userID'];
			        removeMember(<?php echo $groupID; ?>,userID,$('#memContainer'));
			    });

			    $('#memContainer').on('click',"a.makeAdmin", function(){
			    	if (confirm('Are you sure you want to make this user an admin? This can\'t be undone.')) {
			    		var userID = acceptedGroupMembers[$(this).parent().index()]['profile']['userID'];
			        	makeMemberAdmin(<?php echo $groupID; ?>,userID,0);
			    	}
			       
			    });

			    $('#pendingMemberContainer').on('click',"a.acceptMember", function(){
			        var userID = pendingGroupMembers[$(this).parent().index()]['profile']['userID'];
			        respondToRequest(<?php echo $groupID; ?>,userID,1);
			    });

			    $('#pendingMemberContainer').on('click',"a.declineMember", function(){
			        var userID = pendingGroupMembers[$(this).parent().index()]['profile']['userID'];
			        respondToRequest(<?php echo $groupID; ?>,userID,0);
			    });

	        });
		</script>
		
	</body>