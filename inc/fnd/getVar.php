<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 17.11.2019
 * Time: 16:50
 */

if(isset($_GET['var'])){
    $arr = array("webUrl" => $core->getWebUrl(), "adminUrl" => $core->getWebUrl() . "admin/");
    if(isset($arr[$_GET['var']])){
        echo $arr[$_GET['var']];
        exit;
    }
}
$core->printError("var_not_found");

?>