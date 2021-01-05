<?php
require '../support/environmentsettings.php';
require '../support/pass.php';
require '../support/dbcon.php';
require '../support/user.php';
$dbCon = new dbcon($host, $port, $db, $dbuser, $dbpassword);
$user = new User();
$user->setDbCon($dbCon);
$user->userName = $_SESSION['user'];
$user->getToken_db();
$datatoken=$_SESSION['datatoken'];
?>
<div class="clearfloat fadein" id="appgrid">
  <div class="grid-item fadein">
    <a href="map.php?mapName=mrncapacitychange">MRN Capacity Change Application</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=mfptracking">Municipal Funding Programs Tracking Application</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=goodsmovement">Goods Movement Application</a>
  </div>
</div>
