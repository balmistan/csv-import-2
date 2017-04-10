<?php

session_start();

require_once "../class/db.class.php";
require_once "../class/csv.class.php";
require_once 'language_read.php';
require_once "separator_list.php";
require_once 'debug.php';
require_once"../class/excelconvert.class.php";
require_once '../class/PHPExcel/Classes/PHPExcel.php';
require_once '../class/PHPExcel/Classes/PHPExcel/IOFactory.php';

if (!isset($_SESSION["islogged"])) {
    die();
}


$db = new db();



$arr_in = json_decode(file_get_contents('php://input'), true);


$csv = new csv("../uploads/" . $_SESSION["info"]["fnup"], //csv file link
        $separator_list[$_SESSION["info"]["sep"]][1], $enclosure_list[$_SESSION["info"]["encl"]][1], $_SESSION["info"]["chset"]
);

$arr_csv = $csv->getArrCsv();


if (0) {
    debug("session-info:");
    debug($separator_list[$_SESSION["info"]["sep"]][1]);
    debug($enclosure_list[$_SESSION["info"]["encl"]][1]);
    debug($_SESSION["info"]["chset"]);
}

if (0) {
    debug("arr_csv:");
    debug($arr_csv);
}



if (0) {
    debug("tablename:");
    debug($_SESSION["info"]["tablename"]);

    debug("assoc:");

    debug($arr_in["assoc"]);
}


$issue = $db->insert($_SESSION["info"]["tablename"], $arr_in["assoc"], $arr_csv);

if($issue[0]==1){ //on success

$arr_check = $db->dbcheck($_SESSION["info"]["tablename"], $arr_in["assoc"], $arr_csv);
}

if(count($arr_check)){  
 
    $excel = new excelconvert($arr_in["assoc"], $arr_check);
    
    $excel->Save("../uploads/Fehler.xls");
    
    $issue[0] = 3;   //3 = error + creating differences files
}




echo json_encode($issue);
