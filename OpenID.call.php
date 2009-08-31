<?php

require_once './common.php';
require_once './config.php'; //uchome的配置信息

// openid语言文件
include S_ROOT.'language/lang_openid_'.$_SC['charset'].'.php' ;

include S_ROOT.'language/lang_cp.php' ; // uchome语言文件

//全局配置信息
global $_SGLOBAL;

//openid语言信息
$openidlang = $_SGLOBAL['openidlang'];

//OpenID处理文件
include S_ROOT.'plugins/OpenIDoor/OpenID.inc.php';
?>