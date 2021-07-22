<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 06.11.2019
 * Time: 21:16
 */

if($core->isUserLoggedIn()){
    echo json_encode($core->fullLang());
    exit;
}else{
    header("Location:" . $core->getWebUrl() . "/app/login");
}