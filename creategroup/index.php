<html>
<head>
	<title>Create A Club</title>
	<link href="../css/stylecreateclub.css" rel="stylesheet" type="text/css" >	
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="http://jquery-ui.googlecode.com/svn/tags/latest/ui/jquery.effects.core.js"></script>
<script src="http://jquery-ui.googlecode.com/svn/tags/latest/ui/jquery.effects.slide.js"></script>
	    <script type="text/javascript" src="../js/materialize.min.js"></script>


</head>
<body>
	<?php
		include_once("../databasehelper/databasehelper.php");
    	loginCheck();
		include '../menu.php';
	?>
	<div id="main">

	<h3 id="header" style="color:#333"> Create a Group</h3>

		<div class="row card">
			<form name = "clubInfoForm" action="createGroup_form_action.php" id="form" method="POST" class="col s12">
				<div class="row">
					<div class="input-field col s4">
						<input type="text" name="name" id="name"/>
						<label for="name">Club Name</label>
					</div>
					<div class="input-field col s4 offset-s1">
						<input type="text" name="website" id="website" />
						<label for="website">Website (Optional)</label>
					</div>
				</div>
				<div class="row">
					<div class="col s4">
						 <label>Type</label>
							<select name="type" id="type">
								<option value="club" selected="selected">Club</option>
								<option value="race" >Race Team</option>
								<option value="friends" >Group of Friends</option>
								<option value="other" >Other</option>
							</select>
					</div>
					<div class="input-field col s4 offset-s1">
				        <textarea class="materialize-textarea" id="descTextArea" maxlength="1000"></textarea>
				        <label>Description (Optional)</label>
				     </div>
				</div>
				<div class="row">
						<div class="col s4">
							<label>Sport</label><br>
						    <input name="sport" type="radio" id="sport_cycling" checked="true" value="cycling" />
						    <label for="sport_cycling">Cycling</label>

						    <input name="sport" type="radio" id="sport_running" value="running" />
						    <label for="sport_running">Running</label>
						</div>
				</div>
				
			   
				<div class="row">
					<div class="col s4">
						<input type="checkbox" name="private" id="private" value="true" />
						<label for="private">Private (Invite Only)</label>
					</div>
				</div>

				<input type="hidden" name="description" id="description" />
				
				<br>
				<button class="btn waves-effect waves-light pink" type="submit" name="action" style="position: absolute; right: 10px; bottom: 10px;">Create Group
					<i class="mdi-content-send right"></i>
				</button>
			</form>
		</div>
		<div id="requiredFields">
			<div class="row">
		        <div class="col s12 m6">
		          <div class="card blue-grey darken-1">
		            <div class="card-content white-text">
		              <span class="card-title">Missing Required Field</span>
		              <p>Club name is required.</p>
		            </div>
		            <div class="card-action">
		              <a href="#" id="dismiss">Ok</a>
		            </div>
		          </div>
		        </div>
		      </div>
      	</div>

	<script type="text/javascript">

		$(document).ready(function() {
			$(".button-collapse").sideNav();
			$(".dropdown-button").dropdown({hover:true});
	    	$('select').material_select();

	    	function isNull(id){
	          if($(id).val() == null || $(id).val().trim() == ""){
	            $('#requiredFields').show('slide', {direction: 'down'}, 500);
	            return true;
	          }else{
	          	$('#requiredFields').hide();
	            return false;
	          }
		    }

		    $('#dismiss').click(function(){
		    	$('#requiredFields').hide('slide', {direction: 'down'}, 500);
		    });

	    	$('#requiredFields').hide();

				$("#form").submit(function(){
				 	if(isNull("#name")){
						return false;
					}
						
					var text = $('#descTextArea').val();
		    		$('input[name="description"]').val(text);

					return true; // Prevent page refresh
				});
		  	});

		 $(window).load(function() {
			var frm = document.getElementsByName('clubInfoForm')[0];
		    frm.reset();  
		});

	</script>
		
	</div>
</body>
</html>
