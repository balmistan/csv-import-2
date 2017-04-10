<?php
error_reporting(E_ALL | E_STRICT); // all = 2147483647  

ini_set('display_error', 1);
ini_set('ignore_repeated_errors', 0);
ini_set('ignore_repeated_source', 0);

session_start();

//echo ini_get('upload_max_filesize');
require_once '../config.php';
require_once "../class/db.class.php";
require_once 'language_read.php';
require_once "separator_list.php";



if (!isset($_SESSION["islogged"])) {
    header("Location: ../index.php");
    die();
}


if (!isset($_SESSION["info"])) {
    $_SESSION["info"] = array();
}

$optid = isset($_SESSION["info"]["pos"]) ? intval($_SESSION["info"]["pos"]) : 0; //option table db selected

$fnup = isset($_SESSION["info"]["fnup"]) ? urldecode($_SESSION["info"]["fnup"]) : ""; //csv filename

$chset = isset($_SESSION["info"]["chset"]) ? urldecode($_SESSION["info"]["chset"]) : "";  //charset selected

$sep = isset($_SESSION["info"]["sep"]) ? urldecode($_SESSION["info"]["sep"]) : ""; //separator selected

$encl = isset($_SESSION["info"]["encl"]) ? $_SESSION["info"]["encl"] : '';

//--------------------------

$show = isset($_SESSION["info"]["show"]) ? $_SESSION["info"]["show"] : '';  //preview show db-table or csv-table

$dir = isset($_SESSION["info"]["dir"]) ? $_SESSION["info"]["dir"] : ''; //direction  import or export

$autoconf = isset($_SESSION["info"]["autoconf"]) ? $_SESSION["info"]["autoconf"] : true; //checkbox autoconf


$db = new db();

$arr_table_names = $db->getTableNames();

//echo "<br />".$optid."<br />";
?>

