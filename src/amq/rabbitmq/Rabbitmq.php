<?php
namespace tools\amq\rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class Rabbitmq {

    private  $connection;

    private  $channel;

    /**
     * @param string $host
     * @param string $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $login_method
     * @param null $login_response @deprecated
     * @param string $locale
     * @param float $connection_timeout
     * @param float $read_write_timeout
     * @param null $context
     * @param bool $keepalive
     * @param int $heartbeat
     * @param float $channel_rpc_timeout
     * @param string|null $ssl_protocol
     */
    public function __construct($config = [])
    {
        $host = isset($config['host']) && !empty($config['host']) ? $config['host'] : 'localhost';
        $port = isset($config['port']) && !empty($config['port']) ? $config['port'] : 5672;
        $user = isset($config['user']) && !empty($config['user']) ? $config['user'] : 'guest';
        $password = isset($config['password']) && !empty($config['password']) ? $config['password'] : 'guest';
        $this->connection = new AMQPStreamConnection($host,$port,$user,$password);
        $this->channel = $this->connection->channel();
    }

    /**
     * 发送消息
     * @param string $queue_name
     * @param array  $body
     * @param string $routing_key
     */
    public  function send($queue_name,$body,$routing_key='')
    {
        //声明队列
        $this->channel->queue_declare($queue_name,'',false,false,false,false);
        
        $msg = new AMQPMessage($body);

        $this->channel->basic_publish($msg,'',$routing_key);

        $this->connection->close();
        $this->channel->close();
    }

    /**
     * 接收消息
     */
    public function receive($queue_name)
    {
        $this->channel->queue_declare($queue_name,'',true,false,false,false);

    }


    /**
     * task队列（发送）
     */
    public function send_task()
    {

    }

    /**
     * task队列（接收）
     */
    public function receive_task()
    {

    }


}
