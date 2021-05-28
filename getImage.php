<?php
session_start();
if (isset($_SESSION['status'])) {
    if (session_status() === PHP_SESSION_ACTIVE && $_SESSION['status'] == 'loggedin') {
        if (isset($_GET['imgpath'])) {
            header('Content-type: image/jpg');
            $imagePath = $_GET['imgpath'];
            readFile($imagePath);
        }
    }
}
