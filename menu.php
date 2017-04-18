<?php
if(isset($_SESSION['UserID'])){
  $mysqli = getDB();
  $menuProfile = getUserProfileFromDB($mysqli,$_SESSION['UserID']);//need to name variable menuProfile or it overrides the profile variable on the profile page
  $mysqli->close();
}
?>
<link rel="stylesheet" href="http://www.fondobike.com/css/menustyles.css">
<div class="navbar-fixed">

<ul id="dropdown1" class="dropdown-content">
  <li><a href='http://www.fondobike.com/settings'>Settings</a></li>
  <li><a href='http://www.fondobike.com/logout.php'>Log Out</a></li>
</ul>

<ul id="dropdown2" style="width:auto !important;" class="dropdown-content">
  <?php
    $groups = $menuProfile->groups;

    foreach ($groups as $g) {
      echo "<li><a href='http://www.fondobike.com/group/?id=".$g->groupID."'>".$g->name."</a></li>";
    }
  ?>
  <li><a href='http://www.fondobike.com/explore' class="black-text">Find Group</a></li>
</ul>

   <nav>
     <div class="nav-wrapper">
         <a href="#!" class="brand-logo center">Logo</a>
         <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
         <?php

            if(isset($_SESSION['UserID'])){//below goes first because float right needs to be first for chrome
               $image = isset($menuProfile->profPicURL) ? "<div id='profImageContainer'><img id='profImage' src = 'http://www.fondobike.com/profilepics/".$menuProfile->profPicURL."'/></div>" : '';
               $fullName = $menuProfile->firstName.' '.$menuProfile->lastName;
             
              echo "<ul class='right hide-on-med-and-down'>
                        <li><a href='http://www.fondobike.com/profile/index.php?id=".$_SESSION['UserID']."'><span>".$image." ".$fullName."</span></a></li>
                        <li><a class='dropdown-button' data-activates='dropdown1' href='#'><i class='mdi-action-settings'></i></a></li>
                     </ul>";

               echo "<ul class='left hide-on-med-and-down'>
                     <li><a href='http://www.fondobike.com'><span>Home</span></a></li>
                     <li><a href='http://www.fondobike.com/planride'><span>Plan a Ride</span></a></li>
                     <li><a href='http://www.fondobike.com/createroute'><span>Map Out Route</span></a></li>
                     <li><a class='dropdown-button' data-activates='dropdown2' href='#'><span>My Groups</span></a></li>
                     <li class='last'><a href='http://www.fondobike.com/explore'><span>Find Friends</span></a></li>
                     </ul>";//<li><a href='http://www.fondobike.com/explore'><span>Explore</span></a></li>
            }else{
               echo "<ul class='right hide-on-med-and-down'>
                        <li><a href='/login'><span>Log In</span></a></li>
                     </ul>";

               echo "<ul class='left hide-on-med-and-down'>
                     <li><a href='http://www.fondobike.com'><span>Home</span></a></li>
                     </ul>";
            }
         ?>

       <ul class="side-nav" id="mobile-demo">
       <?php

            if(isset($_SESSION['UserID'])){//below goes first because float right needs to be first for chrome
              echo "<li><a href='http://www.fondobike.com/profile/index.php?id=".$_SESSION['UserID']."'><span>".$image." ".$fullName."</span></a></li>
                     <li><a href='/projects/biketime'><span>Home</span></a></li>
                     <li><a href='http://www.fondobike.com/planride'><span>Plan a Ride</span></a></li>
                     <li><a href='http://www.fondobike.com/createroute'><span>Create a Route</span></a></li>
                     <li class='last'><a href='http://www.fondobike.com/explore'><span>Explore</span></a></li>
                     <li><a href='http://www.fondobike.com/settings/profile'>Settings</a></li>
                     <li><a href='http://www.fondobike.com/logout.php'>Log Out</a></li>";
            }else{
               echo "<li><a href='http://www.fondobike.com'><span>Home</span></a></li>
                     <li><a href='http://www.fondobike.com/login'><span>Log In</span></a></li>";
            }
         ?>
      </ul>
     </div>
   </nav>
</div>