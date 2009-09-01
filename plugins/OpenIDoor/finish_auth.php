<?php
function run() {
    $consumer = getConsumer();

    // Complete the authentication process using the server's
    // response.
    $return_to = getReturnTo();
    $response = $consumer->complete($return_to);

    // Check the response status.
    if ($response->status == Auth_OpenID_CANCEL) {
        // This means the authentication was cancelled.
        $msg = '验证被取消。';
		//showmessage('cancel_openid_auth');
    } else if ($response->status == Auth_OpenID_FAILURE) {
        // Authentication failed; display the error message.
        $msg = "OpenID 认证失败： " . $response->message;
    } else if ($response->status == Auth_OpenID_SUCCESS) {
        // This means the authentication succeeded; extract the
        // identity URL and Simple Registration data (if it was
        // returned).
        
        // 将openid记录到session里，在session超时时间内，由以后的逻辑绑定到已注册的uid上。
        $_SESSION['openid_identifier'] = $response->getDisplayIdentifier();
				$_SESSION['openid_binding'] = time();//标记可以绑定了，但在发现是可直接登录用户时在要unset

        if ($response->endpoint->canonicalID) {
					$_SESSION['xri_canonicalid'] = $response->endpoint->canonicalID;
        }

        $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
				$_SESSION['openid_sreg'] = $sreg_resp->contents();//NOTE:记录SREG到会话
		//print var_dump($_SESSION['openid_sreg']);
		_OpenID_Action();//添加动作
    }
	displayError($msg);
}

run();

?>