<?php
session_start();
require 'header.php';
require_once '../support/environmentsettings.php';
if(isset($_SESSION['status'])){
    if($_SESSION['status']=="loggedin"){
	if(!empty($_GET['mapName'])){
	    $mapName=$_GET['mapName'];
	}
	if(!empty($_SESSION['datatoken'])){
	    $datatoken = $_SESSION['datatoken'];
        //include check for datatoken currency
	}
	require 'mapbody.php';
    }
    else{
	$redirectLink = ltrim($_SERVER['REQUEST_URI'],"/");
	$_SESSION['redirectLink']=$redirectLink;
	//echo $redirectLink;
	header("Location: signin.php");
	//redirect to login page
	//remember link they're trying to access
	//after successful login redirect to page
    }
}
else{
    $redirectLink = ltrim($_SERVER['REQUEST_URI'],"/");
    $_SESSION['redirectLink']=$redirectLink;
    //echo $redirectLink;
    header("Location: signin.php");
    //redirect to login page
    //remember link they're trying to access
    //after successful login redirect to page
}
?>



