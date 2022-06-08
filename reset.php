<?php
session_start();
require "../support/User.php";
require 'header.php';
if (isset($_POST['confirmpassword']) && isset($_POST['password']) && isset($_POST['token']) && isset($_POST['submitButton'])) {
    $user = new User();
    $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbCon);
    $user->setTemporaryPasswordToken($_POST['token']);
    try {
        $user->getUserFromPasswordToken();
    } catch (TypeError $e) {
        $msg = '<div>Link has expired.  Password not changed.</div>';
        //header('Location: index.php');
    }
    if ($user->exists() == TRUE) {
        $user->setPassword($_POST['password']);
        $user->setPassword_db();
        $user->setTemporaryPasswordToken("");
        $user->setPasswordResetToken_db();
        $_SESSION['status'] = 'reset';
        header('Location: index.php');
    } else {
        $msg = '<div style="text-align: center; color: red; font-weight: bold;">Link has expired.  Password not changed.</div>';
    }
}
if (isset($_GET['token']) && strlen($_GET['token']) == 20) {
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
    if (password == confirmPassword && isStrong(password)){
$("button[name='submitButton']").prop("disabled", false);
$("#checkPasswordMatch").html("Passwords match and meet complexity standards.");
$("button[name='submitButton']").css("cursor","default");
}
else if (password == confirmPassword && isStrong(password)==false){
    if (password.length==0){
        var msg = "Please enter a password."
    }
    else{
        var msg = "Passwords match but do not meet complexity standards.";
    }            
    $("button[name='submitButton']").prop("disabled", true);
    $("#checkPasswordMatch").html(msg);
    $("button[name='submitButton']").css("cursor","not-allowed");
}
else if (password != confirmPassword && isStrong(password)){
    $("button[name='submitButton']").prop("disabled", true);
    $("#checkPasswordMatch").html("Passwords do not match!");
    $("button[name='submitButton']").css("cursor","not-allowed");
}
    else{
$("button[name='submitButton']").prop("disabled", true);
$("#checkPasswordMatch").html("Passwords do not match!");
$("button[name='submitButton']").css("cursor","not-allowed");
}
}
function isStrong(password){
    var regExp = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%&*()]).{8,}/; 
    var validPassword = regExp.test(password);
    return validPassword;    
}
$(document).ready(function () {
$("button[name='submitButton']").prop("disabled", true);
$("button[name='submitButton']").css("cursor", "not-allowed");
$("#confirmpassword").keyup(checkPasswordMatch);
$("#password").keyup(checkPasswordMatch);
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
<div id="passwordInstructions">Passwords must: 
<ul><li>Contain at least 1 uppercase letter</li> 
<li>Contain at least 1 special character</li>
<li>Contain at least 1 number</li>
<li>Be at least 8 characters long</li>
</div>
<div id="checkPasswordMatch" style="text-align:center;"> </div>
EOT;
    echo $msg;
    echo "</div>";
} else {
    header('Location: index.php');
}
require 'footer.php';
