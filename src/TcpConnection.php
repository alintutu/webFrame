<?php
namespace webFrame;

class TcpConnection
{
    public $_socketFd;
    public string $_clientIp;
    public Server $_serve;
    public int $_readBufferSize = 1024;

    public $_recvBufferSize = 1024*100; //当前连接接收缓冲区的大小
    public $_recvLen = 0;               //当前连接目前接收的字节大小
    public $_recvBufferFull = 0;    //当前连接目前是否超出缓冲区
    public $_recvBuffer = ''; //缓冲区

    public function __construct($_socketFd, $_clientIp, $serve){
        $this->_socketFd = $_socketFd;
        $this->_clientIp = $_clientIp;
        $this->_serve = $serve;
    }

    public function socketFd(){
        return $this->_socketFd;
    }

    public function recv4socket(){
        if($this->_recvLen < $this->_recvBufferSize){
            $data = fread($this->_socketFd, $this->_readBufferSize);
            if($data === '' || $data === false){
                if(feof($this->_socketFd) || !is_resource($this->_socketFd)){
                    $this->close();
                }
            }else{
                $this->_recvBuffer .= $data;
                $this->_recvLen += strlen($data);
            }
            if($this->_recvLen > 0){
                // stream 字节流协议
               // 封包和拆包的条件：必须有相应的字段来表示这条消息的完整
            }
//            if($data){
//                $this->_serve->runEventCallBack('receive', [$data, $this]);
//            }
        }else{
            $this->_recvBufferFull++;
        }
    }

    public function close(){
        if(is_resource($this->_socketFd)){
            fclose($this->_socketFd);
        }
        $this->_serve->runEventCallBack('close', [$this->_serve]);
        $this->_serve->onClientLeave($this->_socketFd);
    }

    public function write2socket(string $data){
        fwrite($this->_socketFd, $data, strlen($data));
        fprintf(STDOUT, "写入数据: %s", $data);
    }
}