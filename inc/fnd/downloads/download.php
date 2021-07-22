<?php
if(!empty($_GET['file'])){
    $fileName = $_GET['file'];
    $file = dirname(__FILE__) . '/' . $fileName;

    if(!file_exists($file)){ // file does not exist
        die('file not found');
    } else {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");

        readfile($file);
        exit;
    }
}else{
    header("Location:" . $core->getWebUrl());
}