<?php
@session_start();//开始记录session信息

// echo dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenID.cfg.php'."<br/>";
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenID.cfg.php');//引入配置文件
//echo var_dump($_SCONFIG['sitekey']).'--sitekey1234<br>';

// include(S_ROOT."/language/lang_openid_$langcharset.php");//语言包

$openid_identifier = $_SESSION['openid_identifier'];//记下OpenID URL方便使用

$lang_login_add = $openidlang['openid_login'];

/////////////////////////////////////
//好友邀请数据预处理
include_once(S_ROOT.'./source/function_cp.php');
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$code = empty($_GET['code'])?'':$_GET['code'];
$app = empty($_GET['app'])?'':intval($_GET['app']);
$invite = empty($_GET['invite'])?'':$_GET['invite'];
$invitearr = array();
$reward = getreward('invitecode', 0);
$pay = $app ? 0 : $reward['credit'];

if($uid && $code && !$pay) {//邀请玩应用home就不给奖励了？
	$m_space = getspace($uid);//$_SN在此被赋值
	// echo var_dump($_SN)."--_SN在getspace这个函数中赋值了...<br>";
	$_SESSION['SN'] = $_SN;//后续函数内部需要这个全局变量,将其放入session供后续调用...
	// echo var_dump($_SESSION['SN'])."--_SESSION['SN']<br>";
	// echo var_dump($m_space['uid'])."--m_space['uid']<br>";	
	// echo var_dump($app)."--app<br>";
	// echo var_dump($code)."--code<br>";	
	// echo var_dump($_SCONFIG['sitekey']).'--sitekey<br>';
	// echo space_key($m_space, $app)."--space_key<br>";	
	if($code == space_key($m_space, $app)) {//验证通过
		$invitearr['uid'] = $uid;
		$invitearr['username'] = $m_space['username'];
	}
	$url_plus = "uid=$uid&app=$app&code=$code";
	// echo var_dump($uid)."--uid<br>";
	// echo var_dump($m_space['username'])."--m_space['username']<br>";
	// echo var_dump($invitearr)."--_invitearr1<br>";
	// echo var_dump($url_plus)."--url_plus1<br>";
} elseif($uid && $invite) {
	include_once(S_ROOT.'./source/function_cp.php');
	$invitearr = invite_get($uid, $invite);
	$url_plus = "uid=$uid&invite=$invite";
	// echo var_dump($invitearr)."--_invitearr2<br>";
}

$jumpurl = $app?"userapp.php?id=$app&my_extra=invitedby_bi_{$uid}_{$code}&my_suffix=Lw%3D%3D":'space.php?do=home';

$_SESSION['invitearr'] = $invitearr;//将已有帐户登录时的邀请信息放到会话里，不用发到通行证
$_SESSION['url_plus'] = $url_plus;//将已有帐户登录时的邀请信息放到会话里，不用发到通行证
$_SESSION['app'] = $app;//将已有帐户登录时的邀请信息放到会话里，不用发到通行证
$_SESSION['jumpurl'] = $jumpurl;

// echo var_dump($invitearr)."--_invitearr<br>";
// echo var_dump($_SESSION['invitearr'])."--_SESSION['invitearr']<br>";
// echo var_dump($_SESSION['url_plus'])."--_SESSION['url_plus']<br>";
// echo var_dump($_SESSION['jumpurl'])."--_SESSION['jumpurl']<br>";

// 3分钟内
$is_binding = 0;
if(time()-$_SESSION['openid_binding']<180)
	$is_binding = 1;
	
	// breakpoint();

/////////////////////////////////////////////
// 去掉人工取消绑定，未来可能加入绑定时选择冲突的ucenter用户名的逻辑
// if($_GET['openid_action']=='cancel')//取消绑定
// {
// 	$_SESSION['openid_binding'] = 0;
// 	$is_binding = 0;
// }

// 去除取消openid和uid关联的页面(OpenID.html)和逻辑
//if(!empty($_POST['delete']))
//{
//	$str = implode(',',$_POST['delete']);
//	$db->query("DELETE FROM {$tablepre}user_openids WHERE uid = $uid AND id IN ($str)");
//}

//////////////////////////////////////////	
// 将绑定现有uid改为注册新uid并关联到openid
// 开始注册uchome本地用户并绑定openid操作

