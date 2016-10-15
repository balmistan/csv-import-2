<?php

session_start();

require_once "../class/db.class.php";

$db = new db();


if (!$db->isLogged()) {
    die();
}

if (isset($_SESSION["csvexportedfilename"])) {
    $file = "../uploads/" . $_SESSION["csvexportedfilename"];
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
}
?>