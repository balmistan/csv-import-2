<?php

session_start();

require_once "../class/db.class.php";
require_once "../class/csv.class.php";
require_once "separator_list.php";
require_once 'debug.php';


if (!isset($_SESSION["islogged"])) {
    die();
}


$db = new db();



$arr_in = json_decode(file_get_contents('php://input'), true);


    $csv = new csv("../uploads/" . $_SESSION["info"]["fnup"], //csv file link
        $separator_list[$_SESSION["info"]["sep"]][1], 
        $enclosure_list[$_SESSION["info"]["encl"]][1], 
        $_SESSION["info"]["chset"]
        );

    $arr_csv = $csv->getArrCsv();


$issue = $db->insert($_SESSION["info"]["tablename"], $arr_in["assoc"], $arr_csv);


echo json_encode($issue);