<?php

// OpenID Language Pack for Discuz! Version 1.0.0
// Translated by wukan

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['openidlang'] = array(
(
	'openid_login' => '��¼',
	'submit' => '�ύ',
	'openid_intro' => '<a href="http://www.openidoor.com/openidintro/openidfaq.html" target=_blank>1.ʲô��OpenID��</a>',
	'openid_guide' => '<a href="http://www.openidoor.com/openidintro/useopenid.html" target=_blank>2.OpenIDʹ��ָ��</a>',
	'openid_sites' => '<a href="http://www.openidoor.com/openidsource.html" target=_blank>3.OpenIDվ��</a>',
	'openid_auth' => '��֤�С���',
	'openid_add' => '���',
	'openid_del' => 'ɾ��',
	'openid_list' => 'OpenID�б�',
	'openid_msg' => 'OpenID��Ϣ',
	'openid_select' =>'OpenID��ѡ�� - ��ǰOpenID URL��'.$_SESSION['openid_identifier'],
	'openid_tip' => '���<font color=red>3����</font>��δ��ɵ�¼�󶨻�ע��󶨣��󶨲������Զ�ʧЧ',
	'bind_intro_login' => "1.�����<a href=do.php?ac=login><font color=red>��¼��</font></a>�����뵽��¼���棬��¼�ɹ��ٽ����ҳ����Զ��뵱ǰOpenID��",
	'bind_intro_reg' => "2.�����<a href=do.php?ac=register><font color=red>ע���</font></a>�����뵽ע����棬ע��ɹ��ٽ����ҳ����Զ��뵱ǰOpenID��",
	'bind_intro_cancel' => "3.�����<a href=".$plugin_url."?openid_action=cancel><font color=red>ȡ����</font></a>����ȡ���뵱ǰOpenID�İ󶨲���",
	'msg_bind_error' => $_SESSION['openid_identifier'].' �Ѿ������ˡ�',
	'msg_bind_ok' => $_SESSION['openid_identifier']." <===> $username �󶨳ɹ�",
	'del_alert' => '�����Ҫȫ��ɾ���𣿽������ٱ���һ����¼���Է����������û�а���������´��˺���ȫ��ʧ��'
);

?>