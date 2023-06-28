<?php
require '../support/User.php';
require 'header.php';
if (!empty($_POST['user']) && !empty($_POST['submitButton'])) {
    $user = new User();
    $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbCon);
    $user->setUserName($_POST['user']);
    if ($user->approved()) {
        $user->generateRandom(20, "temppasswordtoken");
        $user->setPasswordResetToken_db();
        $to = strtolower($user->userName);
        $msg = wordwrap('<p>You have requested that your password to Regional Roads be reset. Please follow the link to set your password.</p><p><a href="' . $baseURL . 'regionalroads.com/reset.php?token=' . $user->temporaryPasswordToken . '">Set Password</a>', 70, "\r\n");
        $text_msg = 'You have requested that your password to Regional Roads be reset. Please copy paste the link into your browser to set your password: ' . $baseURL . 'regionalroads.com/reset.php?token=' . $user->temporaryPasswordToken;
        $subject = "Regional Roads - Reset Password";
        $sendMail = sendMail($to, $subject, $msg, $text_msg);
        $msg = '<div style="text-align: center; padding: 10px;">Password reset email has been sent.</div>';
    } else {
        $msg = '<div style="text-align: center; padding: 10px;">User not found.</div>';
    }
} else if (!empty($_POST['submitButton'])) {
    $msg = '<divstyle="text-align: center; padding: 10px;">Please enter a valid email.</div>';
}
?>
<div class="login fadein">
    <h1>Reset Password</h1>
    <form method="post">
        <label for="user">Email Address:</label><br>
        <input type="text" id="user" name="user" placeholder="johndoe@example.com" required><br>
        <button type="submit" class="btn btn-primary btn-block btn-large" value="Reset Password" name="submitButton">Reset Password</button>
    </form>
    <?php echo $msg; ?>
</div>
<?php
require 'footer.php'
?>