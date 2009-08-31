<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenID.cfg.php');

//$_SESSION['openid_binding']		注册/登录/添加绑定时都为TRUE，结束后用FALSE
//$_SESSION['openid_identifier']	认证通过时记下的OpenID URL
//$_SESSION['openid_action']		由表单隐藏域传过来的参数
//$_SESSION['openid_sreg']			认证通过时记录下的简单注册信息

/////////////////////////////////////////////////////////
//OpenID关系表
function DB_Get_UserID_By_OpenID($openid_url){
	global $tablepre,$db;
	$query = $db->query("SELECT uid FROM {$tablepre}user_openids WHERE url ='$openid_url'");
	if($row=$db->fetch_array($query))
	{
		return $row['uid'];
	}
	return 0;
}

///////////////////////////////////////////////////////////
//用户信息表
function DB_Set_Logined($uid){
	global $db;
	
	$setarr = array(
		'uid' => $uid,
		'username' => '',
		'password' =>''
	);
	
 	$query = $db->query("SELECT username,password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
	if($value = $db->fetch_array($query)) {
		$setarr['username'] = addslashes($value['username']);
		$setarr['password'] = addslashes($value['password']);
	}
	
	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
	ssetcookie('loginuser', $passport['username'], 31536000);
	ssetcookie('_refer', '');

	//同步登录
	include_once S_ROOT.'./uc_client/client.php';
	$ucsynlogin = uc_user_synlogin($setarr['uid']);

}
	//////////////////////////////////////////////////////////////////////////////
//OpenID认证通过后进行的动作分发
function _OpenID_Action(){
	global $plugin_url,$uid;
	$_uid = DB_Get_UserID_By_OpenID($_SESSION['openid_identifier']);
	if(!empty($_uid)&&empty($uid))
	{
		$_SESSION['openid_binding'] = 0;//不用绑定
		DB_Set_Logined($_uid);//去登录成功
		echo 145;
		showmessage('login_success','space.php?do=home');//, dreferer());
	}
	header("Location: $plugin_url");//添加、没有记录都到这来报道
	exit(0);//一定要退出
}
?>