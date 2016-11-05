<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

require_once "../class/db.class.php";


if (!isset($_SESSION["islogged"])) {
    die();
}

$output_dir = "../uploads/";
if (isset($_FILES["myfile"])) {
    $ret = array();

//	This is for custom errors;	
    /* 	$custom_error= array();
      $custom_error['jquery-upload-file-error']="File already exists";
      echo json_encode($custom_error);
      die();
     */
    $error = $_FILES["myfile"]["error"];
   
    if (!is_array($_FILES["myfile"]["name"])) { //single file
        $fileName = $_FILES["myfile"]["name"];
        if(!move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $fileName)){
            die("Error on move_upload_file");
        }
        $ret[] = $fileName;
    }
    
    $_SESSION["info"]["fnup"] = $fileName;

    echo json_encode($ret);
}
?>

