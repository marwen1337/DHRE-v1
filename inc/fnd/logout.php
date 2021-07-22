<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 10.08.2019
 * Time: 14:04
 */

unset($_SESSION['uid']);
if(!empty($_COOKIE['autologin_token']) && !empty($_COOKIE['autologin_pass'])){
    $stmt = $core->getPDO()->prepare("DELETE FROM autologin WHERE login_token = :autologin_token AND login_pass = :autologin_pass");
    $stmt->execute(array(":autologin_token" => $_COOKIE['autologin_token'], ":autologin_pass" => $_COOKIE['autologin_pass']));
    unset($_COOKIE['autologin_token']);
    unset($_COOKIE['autologin_pass']);
    setcookie("autologin_token", null, -1);
    setcookie("autologin_pass", null, -1);
}
session_destroy();
session_start();
header("Location: login");