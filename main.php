<?php
require '../support/User.php';
$dbCon = new DbCon($host, $port, $db, $dbuser, $dbpassword);
$user = new User();
$user->setDbCon($dbCon);
$user->setUserName($_SESSION['user']);
$user->getToken_db();
$dataToken = $_SESSION['datatoken'];
$user->getUserFromToken($dataToken);
$appList = json_decode($user->getAppList(), TRUE);
if (count($appList) > 0) {
  echo '
<div class="clearfloat fadein" id="appgrid">';
  foreach ($appList as $value) {
    echo <<<EOD
  <div class="grid-item fadein">
  <a href="{$value['link']}">{$value['displayname']}</a>
  </div>
EOD;
  }
  echo '</div>';
} else {
  echo '<div class="fadein clearfloat" id="welcome"><h1>No Apps to show.</h1></div>';
}
echo '</div>';
