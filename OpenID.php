<?php
/**
 * @author wukan
 * @copyright Copyright &copy; 2008, OpenIDoor.com
 * @version 1.1
 * @date 2008-07-22
 */
@session_start();//开启会话
define('OPENID_ROOT',dirname(__FILE__).DIRECTORY_SEPARATOR.'./plugins/OpenIDoor/');//OpenID文件夹目录

require_once(OPENID_ROOT.'OpenID.cfg.php');	//有数据库连接语句
require_once(OPENID_ROOT.'common.php');		//连接数据库和

if(!empty($_REQUEST['openid_action'])) //将OpenID输入域隐藏域记录到SESSION
	$_SESSION['openid_action'] = $_REQUEST['openid_action'];

if(empty($_REQUEST['openid_identifier']))//通过openid_identifier变量是否存在调用不同阶段的处理功能
{
	require_once(OPENID_ROOT.'OpenID.func.php');	//动作定向语句
	include_once(OPENID_ROOT.'finish_auth.php');
}
else
	include_once(OPENID_ROOT.'try_auth.php');
?>