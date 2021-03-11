<?php
session_start();
require '../support/User.php';
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_ACTIVE && $_SESSION['status'] == 'loggedin') {
    $user = new User();
    $dbCon = new DbCon($_ENV['host'], $_ENV['port'], $_ENV['db'], $_ENV['dbuser'], $_ENV['dbpassword']);
    $user->setDbCon($dbCon);
    $user->setUserName($_SESSION['user']);
    $user->setPassword($_SESSION['password']);
    $user->setToken($_SESSION['datatoken']);
    $user->getUserFromToken();
    $user->checkPassword();
    if ($user->isValid()) {
        $user->checkToken();
        $tokenObj = $user->refreshToken();
        $_SESSION['datatoken'] = $user->token;
        echo $tokenObj;
    } else {
        returnError(400, "Unauthorized");
    }
} else {
    returnError(400, "Unauthorized");
}
function returnError($responseCode, $responseText)
{
    http_response_code($responseCode);
    echo '{"error": "' . $responseText . '"}';
    die();
}