<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8"/>
        <title>csv-import-2</title>
        <script src="js/jquery-1.11.1.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script type="text/javascript" src="js/mytable.js"></script>
        <script src="js/jquery.form.js"></script>
        <script src="js/jquery.uploadfile.min.js"></script>
        <script src="js/jsfunction.js"></script>
        <script src="../dragscroll-master/dragscroll.js"></script>
        <script src="js/main.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
        <link rel="stylesheet" type="text/css" href="../css/mytable.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="../css/screen.css" />
        <link rel="stylesheet" type="text/css" href="../css/main.css" />
    </head>
    <body>
        <span id="errors">
            <?php
            if (!is_writable("../logs/")) {
                echo $msg['write_denied'] . 'logs' . '<br />';
            }
            if (!is_writable("../uploads/")) {
                echo $msg['write_denied'] . 'uploads' . '<br />';
            }
            ?>
        </span>
        <div id="wrapper">

            <div id="content">
                <?php echo"<input type=\"button\" onclick=\"window.location = '?lang=it';\" value=\"IT\" /><input type=\"button\" onclick=\"window.location = '?lang=de';\" value=\"DE\" />"; ?>
                <fieldset>
                    <?php
                    echo "<legend>" . $legend["database_name"] . ": " . $_SESSION["dbname"] . "</legend>";
                    ?>
                    <select id="select-mysql-table">
                        <?php
                        echo "<option data-id=\"0\" value = \"\" >" . $option_select["select_table"] . "</option>";

                        for ($i = 1; $i <= count($arr_table_names); $i++) {
                            if ($i == $optid) {
                                echo "<option data-id=\"" . $i . "\" value=\"" . $arr_table_names[$i - 1] . "\" selected=\"selected\">" . $arr_table_names[$i - 1] . "</option>\n";
                            } else {
                                echo "<option data-id=\"" . $i . "\" value=\"" . $arr_table_names[$i - 1] . "\">" . $arr_table_names[$i - 1] . "</option>\n";
                            }
                        }
                        ?>
                    </select> 
                    <?php
                    echo "<img src=\"../css/garbage.png\" alt=\"truncate table\" title=\"" . $info["trash"] . "\" id=\"garbage\" />";
                    ?>
                </fieldset>



                <div id="upload-download-block">

                    <div id="div_radio_btn">
                        <?php
                        if ($dir == "dbtocsv") {

                            echo"<label for=\"csv_db\">
                            <input type=\"radio\" id=\"csv_db\" name=\"radio_inp_exp\" value=\"csvtodb\">CSV --- > DB
                                <img src=\"../css/info.png\" title=\"" . $info["info_1"] . "\" alt=\"info\" class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\" />
                        </label>
                        -
                        <br />
                        <label for=\"db_csv\">
                            <input type=\"radio\" id=\"db_csv\" checked=\"checked\" name=\"radio_inp_exp\" value=\"dbtocsv\">DB --- > CSV
                        </label>";
                        } else {
                            echo"<label for=\"csv_db\">
                            <input type=\"radio\" id=\"csv_db\" checked=\"checked\" name=\"radio_inp_exp\"  value=\"csvtodb\">CSV --- > DB
                                <img src=\"../css/info.png\" title=\"" . $info["info_1"] . "\" alt=\"info\" class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\" />
                        </label>
                        -
                        <br />
                        <label for=\"db_csv\">
                            <input type=\"radio\" id=\"db_csv\" name=\"radio_inp_exp\" value=\"dbtocsv\">DB --- > CSV
                        </label>";
                        }
                        ?>
                    </div>

                    <div id="fileuploader" class="csvtodb"></div> 
                    <?php
                    echo "<p id=\"uploaded-file-name\" class=\"csvtodb\">" . $legend["uploaded"] . "&nbsp;<span><?php echo $fnup; ?></span></p>
                   
                        <fieldset>
                        <legend>" . $legend["csv_settings"] . "</legend>

                        <label for=\"autoconf\">";

                    if ($autoconf) {
                        echo "<input type=\"checkbox\" name=\"autoconf\" id=\"autoconf\" checked=\"checked\" />" . $label["autosetting"] . "</label>";
                    } else {
                        echo "<input type=\"checkbox\" name=\"autoconf\" id=\"autoconf\" />" . $label["autosetting"] . "</label>";
                    }


                    echo"<label for=\"separator\">" . $label["separator"] . "</label>

                    <select name=\"separator\" id=\"separator\" class=\"csvconf\" disabled=\"disabled\">";


                    foreach ($separator_list as $key => $value) {
                        if ($key == $sep) {
                            echo "<option selected=\"selected\" value=\"" . $key . "\">" . $value[0] . "</option>\n";
                        } else {
                            echo "<option value=\"" . $key . "\">" . $value[0] . "</option>\n";
                        }
                    }
                    ?>

                    </select>
                    <?php
                    echo"<label for=\"enclosure\">" . $label["enclosure"] . "</label>
                    <select name=\"enclosure\" id=\"enclosure\" class=\"csvconf\" disabled=\"disabled\">";

                    foreach ($enclosure_list as $key => $value) {
                        if ($key == $encl) {
                            echo "<option selected=\"selected\" value=\"" . $key . "\">" . $value[0] . "</option>\n";
                        } else {
                            echo "<option value=\"" . $key . "\">" . $value[0] . "</option>\n";
                        }
                    }

                    echo"</select>
                    <span class=\"csvtodb\">

                        <label for=\"charset\">" . $label["charset"] . "</label>
                        <select name=\"charset\" id=\"charset\" class=\"csvconf\" disabled=\"disabled\">
                            <option value=\"UTF-8\">UTF-8</option>";

                    if ($chset == "ISO-8859-1") {
                        echo "<option selected=\"selected\" value=\"ISO-8859-1\" >ISO-8859-1</option>";
                    } else {
                        echo "<option value=\"ISO-8859-1\">ISO-8859-1</option>";
                    }

                    echo"</select>
                    <img src=\"../css/info.png\" title=\"".$info["info_2"]."\" alt=\"info\" class=\"info-icon\" onclick=\"alert(this.getAttribute('title'))\" />
                    </span>
                    <p></p>
                    <span class=\"label dbtocsv\">".$label["charset"]." UTF-8</span>
                    </fieldset>";
                    ?>

                </div>
                <?php
                echo"<button id=\"reinit-btn\">" . $button["reset"] . "</button>
                <button id=\"logout-btn\" onclick=\"window.location.href = 'logout.php?lang=" . $language . "'\">" . $button["logout"] . "</button>";
                ?>
            </div>

            <div id="sidebar-wrapper">

                <span id="table-title" class="table-title">
                    <fieldset>
                        <?php
                        echo "<legend>" . $legend["preview_table"] . "</legend>";


                        if ($show == "csv") {

                            echo"
                            
<input type=\"radio\" id=\"prevcsv\" class=\"csvtodb\" name=\"radiopreview\" value=\"csv\" checked=\"checked\" ><span class=\"csvtodb\">CSV</span>

                                <input type=\"radio\" id=\"prevdb\" name=\"radiopreview\" value=\"db\" >DB
                       ";
                        } else {
                            echo"
                           
                                <input type=\"radio\" id=\"prevcsv\" class=\"csvtodb\" name=\"radiopreview\" value=\"csv\" ><span class=\"csvtodb\">CSV</span>
                            
                                <input type=\"radio\" id=\"prevdb\" name=\"radiopreview\" value=\"db\" checked=\"checked\" >DB
                       ";
                        }
                        ?>

                </span>

                </fieldset>
                <div id="info-msg"></div>

                <div id="sidebar">
                    <img src="../css/ajax-loader.png" alt="BITTE WAIT" id="wait-icon" />
                    <!--   preview tables   -->



                    <div id="div-preview-table" class="dragscroll">     

                        <table id="mytable"></table>
                    </div>

                </div>
            </div>
            <div id="sidenotes-wrapper">

                <div id="sidenotes">

                    <table id="configuration-table">

                    </table>

                </div>

            </div>
            <!-- <div id="debug-div"></div>-->
            <button id="show_diff_file">Fehler-Datei</button>
        </div>

        <div id="dialog" title="Basic dialog">
            <!--<p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p> -->
        </div>
        <?php
        echo "<input type=\"hidden\" id=\"language\" value=\"" . $language . "\" />";
        ?>
    </body>
</html>
