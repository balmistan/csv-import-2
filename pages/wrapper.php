<?php

session_start();

require_once "../class/db.class.php";


if (!isset($_SESSION["islogged"])) {
    die();
}

$filename = isset($_GET["filename"]) ? htmlentities($_GET["filename"]) : $_SESSION["csvexportedfilename"];


    $file = "../uploads/" . $filename;
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

?>