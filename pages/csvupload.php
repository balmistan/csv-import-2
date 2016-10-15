<?php

//make upload und return issue and file name.  Return like json string
echo upload();

function upload() {

    $ret = array();

    $target_file = "../uploads/" . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = pathinfo($target_file, PATHINFO_EXTENSION);

    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $ret["filename"] = "";  //Error in upload
    } else {
        $ret["filename"] = $_FILES["fileToUpload"]["name"];
    }
    
    
 
    
    return json_encode($ret);
    
}



?> 
