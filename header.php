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
    require_once '../support/User.php';


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

        #determine if we display the data catalogue link
        $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
        $user2 = new User();
        $user2->setDbCon($dbCon);
        $items = $user2->getPermList(PermType::EXTERNAL, 'map', null);
        $showdata = array_key_exists('doc.app', $items) && in_array('read', $items['doc.app']);

        #echo logged out header
        echo '<div class="signincontrols">';
        if ($showdata) {
            echo '<a href="/mfp/doc" class="headerlink btn btn-primary btn-block btn-large">Data Catalogue</a>';
        }
        echo '<a href="signup.php" class="headerlink btn btn-primary btn-block btn-large margin-left-5">Apply for Access</a>';
        echo '<a href="signin.php" class="headerlink btn btn-primary btn-block btn-large margin-left-5">Sign In</a>';
        echo '</div>';
    }
    ?>
</div>