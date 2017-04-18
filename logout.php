<?php

session_start();

/*
function isLoggedIn()
{
    $id=0;       
    if(isset($_SESSION['token']))
    {
        $session = new FacebookSession($_SESSION['token']);

        $request = new FacebookRequest($session, 'GET', '/me');
        $response = $request->execute();

        $graphObject = $response->getGraphObject();

        $id = $graphObject->getProperty("id");                        
    } 

    return $id!=0;
}
*/

setcookie("UserID", null, 0, "/", ".fondobike.com");
setcookie("UserID", null, 0,"/");
setcookie("UserID", null, 0);//doing both, only thing that works...
session_destroy();

header("Location: login")

?>