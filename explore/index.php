<?php
 include_once("../databasehelper/databasehelper.php");
    loginCheck();
?>
<html>
<title>Explore</title>
<head>
	<link href="../css/styleexplore.css" rel="stylesheet" type="text/css"/>
	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/materialize.min.js"></script>

	<?php
	include '../menu.php';
	?>
</head>
<body>
	<div id="main">
		<div id="search">
			<h3>Athlete or Group Search</h3>

				<div id="searchBox" class="shadow">
					<form id="search" class="col s12">
						<div class="row">
						 	<div class="input-field col s1.5">
								<input type="radio" name="groupOrAthlete" checked="true" id="athlete"/>
									<label for="athlete">Athlete</label>
							</div>

							<div class="input-field col s2">
								<input type="radio" name="groupOrAthlete" id="group"/>
								 	<label for="group">Group</label>
							</div>
						</div>

						<div class="row">
					      <div class="input-field col s6">
					        <input type="text" name="firstName" <?php if (!empty($profile->firstName)) {echo 'value="'.$profile->firstName.'"';} ?> id="firstName"/>
					        <label for="first_name">Athlete or Group Name</label>
					      </div>

					      <div class="col s4">
					      	<button class="btn waves-effect waves-light" id="submit" type="submit" name="action" style="top: 25px;">Search
						    	<i class="mdi-action-search right"></i>
						  	</button>
						  </div>
					    </div>
					</form>
				</div>

				<div id="inviteBox" class="shadow">
					<div id="invite">
						<center><a class="waves-effect waves-light btn pink" id="inviteFriends"><i class="mdi-social-person-add left"></i>Invite Friends</a></center>
						<br>
						<center><a class="waves-effect waves-light btn pink" href="../creategroup" id="creategroup"><i class="mdi-social-group-add left"></i>Create Group</a></center>			
					</div>
				</div>

		</div>

		<div id="results">
				<table class="responsive-table centered hoverable">
		        <thead>
		          <tr>
		              <th data-field="id">Name</th>
		              <th data-field="name">Privacy</th>
		              <th data-field="price">Sport</th>
		          </tr>
		        </thead>
		        <tbody>
	        
				<?php

					$mysqli = getDB();

					$groups = getMultipleGroups($mysqli);

						for ($i=0; $i < sizeof($groups); $i++) { 
							$group = $groups[$i];
							$privacy;
							if($group->private){
								$privacy = "Private";
							}else{
								$privacy = "Public";
							}

							$link = "../group/?id=".$group->groupID."";

							echo '<tr>';
							echo '<td><a href="'.$link.'">'.$group->name.'</a></td>';
							echo "<td>".$privacy."</td>";
							echo "<td>".ucfirst($groups[$i]->sport)."</td>";
							echo "</tr>";
						}

					$mysqli -> close();

				?>
				</tbody>
		      </table>
			</div>
		</div>
	</div>
</body>
<script type="text/javascript">
		$(document).ready(function(){

			$("tr").css("cursor","pointer");
			$(".button-collapse").sideNav();
			$(".dropdown-button").dropdown({hover:true});
		  });

</script>
</html>