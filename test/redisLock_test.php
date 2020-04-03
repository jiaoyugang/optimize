<?php
require dirname(__DIR__).'/vendor/autoload.php';
use Optimize\redis\RedisLock;
$redis = new \Redis();

$redis->connect('127.0.0.1','6379');

$lockTimeOut = 5;
$redisLock = new RedisLock($redis,$lockTimeOut);

$lockKey    = 'lock:user:wallet:uid:1001';
$lockExpire = $redisLock->getLock($lockKey,3);

if($lockExpire) {
    try {
        $goodsPrice = 80;
        $pdo = new PDO('mysql:host=localhost;dbname=optimize','root','My060824@srr');
        $sql = 'SELECT balance FROM op_order WHERE id =:id';
        $pre_sql = $pdo->prepare($sql);
        $pre_sql->execute(array('id' => 1));
        $res = $pre_sql->fetch();
        $userBalance = $res["balance"];
        if($userBalance >= $goodsPrice) { //余额充足就更新，否则就抛出异常
            $newUserBalance = sprintf("%.2f",($userBalance - $goodsPrice));
            $pre_sql2 = $pdo->prepare('UPDATE op_order SET balance = '.$newUserBalance.' WHERE id = 1');
            $pdo->beginTransaction();
            $pre_sql2->execute();
            $pdo->commit();            
        }else {
            throw new \Exception('账户金额不足');
        }
        $redisLock->releaseLock($lockKey,$lockExpire);
    } catch (\Exception $excep) {
        //异常释放锁
        $redisLock->releaseLock($lockKey,$lockExpire);
        var_dump($excep->getMessage());
    }
}