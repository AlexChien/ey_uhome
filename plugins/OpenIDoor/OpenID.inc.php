<?php
@session_start();
echo dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenID.cfg.php'."<br/>";
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenID.cfg.php');//引入配置文件

include S_ROOT."/language/lang_openid_$langcharset.php" ;//语言包

$openid_identifier = $_SESSION['openid_identifier'];	//记下OpenID URL方便使用

$lang_login_add = $openidlang['openid_login'];

// if(!empty($uid))
// 	$lang_login_add = $openidlang['openid_add'];

$is_binding = 0;
if(time()-$_SESSION['openid_binding']<180)
	$is_binding = 1;

if($_GET['openid_action']=='cancel')//取消绑定
{
	$_SESSION['openid_binding'] = 0;
	$is_binding = 0;
}

// 去除取消openid和uid关联的页面(OpenID.html)和逻辑
//if(!empty($_POST['delete']))
//{
//	$str = implode(',',$_POST['delete']);
//	$db->query("DELETE FROM {$tablepre}user_openids WHERE uid = $uid AND id IN ($str)");
//}

// 将绑定现有uid改为注册新uid并关联到openid
// 开始绑定操作
// if(!empty($openid_identifier)&&!empty($is_binding)&&$uid){
if(!empty($openid_identifier)&&!empty($is_binding)){
	$_SESSION['openid_binding'] = 0;//标记绑定完成
	
	$query = $db->query("SELECT uid FROM {$tablepre}user_openids WHERE url ='$openid_identifier'");//查询是否已经被绑定
	if($row=$db->fetch_array($query)){showmessage($openidlang['msg_bind_error'],$plugin_url);}//已经绑定了
	else{
		// 此处为注册新uid逻辑
		include_once(S_ROOT.'./source/function_common.php');
		include_once(S_ROOT.'./uc_client/client.php');
	
	  // OpenID客户端从通行证取回的用户信息
		// print var_dump($_SESSION['openid_sreg']);
		// print var_dump($_SESSION['openid_sreg']['nickname']);
		$username = $_SESSION['openid_sreg']['nickname'];
		// echo var_dump($username);
		// 检查是否有邮件信息
		$email=$_SESSION['openid_sreg']['email'];
		echo var_dump($_SESSION['openid_sreg']['email'])."<br/>";
		echo $email."<br/>";
		if(empty($email)) {
			showmessage('email_format_is_wrong');
		}
						
		// 不采用uhome及ucenter自己的登录机制，所以随机填个它的密码
		$plain_password = rand(24);
		$password = md5($plain_password);
			
		//用ucenter api注册新用户
		$newuid = uc_user_register($username, $password, $email);	  
		if($newuid <= 0) {
			if($newuid == -1) {showmessage('user_name_is_not_legitimate');} 
			elseif($newuid == -2) {showmessage('include_not_registered_words');}
			elseif($newuid == -3) {
				// 如果已经在ucenter存在先通过discuz注册的用户
				// showmessage('user_name_already_exists');
				//同步获取用户源
				if(!$passport = get_passport_by_login($username)) {
					showmessage('login_failure_please_re_login', 'OpenID.call.php');
				}
				echo var_dump($passport)."<br/>";
				$setarr = array(
					'uid' => $passport['uid'],
					'username' => addslashes($passport['username']),
					'password' => md5("$passport[uid]|$_SGLOBAL[timestamp]")//本地密码随机生成
				);
				echo var_dump($setarr)."<br/>";
				regiter_user_to_uchome($setarr, $email, $openid_identifier);
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
			regiter_user_to_uchome($setarr, $email, $openid_identifier);
		}
	}
}

function regiter_user_to_uchome($setarr, $openid_identifier){
	// //开通空间
	// include_once(S_ROOT.'./source/function_space.php');
	// $space = space_open($newuid, $username, 0, $email);

	include_once(S_ROOT.'./source/function_space.php');
	//开通空间
//	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('space')." WHERE uid='$setarr[uid]'");
//	if(!$space = $_SGLOBAL['db']->fetch_array($query)) {
		$space = space_open($setarr['uid'], $setarr['username'], 0, $email);
//	}		
	echo var_dump($query)."<br/>";
	echo var_dump($space)."space"."<br/>";
	$_SGLOBAL['member'] = $space;

	//实名
//	realname_set($space['uid'], $space['username'], $space['name'], $space['namestatus']);

	//检索当前用户
//	$query = $_SGLOBAL['db']->query("SELECT password FROM ".tname('member')." WHERE uid='$setarr[uid]'");
//	if($value = $_SGLOBAL['db']->fetch_array($query)) {
//		$setarr['password'] = addslashes($value['password']);
//	} else {
		//更新本地用户库
		inserttable('member', $setarr, 0, true);
//	}

	//更新本地用户库
	//inserttable('member', $setarr, 0, true);

	// 关联uid和openid
//	$db->query("INSERT INTO {$tablepre}user_openids VALUES (null,$newuid,'$openid_identifier')");
	
	$openids = array(
				'uid' => $setarr['uid'],
				'url' => $openid_identifier
	);
	inserttable('user_openids', $openids, 0, true);

	// showmessage($openidlang['msg_bind_ok'],$plugin_url);//绑定成功

	//默认好友
//	$flog = $inserts = $fuids = $pokes = array();
//	if(!empty($_SCONFIG['defaultfusername'])) {
//		$query = $_SGLOBAL['db']->query("SELECT uid,username FROM ".tname('space')." WHERE username IN (".simplode(explode(',', $_SCONFIG['defaultfusername'])).")");
//		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
//			$value = saddslashes($value);
//			$fuids[] = $value['uid'];
//			$inserts[] = "('$newuid','$value[uid]','$value[username]','1','$_SGLOBAL[timestamp]')";
//			$inserts[] = "('$value[uid]','$newuid','$username','1','$_SGLOBAL[timestamp]')";
//			$pokes[] = "('$newuid','$value[uid]','$value[username]','".addslashes($_SCONFIG['defaultpoke'])."','$_SGLOBAL[timestamp]')";
//			//添加好友变更记录
//			$flog[] = "('$value[uid]','$newuid','add','$_SGLOBAL[timestamp]')";
//		}/////////
//		if($inserts) {
//			$_SGLOBAL['db']->query("REPLACE INTO ".tname('friend')." (uid,fuid,fusername,status,dateline) VALUES ".implode(',', $inserts));
//			$_SGLOBAL['db']->query("REPLACE INTO ".tname('poke')." (uid,fromuid,fromusername,note,dateline) VALUES ".implode(',', $pokes));
//			$_SGLOBAL['db']->query("REPLACE INTO ".tname('friendlog')." (uid,fuid,action,dateline) VALUES ".implode(',', $flog));
//
//			//添加到附加表
//			$friendstr = empty($fuids)?'':implode(',', $fuids);
//			updatetable('space', array('friendnum'=>count($fuids), 'pokenum'=>count($pokes)), array('uid'=>$newuid));
//			updatetable('spacefield', array('friend'=>$friendstr, 'feedfriend'=>$friendstr), array('uid'=>$newuid));
//
//			//更新默认用户好友缓存
//			include_once(S_ROOT.'./source/function_cp.php');
//			foreach ($fuids as $fuid) {friend_cache($fuid);}
//		}
//	}

	//清理在线session
	insertsession($setarr);

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);
	ssetcookie('loginuser', $username, 31536000);
	ssetcookie('_refer', '');

	//同步登录
	if($_SCONFIG['uc_status']) {
		include_once S_ROOT.'./uc_client/client.php';
		$ucsynlogin = uc_user_synlogin($setarr['uid']);
	} else {
		$ucsynlogin = '';
	}

	//好友邀请
//	if($invitearr) {
//		//成为好友
//		invite_update($invitearr['id'], $setarr['uid'], $setarr['username'], $invitearr['uid'], $invitearr['username'], $app);
//	}
//	$_SGLOBAL['supe_uid'] = $space['uid'];
//	//判断用户是否设置了头像
//	$reward = $setarr = array();
//	$experience = $credit = 0;
//	$avatar_exists = ckavatar($space['uid']);
//	if($avatar_exists) {
//		if(!$space['avatar']) {
//			//奖励积分
//			$reward = getreward('setavatar', 0);
//			$credit = $reward['credit'];
//			$experience = $reward['experience'];
//			if($credit) {
//				$setarr['credit'] = "credit=credit+$credit";
//			}
//			if($experience) {
//				$setarr['experience'] = "experience=experience+$experience";
//			}
//			$setarr['avatar'] = 'avatar=1';
//			$setarr['updatetime'] = "updatetime=$_SGLOBAL[timestamp]";
//		}
//	} else {
//		if($space['avatar']) {
//			$setarr['avatar'] = 'avatar=0';
//		}
//	}
//
//	if($setarr) {
//		$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET ".implode(',', $setarr)." WHERE uid='$space[uid]'");
//	}
//
//	if(empty($_POST['refer'])) {
//		$_POST['refer'] = 'space.php?do=home';
//	}
//
//	realname_get();

	showmessage('login_success', $app?"userapp.php?id=$app":$_POST['refer'], 1, array($ucsynlogin));			
}



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