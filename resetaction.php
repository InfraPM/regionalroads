<?php
require "../support/pass.php";
require "../support/dbcon.php";
require "../support/user.php";
echo $_POST['password'];
if (!empty($_POST['confirmpassword'])&& !empty($_POST['password']) && !empty($_POST['token']) && !empty($_POST['submit'])){
    echo "IM IN!";
    $user = new User();
    $user->getUserFromPasswordToken($_POST['token']);
    $user->password = $_POST['password'];
    $user->setPassword_db();
}
?>
