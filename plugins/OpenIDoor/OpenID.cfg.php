<?php
//define('UCH_ROOT',dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);//没必要再定义根路径
// echo var_dump(UCH_ROOT).'--UCH_ROOT<br>';
// echo var_dump($_SCONFIG['sitekey']).'--sitekey345123<br>';
//require(UCH_ROOT.'./config.php');//引入数据库的用户名和密码//common.php内已经require过
// echo var_dump(S_ROOT).'--S_ROOT<br>';
//require(S_ROOT.'./config.php');//引入数据库的用户名和密码//common.php内已经require过
$dsn = 'mysql://'.$_SC['dbuser'].':'.$_SC['dbpw'].'@'.$_SC['dbhost'].'/'.$_SC['dbname'];
//$dsn = "mysql://$user:$pass@$host/$db_name";
$langcharset = $_SC['charset'];	//字符集
$tablepre = $_SC['tablepre'];
//require(UCH_ROOT.'./common.php');
//echo var_dump($dsn).'--dsn<br>';
//echo var_dump($_SCONFIG['sitekey']).'--sitekey111<br>';
//echo var_dump($_SGLOBAL['supe_uid']).'--supe_uid<br>';
//echo var_dump($_SGLOBAL).'--_SGLOBAL<br>';

$uid = $_SGLOBAL['supe_uid'];//当前用户uid
$username = $_SGLOBAL['supe_username'];
$db =  $_SGLOBAL['db'];
//echo var_dump($_SCONFIG['sitekey']).'--sitekey345<br>';
$plugin_url = 'OpenID.call.php';//处理页的URL
//require_once UCH_ROOT.'./source/function_common.php'; //common.php内已经require过
//require_once S_ROOT.'source/function_common.php'; //common.php内已经require过
?>