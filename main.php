<?php
require '../support/User.php';
$dbCon = new DbCon($host, $port, $db, $dbuser, $dbpassword);
$user = new User();
$user->setDbCon($dbCon);
$user->setUserName($_SESSION['user']);
$user->getToken_db();
$datatoken = $_SESSION['datatoken'];
?>
<div class="clearfloat fadein" id="appgrid">
  <div class="grid-item fadein">
    <a href="map.php?mapName=mrncapacitychange">MRN Capacity Change</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=mfptracking">Municipal Funding Programs Tracking</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=goodsmovement">Goods Movement</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=dangerousgoods">Dangerous Goods</a>
  </div>
  <div class="grid-item fadein">
    <a href="map.php?mapName=regionalcycling">Regional Cycling Network</a>
  </div>
</div>