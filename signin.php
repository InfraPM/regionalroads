<?php
session_start();
require '../support/pass.php';
require '../support/dbcon.php';
require '../support/user.php';
require 'header.php';
$user = new User();
if(isset($_POST['user']) && isset($_POST['password']) && isset($_POST['submitButton'])){
    $schema = "gm";
    $userTable = "users";
    $dbCon = new dbcon($host, $port, $db, $dbuser, $dbpassword);
    $user->setDbCon($dbCon);
    $user->setUserName($_POST['user']);
    $user->setPassword($_POST['password']);
    $user->checkPassword();#must be 'Approved' password
    $user->getToken_db();
    if ($user->isValid()){
        $_SESSION['user']=$user->userName;
        $_SESSION['password']=$user->password;
        $_SESSION['status']="loggedin";
        session_regenerate_id();
        $_SESSION['datatoken']=$user->token;
        $user->logEvent("User Log In");
        if (isset($_SESSION['redirectLink']) and strlen($_SESSION['redirectLink'])>0){
            $redirect = $_SESSION['redirectLink'];
            unset($_SESSION['redirectLink']);
            header("Location: " .$redirect);
        }
        else{
            header("Location: index.php");
        }
    }
    else{
        echo '<div class="clearfloat" style="color: red">Invalid Credentials.</div>';
    }
}
?>

    <div class="login fadein">
	<h1>Sign In</h1>
    <form method="post">
	<label for="user">Email Address:</label><br>
	<input type="text" id="user" name="user" placeholder="johndoe@example.com" required><br>
	<label for="password">Password:</label><br>
	<input type="password" id="password" name="password" placeholder="password" required><br><br>
	<button type="submit" class="btn btn-primary btn-block btn-large" value="Log In" name="submitButton">Sign In</button>
    </form>
    <div id="forgot">
	<a href="forgot.php">Forgot password?</a>
    </div>
    </div>
<?php require 'footer.php' ?>
