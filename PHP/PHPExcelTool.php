class ExcelTool{
    public static function downloadXlsx($title,$tablehead,$data,$sub_title=[]) {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Adminstrator")
                    ->setLastModifiedBy("Adminstrator")
                    ->setTitle($title);
        //固定第一行
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->freezePane('A2');

        // Add title
        $cell = $objPHPExcel->setActiveSheetIndex(0);
        $line = 1;
        foreach ($tablehead as $_key=>$value){
            $cell->setCellValue(chr($_key+65).$line, $value);
        }
        // add content, UTF-8
        foreach($data as $key=>$item){
            if( !empty($sub_title) && $key!=0 ){
                $line +=2;
                foreach ($tablehead as $_key=>$value){
                    $cell->setCellValue(chr($_key+65).$line, $value);
                }
            }
            $childs = $item['child'];
            unset($item['child']);
            $line++;
            foreach ($item as $k=>$kv){
                $cell->setCellValue(chr($k+65).$line, $kv);
            }
            if( !empty($childs) ){
                $line ++;
                foreach ($sub_title as $_sk=>$_sv){
                    $cell->setCellValue(chr($_sk+65).$line, $_sv);
                }
                foreach ($childs as $ck=>$child){
                    $line ++;
                    foreach( $child as $_ck=>$cv ){
                        $cell->setCellValue(chr($_ck+65).$line, $cv);
                    }
                }
            }
        }
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($title);


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        ob_clean();
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$title.'.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    /**
     * 将数据保存至excel文件
     * @param type $file_path 保存文件路径
     * @param string $title 表名称
     * @param type $tablehead 表头
     * @param type $data 内容
     * @return boolean
     */
    public static function saveToFile( $file_path,$title,$tablehead,$data ) {
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Adminstrator")
                    ->setLastModifiedBy("Adminstrator")
                    ->setTitle($title);
        //固定第一行
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->freezePane('A2');

        // Add title
        $cell = $objPHPExcel->setActiveSheetIndex(0);
        foreach ($tablehead as $key=>$value){
            $cell->setCellValue(chr($key+65).'1', $value);
        }
        
        // add content, UTF-8
        foreach($data as $key=>$item){
            foreach ($item as $k=>$kv){
                $cell->setCellValue(chr($k+65).(2+$key), $kv);
            }
        }
        
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($title);


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file_path);
        return TRUE;
    }
}
