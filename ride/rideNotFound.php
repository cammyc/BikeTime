<!DOCTYPE html>
<html>
  <head>
  	<title>Ride Not Found</title>
  	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  	 <link type="text/css" rel="stylesheet" href="../css/styleridenotfound.css"  media="screen,projection"/>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <?php
    	include '../databasehelper/databasehelper.php';
    	include '../menu.php';
    ?>
  </head>
  <body>
  	<div id="main">
  		<h5>The selected ride does not exist.</h5>
  		<a href="../planride" class="waves-effect waves-light btn"><i class="mdi-maps-map right"></i>Plan a Ride</a>
  	</div>
  </body>
 </html>
