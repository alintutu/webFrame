<?php

namespace webFrame;

class Client
{
    public $_mainSocket;
    public array $_events = [];
    public $_readBufferSize = 1024*100;

    public $_recvLen = 0;               //当前连接目前接收的字节大小
    public $_recvBufferFull = 0;    //当前连接目前是否超出缓冲区
    public $_recvBuffer = ''; //缓冲区

    public function on($eventName, $eventCall){
        $this->_events[$eventName] = $eventCall;
    }

    public function __construct($local_socket){
        $this->_mainSocket = stream_socket_client($local_socket,$errorNo, $errorStr);
        if(is_resource($this->_mainSocket)){
            $this->runEventCallBack('connect', [$this->_mainSocket]);
        }else{
            $this->runEventCallBack('error', [$this->_mainSocket, $errorNo, $errorStr]);
            exit(0);
        }
    }

    public function runEventCallBack($eventName, $agv){
        if(isset($this->_events[$eventName]) && is_callable($this->_events[$eventName])){
            $this->_events[$eventName]($this, ...$agv);
        }
    }

    public function eventLoop(){
        while (1){
            $readFds = [$this->_mainSocket];
            $writeFds = [$this->_mainSocket];
            $expFds = [$this->_mainSocket];
            $resCount = stream_select($readFds, $writeFds, $expFds, NULL, NULL);
            if($resCount <= 0 || !$resCount){
                break;
            }
            if($readFds){
                $this->recv4socket();
            }
//            if($writeFds){
//
//            }
        }
    }

    public function recv4socket(){
            $data = fread($this->_mainSocket, $this->_readBufferSize);
            if($data === '' || $data === false){
                if(feof($this->_mainSocket) || !is_resource($this->_mainSocket)){
                    $this->onClose();
                }
            }else{
                $this->_recvBuffer .= $data;
                $this->_recvLen += strlen($data);
            }
//            else{
//                $this->_serve->runEventCallBack('receive', [$data, $this]);
//            }

    }

    public function onClose(){
        fclose($this->_mainSocket);
        $this->runEventCallBack('close', [$this]);
        exit();
    }
}