<?php
include './common.php';
// require_once './config.php'; //uchome的配置信息

// openid语言文件
include S_ROOT.'language/lang_openid_'.$_SC['charset'].'.php' ;

include S_ROOT.'language/lang_cp.php' ; // uchome语言文件

//全局配置信息
// global $_SGLOBAL;

//openid语言信息
$openidlang = $_SGLOBAL['openidlang'];
// echo var_dump($openidlang)."--openidlang<br>";
//OpenID处理文件
include S_ROOT.'plugins/OpenIDoor/OpenID.inc.php';
?>