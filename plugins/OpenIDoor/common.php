
<?php
set_include_path( dirname(__FILE__) . PATH_SEPARATOR . get_include_path() );


function displayError($message) {
    showmessage($message);
}

function doIncludes() {
    /**
     * Require the OpenID consumer code.
     */
    require_once "Auth/OpenID/Consumer.php";

    /**
     * Require the "file store" module, which we'll need to store
     * OpenID information.
     */
    require_once "Auth/OpenID/FileStore.php";
	require_once "Auth/OpenID/MySQLStore.php";

    /**
     * Require the Simple Registration extension API.
     */
    require_once "Auth/OpenID/SReg.php";

    /**
     * Require the PAPE extension module.
     */
    //require_once "Auth/OpenID/PAPE.php";
}

doIncludes();

/*global $pape_policy_uris;
$pape_policy_uris = array(
			  PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
			  PAPE_AUTH_MULTI_FACTOR,
			  PAPE_AUTH_PHISHING_RESISTANT
			  );*/

function &getStore() {
    /**
     * This is where the example will store its OpenID information.
     * You should change this path if you want the example store to be
     * created elsewhere.  After you're done playing with the example
     * script, you'll have to remove this directory manually.
     */

//如果总是出现Server denied check_authentication错误，
//你可以试着修改用Auth_OpenID_FileStore记录关联信息，
//而把Auth_OpenID_MySQLStore这一段注释

//Auth_OpenID_FileStore--开始
/*	$store_path = 'tmp';//由于会产生大量文件，最好设置为到其它分区
    if (!file_exists($store_path) &&
        !mkdir($store_path)) {
        print "Could not create the FileStore directory '$store_path'. ".
            " Please check the effective permissions.";
        exit(0);
    }
    return new Auth_OpenID_FileStore($store_path);*/
//Auth_OpenID_FileStore--结束

//Auth_OpenID_MySQLStore--开始
	global $dsn,$tablepre;
	require_once('DB.php');	//引入数据库相关的类
	$openid_db = &DB::connect($dsn);
 	return new Auth_OpenID_MySQLStore($openid_db,$tablepre.'associations',$tablepre.'nonces');
//Auth_OpenID_MySQLStore--结束
	
}

function &getConsumer() {
    /**
     * Create a consumer object using the store object created
     * earlier.
     */
    $store = getStore();
    $consumer =& new Auth_OpenID_Consumer($store);
    return $consumer;
}

function getScheme() {
    $scheme = 'http';
    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
        $scheme .= 's';
    }
    return $scheme;
}

function getReturnTo() {
	// 如果部署在根路径
	if (dirname($_SERVER['PHP_SELF']) == "/"){
		return sprintf("%s://%s:%s/OpenID.php",
			                   getScheme(), $_SERVER['SERVER_NAME'],
			                   $_SERVER['SERVER_PORT']);
	}
	else{
    return sprintf("%s://%s:%s%s/OpenID.php",
                   getScheme(), $_SERVER['SERVER_NAME'],
                   $_SERVER['SERVER_PORT'],
                   dirname($_SERVER['PHP_SELF']));		
	}
}


function getTrustRoot() {
    return sprintf("%s://%s:%s%s",
                   getScheme(), $_SERVER['SERVER_NAME'],
                   $_SERVER['SERVER_PORT'],
                   dirname($_SERVER['PHP_SELF']));
}
echo getTrustRoot();
?>