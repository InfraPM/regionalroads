<?php
session_start();
require '../support/User.php';
require 'header.php';

$user = new User();
$msg = "";

if (isset($_POST['user']) && isset($_POST['password']) && isset($_POST['submitButton'])) {
    $schema = "gm";
    $userTable = "users";
    $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbCon);
    $user->setUserName($_POST['user']);
    $user->setPassword($_POST['password']);
    $user->checkPassword();
    $user->getToken_db();
    if ($user->isValid()) {
        $_SESSION['user'] = $user->userName;
        $_SESSION['status'] = "loggedin";
        session_regenerate_id();
        $_SESSION['datatoken'] = $user->token;
        $user->logEvent("User Log In");
        if (isset($_SESSION['redirectLink']) and strlen($_SESSION['redirectLink']) > 0) {
            $redirect = $_SESSION['redirectLink'];
            unset($_SESSION['redirectLink']);
            header("Location: " . $redirect);
        } else {
            header("Location: index.php");
        }
    } else {
        $msg = '<div style="color: red; text-align: center; font-weight: bold; padding: 10px;">Invalid Credentials.</div>';
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
    <?php echo $msg ?>
</div>
<?php require 'footer.php' ?>
