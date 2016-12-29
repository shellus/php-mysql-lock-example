<?php
/**
 * Created by PhpStorm.
 * User: shellus
 * Date: 2016/12/28
 * Time: 9:27
 *
http://localhost/example.php?reset=1

http://localhost/example.php?ts=1

http://localhost/example.php?ts=0

 */




$db = new PDO('mysql:host=localhost;port=3306;dbname=test', "root", "root", [PDO::ERRMODE_EXCEPTION]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$users = ['小明', '小红', '小白'];
$defaultMoney = 10000;

if (@$_GET['reset']) {
    require 'generate.php';
    var_dump($users);
    echo "表和数据重置完成<br>";
    die();
}


$from_user = array_splice($users, rand(0, count($users) - 1), 1)[0];
$to_user = array_splice($users, rand(0, count($users) - 1), 1)[0];
$money = rand(0.01, 100);

echo "$from_user 转账给 $to_user $money 元<br>";

try {

    if (@$_GET['ts']) $db->beginTransaction();
// 获取双方余额，并检查转账后是否低于0元
// * 从现在起，就要保证其他线程无法读出余额

    if (@$_GET['ts']){
        $q = $db->prepare('SELECT * FROM `users` WHERE `name`=? FOR UPDATE');
    }else{
        $q = $db->prepare('SELECT * FROM `users` WHERE `name`=?');
    }
    $q->execute([$from_user]);
    $from_user_money = (float)$q->fetchColumn(2);

    $q->execute([$to_user]);
    $to_user_money = (float)$q->fetchColumn(2);

    if ($from_user_money - $money < 0) {
        throw new Exception("转账失败，余额不足");
    }


// 改变双方用户金额
    $q = $db->prepare('UPDATE `users` SET `money`=? WHERE `name`=?');
    $q->execute([$from_user_money - $money, $from_user]);
    $q->execute([$to_user_money + $money, $to_user]);

    if (@$_GET['ts']) $db->commit();

// 记录成功转账日志
    $q = $db->prepare('INSERT INTO `transfer`(`from_user`, `to_user`, `money`, `success`) VALUES (?,?,?,?)');
    $r = $q->execute([$from_user, $to_user, $money, 1]);


} catch (\Exception $e) {

    if (@$_GET['ts']) $db->rollBack();

    // 记录失败转账日志
    $q = $db->prepare('INSERT INTO `transfer`(`from_user`, `to_user`, `money`, `success`) VALUES (?,?,?,?)');
    $r = $q->execute([$from_user, $to_user, $money, 0]);
    header("HTTP/1.1 403 Forbidden");
    echo $e->getMessage() . "<br>";
    die();
}
echo "转账成功！<br>";



$s = $db -> query('SELECT * FROM `users`') -> fetchAll();

echo "<ul>";
foreach ($s as $item){
    echo "<li>用户：{$item['name']}   资金：{$item['money']}</li>";
}
echo "</ul><br>";

$s = $db -> query('SELECT SUM(`money`) AS money FROM `users`') -> fetch();
echo "仓库共剩！{$s['money']}<br>";