<?php
namespace Optimize\redis;
/**
 * 悲观锁，通过获取到对应的权限后（有效key值），才能进行操作
 * 并发量不大的情况下使用
 */
final class RedisLock {
    
    private $redis;
    private $timeout = 3;//seconds

    /**
     * Initializes the redis object
     */
    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    /**
     * Set the cache key
     */
    private function getCachekey($key){
        return "lock_{$key}";
    }

    /**
     * Acquiring a lock
     * 设置一个key，不存在就设置，然后判断该key是否超时，如果超时就重置掉该锁
     */
    public function getLock($key, $timeout = NULL){
        $timeout = $timeout ? $timeout : $this->timeout;
        $lockCacheKey = $this->getCachekey($key);
        $expireAt = time() + $timeout;
        $isGet = (bool)$this->redis->setnx($lockCacheKey, $expireAt);
        if ($isGet) {
            return $expireAt;
        }
        while (true) {
            usleep(10);
            $time = time();
            $oldExpire = $this->redis->get($lockCacheKey);
            if ($oldExpire >= $time) {
                continue;
            }
            $newExpire = $time + $timeout;
            $expireAt = $this->redis->getset($lockCacheKey, $newExpire);
            if ($oldExpire != $expireAt) {
                continue;
            }
            $isGet = $newExpire;
            break;
        }
        return $isGet;
    }

    /**
     * Release the lock
     */
    public function releaseLock($key,$newExpire)
    {
        # code...
        $lockCacheKey = $this->getCachekey($key);
        if ($newExpire >= time()) {
            return $this->redis->del($lockCacheKey);
        }
        return true;
    }

}