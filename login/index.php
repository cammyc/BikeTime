<?php
session_start();

if(isset($_SESSION['UserID'])){
  header("Location: ../profile/index.php?id=".$_SESSION['UserID']."");
}

/*

So if user still has cookie and comes to this page with session expired I think they just re-login, if they go to a different page they auto-login

*/

$falseInfo = false;

if(isset($_GET['invalid'])){
  $falseInfo = true;
}

require_once('../Facebook/autoload.php');


$fb = new Facebook\Facebook([
  'app_id' => '301357286684630',
  'app_secret' => 'd5a8ec8544c1db365735c12074c04d35',
  'default_graph_version' => 'v2.5',
]);

?>

<html>
<title>Login</title>
<head>
<link href="../css/stylelogin.css" rel="stylesheet" type="text/css"/>
<link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>

<script type="text/javascript" src="../js/materialize.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>


<?php 

include 'loginMenu/index.php';
?>

</head>
<body>
<!--
  Below we include the Login Button social plugin. This button uses the JavaScript SDK to
  present a graphical Login button that triggers the FB.login() function when clicked. -->
<div id = "loginBox">
<div id = "loginTitle"> 

<h1>Login</h1>
</div>


<?php
echo "<div id = 'loginbutton'>";
  $helper = $fb->getRedirectLoginHelper();
  $permissions = ['email', 'user_likes', 'user_birthday']; 
  $loginUrl = $helper->getLoginUrl('http://www.fondobike.com/redirect.php', $permissions);

      //PUBLISH ACTIONS REQUIRES APP TO BE REVIEWED

      echo '<a id="fbButton" class="waves-effect waves-light btn" href="'. $loginUrl . '"> Login with Facebook</a><a class="waves-effect waves-light btn white blue-text" href="../createaccount" >Create an Account</a>';
    
echo "</div>";
  ?>

  <div id="inputFields">

  <p id="orWith"> Or with your email</p>

  <form action="login_form_action.php" method="POST" id="loginForm">
    <input type="text" id="email" name="email" placeHolder="Your Email" class="inputText" />
    <br>
    <input type="password" name="password" placeHolder="Password" class="inputText" />
    <br><br>
    <button class="waves-effect waves-light btn" id="bLogin">Login</button> 
  </form>

  <?php
    if($falseInfo){
      echo "<p id='invalid'>Email and password don't match.</p>";
    }
  ?>

  <a id="forgetPW" href="#">Forget your password? </a>

  </div>
</div>

<script type="text/javascript">

  $(document).ready(function(){

    $("#loginForm").submit(function(){

        return true;
    });

  });
  

</script>


</body>
</html>