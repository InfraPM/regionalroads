<?php
session_start();
require 'header.php';
if (isset($_SESSION['status'])){
    if($_SESSION['status']=="loggedin"){
        if (isset($_GET['p'])){
            if ($_GET['p']=='trnreview'){
                include 'trnsurvey.php';                
            }
            else if ($_GET['p']=='datalist'){
                include 'getdatalist.php';                
            }
            else{
                include 'main.php';                
            }
        }
        else{
            include 'main.php';            
        }
    }
    elseif($_SESSION['status']=="pending"){
        echo '<div class="clearfloat">Application sent to your administrator for review.</div>';
        session_destroy();
    }
    elseif($_SESSION['status']=="reset"){
        echo '<div class="clearfloat">Password changed.  Please log in with your new password.</div>';
        session_destroy();
    }
    else{
        $redirectLink = ltrim($_SERVER['REQUEST_URI'],"/");
        $_SESSION['redirectLink']=$redirectLink;        
        if (!empty($redirectLink) && $redirectLink!='index.php'){
            header("Location: signin.php");
            }
        else{
            echo '<div class="fadein clearfloat" id="welcome"><h1>Welcome to RegionalRoads.com</h1><div id="joke"><h1>Changing the world.  One road at a time.</h1></div></div>';
        }
    }
}
else{
    $redirectLink = ltrim($_SERVER['REQUEST_URI'],"/");
    $_SESSION['redirectLink']=$redirectLink;
    header("Location: signin.php");
}
require 'footer.php';
?>
