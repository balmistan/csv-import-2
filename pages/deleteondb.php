<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();

if (!isset($_SESSION["islogged"])) {
    die();
}

require_once "../class/db.class.php";

$db = new db;

$db->truncateTable(addslashes($_POST["tablename"]));

echo '[]';