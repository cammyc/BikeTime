 
// ALWAYS PLACE THIS FILE AFTER JQUERY EXTENSION
		function textFieldIsNull(id){
		  if($(id).val() == null || $(id).val().trim() == ""){
		    return true;
		  }else{
		    return false;
		  }
		}

		// function changeBorderOld(id, color){
		// 	$(id).css("border-bottom", "1px solid " + color);
		// 	$(id).css("box-shadow", "0 1px 0 0 " + color)
		// }


		function validateForm(id) {
		  var isValid = true;
		  $(id).each(function() {
		    if ( $(this).val() === '' )
		        isValid = false;
		  });
		  return isValid;
		}

		function feet_to_cm(feet, inches){
			return (feet*30.48) + (inches*2.54);
		}

function emailValid(emailID,emailWarningID){
	var valid = true;
	var re = /\S+@\S+\.\S+/;
		var validated = re.test($(emailID).val());
	 	if(validated){
	 		var checkEmailRequest = checkEmailAjax($(emailID).val());

	 		checkEmailRequest.done(function(result) {
			    if(result === "true"){//If taken
			    	valid = false;
			    	emailWarningText(emailWarningID,true)//show email is taken
			    }else{
			    	valid = true;
			    	emailWarningText(emailWarningID,false)

			    }

			}).fail(function() {
			    alert("Unable to check if email is taken. Please check your internet connection.");
			});

	 	}else{
	 		valid = false;
	 		setWarning(emailWarningID,"Invalid Email");
	 		$(emailWarningID).css("color", "red");
	 	}
	return valid;
}		


function checkEmailAjax(email) {
	var queryString = "?email=" + email;

    return $.ajax({
        url: "http://www.fondobike.com/ajax/ajax-checkemail.php" + queryString,
        success: function(response) {
            result = response;
        }
    });
}

		function emailIsNull(emailID,emailWarningID){
			if(textFieldIsNull(emailID)){
				setWarning(emailWarningID,"Invalid Email");
	 			$(emailWarningID).css("color", "red");
	 			return true;
	 		}else{
	 			return false;//don't hide because warning is always showing;
	 		}
		}

		function emailWarningText(id,isTaken){
			$(id).css("visibility", "visible");
			if(isTaken){
				$(id).text("*Email is taken");
				$(id).css("color", "red");
			}else{
				$(id).text("*Email Available");
				$(id).css("color", "#2ecc71");
			}
		}

function verifyPasswords(pw1,pw2,warningID){//MAIN FUNCTION
	var pwMatch = passwordsMatch(pw1,pw2,warningID);
	var pwIsRequiredLength = passwordLength(pw1,warningID);

	var checkArray = [pwMatch, pwIsRequiredLength];

	var pwConfirm = true;

	for(var i=0; i<checkArray.length; i++){
		if (!checkArray[i]){
			pwConfirm = false;
			break;
		}
	}

	if(pwConfirm){
		hideWarning(warningID);
	}

	return pwConfirm;
}

		function passwordsMatch(pw1, pw2, warningID){

			if($(pw1).val() == $(pw2).val() && !textFieldIsNull(pw1) && !textFieldIsNull(pw2)){
				//don't hide here, call in main method where both length and pwMatch are called;
				return true;
			}else{
				setWarning(warningID,"Passwords Dont Match");
				return false;
			}
		}

		function passwordLength(pw, warningID){
			if($(pw).val().length < 5){
					setWarning(warningID,"Password must be at least 5 characters");
				return false;
			}else{
				//don't hide here, call in main method where both length and pwMatch are called;
				return true
			}
		}

		function setWarning(warningID,text){
			$(warningID).text(text);
			$(warningID).css("visibility", "visible");
		}

		function hideWarning(id){
			$(id).css("visibility", "hidden");
		}


		function emailTakenBorder(id,isTaken){
			  $('#emailTakenWarning').css("visibility", "visible");
			  if(isTaken){
			    $(id).css("border", "1px solid red");
			    $('#emailTakenWarning').css("color", "red");
			    $('#emailTakenWarning').text("*Email has already been taken");
			  }else{
			    $(id).css("border", "1px solid #2ecc71");
			    $('#emailTakenWarning').css("color", "#2ecc71");
			    $('#emailTakenWarning').text("*Email available");
			  }
			}

function convertTimeToUTC(time){
	var date = new Date("1/1/1970 " + time).getTime()/1000;
	console.log(time);
}