if(!empty($openid_identifier)&&!empty($is_binding)){
	$_SESSION['openid_binding'] = 0;//标记绑定完成
	
	$query = $db->query("SELECT uid FROM {$tablepre}user_openids WHERE url ='$openid_identifier'");//查询是否已经被绑定
	if($row=$db->fetch_array($query)){showmessage($openidlang['msg_bind_error'],$plugin_url);}//已经绑定了
	else{
		// 此处为注册新uid逻辑
	    // OpenID客户端从通行证取回的用户信息
		// $username = $_SESSION['openid_sreg']['nickname'];//不能以nickname注册，必须以星尚通行证的login注册
		
		// echo var_dump($openid_identifier)."---openid_identifier<br>";
		// $pieces=explode("http://openid.enjoyoung.cn/", $openid_identifier);//线上运营
		$pieces=explode("http://openid.localhost.com/", $openid_identifier);//本地开发
		// echo var_dump($pieces[0])."---pieces[0]<br>";
		// echo var_dump($pieces[1])."---pieces[1]<br>";
		
		if($login = $pieces[1]){
			$username = $login;
		} else {
			// echo var_dump($pieces[0])."---pieces[0]<br>";
			// echo var_dump($pieces[1])."---pieces[1]<br>";
			// breakpoint();
			showmessage('only_xingshang');
		}
		
		// echo var_dump($login)."---login<br>";
		// breakpoint();
		
		// echo var_dump($username);
		// 检查是否有邮件信息
		$email=$_SESSION['openid_sreg']['email'];
		// echo var_dump($_SESSION['openid_sreg']['email'])."<br/>";
		// echo $email."<br/>";
		if(empty($email)) {
			showmessage('email_format_is_wrong');
		}
						
		// 不采用uhome及ucenter自己的登录机制，所以随机填个它的密码
		$password = md5("$newuid|$_SGLOBAL[timestamp]");//本地密码随机生成
			
		//用ucenter api注册新用户
		include(S_ROOT.'./uc_client/client.php');
		$newuid = uc_user_register($username, $password, $email);
		// echo var_dump($newuid)."--newuid<br/>";	  
		if($newuid <= 0) {
			if($newuid == -1) {showmessage('user_name_is_not_legitimate');} 
			elseif($newuid == -2) {showmessage('include_not_registered_words');}
			elseif($newuid == -3) {// showmessage('user_name_already_exists');
				// 如果已经在ucenter存在先通过discuz注册的用户,则为他开通uchome
			
				//同步获取用户源
				if(!$passport = get_passport_by_login($username)) {
					showmessage('login_failure_please_re_login', 'OpenID.call.php');
				}
				// echo var_dump($passport)."--passport<br/>";
				$setarr = array(
					'uid' => $passport['uid'],
					'username' => addslashes($passport['username']),
					'password' => md5("$passport[uid]|$_SGLOBAL[timestamp]")//本地密码随机生成
				);
				// echo var_dump($setarr)."--setarr<br/>";
				// echo var_dump($email)."--email<br/>";
				regiter_user_to_uchome();
			} 
			elseif($newuid == -4) {showmessage('email_format_is_wrong');} 
			elseif($newuid == -5) {showmessage('email_not_registered');}
			elseif($newuid == -6) {showmessage('email_has_been_registered');} 
			else {showmessage('register_error');}
		} 
		else 
		{
			$setarr = array(
				'uid' => $newuid,
				'username' => $username,
				'password' => md5("$newuid|$_SGLOBAL[timestamp]")//本地密码随机生成
			);
			regiter_user_to_uchome();
		}
	}
}

