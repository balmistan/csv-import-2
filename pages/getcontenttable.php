<?php

session_start();

require_once "../class/db.class.php";
require_once 'debug.php';


if (!isset($_SESSION["islogged"])) {
    die();
}

$arr_in = json_decode(file_get_contents('php://input'), true);

$db = new db();

$ret_var = array("tabheader" => array(), "tabcontent" => array());

if ($_SESSION["info"]["tablename"] != "") {

    $ret_var = $db->getContentTable($_SESSION["info"]["tablename"], "*", $arr_in["start"], $arr_in["limit"]);
}
echo json_encode($ret_var);
