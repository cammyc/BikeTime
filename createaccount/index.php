<?php
session_start();

if(isset($_SESSION['UserID'])){//may need to change this
  header("Location: ../profile/index.php?id=".$_SESSION['UserID']."");
  //send to profile where if the cookie has been modified the loginCheck will handle it
}
require_once( '../Facebook/autoload.php');

$fb = new Facebook\Facebook([
  'app_id' => '301357286684630',
  'app_secret' => 'd5a8ec8544c1db365735c12074c04d35',
  'default_graph_version' => 'v2.5',
]);
?>

<html>
<head>
	<title>Create Account</title>

	<link href="../css/stylecreateaccount.css" rel="stylesheet" type="text/css"/>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/jstz-1.0.4.min.js"></script>
	<script src="../js/toolbox.js"></script> <!-- NEED INTERNET TO TEST THIS OUT -->


</head>

<body>

	<div id="main">

			<div id="title">
				<h1>Create a Free Account</h1>
			</div>

			<div style="margin-left:25px;">
				<?php
					     echo "<div id = 'loginbutton'>";
						      $helper = $fb->getRedirectLoginHelper();
							  $permissions = ['email', 'user_likes', 'user_birthday']; 
							  $loginUrl = $helper->getLoginUrl('http://www.fondobike.com/redirect.php', $permissions);
													      //PUBLISH ACTIONS REQUIRES APP TO BE REVIEWED

						      echo '<a id = "fbButton" href="'. $loginUrl . '"> Sign up with Facebook</a>';
						      echo '<a id = "backToLogin" href="../login"> Go back to Login</a>';
						    
						 echo "</div>";
		  		?>

			  	<div id="accountInfo">
			  		<p id="orEmail">Or sign up with email</p>
					<form action="createaccount_form_action.php" method="POST" id="createaccountForm">
				  		<input type="text" id="firstName" name = "firstName" placeHolder="First Name" class="inputText" />
				  		<input type="text" id="lastName" name = "lastName" placeHolder="Last Name" class="inputText" />
				  		<br>
				  		<input type="text" id="firstEmail" name="firstEmail" placeHolder="Your Email" class="inputText" />
				  		<input type="text" id="confirmEmail" name="confirmEmail" placeHolder="Confirm Email" class="inputText" />
				  			<p class="error" id="emailWarning" style="display:none;">Emails don't match</p>

				  		<br>
				  			<p id="emailTakenWarning">*Email available</p> <!-- HIDE THIS At first -->

				  		<br>
				  		<br>
				  		<input type="password" id="createPassword" name="createPassword" placeHolder="Create a Password" class="inputText" />
				  		<br>
				  		<input type="password" id="confirmPassword" name="confirmPassword" placeHolder="Confirm Password" class="inputText" />
				  			<p class="error" id="passwordWarning" style="display:none;">Passwords don't match</p>

				  		<br><br>
				  		<input type="checkbox" checked="true" class="sendUpdates" id="sendUpdates" name="sendUpdates" value="true" /> <label class="sendUpdates">Send me BikeTime updates and tips</label>
				  		<br><br>
				  		<input type="hidden" name="timezoneOffset" id="timezoneOffset">

				  		<input type="submit" id="signUp" value="Sign Up" />
				  		<br>
				  		<br>
				  		<label class="sendUpdates">By signing up for biketime you agree to our terms and conditions.</label>
			  		</form>
				</div>
			</div>
	</div>

	<script type="text/javascript">

	var emailTaken = true;

		 function isNull(id){
	          if($(id).val() == null || $(id).val().trim() == ""){
	            $(id).css("border", "1px solid red");
	            return true;
	          }else{
	            $(id).css("border", "1px solid #666");
	            return false;
	          }
	        }

	     function matchFields(id1,id2,warning){
	     	if($(id1).val() == $(id2).val() && !isNull(id1) && !isNull(id2)){ //ERROR IS FROM HERE
	     		return true;//dont hide() on true or warning might not be visible
	     	}else{
	     		$(warning).show();
	     		if(warning == "#emailWarning"){//method is used for email and password so we need to change only one text
	     			$(warning).text("Emails don't match");
	     		}else if(warning == "#passwordWarning"){
	     			$(warning).text("Passwords don't match");
	     		}
	     		return false;
	     	}
	     }

	     function checkLength(id,warning){
	     	if($(id).val().length < 5){
	     		$(warning).text("Password must be at least 5 characters").show();
	     		return false;
	     	}else{
	     		return true;//dont hide() on true or warning might not be visible
	     	}
	     }

	     function validateEmail(id) 
			{
			    var re = /\S+@\S+\.\S+/;
			    var validated = re.test($(id).val());

			    if(!validated){
			    	$("#emailWarning").show();//need to show because match fields will hide it if they match
					$("#emailWarning").text("Must be a valid email");
			    }//dont hide() on true or warning might not be visible

			    return validated
			}

	     function displayWarnings(){
	     	isNull("#firstName")
			isNull("#lastName")
			isNull("#firstEmail")
			isNull("#confirmEmail")
			isNull("#createPassword")
			isNull("#confirmPassword")
			var match = matchFields("#firstEmail","#confirmEmail","#emailWarning");
			var match1 = matchFields("#createPassword","#confirmPassword","#passwordWarning");
			var valid = validateEmail("#firstEmail");
			var pwLength = checkLength("#createPassword","#passwordWarning")

			if(match && valid){
				$("#emailWarning").hide();
			}

			if(match1 && pwLength){
				$("#passwordWarning").hide();
			}
	     }


		 $("#firstEmail").keyup(function() {
		 	var re = /\S+@\S+\.\S+/;
			var validated = re.test($("#firstEmail").val());
		 	if(validated){
		 		var checkEmailRequest = checkEmailAjax($("#firstEmail").val());

		 		checkEmailRequest.done(function(result) {
		 			console.log(result)
				    if(result === "true"){//If taken
				    	emailTaken = true;
				    }else{//MAKE THIS WORK THEN FIX TIMEZONE
				    	emailTaken = false;
				    }

				    emailTakenBorder("#firstEmail",emailTaken);

				}).fail(function() {
				    alert("Unable to check if email is taken. Please check your internet connection.");
				});

		 	}else{
		 		$('#emailTakenWarning').css("visibility", "hidden");//If not a valid email dont show warning
		 		$('#firstEmail').css("border", "1px solid #666");

		 	}
		 })

		 $("#createaccountForm").submit(function(){

		 	displayWarnings();//show all warnings

		 	if(isNull("#firstName")){ //check that every field is validated
				return false;
			}else if(isNull("#lastName")){
				return false;
			}else if(isNull("#firstEmail")){
				return false;
			}else if(isNull("#confirmEmail")){
				return false;
			}else if(isNull("#createPassword")){
				return false;
			}else if(isNull("#confirmPassword")){
				return false;
			}else if(!validateEmail("#firstEmail")){
				return false;
			}else if(!matchFields("#firstEmail","#confirmEmail","#emailWarning")){
				return false;
			}else if(!matchFields("#createPassword","#confirmPassword","#passwordWarning")){
				return false;
			}else if(!checkLength("#createPassword","#passwordWarning")){
				return false;
			}

			if(emailTaken){
				return false;
			}

			$("#timezoneOffset").val(jstz.determine().name());

			return true; // Prevent page refresh
		 });

		 $(window).load(function() {
			var frm = $('#createaccountForm')[0];
		    frm.reset();  
		});

	</script>
	
</body>

</html>