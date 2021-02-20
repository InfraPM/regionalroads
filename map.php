<?php
session_start();
require 'header.php';
require_once '../support/environmentsettings.php';
if (isset($_SESSION['status'])) {
    if (session_status() === PHP_SESSION_ACTIVE && $_SESSION['status'] == "loggedin") {
        if (!empty($_GET['mapName'])) {
            $mapName = $_GET['mapName'];
        }
        if (!empty($_SESSION['datatoken'])) {
            $datatoken = $_SESSION['datatoken'];
        }
        if ($mapName == "mapbuilder") {
            require 'mapbuilder.php';
        } else {
            require 'mapbody.php';
        }
    } else {
        $redirectLink = ltrim($_SERVER['REQUEST_URI'], "/");
        $_SESSION['redirectLink'] = $redirectLink;
        header("Location: signin.php");
    }
} else {
    $redirectLink = ltrim($_SERVER['REQUEST_URI'], "/");
    $_SESSION['redirectLink'] = $redirectLink;
    header("Location: signin.php");
}
