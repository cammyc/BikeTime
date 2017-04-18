<!DOCTYPE html>
<html>
  <head>
  	<title>Route Not Found</title>
  	<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  	 <link type="text/css" rel="stylesheet" href="../css/styleprivateroute.css"  media="screen,projection"/>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <?php
    	include '../databasehelper/databasehelper.php';
    	include '../menu.php';
    ?>
  </head>
  <body>
  	<div id="main">
  		<h5>You do not have permission to view the selected route.</h5>
  		<a href="../createroute" class="waves-effect waves-light btn"><i class="mdi-maps-map right"></i>Create Route</a>
  	</div>
  </body>
 </html>
