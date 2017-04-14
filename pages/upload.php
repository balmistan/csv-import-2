<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

require_once "../class/db.class.php";
require_once 'debug.php';


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

        if (!is_uploaded_file($_FILES["myfile"]["tmp_name"])) {
            die("Error on file upload");
        }

        if (is_resource($zip = zip_open($_FILES["myfile"]["tmp_name"]))) {

            $zip_entry = zip_read($zip); //read only the first file found.

            if (zip_entry_open($zip, $zip_entry, "r")) {

// The name of the file to save on the disk
                $fileName = zip_entry_name($zip_entry);

// Get the content of the zip entry
                $fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                file_put_contents($output_dir . $fileName, $fstream);
// Set the rights
                //chmod($output_dir . $fileName, 0777);
            }

            zip_close($zip);
//this is a zip archive
        } else {  //if not compress file
            if (!move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $fileName)) {
                die("Error on move_upload_file");
            }
        }

        $ret[] = $fileName;
    }

    $_SESSION["info"]["fnup"] = $fileName;
    $_SESSION["info"]["csvnumrows"] = -1;

    echo json_encode($ret);
}
?>

