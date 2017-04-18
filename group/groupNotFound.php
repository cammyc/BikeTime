<html>
	<title>Club Not Found</title>
	<head>

	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script type="text/javascript" src="../js/materialize.min.js"></script>

	<?php
		include '../menu.php';
	?>

	<style type="text/css">

		html {
			height: 100%; 
			background: #ECEFF1;
		}

		#main{
			position: relative;
			width: 1200px;
			height: 600px;
			margin: auto;
			top: 50%;
			margin-top: -300px;
		}

	</style>
	</head>

	<body>

		<div id="main">

			<h3 class="valign"> Sorry, Club Not Found.</h3>

			<a class="waves-effect waves-light btn" href="../explore" id="createclub"><i class="mdi-action-search left"></i>Find Club</a>
			<a class="waves-effect waves-light btn" href="../createclub" id="createclub"><i class="mdi-social-group-add left"></i>Create Club</a>
			
		</div>
		
	</body>

</html>