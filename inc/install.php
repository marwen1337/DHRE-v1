<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 09.09.2019
 * Time: 14:46
 */

require 'AppCore.php';

$core = new AppCore(dirname(__FILE__) . "/../config/default_config.php");

$statement = $core->getPDO()->prepare("
CREATE TABLE `autologin` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_owner` int(11) NOT NULL,
  `login_token` varchar(32) NOT NULL,
  `login_pass` varchar(16) NOT NULL,
  `login_type` varchar(8) NOT NULL,
  PRIMARY KEY (`login_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `shortlinks` (
  `short_id` int(11) NOT NULL AUTO_INCREMENT,
  `short_creator` int(11) NOT NULL,
  `short_token` varchar(64) NOT NULL,
  `short_targeturl` varchar(4096) NOT NULL,
  `short_created` int(11) NOT NULL,
  PRIMARY KEY (`short_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) NOT NULL,
  `user_password` varchar(512) NOT NULL,
  `user_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;");

$statement->execute();

$statement = $core->getPDO()->prepare("INSERT INTO users (user_name, user_password, user_active) VALUES (?, ?, ?)");
$pwd = $core->randomString(32);
$statement->execute(array("admin", $core->hash($pwd), true));

echo "Successfully installed.\nAdministrator Login: admin / " . $pwd;

?>