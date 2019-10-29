<?php
date_default_timezone_set('Asia/Shanghai');
$ip = $_SERVER["REMOTE_ADDR"];//记录访问者的ip
$filename = $_SERVER['PHP_SELF'];//访问者要访问的文件名
$parameter = $_SERVER["QUERY_STRING"];//访问者要请求的参数
$method = $_SERVER['REQUEST_METHOD'];//请求方法
$time = date('Y-m-d H:i:s',time());//访问时间
$post = file_get_contents("php://input",'r');//接收POST数据
$others    = '...其他你想得到的信息...';
$logadd = '访问时间：'.$time.'-->'.'访问链接：http://'.$ip.$filename.'?'.$parameter.'请求方法：'.$method."\r\n";
// log记录
$fh = fopen("log.txt", "a");
fwrite($fh, $logadd);
fwrite($fh,print_r($_COOKIE, true)."\r\n");
fwrite($fh,$others."\r\n");
fclose($fh);
?>