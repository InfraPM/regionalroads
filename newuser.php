<?php
require "../support/User.php";

if (!empty($_GET['token']) && !empty($_GET['action'])) {
    $user = new User();
    $dbcon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbcon);
    $user->setSignupToken($_GET['token']);
    $user->getUserFromSignupToken();
    if (strlen($user->userName) > 0 || strlen($user->password) > 0) {
        if ($_GET['action'] == 'approve') {
            $user->approve();
            echo 'User ' . $user->userName . " has been approved.";
        } elseif ($_GET['action'] == 'deny') {
            $user->deny();
            echo 'User ' . $user->userName . " has been denied.";
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
}
