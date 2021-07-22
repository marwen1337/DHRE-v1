<?php

if(!empty($_GET['time'])){
    $stmt = $core->getPDO()->prepare("SELECT img_token, img_file FROM images WHERE img_creator = :uid AND img_created <= :time");
    $stmt->execute(array(":uid" => $core->getUserID(), ":time" => time() - $_GET['time']));
    foreach ($stmt->fetchAll() as $item){
        if(unlink($item['img_file'])){
            $stmt = $core->getPDO()->prepare("DELETE FROM images WHERE img_token = :token");
            $stmt->execute(array(":token" => $item['img_token']));
            print_r("Deleted " . $item['img_token'] . "<br>");
        }else{
            print_r("Error while deleting " . $item['img_token'] . "<br>");
        }
    }
}else{
    print_r("Please enter ?time=[older than the seconds you type] (E.g. older than: 1hour: 3600, 1day: 86400, 1week: 604800 and so on)");
}