<?php

$language = "de";  //default language

if(isset($_GET["lang"])){
    switch($_GET["lang"]){
        case "it":
            $language = "it";
            break;
        case "de":
            $language = "de";
            break;
        default:
            //it is in config.php defined
            break;
    }
}

$page = file_get_contents("../lang/lang_".$language.".json");
$ltext = json_decode($page, true);
$button = $ltext["button"];
$label = $ltext["label"];
$msg = $ltext["msg"];
$error = $ltext["error"];
$legend = $ltext["legend"];
$option_select = $ltext["option_select"];
$info = $ltext["info"];
