<?php
require_once "vendor/autoload.php";

// tcp connect/receive/close
// upd packet/close
// stream text
// http request
// ws open/message
// mqtt connect/subscribe/unsubscribe/publish/close

$server = new \webFrame\Server("tcp://0.0.0.0:8080");

$server->on('connect', function (\webFrame\Server $server, \webFrame\TcpConnection $connection){
   fprintf(STDOUT, "有客户端链接了 \n");
});

$server->on("receive", function (\webFrame\Server $server, $msg, \webFrame\TcpConnection $connection){
    fprintf(STDOUT, "有客户端发送了: %s \n", $msg);
    $connection->write2socket("{$msg}-ww \n");
});

$server->on("close", function (\webFrame\Server $server, $msg, \webFrame\TcpConnection $connection){
    fprintf(STDOUT, "有客户端断开了 \n");
});

$server->start();
fprintf(STDOUT, "------------------\n");