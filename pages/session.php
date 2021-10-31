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
    header("Location: ../index.php");
    die();
}


$arr_in = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION["info"])) {
    $_SESSION["info"] = array();
}

foreach($arr_in as $key=>$value){
    $_SESSION["info"][$key] = addslashes($value);
}


echo "[]";