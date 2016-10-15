<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

require_once "../class/db.class.php";

$db = new db();


if (!$db->isLogged()) {
    header("Location: ../index.php");
    die();
}


if(isset($_SESSION["info"])){
   unset($_SESSION["info"]); 
}

echo '[]';

