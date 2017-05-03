<?php

/* error_reporting(E_ALL | E_STRICT); // all = 2147483647  

  ini_set('display_error', 1);
  ini_set('ignore_repeated_errors', 0);
  ini_set('ignore_repeated_source', 0);
 */
session_start();

if (isset($_SESSION["islogged"])) {
    header("Location: main.php");
}

require_once '../config.php';
include("../class/db.class.php");
require_once 'language_read.php';




$post_var = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$err_msg = '';

if (isset($post_var["loginbtn"])) {

    if (trim($post_var["access"]) != "") {
        $arr_tmp = explode(":", trim($post_var["access"]));

        $_SESSION["dbname"] = $post_var["dbname"]=trim($arr_tmp[0]);
        $_SESSION["hostname"] = $post_var["hostname"]= $arr_tmp[1];
        $_SESSION["port"] = $post_var["port"]= $arr_tmp[2];
        $_SESSION["user"] = $post_var["user"]= $arr_tmp[3];
        $_SESSION["password"] = $post_var["password"]= $arr_tmp[4];
    } else {
        $_SESSION["dbname"] = $post_var["dbname"];
        $_SESSION["hostname"] = $post_var["server"];
        $_SESSION["user"] = $post_var["username"];
        $_SESSION["password"] = $post_var["password"];
        $_SESSION["port"] = $post_var["port"];
    }




    $db = new db(false);    // true = active debug

    if ($db->isLogged() == NULL) {
        $err_msg = "Die Anmeldung am MySQL-Server ist fehlgeschlagen!";
        $err_msg .= " - " . $db->getError();
    } else {
        if ($db->setDB($post_var["dbname"])) {
            $_SESSION["islogged"] = true;
            header("Location: main.php?lang=" . $language);
        } else {
            $err_msg = "Datenbank nicht gefunden!";
            $err_msg .= " - " . $err_msg = $db->getError();
        }
    }
}



echo "
<!DOCTYPE HTML>
<html>
    <head>
        <title>csv-import</title>
        <meta charset=\"UTF-8\" />
        <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/screen.css\">
        <link rel=\"stylesheet\" type=\"text/css\" href=\"../css/login.css\">
    </head>

    <body>

        <form class=\"box login\" action=\"?lang=" . $language . "\" method=\"post\">
            
        ";

if ($err_msg != "") {
    echo "<div class=\"error\"><span>" . $error["login_failed"] . "</span><img class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\" src=\"../css/info.png\" alt=\"info\" title=\"" . $err_msg . "\" /></div>";
}
echo "
            <fieldset class=\"boxBody\">
                <input type=\"button\" onclick=\"window.location = '?lang=it';\" value=\"IT\" \><input type=\"button\" onclick=\"window.location = '?lang=de';\" value=\"DE\" \>
                <label for=\"dbname\">" . $label['dbname'] . "</label>
                <input type=\"text\" name=\"dbname\" id=\"dbname\"/>
                <label for=\"server\">" . $label['server'] . "</label>
                <input type=\"text\" name=\"server\" id=\"server\" value=\"localhost\" />
                <label for=\"port\">" . $label['port'] . "</label>
                <input type=\"text\" name=\"port\" id=\"port\" value=\"3306\" />       
                <label for=\"username\">" . $label['username'] . "</label>
                <input type=\"text\" name=\"username\" id=\"username\" value=\"root\" />
                <label for=\"password\">" . $label['password'] . "</label>
                <input type=\"Password\" name=\"password\" id=\"password\"/>
                <label for=\"access\">Access</label>
                <input type=\"text\" name=\"access\" id=\"access\"/>
            </fieldset>

            <footer>

                <input type=\"submit\" name=\"loginbtn\" class=\"btnLogin\" value=\"" . $label['loginbtn'] . "\" />

            </footer>
        </form>
    </body>
</html>
";




