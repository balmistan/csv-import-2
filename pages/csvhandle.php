<?php

session_start();

require_once "../class/db.class.php";
require_once "../class/csv.class.php";
require_once "separator_list.php";
require_once 'debug.php';

$db = new db();

if (!isset($_SESSION["islogged"])) {
    die();
}


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

$csv = new csv("../uploads/" . $_SESSION["info"]["fnup"], //csv file link
        $separator_list[$_SESSION["info"]["sep"]][1], 
        $enclosure_list[$_SESSION["info"]["encl"]][1], 
        $_SESSION["info"]["chset"], "200"
        );


//debug($_SESSION["info"]);

$csvcontent = $csv->getArrCsv();

//debug($csvcontent);

$csvcontent["info"] = $_SESSION["info"];

echo json_encode($csvcontent);
