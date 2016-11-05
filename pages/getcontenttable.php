<?php

session_start();

require_once "../class/db.class.php";
require_once 'debug.php';


if (!isset($_SESSION["islogged"])) {
    die();
}

$db = new db();

$ret_var = array("tabheader" => array(), "tabcontent" => array());

if ($_SESSION["info"]["tablename"] != "") {
    $ret_var["tabheader"] = $db->getColumnsName($_SESSION["info"]["tablename"]);

    $ret_var["tabcontent"] = $db->getContentTable($_SESSION["info"]["tablename"], "*", "");
}
echo json_encode($ret_var);
