<?php
namespace webFrame;

class Server
{

    public $_mainSocket;
    public $_localSocket;
    static public array $_connections = [];

    public function __construct($localSocket){
        $this->_localSocket = $localSocket;
    }

    public array $_events = [];
    public function on($eventName, $eventCall){
        $this->_events[$eventName] = $eventCall;
    }

    public function start(){
        $this->listen();
        $this->eventLoop();
    }

    public function listen(): void
    {
        $flag = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        $opt['socket']['backlog'] = 10;
        $context = stream_context_create($opt);
        $this->_mainSocket = stream_socket_server($this->_localSocket, $error, $errStr, $flag, $context);
        if(!is_resource($this->_mainSocket)){
            fprintf(STDOUT, "server create fail:%s \n", $errStr);
            exit(0);
        }
        fprintf(STDOUT, "listen on %s \n", $this->_localSocket);
    }

    public function eventLoop(){
        $readFds = [];
        $writeFds = [];
        $expFds = [];

        // 时间
        // 0 会快速返回
        // NULL 会阻塞到有客户为止
        while (1){
            /** @var TcpConnection $connection */
            if(!empty(self::$_connections)){
                foreach (self::$_connections as $idx => $connection) {
                    if(is_resource($connection->socketFd())){
                        $readFds[] = $connection->socketFd();
                        $writeFds[] = $connection->socketFd();
                    }
                }
            }
            $readFds[] = $this->_mainSocket;
            set_error_handler(function (){});
            $resCount = stream_select($readFds, $writeFds, $expFds, NULL, NULL);
            restore_error_handler();
            if($resCount === false){
                break;
            }
            if($readFds){
                foreach ($readFds as $readFd){
                    if($readFd == $this->_mainSocket){
                        $this->accept();
                    }else{
                        $connection = self::$_connections[(int)$readFd];
                        $connection->recv4socket();
                    }
                }
            }
        }
    }

    public function runEventCallBack($eventName, $agv){
        if(isset($this->_events[$eventName]) && is_callable($this->_events[$eventName])){
            $this->_events[$eventName]($this, ...$agv);
        }
    }

    public function onClientLeave($socketFd){
        if(isset(self::$_connections[(int)$socketFd])){
            unset(self::$_connections[(int)$socketFd]);
        }
    }

    public function accept(): void
    {
        $connFd = stream_socket_accept($this->_mainSocket, -1, $peerName);
        if(is_resource($connFd)){
            $connection = new TcpConnection($connFd, $peerName, $this);
            self::$_connections[(int)$connFd] = $connection;
            $this->runEventCallBack('connect', [$connection]);
        }
    }

}