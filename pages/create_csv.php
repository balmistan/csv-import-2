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


$arr_in = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION["info"])){
            $_SESSION["info"] = array();
        }


$_SESSION["csvexportedfilename"] = "exported-" . $_SESSION["info"]["tablename"].".csv";

     $csv = new csv("../uploads/".$_SESSION["csvexportedfilename"], //csv file link
        $separator_list[$_SESSION["info"]["sep"]][1], 
        $enclosure_list[$_SESSION["info"]["encl"]][1], 
        $_SESSION["info"]["chset"]
        );
     
     $arr_first_row_csv = array();
     $db_column_names = "";
     
     for($i=0; $i<count($arr_in["assoc"]); $i++){
         array_push($arr_first_row_csv, $arr_in["assoc"][$i]["csvcolumnname"]);
         $db_column_names .= $arr_in["assoc"][$i]["dbcolumnname"] . ",";
     }
     
     //remove last comma:
     $db_column_names = substr_replace($db_column_names, "", strrpos($db_column_names, ","));
     
    
    
    $content = $db->getContentTable($_SESSION["info"]["tablename"], $db_column_names);
    
    $arr_db_columns = explode(",", $db_column_names);
  
    $csvdata = array();
    
    for($i=0; $i<count($content); $i++){
        $csvdata[$i] = array_values($content[$i]);     
    }
    
   
    $issue = $csv->createCsv($arr_first_row_csv, $csvdata);
    
    
   echo json_encode($issue);
    
    