/////////////////////////////////////
// 将openid用户注册到uchome
function regiter_user_to_uchome(){
	global $_SCONFIG, $_SGLOBAL, $_SN, $openid_identifier, $setarr, $email, $username, $newuid;
	// echo var_dump($_SCONFIG)."--_SCONFIG<br/>";
 	// echo var_dump($_SGLOBAL)."--_SGLOBAL<br/>";
	// echo var_dump($setarr)."--setarr<br/>";
	
	$invitearr = $_SESSION['invitearr'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$url_plus = $_SESSION['url_plus'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$app = $_SESSION['app'];//从会话里将已有帐户登录时的邀请信息取出，不用从通行证返回信息里取
	$_SN = $_SESSION['SN'];//从会话里取出后边要用的这个变量
	
	// echo var_dump($invitearr)."--invitearr<br>";
	// echo var_dump($url_plus)."--url_plus<br>";
	// echo var_dump($app)."--app<br>";
	// echo var_dump($_SN)."--_SN1<br>";
	// echo var_dump($_SESSION['jumpurl'])."--_SESSION['jumpurl']<br/>";
	
	//开通空间
	// echo var_dump($_SGLOBAL['db'])."--_SGLOBAL['db']<br/>";
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
	// echo var_dump($query)."--query<br/>";
	
	include(S_ROOT.'./source/function_space.php');
	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		$space = space_open($setarr['uid'], $setarr['username'], 0, $email);
	}		
	// echo var_dump($space)."--space<br/>";
	// breakpoint();
	$_SGLOBAL['member'] = $space;
	// echo var_dump($_SGLOBAL['member'])."--_SGLOBAL['member']<br/>";

	//实名
	realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);//这里$_SN再次被赋值
	
	// echo var_dump($_SN)."--_SNn2<br>";

	//检索当前用户
	$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$setarr['password'] = addslashes($value['password']);
	} else {
		//更新本地用户库
		inserttable('member', $setarr, 0, true);
	}

	// 关联uid和openid
	//$db->query("INSERT INTO {$tablepre}user_openids VALUES (null,$newuid,'$openid_identifier')");//openid自带的db链接
	$openids = array(
				'uid' => $setarr['uid'],
				'url' => $openid_identifier
	);
	inserttable('user_openids', $openids, 0, true);//uchome的db链接

	// showmessage($openidlang['msg_bind_ok'],$plugin_url);//绑定成功

	//默认好友
	$flog = $inserts = $fuids = $pokes = array();
	// echo var_dump($_SCONFIG['defaultfusername'])."--_SCONFIG['defaultfusername']<br/>";
	if(!empty($_SCONFIG['defaultfusername'])) {
		$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode(',', $_SCONFIG['defaultfusername'])).")");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value = saddslashes($value);
			$fuids[] = $value['uid'];
			$inserts[] = "('$newuid','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
      // $inserts[] = "('$value[uid]','$newuid','$username','1','$_SGLOBAL[timestamp]')";
      $inserts[] = "('$value[uid]','$newuid','$setarr[username]','1','$_SGLOBAL[timestamp]')";
			$pokes[] = "('$newuid','$value[uid]','$value[username]','".addslashes($_SCONFIG['defaultpoke'])."','$_SGLOBAL[timestamp]')";
			//添加好友变更记录
			$flog[] = "('$value[uid]','$newuid','add','$_SGLOBAL[timestamp]')";
		}/////////
		if($inserts) {
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',', $inserts));
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('poke')." (uid,fromuid,fromusername,note,dateline) VALUES ".implode(',', $pokes));
			$_SGLOBAL['db']->query("REPLACE INTO ".tname('friendlog')." (uid,fuid,action,dateline) VALUES ".implode(',', $flog));

			//添加到附加表
			$friendstr = empty($fuids)?'':implode(',', $fuids);
			updatetable('space', array('friendnum'=>count($fuids), 'pokenum'=>count($pokes)), array('uid'=>$newuid));
			updatetable('spacefield', array('friend'=>$friendstr, 'feedfriend'=>$friendstr), array('uid'=>$newuid));

			//更新默认用户好友缓存
			include_once(S_ROOT.'./source/function_cp.php');
			foreach ($fuids as $fuid) {friend_cache($fuid);}
		}
	}

	//清理在线session
	insertsession($setarr);

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);
	ssetcookie('loginuser', $username, 31536000);
	ssetcookie('_refer', '');

	// echo var_dump($invitearr)."--invitearr<br/>";	
	//好友邀请
	if($invitearr) {
		//成为好友
		invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
		//统计更新
		include_once(S_ROOT.'./source/function_cp.php');
		if($app) {
			updatestat('appinvite');
		} else {
			updatestat('invite');
		}
	}

	$_SGLOBAL['supe_uid'] = $space['uid'];
	//判断用户是否设置了头像
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

	//变更记录
	if($_SCONFIG['my_status']) inserttable('userlog', array('uid'=>$newuid, 'action'=>'add', 'dateline'=>$_SGLOBAL['timestamp']), 0, true);

	// echo var_dump($_SESSION['jumpurl'])."--_SESSION['jumpurl']<br/>";
	// breakpoint();

	showmessage('login_success', $_SESSION['jumpurl']);			
}

/////////////////////////////////////
// 去除显示绑定记录的页面和逻辑
//if($uid)//显示当前绑定记录
//{
//	$query = $db->query("SELECT id,url FROM {$tablepre}user_openids WHERE uid ='$uid'");
//	while($row=$db->fetch_array($query)) {
//       $lists[] = $row;
//   	}
//}

include template('OpenID');//OpenID页面模板
?>