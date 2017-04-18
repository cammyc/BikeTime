<!doctype html>
<html lang=''>
<head>
   <meta charset='utf-8'>
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="stylesheet" href="../css/menustyles.css">
   <title>CSS MenuMaker</title>
</head>
<body>
<!--didnt import jquery because it is included in pages where this file is included-->

<div id='cssmenu'>
<ul>

   
    <?php
      if(isset($_SESSION['UserID'])){
         echo "<li ><a href='/biketime'><span>Home</span></a></li>
               <li><a href='planride'><span>Plan a Ride</span></a></li>
               <li><a href='createroute'><span>Create a Route</span></a></li>
               <li class='last'><a href='explore'><span>Explore</span></a></li>";
         echo "<li class='right last'><a href='profile/index.php?id=".$_SESSION['UserID']."'><span>Profile</span></a>
                  <ul>
                     <li class='last'><a href='updateaccount.php'>Settings</a></li>
                     <li class='last'><a href='logout.php'><span>Log Out</span></a></li>
                  </ul>
               </li>";
      }else{
         echo "<li class='last'><a href='../biketime'><span>Home</span></a></li>";
         echo "<li class='right last'><a href='login'><span>Log In</span></a> </li>";
      }
   ?>


   

</ul>
</div>

</body>
<html>
