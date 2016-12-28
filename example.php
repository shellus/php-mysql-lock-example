<?php
/**
 * Created by PhpStorm.
 * User: shellus
 * Date: 2016/12/28
 * Time: 9:27
 */

/*
 * sql
 *
CREATE TABLE `users` (
  `name` varchar(25) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `transfer` (
  `from_user` varchar(25) DEFAULT NULL,
  `to_user` varchar(25) DEFAULT NULL,
  `money` decimal(10,2) DEFAULT NULL,
  `success` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8

 */

$db = new PDO('mysql:host=localhost;port=3306;dbname=test', "root", "root", [PDO::ERRMODE_EXCEPTION]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$users = ['小明', '小红', '小白'];

// 初始化数据

/*$db->query("TRUNCATE `users`");
$db->query("TRUNCATE `transfer`");
foreach ($users as $userName) {
    $q = $db->prepare("INSERT INTO `users`(`name`, `money`) VALUES (?, 100);");
    $q->execute([$userName]);
}*/



$from_user = array_splice($users, rand(0, count($users) - 1), 1)[0];
$to_user = array_splice($users, rand(0, count($users) - 1), 1)[0];
$money = rand(0.01, 100);

echo "$from_user 转账给 $to_user $money 元<br>";

try {

    $db->beginTransaction();
// 获取双方余额，并检查转账后是否低于0元
// * 从现在起，就要保证其他线程无法读出余额
    $q = $db->prepare('SELECT * FROM `users` WHERE `name`=? FOR UPDATE');
    $q->execute([$from_user]);
    $from_user_money = (float)$q->fetchColumn(1);

    $q->execute([$to_user]);
    $to_user_money = (float)$q->fetchColumn(1);

    if ($from_user_money - $money < 0) {
        throw new Exception("转账失败，余额不足");
    }


// 改变双方用户金额
    $q = $db->prepare('UPDATE `users` SET `money`=? WHERE `name`=?');
    $q->execute([$from_user_money - $money, $from_user]);
    $q->execute([$to_user_money + $money, $to_user]);

    $db -> commit();

// 记录成功转账日志
    $q = $db->prepare('INSERT INTO `transfer`(`from_user`, `to_user`, `money`, `success`) VALUES (?,?,?,?)');
    $r = $q->execute([$from_user, $to_user, $money, 1]);


} catch (\Exception $e) {

    $db -> rollBack();

    // 记录失败转账日志
    $q = $db->prepare('INSERT INTO `transfer`(`from_user`, `to_user`, `money`, `success`) VALUES (?,?,?,?)');
    $r = $q->execute([$from_user, $to_user, $money, 0]);
    var_dump($r);

    header("HTTP/1.1 403 Forbidden");
    echo $e->getMessage() . "<br>";
    die();
}

echo "转账成功！<br>";