<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of exelconvert
 *
 * @author daniele
 */


class excelconvert {
    
    
private $ArrColumns;  
private $objPHPExcel;
private $col_start;
private $row_start;

    public function __construct($arr_assoc, $arr_check, $fileout = "test.xls", $filein = "template.xls") {
        
        if (!file_exists($filein))
            die("The file " . $filein . " does not exist!");
        
        $this->ArrColumns = array('A','B','C','D','E','F','G','H','I','J','K','L',
            'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO',
            'AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $this->row_start = 3;
        $this ->col_start = 2;
        
        $this->objPHPExcel = PHPExcel_IOFactory::createReaderForFile($filein);
        $this->objPHPExcel = $this->objPHPExcel->load($filein); // Template Sheet
        $objk = $this->objPHPExcel->setActiveSheetIndex(0);
        
        //set columns title
        
        $startcolumn_arr_index = $this->col_start;
        $startrow = $this->row_start;
        
        foreach($arr_assoc as $key=>$arr_value){  
            $objk->
                        //column names setting
                        setCellValue($this->ArrColumns[$startcolumn_arr_index] . "2", $arr_value["dbcolumnname"])->
                        setCellValue($this->ArrColumns[$startcolumn_arr_index+1] . "2", $arr_value["csvcolumnname"]);
                        $startcolumn_arr_index +=3;
        }
        
        //content table setting
        
        $startrow = $this->row_start;
        
        
        foreach($arr_check as $numrow =>$arr_values){   //row by row
            
            $objk->setCellValue("A" . $startrow, $numrow);
            $startcolumn_arr_index = $this ->col_start;
            foreach($arr_values["mysql"] as $value){       
                $objk->setCellValue($this->ArrColumns[$startcolumn_arr_index] . $startrow, $value);     //Mysql value
                $startcolumn_arr_index +=3;
            }
           
            $startcolumn_arr_index = $this ->col_start +1;
            
            foreach($arr_values["csv"] as $value){       
                $objk->setCellValue($this->ArrColumns[$startcolumn_arr_index] . $startrow, $value);     //Mysql value
                $startcolumn_arr_index +=3;
            }
            
            
            $startrow++;
        }
        
        
        
        /*
        
        foreach($arr_check as $key=>$arr_value){ //row by row
            setCellValue("A" . $startrow, $key)->     //column number
            setCellValue($this->ArrColumns[$startcolumn_arr_index] . $startrow, $arr_value["mysql"])->     //Mysql value
             setCellValue($this->ArrColumns[$startcolumn_arr_index+1] . $startrow, $arr_value["csv"]);    //csv value  
            
            $startcolumn_arr_index +=3;
            $startrow++;
        }
        */
        
    }
    
    
    
     public function Output($fileout) {

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

        //$objWriter->save($fileout);

        header("Content-Disposition: attachment;filename=" . $fileout);

        //header("Content-Type:   application/vnd.ms-excel; charset=utf-8");

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

        ob_end_clean();
        
        $filePath = $fileout;
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

//class close