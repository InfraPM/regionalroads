<?php
//not in use - requires changes to be reinstated
//session_start();
require_once '../support/environmentsettings.php';
?>

<style>
    table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    td,
    th {
        text-align: left;
        padding: 8px;
    }

    tr:nth-child(even) {
        background-color: rgba(0, 0, 0, .25);
    }

    form input[type="submit"] {

        background: none;
        border: none;
        color: blue;
        text-decoration: underline;
        cursor: pointer;
    }
</style>

<?php
#require being logged in to a session?
require '../support/pass.php';
require '../support/dbcon.php';
require_once '../support/user.php';
if (!empty($_SESSION['user']) && $_SESSION['status'] == "loggedin") {
    echo '<div class="clearfloat" style="padding: 5px"><h4>Welcome ' . $_SESSION['user'] . ', here is your data:<h4></div>';
    $dbCon = new dbcon($host, $port, $db, $dbuser, $dbpassword);
    $user = new User();
    $user->setDbCon($dbCon);
    $user->userName = $_SESSION['user'];
    //$user->getToken_db();
    //$datatoken=$user->token;
    $datatoken = $_SESSION['datatoken'];
    #$user->password = $_POST['password'];
    #$user->setDomain();
    #$user->checkPassword();
    #if ($user->isValid()){
    $dataList = $user->getDataList();
    #echo $dataList;
    #echo "\n";
    $echoString = "";
    $echoString .= '<div id="datatable" class="fadein"><table><tr><th>Dataset</th><th>GeoJSON Download</th><th>KML Download</th><th>Shapefile Download</th><th>Map View Link</th></tr>';
    foreach ($dataList as $key => $item) {
        $echoString .= '<tr><td>' . $item['aliasname'] . '</td><td>';
        $echoString .= '<a href="' . $baseTRPURL . 'trp.regionalroads.com/api/?data=' . $item['name'] . '&format=geojson&download=true&datatoken=' . $datatoken . '">GeoJSON</a></td><td><a href="' . $baseTRPURL . 'trp.regionalroads.com/api/?data=' . $item['name'] . '&format=kml&download=true&datatoken=' . $datatoken . '">KML</a></td><td><a href="' . $baseTRPURL . 'trp.regionalroads.com/api/?data=' . $item['name'] . '&format=shapefile&download=true&datatoken=' . $datatoken . '">Shapefile</a></td><td><a href="map.php?data=' . $item['name'] . '">View in Map</a></td></tr>';

        ###echo '<tr><td>'.$value.'</td><td><form method="post" action="'.$baseTRPURL.'trp.regionalroads.com/api/?data='.$value.'&format=geojson&download=true"><input type="hidden" value="'.$user->token.'" name="datatoken" id="datatoken"><input type="submit" value="GeoJson"></form></td><td><form method="post" action="'.$baseTRPURL.'trp.regionalroads.com/api/?data='.$value.'&format=kml&download=true"><input type="hidden" value="'.$user->token.'" name="datatoken" id="datatoken"><input type="submit" value="KML"></form></td><td><form method="post" action="'.$baseTRPURL.'trp.regionalroads.com/api/?data='.$value.'&format=shapefile&download=true"><input type="hidden" value="'.$user->token.'" name="datatoken" id="datatoken"><input type="submit" value="Shapefile"></form></td><td><form method="post" action="'.$baseURL.'regionalroads.com/closures/map.php?data='.$value.'"><input type="hidden" value="'.$user->token.'" name="datatoken" id="datatoken"><input type="submit" value="Map View"></form></td></tr>';
    }
    $echoString .= "</table></div>";
    echo $echoString;
}

?>