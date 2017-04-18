<html>

<?php

include_once('../databasehelper/databasehelper.php');

loginCheck();

$userID = $_SESSION['UserID'];

?>

<head>
	<title>Edit Profile</title>

	<link href="../css/stylesettings.css" rel="stylesheet" type="text/css"/>
	<link href="../css/stylesettingsaccount.css" rel="stylesheet" type="text/css"/>
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="../js/toolbox.js"></script>

    <script src="../datetimepicker/jquery.js"></script>
    <script src="../datetimepicker/jquery.datetimepicker.js"></script>
     <script type="text/javascript" src="../js/materialize.min.js"></script>

   
		<!-- <ul id="dropdown1" class="dropdown-content">
		  <li><a href="#!">one</a></li>
		  <li><a href="#!">two</a></li>
		  <li class="divider"></li>
		  <li><a href="#!">three</a></li>
		</ul>
		<nav>
		  <div class="nav-wrapper light-blue">
		    <div class="col s12">
		      <a href="#" class="brand-logo">Logo</a>
		      <ul class="right side-nav">
		        <li><a href="sass.html">Sass</a></li>
		        <li><a href="components.html">Components</a></li>
		        <li><a class="dropdown-button" href="#!" data-activates="dropdown1">Dropdown<i class="mdi-navigation-arrow-drop-down right"></i></a></li>
		      </ul>

		    </div>
		        <a class="button-collapse" href="#" data-activates="nav-mobile"><i class="mdi-navigation-menu"></i></a>
		  </div>
		</nav> -->
        	<!-- $(".dropdown-button").dropdown();
        	$('.button-collapse').sideNav({menuWidth: 240, activationWidth: 70}); -->

        <?php
		include '../menu.php';
		?>
  
</head>

<body>

	<div id="main">

	<div id="settingList">
		<div class="collection">
        <a href="../settings" class="collection-item">Profile</a>
        <a href="../settings/account.php" class="collection-item active">Account</a><!-- 
        <a href="#!" class="collection-item">Email Notifications</a>
        <a href="#!" class="collection-item">Display Preferences</a>
        <a href="#!" class="collection-item">Privacy</a> -->
      </div>
	</div>

	<?php
		$mysqli = getDB();
    	$profile = getUserProfileFromDB($mysqli,$userID);
    	$mysqli->close();
    ?>

	<div class="selection">

		<div class="row">
		  <form class="col s12" action="updateAccount_form_action.php" method="POST" id="updateAccountForm" autocomplete="off">
		    <div class="row">
		      <div class="input-field col s6">
		        <input type="email" name="email" <?php if (!empty($profile->email)) {echo 'value="'.$profile->email.'"';} ?> id="email"/>
		        <label for="email">Email</label>
		      </div>

		      <div class="input-field col s6">
		      	<p id="emailWarning"> *Email Taken </p>
		      </div>

		    </div>

			<div class="row" id="changePWDiv">
				<div class="input-field col s4">
					<a id="bChangePassword" class="waves-effect waves-light blue-text grey lighten-5 btn"><i class="mdi-action-lock right"></i>Change Password</a>
				</div>
			</div>

		    <div class="row" id="passwordFields">
			    <div class="input-field col s4">
			        <input type="password" name="Password" id="newPassword" disabled="true" /> <!-- name is only password for form_action file -->
			        <label for="newPassword">New Password</label>
			    </div>

				<div class="input-field col s4">
			        <input type="password" name="confirmPassword" id="confirmPassword" disabled="true"/>
			        <label for="confirmPassword">Confirm Password</label>
			    </div>

			    <div class="input-field col s4">
			    	<p id="pwWarning">Passwords Don't Match</p>
			    </div>
		    </div>

		    <br>

		    <div class="row">
			    <div class="col s4">
			        <input type="checkbox"  name="receiveUpdates" id="receiveUpdates" value="1" <?php if ($profile->receiveUpdates)/* true if receiveUpdates = 1 */ {echo 'checked="checked"';} ?>/>
			        <label for="receiveUpdates">Receive Email Updates</label>
			    </div>
				
		    </div>
	
		</div>

		<?php if (empty($profile->password)) {echo '<p id="noPassword">You created an account with Facebook, you are not required to, but if you would like to set a password so that you do not need to login with Facebook you can.</p>';} ?>


		 <button class="btn waves-effect waves-light" type="submit" id="submit" name="action">Update Account
		    <i class="mdi-content-send right"></i>
		 </button>

		</form>
		
	</div>
	

		<script type="text/javascript">

		 $(document).ready(function(){
		 	$(".button-collapse").sideNav();
			$(".dropdown-button").dropdown({hover:true});

		 	var emailIsValid = true;

		 	$('#submit').on('click', function(){
				$("#updateAccountForm").submit();
			});

			 $("#email").keyup(function() {
			 	emailIsValid = emailValid("#email","#emailWarning");
			 });

			 $("#updateAccountForm").submit(function(){

		        	var fieldsValid = true

		        	if(!textFieldIsNull("#newPassword") || !textFieldIsNull("#confirmPassword")){
			        	if(!verifyPasswords("#newPassword","#confirmPassword","#pwWarning")){//if one of text fields is filled in and they dont match, then invalid. If neither are filled in ignore
			        		fieldsValid = false
			        	}
		        	}

		        	if(!emailIsValid || emailIsNull("#email","#emailWarning")){
		        		fieldsValid = false;
		        	}
		      
		        	return fieldsValid;

		        });
		 });

		 $("#passwordFields").hide();

		 $("#bChangePassword").click(function(){		 	
		 	$("#changePWDiv").hide();
		 	$("#passwordFields").slideDown();
		 	$("#newPassword").prop('disabled', false);
		 	$("#confirmPassword").prop('disabled', false);
		 });

		 	if(textFieldIsNull("#email")){
		 		$("#emailWarning").css("visibility", "visible");
		 		$("#emailWarning").css("color", "red");
		 		$("#emailWarning").text("*Your account will not be valid until you enter an email.");
		 	}
		 	
			
		</script>
			
		
	</div>
	
</body>

</html>