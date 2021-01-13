<?php
session_start();
require '../support/pass.php';
require '../support/dbcon.php';
require '../support/user.php';
header('Content-Type: application/json');
if ($_SESSION['status']=='loggedin'){
    $user = new User();
    $dbCon = new dbcon($host, $port, $db, $dbuser, $dbpassword);
    $user->setDbCon($dbCon);
    $user->setUserName($_SESSION['user']);
    $user->setPassword($_SESSION['password']);
    $user->token = $_SESSION['datatoken'];
    $user->checkPassword();
    if ($user->isValid()){
        $tokenObj = $user->refreshToken();
        $_SESSION['datatoken']=$user->token;
        echo $tokenObj;
    }            
    else{
        returnError(400, "Unauthorized");
    } 
}
else{
    returnError(400, "Unauthorized");
}
function returnError($responseCode, $responseText){
    http_response_code($responseCode);
    echo '{"error": "'.$responseText.'"}';
    die();
}
?>
