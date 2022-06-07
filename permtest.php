<?php
session_start();
require '../support/User.php';
$dbCon = new DbCon($host, $port, $db, $dbuser, $dbpassword);
$user = new User();
$user->setDbCon($dbCon);
$dbCon->query("SELECT username FROM gm.users");
$result = $dbCon->result;
while ($row = pg_fetch_array($result)) {
    $user->setUserName($row['username']);
    $user->getAllFromdb();
    $user->setDomain();
    print $row['username'] . ": {";
    $modes = ['read', 'insert', 'modify', 'delete', 'list', 'comment'];
    foreach($modes as $mode) {
        print $mode . ": {";
        $appList = json_decode($user->getAppList_New(), TRUE);
        print " apps: [";
        foreach( sortNames($appList) as $app ) {
            print $app . ", ";
        }
        $dataList = json_decode($user->getDataList_New(), TRUE);
        print "] data: [";
        foreach( sortNames($dataList) as $data ) {
            print $data . ", ";
        }
        print "]},<br>\n";
    }
    print "}<br>\n";
    
}

function sortNames($arr) {
    foreach( $arr as $thing ) {
        $names[] = $thing['name'];
    }
    sort($names);
    return $names;
}