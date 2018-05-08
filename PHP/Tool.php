<?php
/**
 * 工具类
 *
 */
class Tools{
    
    /**
     * 给图片打水印
     * @param string $imagePath 图片文件的路径      
     * @param string $mime 图片文件的MIME类型
     * 
     * @return boolean $success 成功返回true，否则返回false
     */
    public static function addWaterMask($imagePath, $mime, $maskPath, $maskPos = 'left-bottom') {
        switch ($mime) {
            case 'image/jpeg':
                $dst_im = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $dst_im = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
            default: 
                $dst_im = false;
                break;
        }

        // 图像创建失败或遇到不支持的格式
        if ($dst_im == false) {            
            return false;
        }

        $src_im = imagecreatefrompng($maskPath);
        
        $src_w = imagesx($src_im);
        $src_h = imagesy($src_im);       
        $dst_width = imagesx($dst_im);
        $dst_height = imagesy($dst_im);
        
        // 默认将水印放在图片的左下角
        switch ($maskPos) {
            case 'right-bottom':
                $dst_x = $dst_width - $src_w;
                $dst_y = $dst_height - $src_h;
                break;
            default:
                $dst_x = 0;
                $dst_y = $dst_height - $src_h;
                break;
        }
        imagesavealpha($dst_im, true);
        // 将水印平滑插入至目标区域
        imagecopyresampled($dst_im, $src_im, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $src_w, $src_h);

        switch ($mime) {
            case 'image/jpeg':
                $success = imagejpeg($dst_im, $imagePath, 100);
                break;
            case 'image/png':
                $success = imagepng($dst_im, $imagePath);
                break;
            case 'image/gif':
            default:
                $success = false;
                break;            
        }   
        imagedestroy($src_im);
        imagedestroy($dst_im);
        return $success;
    }
    
    public static function buildImageVerify($length=4, $type='png', $width=48, $height=22, $verifyName='verify', $filename='') {
        $string = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $randval ='';
        $max = strlen($string)-1;
        for($i=0;$i<$length;$i++){
            $key = mt_rand(0, $max);
            $randval .= $string{$key};
        }
        $session = Phalcon\Di::getDefault()->get('session');
        $session->set($verifyName, md5( strtolower($randval) ));
        $width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($width, $height);
        } else {
            $im = imagecreate($width, $height);
        }
        $r = Array(225, 255, 255, 223);
        $g = Array(225, 236, 237, 255);
        $b = Array(225, 236, 166, 125);
        $key = mt_rand(0, 3);

        $backColor = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        // 干扰
        for ($i = 0; $i < 5; $i++) {
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $stringColor);
        }
        for ($i = 0; $i < 40; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $stringColor);
        }
        for ($i = 0; $i < $length; $i++) {
            imagestring($im, 5, $i * 10 + 5, mt_rand(1, 8), $randval{$i}, $stringColor);
        }
        ob_clean();
        header("Content-type: image/" . $type);
        $ImageFun = 'image' . $type;
        if (empty($filename)) {
            $ImageFun($im);
        } else {
            $ImageFun($im, $filename);
        }
        imagedestroy($im);
    }
    /**
     * 去除数组中的空值项
     * @param type $arr
     * @return type
     */
    public static function formatArray(&$arr) {
        foreach ($arr as $key=>$value){
            if( empty($value) || is_array($value) && empty(self::formatArray($arr[$key]) ) ){
                unset($arr[$key]);
            }
        }
        return $arr;
    }
    
    public static function outputFiles( $filename,$files ) {
        $zip_file = self::zipFiles($files);
        if(empty($zip_file)){
            return FALSE;
        }
        ob_clean();
        header("Cache-Control: public"); 
        header("Content-Description: File Transfer"); 
        header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
        header("Content-Type: application/zip"); //zip格式的
        header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
        header('Content-Length: '. filesize($zip_file)); //告诉浏览器，文件大小   
        @readfile($zip_file);
        $files[] = $zip_file;
        foreach($files as $file){
            @unlink($file);
        }
    }
    
    public static function zipFiles($files,$zip_path='/tmp/') {
        $zip = new ZipArchive();
        $zipfile = tempnam($zip_path, 'zip');
        if ($zip->open($zipfile) === TRUE) {
            foreach($files as $file){
                $zip->addFile($file, basename($file));
            }
            $zip->close();
            return $zipfile;
        }
        return FALSE;
    }
    /**
     * 输出文件到浏览器
     * @param type $filepath 文件绝对路径，路径中不能有中文，否则可能会异常
     * @param type $filename 输出到浏览器的文件名
     */
    public static function outputFile($filepath,$filename) {
        $filepath = realpath($filepath);
        $file_extension = strtolower(substr(strrchr($filepath,"."),1));
        $filename .= '.'.$file_extension;
        
        switch ($file_extension) {
            case "pdf": $ctype="application/pdf"; break;
            case "exe": $ctype="application/octet-stream"; break;
            case "zip": $ctype="application/zip"; break;
            case "doc": $ctype="application/msword"; break;
            case "xls": $ctype="application/vnd.ms-excel"; break;
            case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "jpe":
            case "jpeg":
            case "jpg": $ctype="image/jpg"; break;
            default: $ctype="application/force-download";
        }

        if (!file_exists($filepath)) {
            die("NO FILE HERE");
        }
        ob_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=\"".$filename."\";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".@filesize($filepath));
        set_time_limit(0);
        flush();
        if( ! @readfile($filepath) ){
            die("File not found.");
        }
        exit();
    }

    /*
     *进行图片安全判断并上传（jpg,gif,png）
     *@param $file  $_FILES['']获取的值 ; $path 图片生成的物理路径（包含图片名称）
     *return 上传成功 true ;  图片类型异常 0 ;上传失败 false;
     */
    public static function image_save($file, $path)
    {
        if ($file->getType() == "image/gif") {
            @$im = imagecreatefromgif($file->getTempName());
            if ($im) {
                $sign = imagegif($im, $path);
            } else {
                return 0;
            }
        } elseif ($file->getType() == "image/png" || $file->getType() == "image/x-png") {
            @$im = imagecreatefrompng($file->getTempName());
            if ($im) {
                imagesavealpha($im, true);
                $sign = imagepng($im, $path);
            } else {
                return 0;
            }
        } else {
            @$im = imagecreatefromjpeg($file->getTempName());
            if ($im) {
                $sign = imagejpeg($im, $path, 100);
            } else {
                return 0;
            }
        }
        return $sign;
    }
    
    /*
     * @param $low 安全别级低
     */
    public static function clean_xss(&$string, $low = true)
    {
        if (!is_array($string)) {
            $string = trim($string);
            $string = strip_tags($string);
            $string = htmlspecialchars($string);
            if ($low) {
                return True;
            }
            $string = str_replace(array('"', "\\", "'", "/", "..", "../", "./", "//"), '', $string);
            $no = '/%0[0-8bcef]/';
            $string = preg_replace($no, '', $string);
            $no = '/%1[0-9a-f]/';
            $string = preg_replace($no, '', $string);
            $no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
            $string = preg_replace($no, '', $string);
            return True;
        }
        $keys = array_keys($string);
        foreach ($keys as $key) {
            self::clean_xss($string [$key]);
        }
    }
}
