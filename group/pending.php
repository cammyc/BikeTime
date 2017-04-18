<script>

	var groupID = <?php echo $groupID; ?>;
	var userID = <?php echo $_SESSION['UserID']; ?>;

	$(document).ready(function(){

		$(".button-collapse").sideNav();
		$(".dropdown-button").dropdown({hover:true});

		$('#bJoinGroup').click(function(){
				var f = document.createElement("form");
					f.setAttribute('method',"post");
					f.setAttribute('action',"join_group_action_form.php");

				var gID = document.createElement("input"); //input element, text
					gID.setAttribute('type',"hidden");
					gID.setAttribute('name',"groupID");
					gID.setAttribute('value',groupID);

				var uID = document.createElement("input"); //input element, text
					uID.setAttribute('type',"hidden");
					uID.setAttribute('name',"userID");
					uID.setAttribute('value',userID);

				var isAc = document.createElement("input"); //input element, text
					isAc.setAttribute('type',"hidden");
					isAc.setAttribute('name',"isAccepted");
					isAc.setAttribute('value',false);

				f.appendChild(gID);
				f.appendChild(uID);
				f.appendChild(isAc);

				f.submit();

			});
	});

</script>

<body>

	<div id="privateGroupContainer">

	<div class="card" id="privateGroupInfo">

	<h3> <?php  echo $group->name; ?> </h3>

	<p><b>Sport:</b> <?php  echo ucfirst($group->sport); ?> </p>

	<?php
		if(!empty($group->description)){
			echo "<p><b>Description:</b> ".$group->description."</p>";
		}
	?>

	<?php
		if(!empty($group->website)){
			echo "<p><b>Website:</b> <a class='blue-text' href='".$group->website."'>".$group->website."</a></p>";
		}
	?>
		
	</div>

	<div class="card" id="privateGroupPrompt">

		<center><h4>This Group Is Private.</h3></center>

		<?php
			if($memberProfile->isMember == 0 || $memberProfile->isMember == 2){
					if($memberProfile->isMember == 0){
						echo '<center><a class="waves-effect waves-light btn white-text" id="bJoinGroup"><i class="mdi-file-cloud left"></i>Join Group</a></center>';
					}else{
						echo "<center><p class='blue-text'>Membership Request Pending</p></center>";
					}
			}
		?>
	</div>

	</div>

</body>