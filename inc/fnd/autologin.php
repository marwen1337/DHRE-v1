<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 12.08.2019
 * Time: 20:06
 */

if(!empty($_COOKIE['autologin_token']) && !empty($_COOKIE['autologin_pass'])){

    $stmt = $core->getPDO()->prepare("SELECT login_id, login_owner FROM autologin WHERE login_token = :autologin_token AND login_pass = :autologin_pass AND login_type = 'normal'");
    $stmt->execute(array(":autologin_token" => $_COOKIE['autologin_token'], ":autologin_pass" => $_COOKIE['autologin_pass']));
    if($stmt->rowCount() == 1){
        $_SESSION['uid'] = $stmt->fetch()['login_owner'];
        header("Location: " . $_GET['goto']);
        exit;
    }else{
        header("Location: login");
    }

}else $core->printError("wrong_args");