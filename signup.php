<?php
session_start();
require '../support/User.php';
require 'header.php';
if (isset($_POST['user']) && isset($_POST['submitButton'])) {
    $dbcon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $addUser = new User();
    $addUser->setDbCon($dbcon);
    #check userName for correct format
    $addUser->setUserName($_POST['user']);
    if ($addUser->existsWithAnyStatus() == FALSE) {
        $addUser->generateRandom(10, "password");
        $addUser->generateRandom(20, "token");
        $addUser->setPasswordStatus("Temporary");
        $addUser->setUserStatus("Pending");
        $domainAdmin = $addUser->findDomainAdmin();

        if ($domainAdmin !== null) {
            $addUser->generateRandom(20, "signuptoken");
            $addUser->add();
            $to = $domainAdmin;
            $subject = "Regional Roads - Approve New User";
            $msg = 'Please approve or deny ' . $addUser->userName . ' to access Regional Roads.<p><a href="' . $baseURL . 'regionalroads.com/newuser.php?token=' . $addUser->signupToken . '&action=approve">Approve</a></p><p><a href="' . $baseURL . 'regionalroads.com/newuser.php?token=' . $addUser->signupToken . '&action=deny">Deny</a></p>';
            $headers = "From: ec2-user@regionalroads.com" . "\r\n";
            $headers  .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $mail = mail($to, $subject, $msg, $headers);
            $_SESSION['status'] = 'pending';
            #echo "To: ".$to;
            #echo "Subject: ".$subject;
            #echo "Message: ".$msg;
            header("Location: index.php");
        } else {
            $msg = '<div style="text-align: center; padding: 10px;">Your domain name is not on our approved list.</div>';
        }
    } else {
        $msg = '<div style="text-align: center; padding: 10px;">User already exists.</div>';
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
    <?php echo $msg ?>
</div>

<?php require 'footer.php'; ?>
