<?php
require "../support/User.php";

if (!empty($_GET['token']) && !empty($_GET['action'])) {
    $user = new User();
    $dbcon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbcon);
    $user->setSignupToken($_GET['token']);
    try {
        $user->getUserFromSignupToken();
    } catch (Error $e) {
        echo "Link expired.";
        die();
    }
    if (strlen($user->userName) > 0 || strlen($user->password) > 0) {
        if ($_GET['action'] == 'approve') {
            $buttonText = "Approve " . $user->userName;
            $action = 'approve';
            //$user->approve();
            //echo 'User ' . $user->userName . " has been approved.";
        } elseif ($_GET['action'] == 'deny') {
            $buttonText = "Deny " . $user->userName;
            $action = 'deny';
            //$user->deny();
            //echo 'User ' . $user->userName . " has been denied.";
        }
    } else {
        if ($_GET['action'] == "approve") {
            echo "User aproved.";
        } elseif ($_GET['action'] == "deny") {
            echo "User denied.";
        } else {
            echo "Link expired.";
        }
    }
    if (!empty($_POST['submitButton'])) {
        if ($_POST['submitButton'] == 'approve') {
            $user->approve();
        }
        if ($_POST['submitButton'] == 'deny') {
            $user->deny();
        }
        echo "Action completed, please close this window.";
    } else {
        $htmlForm = '<form method="post">
        <button type="submit" class="btn btn-primary btn-block btn-large" value="' . $action . '" name="submitButton">' . $buttonText . ' </button>
    </form>';
        echo $htmlForm;
    }
}
