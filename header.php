<!DOCTYPE HTML>
<html>
<header>
    <link rel="stylesheet" type="text/css" href="main.css">
</header>
<div id="headerwrapper">
    <div id="logo"><img src="img/roadslogo.png" height="50px" width="95px"></div>
    <div id="titlecontainer">
        <h1><a href="index.php">Regional Roads</a></h1>
    </div>
    <?php
    require '../support/environmentsettings.php';
    if (!isset($_SESSION)) {
        session_start();
    }
    if (empty($_SESSION['status'])) {
        $_SESSION['status'] = "unset";
    }
    if ($_SESSION['status'] == "loggedin") {
        #echo logged in header
        #echo '<div class="signincontrols"><a href="">'.$_SESSION['user'].'</a> <a href="signout.php" class="headerlink btn btn-primary btn-block btn-large">Log Out</a></div>';
        echo '<div class="signincontrols"><a href="signout.php" class="headerlink btn btn-primary btn-block btn-large">Sign Out</a></div>';
    } elseif ($_SESSION['status'] == "unset" || $_SESSION['status'] == "pending" || $_SESSION['status'] == "reset") {
        #echo logged out header
        echo '<div class="signincontrols"><a href="signup.php" class="headerlink btn btn-primary btn-block btn-large">Apply for Access</a> <a href="signin.php" class="headerlink btn btn-primary btn-block btn-large">Sign In</a></div>';
    }
    ?>
</div>