<?php
session_start();
require "../support/User.php";
require 'header.php';
if (isset($_POST['confirmpassword']) && isset($_POST['password']) && isset($_POST['token']) && isset($_POST['submitButton'])) {
    $user = new User();
    $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbCon);
    $user->setTemporaryPasswordToken($_POST['token']);
    $user->getUserFromPasswordToken();
    if (strlen($user->exists()) > 0) {
        $user->setPassword($_POST['password']);
        $user->setPassword_db();
        $user->setTemporaryPasswordToken("");
        $user->setPasswordResetToken_db();
        $_SESSION['status'] = 'reset';
        header('Location: index.php');
    } else {
        echo '<div class="clearfloat">Link has expired.  Password not changed.</div>';
    }
}
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    echo <<<EOT
<script
	    src="https://code.jquery.com/jquery-3.4.1.min.js"
	    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	    crossorigin="anonymous"></script>
<script>
function checkPasswordMatch() {
    var password = $("#password").val();
    var confirmPassword = $("#confirmpassword").val();

    if ((password == confirmPassword) && password.length>0){
$("button[name='submitButton']").prop("disabled", false);
$("#checkPasswordMatch").html("Passwords match.");
$("button[name='submitButton']").css("cursor","default");
}
    else{
$("button[name='submitButton']").prop("disabled", true);
$("#checkPasswordMatch").html("Passwords do not match!");
$("button[name='submitButton']").css("cursor","not-allowed");
}
}

$(document).ready(function () {
$("button[name='submitButton']").prop("disabled", true);
$("button[name='submitButton']").css("cursor", "not-allowed");
$("#confirmpassword").keyup(checkPasswordMatch);
});
</script>
<div class="login fadein">
<h1>Reset Password</h1>
    <form method="post">
    <label for="password">Password:</label><br>
	<input type="password" id="password" name="password" placeholder="password"><br>
    <label for="confirmpassword">Confirm Password:</label><br>
	<input type="password" id="confirmpassword" name="confirmpassword" placeholder="password"><br><br>
<input type="hidden" id="token" name="token" value="$token">
	<button type="submit" class="btn btn-primary btn-block btn-large" value="Log In" name="submitButton">Change Password</button>
    </form>
<div id="checkPasswordMatch"></div>
</div>
EOT;
}
require 'footer.php';
