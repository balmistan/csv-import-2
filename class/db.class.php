<?php

require_once '../pages/debug.php';

class db {

    private $conn;
    private $dbname;
    private $hostname;
    private $user;
    private $password;
    private $port;
    private $debug;
    private $error;

    public function __construct($debug = false) {
        $this->conn = NULL;
        $this->dbname = (isset($_SESSION["dbname"]) ? $_SESSION["dbname"] : "");
        $this->hostname = (isset($_SESSION["hostname"]) ? $_SESSION["hostname"] : "");
        $this->user = (isset($_SESSION["user"]) ? $_SESSION["user"] : "");
        $this->password = (isset($_SESSION["password"]) ? $_SESSION["password"] : "");
        $this->port = (isset($_SESSION["port"]) ? $_SESSION["port"] : "");

        $this->debug = $debug;


        $this->dbConnect();
    }

    private function dbConnect() {
        /* Connect to an ODBC database using driver invocation */
        $dsn = 'mysql:host=' . $this->hostname . ';port=' . $this->port . ';charset=UTF8;';

        try {
            $this->conn = new PDO($dsn, $this->user, $this->password, array(
                PDO::ATTR_PERSISTENT => true
            ));

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->setDB($this->dbname);
        } catch (PDOException $e) {
            $this->conn = NULL;
            $this->error = $e->getMessage();
            if ($this->debug) {
                echo 'ERROR: ' . $this->error;
            }
        }
    }

    public function isLogged() {

        if ($this->conn == NULL) {
            return false;
        }
        return true;
    }

    public function getTableNames() {

        $nameslist = array();

        $sql = $this->conn->prepare("SHOW TABLES");
        $sql->execute();

        while ($res = $sql->fetchColumn()) {
            array_push($nameslist, $res);
        }

        return $nameslist;
    }

    /**
     * This method before performing query, check if table name exists. This protects from mysql-Injection.
     * @param type $table_name
     */
    public function getColumnsName($table_name) {

        if (!in_array($table_name, $this->getTableNames())) {
            return array();
        }

        $columnslist = array();

        $sql = $this->conn->prepare("SHOW COLUMNS FROM " . $table_name);
        $sql->execute();

        while ($res = $sql->fetchColumn()) {
            //array_push($columnslist, $res);
            $columnslist[] = array("title" => $res, "data" => $res);
        }

        return $columnslist;
    }

    public function getError() {
        return $this->error;
    }

    public function insert($table_name, $config_assoc, $csvarray) {
        $issue = array();

        if (count($csvarray["tabcontent"]) == 0) {
            $issue = array(0, "Keine data zu laden");   //0=msg from my function
        } else {
            $str_debug = "";

            $columns = "";

            $arr_csv_index = array();

            $str_values = "";

            for ($i = 0; $i < count($config_assoc); $i++) {
                $columns .= $config_assoc[$i]["dbcolumnname"] . ",";
                array_push($arr_csv_index, $config_assoc[$i]["csvindex"]);
                $str_values .= "?,";
            }

            //remove last comma

            $columns = substr_replace($columns, "", strrpos($columns, ","));
            $str_values = substr_replace($str_values, "", strrpos($str_values, ","));

            $query = "INSERT INTO " . $table_name . " (" . $columns . ") VALUES (" . $str_values . ")";

            // $str_debug .= "\$sql = \$this->conn->prepare(" . $query . ");\n";



            try {

                $this->conn->beginTransaction();

                $sql = $this->conn->prepare($query);

                foreach ($csvarray["tabcontent"] as $arrval) {
                    $count = 1;
                    foreach ($arr_csv_index as $csv_index) {
                        $sql->bindValue($count, $arrval[$csv_index]);
                        //   $str_debug .= "\$sql->bindValue(" . $count . ", '" . $arrval[$csv_index] . "');\n<br />";
                        $count++;
                    }
                    $sql->execute();
                    //       $str_debug .= "\$sql->execute();\n<br />";
                }


                $issue = array(1, $this->conn->commit(), $str_debug);   //1= success
            } catch (PDOException $er) {
                $issue = array(2, $er->getMessage(), $str_debug);      //2= Error + msg from DBMS  
                $this->conn->rollBack();
            }
        }
        return $issue;
    }

