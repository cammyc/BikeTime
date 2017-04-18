<html>

<?php

include_once('../databasehelper/databasehelper.php');

loginCheck();

$userID = $_SESSION['UserID'];

?>

<head>
	<title>Edit Profile</title>

	<link href="../css/stylesettings.css" rel="stylesheet" type="text/css"/>
	<link href="../css/stylesettingsprofile.css" rel="stylesheet" type="text/css"/>
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
	<link rel="stylesheet" type="text/css" href="../datetimepicker/jquery.datetimepicker.css"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="../js/toolbox.js"></script>
	<script src="../js/imageparser.js"></script>


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
        <a href="../profile" class="collection-item  active">Profile</a>
        <a href="account.php" class="collection-item">Account</a><!-- 
        <a href="#!" class="collection-item">Email Notifications</a>
        <a href="#!" class="collection-item">Display Preferences</a>
        <a href="#!" class="collection-item">Privacy</a> -->
      </div>
	</div>

	<?php

		$mysqli = getDB();//have to have this after include menu because menu opens DB also
    	$profile = getUserProfileFromDB($mysqli,$userID);
    	$mysqli->close();
    ?>

     <?php

	    function convert_to_inches($cm) {
	        $inches = round($cm * 0.393701);
	        $result = [
	            'ft' => intval($inches / 12),
	            'in' => $inches % 12,
	        ];
	        return $result;
	    }

	    $heightFeet = null;
	    $heightInches = null;

	    if(!empty($profile->height)){
	      $heightArray = convert_to_inches($profile->height);
	      $heightFeet = $heightArray['ft'];
	      $heightInches = $heightArray['in'];
	    }

	 ?>

	<div class="selection">
		<div>
			<div id="profPicContainer"> 
				<img id="updateProfPic" <?php if (!empty($profile->profPicURL)) {echo 'src = "../profilepics/'.$profile->profPicURL.'"';}else{echo 'src="../images/noProfPic.png"';} ?>/>
				<div class="file-field input-field" id="updatePicPrompt">
			      <div class="btn">
			        <span><i class="mdi-file-file-upload"></i></span>
			        <input type="file" id="profPicFile"/>
			      </div>
			    </div>
			</div> 
			<center><label>Update Display Picture</label></center>
		</div>

		<br>

		<div class="row">
		  <form class="col s12" action="updateProfile_form_action.php" method="POST" id="updateAccountForm">
		    <div class="row">
		      <div class="input-field col s4">
		        <input type="text" class="validate" name="firstName" <?php if (!empty($profile->firstName)) {echo 'value="'.$profile->firstName.'"';} ?> id="firstName"/>
		        <label for="first_name">First Name</label>
		      </div>
		      <div class="input-field col s4">
		        <input type="text" class="validate" name="lastName" <?php if (!empty($profile->lastName)) {echo 'value="'.$profile->lastName.'"';} ?> id="lastName"/>
		        <label for="last_name">Last Name</label>
		      </div>
		    </div>
		    <div class="row">
		      <div class="col s2">
		        <label>Gender</label>
				  <select name="gender" id="gender">
				    <option value="male" <?php if (strcasecmp($profile->gender,"male") == 0){echo "selected";}?>>Male</option>
				    <option value="female" <?php if (strcasecmp($profile->gender,"female") == 0){echo "selected";}?>>Female</option>
				  </select>
		      </div>
		    </div>

		     <div class="row">
		       <div class="input-field col s1">
		        <input class="validate" type="number" min="0" name="heightFeet" id="heightFeet" <?php if(!empty($profile->height)){echo 'value="'.$heightFeet.'"';}?>/>
		        <label for="Height">Height (ft)</label>
		      </div>
		       <div class="input-field col s1">
		        <input class="validate" type="number" min="0" name="heightInches" id="heightInches" <?php if(!empty($profile->height)){echo 'value="'.$heightInches.'"';}?>/>
		        <label for="Inches">In</label>
		      </div>
		    </div>

		     <div class="row">
		       <div class="input-field col s2">
		        <input class="validate" type="number" min="0" type="number" name="weight" id="weight" <?php if(!empty($profile->weight)){echo 'value="'.$profile->weight.'"';}?>/>
		        <label for="Weight">Weight (Lbs)</label>
		      </div>
		    </div>

		     <div class="row">
		      <div class="input-field col s2">
		        <input class="validate" type="text" name="birthday" id="birthday" <?php if(!empty($profile->birthday)){echo 'value="'.date("Y/m/d", strtotime($profile->birthday)).'"';}?>/>
		        <label for="Birthday">Birthday</label>
		      </div>
		     
		    </div>
		    <div class="row">
		       <div class="input-field col s6">
		        <textarea class="materialize-textarea" id="bio" name="bio" ><?php if(!empty($profile->bio)){echo $profile->bio;} ?></textarea>
		        <label>Bio (Not Required)</label>
		      </div>
		    </div>
		</div>

			<input type="hidden" name="height"/>

		 <button class="btn waves-effect waves-light" type="submit" id="submit" name="action">Update Profile
		    <i class="mdi-content-send right"></i>
		 </button>

		</form>
	
	</div>

	<script>
	    $(document).ready(function(){

	     	$('select').material_select();
	      	$(".button-collapse").sideNav();
			$(".dropdown-button").dropdown({hover:true});

			$("#updateAccountForm").submit(function(){

	        	var fields = [textFieldIsNull("#firstName"), textFieldIsNull("#lastName"), textFieldIsNull("#heightFeet"), textFieldIsNull("#heightInches"), textFieldIsNull("#weight"), textFieldIsNull("#birthday")];

	        	var noFieldsNull = true;

	        	for(var i = 0; i<fields.length; i++){
	        		if(fields[i]){
	        			noFieldsNull = false;
	        			break;
	        		}
	        	}

	        	var height = feet_to_cm($("#heightFeet").val(),$("#heightInches").val());

	        	$('input[name="height"]').val(height)

	        	//FIX THIS AREA

	        	if(!noFieldsNull){
	        		Materialize.toast('Complete all required fields', 4000);
	        	}

	        	return noFieldsNull;
        	});

				jQuery('#birthday').datetimepicker({
		          lang:'en',
		           timepicker:false,
		           format:'Y/m/d',
		           scrollInput: false
		        });

			var fileInput = document.getElementById('profPicFile');
	        $('#profPicFile').on("change", function(){ handleFileSelection(fileInput.files[0],1,<?php echo $userID; ?>); });

	        $('#updateProfPic').css('top','50%');
	        var height = $('#updateProfPic').height()/2;
	        $('#updateProfPic').css('margin-top','-' + height + 'px');

	    });
  </script>
	
</body>

</html>