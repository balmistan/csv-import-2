<?php

session_start();

require_once "../class/db.class.php";
require_once "../class/csv.class.php";
require_once 'language_read.php';
require_once "separator_list.php";
require_once 'debug.php';

$db = new db();

if (!isset($_SESSION["islogged"])) {
    die();
}

//$start = microtime(1);

$arr_in = json_decode(file_get_contents('php://input'), true);


if($_SESSION["info"]["autoconf"]=="1"){
    $content = file_get_contents("../uploads/" . $_SESSION["info"]["fnup"]);
     $temp = array();

        foreach ($separator_list as $key => $value) {
            $temp[$key] = substr_count($content, $value[1]);
        }

        arsort($temp);
        $keys = array_keys($temp);

        $_SESSION["info"]["sep"] = $keys[0];

        $_SESSION["info"]["chset"] = mb_detect_encoding($content, 'UTF-8, ISO-8859-1');
        

}

//debug($_SESSION["info"]);


$ret = array();


$getnumrows = false;

if($_SESSION["info"]["csvnumrows"] == -1){
    $getnumrows = true;
}



$csv = new csv("../uploads/" . $_SESSION["info"]["fnup"], //csv file link
        $separator_list[$_SESSION["info"]["sep"]][1], 
        $enclosure_list[$_SESSION["info"]["encl"]][1], 
        $_SESSION["info"]["chset"], $arr_in["start"], $arr_in["limit"]
        );


//debug($_SESSION["info"]);

$csvcontent = $csv->getArrCsv($getnumrows);


if($getnumrows){
    $_SESSION["info"]["csvnumrows"] = $csvcontent["numrows"];
}else{
    $csvcontent["numrows"] = $_SESSION["info"]["csvnumrows"];
}



//debug($csvcontent);

$csvcontent["info"] = $_SESSION["info"];




//$end = microtime(1);
//exectime($start, $end);

echo json_encode($csvcontent);
