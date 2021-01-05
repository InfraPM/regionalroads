<?php
require '../support/environmentsettings.php';
require '../support/pass.php';
require '../support/dbcon.php';
require '../support/user.php';
require 'header.php';
if (!empty($_POST['user']) && !empty($_POST['submitButton'])){
    #set a new temporarypasswordtoken
    $user = new User();
    $dbCon = new dbcon($host, $port, $db, $dbuser, $dbpassword);
    $user->setDbCon($dbCon);
    $user->setUserName($_POST['user']);
    if ($user->approved()){
	$user->generateRandom(20,"temppasswordtoken");
	$user->setPasswordResetToken_db();
	$to = strtolower($user->userName);
	$headers = "From: ec2-user@regionalroads.com" . "\r\n";
	$headers  .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$msg = '<p>You have requested that your password to RegionalRoads.com be reset! Please follow the link to set your password.</p><p><a href="'.$baseURL.'regionalroads.com/reset.php?token='.$user->temporaryPasswordToken.'">Set Password</a>';
	$subject = "Reset Password - RegionalRoads.com";
	$mail = mail($to,$subject,$msg,$headers);
	#email user with temporarypasswordtoken
    }
    else{
	echo '<div class="clearfloat">User not found.</div>';
    }
}
?>
<div class="login fadein">
    <h1>Reset Password</h1>
    <form method="post">
	<label for="user">Email Address:</label><br>
	<input type="text" id="user" name="user" placeholder="johndoe@example.com"><br>
	<button type="submit" class="btn btn-primary btn-block btn-large" value="Reset Password" name="submitButton">Reset Password</button>
    </form>
</div>
    <?php
    require 'footer.php'
    ?>
