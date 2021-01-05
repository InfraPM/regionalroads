<?php
session_start();
require '../support/environmentsettings.php';
require '../support/pass.php';
require '../support/dbcon.php';
require '../support/user.php';
require 'header.php';
if(isset($_POST['user']) && isset($_POST['submitButton'])){
    $dbcon = new dbcon($host, $port,$db, $dbuser, $dbpassword);
    $addUser = new User();
    $addUser->setDbCon($dbcon);
    #check userName for correct format
    $addUser->setUserName($_POST['user']);
    if($addUser->exists()==FALSE){
	$addUser->generateRandom(10, "password");
	$addUser->generateRandom(20, "token");
	$addUser->setPasswordStatus("Temporary");
	$addUser->setUserStatus("Pending");
	$addUser->setInternal('f');
	$addUser->setAdmin('f');
	$addUser->setGodMode('f');
	$addUser->setDomain();
	#echo $addUser->domain;
	if ($addUser->checkDomain()){
            $addUser->generateRandom(20, "signuptoken");
            $addUser->getAdmin();
            $addUser->add();
            $to = $addUser->adminName;
            $subject = "RegionalRoads.com - Approve New User";
            $msg = 'Please approve or deny '.$addUser->userName.' to access Regional Roads.com.<p><a href="'.$baseURL.'regionalroads.com/newuser.php?token='.$addUser->signuptoken.'&action=approve">Approve</a></p><p><a href="'.$baseURL.'regionalroads.com/newuser.php?token='.$addUser->signuptoken.'&action=deny">Deny</a></p>';
            $headers = "From: ec2-user@regionalroads.com" . "\r\n";
            $headers  .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $mail = mail($to,$subject,$msg,$headers);
            $_SESSION['status'] = 'pending';
	    #echo "To: ".$to;
	    #echo "Subject: ".$subject;
	    #echo "Message: ".$msg;
        header("Location: index.php");
	}
	else{
            echo '<div class="clearfloat">Your domain name is not on our approved list.</div>';
	}
    }
    else{
        echo '<div class="clearfloat">User already exists.</div>';
    }
}


?>
<div class="login fadein">
    <h1>Apply for Access</h1>
    <form method="post">
	<label for="user">Email Address:</label><br>
	<input type="text" id="user" name="user" placeholder="johndoe@example.com" required><br>
	<button type="submit" class="btn btn-primary btn-block btn-large" value="Apply for Access" name="submitButton">Apply for Access</button>
    </form>
</div>
<?php require 'footer.php'; ?>
