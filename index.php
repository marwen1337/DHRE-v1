<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 08.08.2019
 * Time: 15:31
 */

require_once 'inc/AppCore.php';
require_once 'inc/Router.php';

$core = new AppCore(dirname(__FILE__) . "/config/default_config.php", dirname(__FILE__));

if(!empty($_GET['lang']) && $_GET['lang'] != $_COOKIE['lang']){
    if(!empty($core->languages()[$_GET['lang']])){
        setcookie("lang", $_GET['lang'], time() + 3600 * 24 * 365 * 10);
        header("Refresh: 0");
        exit;
    }
}

$args = explode("/", str_replace($core->getWebUrl(), "", strtok($core->getUrl(), "?")));

if($args[0] == "app" && $args[1] != "autologin" && !$core->isUserLoggedIn() && !empty($_COOKIE['autologin_token']) && !empty($_COOKIE['autologin_pass'])){
    header("Location:" . $core->getWebUrl() . "app/autologin?goto=" . $core->getUrl());
}


if($args[0] == "app" || empty($args[0])){
    if(empty($args[1])){
        header("Location: dash");
        exit;
    }

    $s = str_replace($core->getWebUrl() . "app/", "", strtok($core->getUrl(), "?"));
    loadSite($s);

}else if($args[0] == "img"){
    if(empty($args[1])){
        header("Location: " . $core->getWebUrl());
        exit;
    }

    if(!isset($_GET['raw'])){
        loadSite("viewImg");
    }else {
        $stmt = $core->getPDO()->prepare("SELECT img_file FROM images WHERE img_token = :token");
        $stmt->execute(array(":token" => $args[1]));
        $file = $stmt->fetch()['img_file'];
        if (file_exists($file)){
            $size = getimagesize($file);
            $fp = fopen($file, 'rb');

            if ($size and $fp){
                header('Content-Type: '.$size['mime']);
                header('Content-Length: '.filesize($file));

                fpassthru($fp);

                exit;
            }
        }
    }
}else{
    $stmt = $core->getPDO()->prepare("SELECT short_id, short_targeturl, short_password FROM shortlinks WHERE short_token = :short_token");
    $stmt->execute(array(":short_token" => $args[0]));
    if($stmt->rowCount() == 1){
        $data = $stmt->fetch();
        $stmt = $core->getPDO()->prepare("INSERT INTO stats (stats_sid, stats_date) VALUES (?, ?)");
        $stmt->execute(array($data['short_id'], date("Y-m-d")));

        if(empty($data['short_password'])){
            header("Location: " . $data['short_targeturl']);
        }else{
            header("Location: " . $core->getWebUrl() . "app/pp?id=" . $args[0]);
        }
    }else{
        header("Location: " . $core->getWebUrl() . "app/home?error=404");
    }
}

function loadSite($s){

    GLOBAL $core;


    if(substr($s, 0, 1) == "/"){
        $s = substr($s, 1);
    }

    $site = dirname(__FILE__) . "/inc/fnd/"  . $s;

    if(is_dir($site)){
        loadSite($s . "/index");
        return;
    }

    $site = $site . ".php";

    if(file_exists($site)){
        include "$site";
    }else{
        $core->printError("Site not found.");
    }
}
