<?php

ini_set('auto_detect_line_endings', true);

require_once '../pages/debug.php';

class csv {

    private $Filename;
    private $Charset;
    private $Separator;
    private $Enclosure;
    private $Debug;
    private $ArrCSV;
    private $ErrorStr;
    private $SeparatorList;
    private $EnclosureList;
    private $Limit;
    private $UseLimit;
    private $Start;

    // $limit = "" means unlimited 
    public function __construct($filename, $separator, $enclosure, $charset = "UTF-8", $start = 0, $limit = "") {
        $this->Charset = $charset;
        $this->Filename = $filename;
        $this->Separator = ($separator == "\t") ? chr(9) : $separator;
        $this->Enclosure = $enclosure;
        $this->ArrCSV = array("tabheader" => array(), "tabcontent" => array());
        $this->Limit = intval($limit);
        $this->UseLimit = ($limit=="") ? 0 : 1;
        $this->Start = intval($start);
    }

    private function setArrCSV() {
        $rowcount = 0;

        if ($fp = fopen($this->Filename, "r")) {

            $arr_row = array();

            $arr_row = fgetcsv($fp, 0, $this->Separator, $this->Enclosure);  //first row are titles

            foreach ($arr_row as $title) {
                $this->ArrCSV["tabheader"][] = array("title" => $title);
            }

            while (($arr_row = fgetcsv($fp, 0, $this->Separator, $this->Enclosure)) !== false) { //false means end of file for exemple
            
                if ($arr_row === null) {  //an error occurred 
                    debug("1");
                    break;
                }

                if (count($arr_row) == 1 && $arr_row[0] === null) { //an empty row
                    debug("2");
                    continue;
                }

                $rowcount++;

                if ($rowcount <= $this->Start) {
                    debug("3");
                    continue;
                }

                if ($this->UseLimit && ($rowcount > $this->Start + $this->Limit)) {
                    debug("4");
                    continue;
                }

                // $this->ArrCSV["data"][] = implode("-", $arr_row);
                //$this->ArrCSV["data"][] = array_combine($this->ArrCSV["tabheader"], $arr_row);
                $this->ArrCSV["tabcontent"][] = $arr_row;
                debug($arr_row);
                
            }//while close

            $this->ArrCSV["numrows"] = $rowcount;
            fclose($fp);
            //debug($this->ArrCSV["tabcontent"]);
        }else{
            debug("impossibile aprire il file: ".$this->Filename);
        }
    }

    private function converter() {

        if ($this->Charset != "UTF-8") {
            array_walk_recursive($this->ArrCSV, function(&$item) {
//$item = mb_convert_encoding($item, "ISO-8859-1", 'UTF-8');
                $item = iconv($this->Charset, 'UTF-8', $item);
            });
        }
    }

    public function getArrCsv() {
        $this->setArrCSV();
        $this->converter();
        return $this->ArrCSV;
    }

    private function getKey($arr, $val_in_arr) {

        if ($val_in_arr == chr(9)) {
            $val_in_arr = "\t";
        }
        $retkey = null;
        foreach ($arr as $key => $value) {
            if ($arr[$key][1] == $val_in_arr) {
                $retkey = $key;
                break;
            }
        }
        return $retkey;
    }

    public function createCsv($firstrow, $otherrow) {
        if ($fp = fopen($this->Filename, "w")) {

            fputcsv($fp, $firstrow, $this->Separator, $this->Enclosure);

            foreach ($otherrow as $row) {
                fputcsv($fp, $row, $this->Separator, $this->Enclosure);
            }

            fclose($fp);
            return "success";
        }
        return "error";
    }

    private function fileOpen($filename) {
        $fp = null;

        try {

            if (!file_exists($filename)) {
                throw new Exception('File not found.');
            }

            $fp = fopen($filename, "r");
            if (!$fp) {
                throw new Exception('File open failed.');
            }
        } catch (Exception $e) {
            $this->ErrorStr = "Exeption on fileOpen(" . $filename . ")";
        }
        return $fp;
    }

    public function test() {
        $retarr = array();
        $retarr["chset"] = $this->Charset;
        $retarr["sep"] = $this->Separator;
        $retarr["encl"] = $this->Enclosure;

        return $retarr;
    }

}
