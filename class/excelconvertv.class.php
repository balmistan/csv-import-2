<?php

class excelconvert {

    private $ArrColumns;
    private $FirstRowtables;    //Prima riga excel per ciascuna tabella
    private $objPHPExcel;
    private $LastRow;
    private $ProductIds;   //Elenco id prodotti inseriti in tabelle (in ordine di inserimento)
    private $logger;

    public function __construct($fileout = "test.xlsx", $filein = "template.xlsx") {
        if (!file_exists($filein))
            die("Il file " . $filein . " non esiste!");
        //$this->objPHPExcel = new PHPExcel();
        $this->ArrColumns = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $this->FirstRowtables = array(4, 38, 72, 106, 140, 174, 208, 242, 276, 310); //Righe posizioni tabelle excel 
        $this->ProductIds = array();
        $this->objPHPExcel = PHPExcel_IOFactory::createReaderForFile($filein);
        $this->objPHPExcel = $this->objPHPExcel->load($filein); // Template Sheet
        $this->objPHPExcel->setActiveSheetIndex(0);

        $this->LastRow = 1;     // inizio dalla riga 1 a compilare l' excel
        //$this->logger = new logger("excel.log");
        //Setto larghezza colonne
        // $this->objPHPExcel->getActiveSheet()->getColumnDimension('A:K')->setWidth(25);
        //Allineamento testo celle
        //$this->objPHPExcel->getDefaultStyle()
        //       ->getAlignment()
        //       ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    }

    public function setHeader($sedename, $reg_ue) {
        $this->objPHPExcel->getActiveSheet()->
                setCellValue("A2", $sedename)->
                setCellValue("G2", $reg_ue);
    }

    public function setProductsName($arr_products, $arr_umis) { //setta nomi prodotti e unità di misura
        for ($i = 0; $i < count($arr_products); $i++) {
            $eq = 0; // Mi serve per settare indici inesistenti sui prodotti

            $this->ProductIds[$i] = array(
                ($arr_products[$i][0]["idproduct"] == 0) ? "a" . ($eq++) : $arr_products[$i][0]["idproduct"],
                ($arr_products[$i][1]["idproduct"] == 0) ? "a" . ($eq++) : $arr_products[$i][1]["idproduct"],
                ($arr_products[$i][2]["idproduct"] == 0) ? "a" . ($eq++) : $arr_products[$i][2]["idproduct"]);

            $obj = $this->objPHPExcel->setActiveSheetIndex($i);
            //nomi prodotti
            $obj->setCellValue("D" . $this->FirstRowtables[0], $arr_products[$i][0]["nameproduct"])->
                    setCellValue("H" . $this->FirstRowtables[0], $arr_products[$i][1]["nameproduct"])->
                    setCellValue("L" . $this->FirstRowtables[0], $arr_products[$i][2]["nameproduct"]);
            //unità di misura
            $obj->setCellValue("D" . (2 + $this->FirstRowtables[0]), $arr_umis[$arr_products[$i][0]["idproduct"]]);
            if (isset($arr_umis[$arr_products[$i][1]["idproduct"]]))
                $obj->setCellValue("H" . (2 + $this->FirstRowtables[0]), $arr_umis[$arr_products[$i][1]["idproduct"]]);
            if (isset($arr_umis[$arr_products[$i][2]["idproduct"]]))
                $obj->setCellValue("L" . (2 + $this->FirstRowtables[0]), $arr_umis[$arr_products[$i][2]["idproduct"]]);
        }
    }

    public function addPageContent($arr_content, $page) { //Se page=1 compila ad esempio tutte le pagine 1 dei registri
        //Il ciclo riempe le prime righe di ogni tabella della pagina $page, poi le seconde e così via...
        $startrow = 2 + $this->FirstRowtables[$page];
        for ($k = 0; $k < 25; $k++) {
            for ($i = 0; $i < count($this->ProductIds); $i++) {

                $objk = $this->objPHPExcel->setActiveSheetIndex($i);

                if ($arr_content[$k][intval($this->ProductIds[$i][0])]["carico"] != "" ||
                        $arr_content[$k][intval($this->ProductIds[$i][1])]["carico"] != "" ||
                        $arr_content[$k][intval($this->ProductIds[$i][2])]["carico"] != "") {

                    for ($numsheet = 0; ($numsheet < $this->objPHPExcel->getSheetCount()); $numsheet++)
                        $this->objPHPExcel->setActiveSheetIndex($numsheet)->getStyle('B' . ($k + $startrow) . ':P' . ($k + $startrow))->getFont()->getColor()->setRGB('FF0000');
                }


                $objk->
                        //Registro $i k-esima riga
                        setCellValue("B" . ($k + $startrow), $arr_content[$k]["date"])->
                        setCellValue("C" . ($k + $startrow), $arr_content[$k]["numrif"])->
                        setCellValue("P" . ($k + $startrow), $arr_content[$k]["numindig"]);



                //carico
                if ($arr_content[$k][intval($this->ProductIds[$i][0])]["carico"] != "")
                    $objk->setCellValue("E" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][0])]["carico"]);
                if ($arr_content[$k][intval($this->ProductIds[$i][1])]["carico"] != "")
                    $objk->setCellValue("I" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][1])]["carico"]);
                if ($arr_content[$k][intval($this->ProductIds[$i][2])]["carico"] != "")
                    $objk->setCellValue("M" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][2])]["carico"]);

                //scarico
                if ($arr_content[$k][intval($this->ProductIds[$i][0])]["scarico"] != "")
                    $objk->setCellValue("F" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][0])]["scarico"]);
                if ($arr_content[$k][intval($this->ProductIds[$i][1])]["scarico"] != "")
                    $objk->setCellValue("J" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][1])]["scarico"]);
                if ($arr_content[$k][intval($this->ProductIds[$i][2])]["scarico"] != "")
                    $objk->setCellValue("N" . ($k + $startrow), $arr_content[$k][intval($this->ProductIds[$i][2])]["scarico"]);
            }
        }
    }

    public function Output($fileout) {

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

        //$objWriter->save($fileout);

        header("Content-Disposition: attachment;filename=" . $fileout);

        //header("Content-Type:   application/vnd.ms-excel; charset=utf-8");

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

        ob_end_clean();
        
        $filePath = "../Personal/".$fileout;
        $objWriter->save($filePath);
        readfile($filePath);
        unlink($filePath);
        
       // $objWriter->save('php://output');
    }

    public function Save($fileout) {
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        ob_end_clean();
        $objWriter->save($fileout);
    }

}

//close class
?>