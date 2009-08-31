<?php

// OpenID Language Pack for Discuz! Version 1.0.0
// Translated by wukan

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['openidlang'] = array(
(
	'openid_login' => '登录',
	'submit' => '提交',
	'openid_intro' => '<a href="http://www.openidoor.com/openidintro/openidfaq.html" target=_blank>1.什么是OpenID？</a>',
	'openid_guide' => '<a href="http://www.openidoor.com/openidintro/useopenid.html" target=_blank>2.OpenID使用指南</a>',
	'openid_sites' => '<a href="http://www.openidoor.com/openidsource.html" target=_blank>3.OpenID站点</a>',
	'openid_auth' => '认证中……',
	'openid_add' => '添加',
	'openid_del' => '删除',
	'openid_list' => 'OpenID列表',
	'openid_msg' => 'OpenID消息',
	'openid_select' =>'OpenID绑定选择 - 当前OpenID URL：'.$_SESSION['openid_identifier'],
	'openid_tip' => '如果<font color=red>3分钟</font>内未完成登录绑定或注册绑定，绑定操作将自动失效',
	'bind_intro_login' => "1.点击“<a href=do.php?ac=login><font color=red>登录绑定</font></a>”进入到登录界面，登录成功再进入此页面后自动与当前OpenID绑定",
	'bind_intro_reg' => "2.点击“<a href=do.php?ac=register><font color=red>注册绑定</font></a>”进入到注册界面，注册成功再进入此页面后自动与当前OpenID绑定",
	'bind_intro_cancel' => "3.点击“<a href=".$plugin_url."?openid_action=cancel><font color=red>取消绑定</font></a>”，取消与当前OpenID的绑定操作",
	'msg_bind_error' => $_SESSION['openid_identifier'].' 已经被绑定了。',
	'msg_bind_ok' => $_SESSION['openid_identifier']." <===> $username 绑定成功",
	'del_alert' => '您真的要全部删除吗？建议至少保留一条记录，以防忘记密码或没有绑定邮箱而导致此账号完全丢失。'
);

?>