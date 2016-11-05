<?php

session_start();

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "../class/db.class.php";
require_once 'debug.php';


if (!isset($_SESSION["islogged"])) {
    die();
}


$db = new db();


$ret_var = $db->getContentTable("comuni", "*", 5);


echo (json_encode($ret_var));
