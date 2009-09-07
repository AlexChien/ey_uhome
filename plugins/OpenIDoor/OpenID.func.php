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
	global $db, $_SGLOBAL, $_SN;

	// echo var_dump($_SN)."--_SN0<br>";

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

	$invitearr = $_SESSION['invitearr'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$url_plus = $_SESSION['url_plus'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$app = $_SESSION['app'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$_SN = $_SESSION['SN'];//从会话里取出后边要用的这个变量
	
	// echo var_dump($invitearr)."--invitearr<br>";
	// echo var_dump($url_plus)."--url_plus<br>";
	// echo var_dump($app)."--app<br>";
	// echo var_dump($_SN)."--_SN1<br>";
	
	include_once(S_ROOT.'./source/function_space.php');
	//开通空间
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		$space = space_open($setarr['uid'], $setarr['username'], 0, $passport['email']);
	}
	
	$_SGLOBAL['member'] = $space;

	//实名
	realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);//这里$_SN再次被赋值
	
	// echo var_dump($_SN)."--_SN2<br>";

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), $cookietime);
	ssetcookie('loginuser', $passport['username'], 31536000);
	ssetcookie('_refer', '');

	//同步登录
	include_once S_ROOT.'./uc_client/client.php';
	$ucsynlogin = uc_user_synlogin($setarr['uid']);
	// echo var_dump($ucsynlogin)."--ucsynlogin<br>";

	//好友邀请
	if($invitearr) {
		// echo $_SGLOBAL."--_SGLOBAL1<br>";
		// echo var_dump($_SN)."--_SN1<br>";
		// echo var_dump($invitearr['id'])."--invitearr['id']<br>";
		// echo var_dump($setarr['uid'])."--setarr['uid']<br>";
		// echo var_dump($setarr['username'])."--setarr['username']<br>";
		// echo var_dump($invitearr['uid'])."--invitearr['uid']<br>";
		// echo var_dump($invitearr['username'])."--invitearr['username']<br>";
		//成为好友
		invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
	}

	// echo var_dump($_SGLOBAL['supe_uid'])."--_SGLOBAL['supe_uid']<br>";
	// echo var_dump($space['uid'])."--space['uid']<br>";
	
	//判断用户是否设置了头像
	$_SGLOBAL['supe_uid'] = $space['uid'];
	$reward = $setarr = array();
	$experience = $credit = 0;
	$avatar_exists = ckavatar($space['uid']);
	if($avatar_exists) {
		if(!$space['avatar']) {
			//奖励积分
			$reward = getreward('setavatar', 0);
			$credit = $reward['credit'];
			$experience = $reward['experience'];
			if($credit) {
				$setarr['credit'] = "credit=credit+$credit";
			}
			if($experience) {
				$setarr['experience'] = "experience=experience+$experience";
			}
			$setarr['avatar'] = 'avatar=1';
			$setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
		}
	} else {
		if($space['avatar']) {
			$setarr['avatar'] = 'avatar=0';
		}
	}
	
	if(empty($_POST['refer'])) {
		$_POST['refer'] = 'space.php?do=home';
	}
	
	realname_get();
	
	showmessage('login_success', $app?"userapp.php?id=$app":$_POST['refer'], 1, array($ucsynlogin));	

}

//////////////////////////////////////////////////////////////////////////////
//OpenID认证通过后进行的动作分发
function _OpenID_Action(){
	global $plugin_url;
	$_uid = DB_Get_UserID_By_OpenID($_SESSION['openid_identifier']);
	if(!empty($_uid))
	{
		$_SESSION['openid_binding'] = 0;//不用绑定
		DB_Set_Logined($_uid);//去登录成功
		// echo 145;
		// showmessage('login_success','space.php?do=home');//, dreferer());
	}
	header("Location: ".$plugin_url."?".$_SESSION['url_plus']);//添加、没有记录都到这来报道
	exit(0);//一定要退出
}
?>