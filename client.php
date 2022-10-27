<?php
require_once "vendor/autoload.php";

$client = new \webFrame\Client("tcp://0.0.0.0:8080");

$client->on('connect', function (\webFrame\Client $client){
    fprintf(STDOUT, "连接成功\n");
});

$client->on('error', function (\webFrame\Client $client, $errorNo, $errorStr){
    fprintf(STDOUT, "error:%d->%s\n", $errorNo, $errorStr);
});

$client->on('receive', function (\webFrame\Client $client){
    fprintf(STDOUT, "接收到数据\n");
});

$client->on('close', function (\webFrame\Client $client){
    fprintf(STDOUT, "连接断开\n");
});


