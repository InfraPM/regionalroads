<?php
require "../support/pass.php";
require "../support/dbcon.php";
require "../support/user.php";

if (!empty($_GET['token']) && !empty($_GET['action'])){
    $user = new User();
    $dbcon = new dbcon($host, $port,$db, $dbuser, $dbpassword);
    $user->setDbCon($dbcon);
    $user->signuptoken=$_GET['token'];
    $user->getUserFromSignupToken();
    if (!empty($user->userName) || !empty($user->password)){
        if ($_GET['action']=='approve'){
            $user->approve();
            echo 'User '.$user->userName." has been approved.";
            #send approved user email with temp password token
            #redirect
        }
        elseif ($_GET['action']=='deny'){
            $user->deny();
            echo 'User '.$user->userName." has been denied.";
            #send denied user email with the bad news
            #redirect
        }
    }
    else{
        if ($_GET['action']=="approve"){
            echo "User aproved.";
        }
        elseif ($_GET['action']=="deny"){
            echo "User denied.";
        }
        else{
            echo "Link expired.";
        }
    }
}
?>