    public function getContentTable($table_name, $columnnames = "*", $start = 0, $numrows = "") {

        $limit = ($numrows == "") ? "" : " limit " . intval($start) . "," . $numrows;

        $temp = array();
        $arr_issue = array();

        //get columns names:

        $arr_issue["tabheader"] = $this->getColumnsName($table_name);

        try {
            $sql = $this->conn->prepare("SELECT " . $columnnames . " FROM " . $table_name . $limit);
            $sql->execute();


            //    $temp = $this->getColumnsName($table_name);
            //    $temp["data"] = array();

            while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {

                $temp[] = $row;
            }
        } catch (PDOException $er) {
            
        }

        $arr_issue["tabcontent"] = $temp;

        //get the total number of rows

        try {
            $sql = $this->conn->prepare("SELECT COUNT(*) FROM " . $table_name);
            $sql->execute();
            $arr_issue["numrows"] = $sql->fetchColumn(0);
        } catch (PDOException $er) {
            
        }

        return $arr_issue;
    }

    public function truncateTable($tablename) {
        //Our SQL statement. This will empty / truncate the table $tablename
        $sql = "TRUNCATE TABLE " . $tablename;

//Prepare the SQL query.
        $statement = $this->conn->prepare($sql);

//Execute the statement.
        $statement->execute();
    }

    public function setDB($dbname) {
        $dbname = addslashes($dbname);
        try {
            $this->conn->exec("USE " . $dbname);
            $this->dbname = $dbname;
            $issue = true;
        } catch (PDOException $e) {
            $issue = false;
            $this->error = $e->getMessage();
            if ($this->debug) {
                echo 'ERROR: ' . $this->error;
            }
        }
        return $issue;
    }

    public function dbcheck($tablename, $arr_assoc, $arr_csv, $start = 0, $numrows = "") {

        $columnames = "";
        $assoc_key_index = array();

//debug($arr_csv);

        foreach ($arr_assoc as $value) {
            $columnames .= $value["dbcolumnname"] . ",";
            $assoc_key_index[$value["dbcolumnname"]] = $value["csvindex"];  //used for reolacing names MySql column with csv column index.
        }

        // debug($assoc_key_index);
        //remove last comma

        $columnames = substr($columnames, 0, strlen($columnames) - 1);  //for query
        // debug($columnames);

        $arr_issue = $this->getContentTable($tablename, $columnames, $start, $numrows);

        //debug($arr_assoc);

        $newarray = array();

        for ($i = 0; $i < count($arr_issue["tabcontent"], 0); $i++) {
            $newarray[$i] = array();
            foreach ($arr_issue["tabcontent"][$i] as $key => $value) {
                $newarray[$i][$assoc_key_index[$key]] = $value;
            }
        }



        $count = 0;
        
        $arr_check = array();

        while (isset($arr_csv["tabcontent"][$count]) && isset($newarray[$count])) {
            /*
              debug("csv:");
              debug( array_intersect_key($arr_csv["tabcontent"][$count], $newarray[$count]) );
              debug("MySql:");
              debug($newarray[$count]);
             */

            if (count($result = array_diff_assoc(
                            array_intersect_key($arr_csv["tabcontent"][$count], $newarray[$count]), //CSV
                            $newarray[$count]     //Mysql Table
                    ))) {
                
                $arr_check[$count+1] = array();      //index is num row Mysql. es is always >=1
                
                $arr_check[$count+1]["csv"] = array_intersect_key($arr_csv["tabcontent"][$count], $newarray[$count]);
                
                $arr_check[$count+1]["mysql"] = $newarray[$count];
                
                $arr_check[$count+1]["mysqlerr"] = $result;
                
                /*
                debug("Row: " . $count+1);
                debug("Error:");
                debug("csv:");
                debug(array_intersect_key($arr_csv["tabcontent"][$count], $newarray[$count]));
                debug("MySql:");
                debug($newarray[$count]);
                debug("diff:");
                debug($result);
                */
            }

            $count++;
        }
        return $arr_check;
    }

}
