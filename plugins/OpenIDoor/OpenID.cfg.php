<?php
define('UCH_ROOT',dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

require(UCH_ROOT.'./config.php');//引入数据库的用户名和密码
$dsn = 'mysql://'.$_SC['dbuser'].':'.$_SC['dbpw'].'@'.$_SC['dbhost'].'/'.$_SC['dbname'];
//$dsn = "mysql://$user:$pass@$host/$db_name";
$langcharset = $_SC['charset'];	//字符集
$tablepre = $_SC['tablepre'];

require(UCH_ROOT.'./common.php');
$uid = $_SGLOBAL['supe_uid'];//当前用户uid
$username = $_SGLOBAL['supe_username'];
$db =  $_SGLOBAL['db'];

$plugin_url = 'OpenID.call.php';	//处理页的URL
require_once UCH_ROOT.'./source/function_common.php';
?>