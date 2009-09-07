<?php
include './common.php';//全站公共配置，基础设施
// require_once './config.php'; //uchome的配置信息//common.php里已经require了

// openid语言文件
include(S_ROOT.'language/lang_openid_'.$_SC['charset'].'.php');

//uchome语言文件//可以考虑将openid的语言文件也重构在内
//include(S_ROOT.'language/lang_cp.php');

//全局配置信息
// global $_SGLOBAL; //common.php里已经声明，在作用域内可以直接使用
//echo var_dump($_SGLOBAL).'-----_SGLOBAL in common.php<br>';
//echo var_dump($_SCONFIG['sitekey']).'--sitekey<br>'; //$_SCONFIG同样common.php里已经声明，在作用域内可以直接使用

//openid语言信息
$openidlang = $_SGLOBAL['openidlang'];//供页面使用

//OpenID处理文件
include S_ROOT.'plugins/OpenIDoor/OpenID.inc.php';
?>