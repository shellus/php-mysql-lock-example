<?php
/**
 * Created by PhpStorm.
 * User: shellus
 * Date: 2016/12/29
 * Time: 13:04
 */

// 初始化数据
$db->query("DROP TABLE IF EXISTS `users`");
$db->query("DROP TABLE IF EXISTS `transfer`");

$db->query("
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(25) NOT NULL,
  `money` DECIMAL(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX name_index (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$db->query("
CREATE TABLE `transfer` (
  `from_user` varchar(25) DEFAULT NULL,
  `to_user` varchar(25) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `success` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

foreach ($users as $userName) {
    $q = $db->prepare("INSERT INTO `users`(`name`, `money`) VALUES (?, ?);");
    $q->execute([$userName, $defaultMoney]);
}