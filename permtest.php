<?php
session_start();
require '../support/User.php';
$dbCon = new DbCon($host, $port, $db, $dbuser, $dbpassword);
$user = new User();
$user->setDbCon($dbCon);
$old = $_GET['old'];
if($old) {
    print "OLD PERMISSIONS CODE\n";
} else {
    print "NEW PERMISSIONS CODE\n";
}
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
        if($old) {
            $appList = json_decode($user->getAppList_Old(FALSE, $mode), TRUE);
        } else {
            $appList = json_decode($user->getAppList(FALSE, $mode), TRUE);
        }
        // check that $user->hasPerm() agrees with the getAppList() call
        // if(count($appList) > 0) {
        //     print "True?: ";
        //     var_dump($user->hasPerm($mode, $appList[0]['name']));
        // }
        print " apps: [";
        foreach( sortNames($appList) as $app ) {
            print $app . ", ";
        }
        if($old) {
            $dataList = json_decode($user->getDataList_Old(FALSE, $mode), TRUE);
        } else {
            $dataList = json_decode($user->getDataList(FALSE, $mode), TRUE);
        }
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