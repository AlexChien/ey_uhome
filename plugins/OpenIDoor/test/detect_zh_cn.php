<?php

$path_extra = dirname(dirname(__FILE__));
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

define('IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

class PlainText {
    function start($title)
    {
        return '';
    }

    function tt($text)
    {
        return $text;
    }

    function link($href, $text=null)
    {
        if ($text) {
            return $text . ' <' . $href . '>';
        } else {
            return $href;
        }
    }

    function b($text)
    {
        return '*' . $text . '*';
    }

    function contentType()
    {
        return 'text/plain';
    }

    function p($text)
    {
        return wordwrap($text) . "\n\n";
    }

    function pre($text)
    {
        $out = '';
        $lines = array_map('trim', explode("\n", $text));
        foreach ($lines as $line) {
            $out .= '    ' . $line . "\n";
        }
        $out .= "\n";
        return $out;
    }

    function ol($items)
    {
        $out = '';
        $c = 1;
        foreach ($items as $item) {
            $item = wordwrap($item, 72);
            $lines = array_map('trim', explode("\n", $item));
            $out .= $c . '. ' . $lines[0] . "\n";
            unset($lines[0]);
            foreach ($lines as $line) {
                $out .= '   ' . $line . "\n";
            }
            $out .= "\n";
            $c += 1;
        }
        return $out;
    }

    function h2($text)
    {
        return $this->h($text, 2);
    }

    function h1($text)
    {
        return $this->h($text, 1);
    }

    function h($text, $n)
    {
        $chars = '#=+-.';
        $c = $chars[$n - 1];
        return "\n" . $text . "\n" . str_repeat($c, strlen($text)) . "\n\n";
    }

    function end()
    {
        return '';
    }
}

class HTML {
    function start($title)
    {
        return '<html><head><title>' . $title . '</title>' .
            $this->stylesheet().
            '</head><body>' . "\n";
    }

    function stylesheet()
    {
        return "<style type='text/css'>\n".
            "p {\n".
            "  width: 50em;\n".
            "}\n".
            '</style>';
    }

    function tt($text)
    {
        return '<code>' . $text . '</code>';
    }

    function contentType()
    {
        return 'text/html';
    }

    function b($text)
    {
        return '<strong>' . $text . '</strong>';
    }

    function p($text)
    {
        return '<p>' . wordwrap($text) . "</p>\n";
    }

    function pre($text)
    {
        return '<pre>' . $text . "</pre>\n";
    }

    function ol($items)
    {
        $out = '<ol>';
        foreach ($items as $item) {
            $out .= '<li>' . wordwrap($item) . "</li>\n";
        }
        $out .= "</ol>\n";
        return $out;
    }

    function h($text, $n)
    {
        return "<h$n>$text</h$n>\n";
    }

    function h2($text)
    {
        return $this->h($text, 2);
    }

    function h1($text)
    {
        return $this->h($text, 1);
    }

    function link($href, $text=null)
    {
        return '<a href="' . $href . '">' . ($text ? $text : $href) . '</a>';
    }

    function end()
    {
        return "</body>\n</html>\n";
    }
}

if (isset($_SERVER['REQUEST_METHOD'])) {
    $r = new HTML();
} else {
    $r = new PlainText();
}

function detect_math($r, &$out)
{
    $out .= $r->h2('大数支持');//'Math support'
    $ext = Auth_OpenID_detectMathLibrary(Auth_OpenID_math_extensions());
    if (!isset($ext['extension']) || !isset($ext['class'])) {
        $out .= $r->p('当前PHP环境不包括大数支持(big integer math)，非SSL安全连接的OpenID服务的安全性将无法保证');
					 /*'Your PHP installation does not include big integer math ' .
					   'support. This support is required if you wish to run a ' .
					   'secure OpenID server without using SSL.'*/

        $out .= $r->p('要使用OpenID Library,有如下选择:');/*'To use this library, you have a few options:'*/

        $gmp_lnk = $r->link('http://www.php.net/manual/en/ref.gmp.php', 'GMP');
        $bc_lnk = $r->link('http://www.php.net/manual/en/ref.bc.php', 'bcmath');
        $out .= $r->ol(array(
			'安装 ' .$gmp_lnk .' PHP 扩展', // 'Install the ' . $gmp_lnk . ' PHP extension',       
			'安装 ' .$bc_lnk . 'PHP 扩展',//'Install the ' . $bc_lnk . ' PHP extension',           
			'如果你的站点是低等级的安全级别，' .
			'调用Auth/OpenID/BigMath.php中定义的Auth_OpenID_setNoMathSupport(),',
			'OpenID Library将能正常使用，但是OpenID服务的安全性将取决站点（连接）本身。'.
			'如果你只是作一个Consumer(消费者)支持功能，你已经能够安全地运作了。'));
            /*'If your site is low-security, call ' .
            'Auth_OpenID_setNoMathSupport(), defined in Auth/OpenID/BigMath.php. ',
			'The library will function, but ' .
            'the security of your OpenID server will depend on the ' .
            'security of the network links involved. If you are only ' .
            'using consumer support, you should still be able to operate ' .
            'securely when the users are communicating with a ' .
            'well-implemented server.'*/
        return false;
    } else {
        switch ($ext['extension']) {
        case 'bcmath':
            $out .= $r->p('PHP环境已经配置BCMATCH支持，它能支持小规模的计算(应用)，但是更精确、更大规模地计算(应用)你可能需要配置支持GMP扩展');
		/*'Your PHP installation has bcmath support. This is ' .
          'adequate for small-scale use, but can be CPU-intensive. ' .
          'You may want to look into installing the GMP extension.'*/

            $lnk = $r->link('http://www.php.net/manual/en/ref.gmp.php');

           /* $out .= $r->p('See ' . $lnk .' for more information ' .
                          'about the GMP extension.');*/
			$out .= $r->p('查看 ' . $lnk .' 获得更多关于GMP 扩展支持的信息 ');
            break;
        case 'gmp':
           /* $out .= $r->p('Your PHP installation has gmp support. Good.');*/
			$out .= $r->p('很好，你的PHP已经配置支持了GMP扩展。');
            break;
        default:
            $class = $ext['class'];
            $lib = new $class();
            $one = $lib->init(1);
            $two = $lib->add($one, $one);
            $t = $lib->toString($two);

            /*$out .= $r->p('Uh-oh. I do not know about the ' .
                          $ext['extension'] . ' extension!');*/	
			$out .= $r->p('Uh-oh. 没有关于 ' .
                          $ext['extension'] . ' 扩展的信息');

            /*if ($t != '2') {
                $out .= $r->p('It looks like it is broken. 1 + 1 = ' .
                  var_export($t, false));				
                return false;
            } else {
                $out .= $r->p('But it seems to be able to add one and one.');
            }*/
			if ($t != '2') {
                $out .= $r->p('计算错误. 1 + 1 = ' .
                  var_export($t, false));				
                return false;
            } else {
                $out .= $r->p('但是它似乎能够逐一添加');
            }
        }
        return true; // Math library is OK
    }
}

function detect_random($r, &$out)
{
    /*$out .= $r->h2('Cryptographic-quality randomness source');*/
	$out .= $r->h2('高质量的随机加密源');
    if (Auth_OpenID_RAND_SOURCE === null) {
        /*$out .= $r->p('Using (insecure) pseudorandom number source, because ' .
                      'Auth_OpenID_RAND_SOURCE has been defined as null.');*/
	      $out .= $r->p('因为Auth_OpenID_RAND_SOURCE已经被定义为null,故采用伪随机数源');
        return false;
    }

    /*$msg = 'The library will try to access ' . Auth_OpenID_RAND_SOURCE
        . ' as a source of random data. ';*/
	$msg = 'OpenID Library将使用 ' . Auth_OpenID_RAND_SOURCE
        . ' 作为随机数源访问. ';

    $numbytes = 6;

    $f = @fopen(Auth_OpenID_RAND_SOURCE, 'r');
    if ($f !== false) {
        $data = fread($f, $numbytes);
        $stat = fstat($f);
        $size = $stat['size'];
        fclose($f);
    } else {
        $data = null;
        $size = true;
    }

    if ($f !== false) {
        $dataok = (Auth_OpenID::bytes($data) == $numbytes);
        $ok = $dataok && !$size;
        $msg .= '程序将退出 ';
		/*$msg .= 'It seems to exist ';*/
        if ($dataok) {
            /*$msg .= 'and be readable. Here is some hex data: ' . */ 
			$msg .= '以下是一些可读的16进制数据: ' .
                bin2hex($data) . '.';
        } else {
            /*$msg .= 'but reading data failed.';但是读取数据失败*/
			$msg .= '读取数据失败.';
        }
        if ($size) {
            /*$msg .= ' This is a ' . $size . ' byte file. Unless you know ' .
                'what you are doing, it is likely that you are making a ' .
                'mistake by using a regular file as a randomness source.';*/
			/*这是一个 XX byte大小的文件,除非你知道你在做什么，你正在使用一个固定的文档作为随机数源*/
			$msg .= ' 这是一个大小为 ' . $size . ' Byte 的文件. ！警告：你将使用一个有规律的、确定的文档(regular file)作为随机数源';
        }
    } else {
        /*$msg .= Auth_OpenID_RAND_SOURCE .
            ' could not be opened. This could be because of restrictions on' .
            ' your PHP environment or that randomness source may not exist' .
            ' on this platform.';*/
		$msg .= Auth_OpenID_RAND_SOURCE .
            ' 无法打开，（请检查你的PHP配置）可能是PHP配置错误或者该数据源没有退出';
        if (IS_WINDOWS) {
            /*$msg .= ' You seem to be running Windows. This library does not' .
                ' have access to a good source of randomness on Windows.';*/
			$msg .= ' 你正在使用windows系统，OpenID库没能获得一个高质量的随机数源';
        }
        $ok = false;
    }

    $out .= $r->p($msg);

    if (!$ok) {
        $out .= $r->p('设置一个随机数源，请设置Auth_OpenID_RAND_SOURCE为源路径'.
					'如果你的系统不支持随机数设置，OpenID Library可以使用低安全级别的伪随机数模式，'.
					'要使用伪随机数模式，请设置Auth_OpenID_RAND_SOURCE为Null');
		$out .= $r->p('你正运行在:');
        $out .= $r->pre(php_uname());
        $out .= $r->p('数据源无效。'.
					  '类Unix平台（包括MacOS X） 请尝试设置为/dev/random 以及/dev/urandom');
		/*$out .=$r->p('To set a source of randomness, define Auth_OpenID_RAND_SOURCE ' .
            'to the path to the randomness source. If your platform does ' .
            'not provide a secure randomness source, the library can' .
            'operate in pseudorandom mode, but it is then vulnerable to ' .
            'theoretical attacks. If you wish to operate in pseudorandom ' .
            'mode, define Auth_OpenID_RAND_SOURCE to null.');
        $out .= $r->p('You are running on:');
        $out .= $r->pre(php_uname());
        $out .= $r->p('There does not seem to be an available source ' .
                      'of randomness. On a Unix-like platform ' .
                      '(including MacOS X), try /dev/random and ' .
                      '/dev/urandom.');*/
    }
    return $ok;
}

function detect_stores($r, &$out)
{
	$out .= $r->h2('数据存储');//'Data storage'

    $found = array();
    foreach (array('sqlite', 'mysql', 'pgsql') as $dbext) {
        if (extension_loaded($dbext) || @dl($dbext . '.' . PHP_SHLIB_SUFFIX)) {
            $found[] = $dbext;
        }
    }
    if (count($found) == 0) {
        $text = '当前PHP环境配置不支持SQL数据库，配置支持请查看PHP手册.';
	/*if (count($found) == 0) {
        $text = 'No SQL database support was found in this PHP ' .
            'installation. See the PHP manual if you need to ' .
            'use an SQL database.';*/
    } else {
        $text = '能找到的支持 ';//Support was found for
        if (count($found) == 1) {
            $text .= $found[0] . '.';
        } else {
            $last = array_pop($found);
            $text .= implode(', ', $found) . ' 和 ' . $last . '.';//'and'
        }
	$text = $r->b($text);
    }
    $text .= 'OpenID Library良好的支持MySQL，PostgreSql，SQLite等搜索引擎以及普通的文件存储。 ' .
        'PEAR DB是OpenID Library支持数据库的必需扩展。';
    $out .= $r->p($text);

    if (function_exists('posix_getpwuid') &&
        function_exists('posix_geteuid')) {
        $processUser = posix_getpwuid(posix_geteuid());
        $web_user = $r->b($r->tt($processUser['name']));
    } else {
        $web_user = 'the PHP process';
    }

    if (in_array('sqlite', $found)) {
       /* $out .= $r->p('If you are using SQLite, your database must be ' .
                      'writable by ' . $web_user . ' and not available over' .
                      ' the web.');*/
		$out .= $r->p('使用SQLite数据库,需要 ' . $web_user . ' 权限。');//？？？？？？？？？？？？？？？？？？？？？？
    }

    $basedir_str = ini_get('open_basedir');
    if (gettype($basedir_str) == 'string') {
        $url = 'http://www.php.net/manual/en/features.safe-mode.php' .
            '#ini.open-basedir';
        $lnk = $r->link($url, 'open_basedir');
        /*$out .= $r->p('If you are using a filesystem-based store or SQLite, ' .
                      'be aware that ' . $lnk . ' is in effect. This means ' .
                      'that your data will have to be stored in one of the ' .
                      'following locations:');*/
		$out .= $r->p('FileSystem Store或者SQLite支持意味着' . $lnk . ' 是有效的， ' .
                      '数据将存储在以下位置中：');
        $out .= $r->pre(var_export($basedir_str, true));
    } else {
       /* $out .= $r->p('The ' . $r->b($r->tt('open_basedir')) . ' configuration restriction ' .
		      'is not in effect.');*/
		$out .= $r->p($r->b($r->tt('open_basedir')) . ' 无效的配置' );
    }

    /*$out .= $r->p('If you are using the filesystem store, your ' .
                  'data directory must be readable and writable by ' .
                  $web_user . ' and not availabe over the Web.');*/
	$out .= $r->p('文件无法读写，使用FileSystem Store必须有 ' . $web_user . ' 权限。');
    return true;
}

function detect_xml($r, &$out)
{
    global $__Auth_Yadis_xml_extensions;

    $out .= $r->h2('XML 支持');/*XML Support*/

    // Try to get an XML extension.
    $ext = Auth_Yadis_getXMLParser();

    /*if ($ext !== null) {
        $out .= $r->p('XML parsing support is present using the '.
                      $r->b(get_class($ext)).' interface.');*/
	if ($ext !== null) {
        $out .= $r->p('正在使用接口 '.
                      $r->b(get_class($ext)).' 解析XML.');
        return true;
    } else {
        /*$out .= $r->p('XML parsing support is absent; please install one '.
                      'of the following PHP extensions:');*/
		$out .= $r->p('XML无法解析，请尝试安装解析或获取PHP相应的扩展:');
        foreach ($__Auth_Yadis_xml_extensions as $name => $cls) {
            $out .= "<li>" . $r->b($name) . "</li>";
        }
        return false;
    }
}

function detect_fetcher($r, &$out)
{
    $out .= $r->h2('HTTP 获取');//HTTP Fetching

    $result = @include 'Auth/Yadis/Yadis.php';

    if (!$result) {
        $out .= $r->p('无法获取yadis.php内容,无法完成HTTP获取测试');/*Yadis code unavailable; could not test fetcher support.*/
	return false;
    }

    if (Auth_Yadis_Yadis::curlPresent()) {
        $out .= $r->p('当前PHP环境支持libcurl(允许你用不同的协议连接和沟通不同的服务器)。');/*This PHP installation has support for libcurl. Good.*/
    } else {
        /*$out .= $r->p('This PHP installation does not have support for ' .
                      'libcurl. CURL is not required but is recommended. '.
                      'The OpenID library will use an fsockopen()-based fetcher.');
        $lnk = $r->link('http://us3.php.net/manual/en/ref.curl.php');
        $out .= $r->p('See ' . $lnk . ' about enabling the libcurl support ' .
                      'for PHP.');*/
		$out .= $r->p('当前PHP环境不支持libcurl。' .
						'Libcurl支持不是OpenID Library必需的，' .
						'但是推荐配置支持Libcurl。' .
						'OpenID Library将使用fsockopen()-based (基本远程读取)。');
        $lnk = $r->link('http://us3.php.net/manual/en/ref.curl.php');
        $out .= $r->p('可以在 ' . $lnk . ' 查看更多关于libcurl支持。');
    }

    $ok = true;
    $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
    $fetch_url = 'http://www.openidenabled.com/resources/php-fetch-test';
    $expected_url = $fetch_url . '.txt';
    $result = $fetcher->get($fetch_url);

    if (isset($result)) {
        $parts = array('一个HTTP请求处理完成。');/*An HTTP request was completed.*/
        // list ($code, $url, $data) = $result;
        if ($result->status != '200' && $result->status != '206') {
            $ok = false;
            $parts[] = $r->b(
                sprintf(
					'当前HTTP状态为 %s ，预期状态为code(200 or 206)',$result->status));
                    /*'Got %s instead of the expected HTTP status ' .
                    'code (200 or 206).', $result->status));*/
        }

        $url = $result->final_url;
        if ($url != $expected_url) {
            $ok = false;
            if ($url == $fetch_url) {
                $msg = '没有重定向的URL链接。';/*The redirected URL was not returned.*/
            } else {
                $msg = '无效的URL链接： <' . $url . '>.';/*An unexpected URL was returned:*/
            }
            $parts[] = $r->b($msg);
        }

        $data = $result->body;
        if ($data != 'Hello World!') {
            $ok = false;
            $parts[] = $r->b('无效的返回数据。');/*Unexpected data was returned.*/
        }
        $out .= $r->p(implode(' ', $parts));
    } else {
        $ok = false;
        $out .= $r->p('取出 URL ' . $lnk . ' 失败!');/*Fetching URL ' . $lnk . ' failed!*/
    }

    if ($fetcher->supportsSSL()) {
        $out .= $r->p('你的PHP已经支持SSL安全连接，能够支持HTTPS的OpenID身份认证。');
						/*Your PHP installation appears to support SSL, so it ' .
                      'will be able to process HTTPS identity URLs and server URLs.*/
    } else {
        $out .= $r->p('你的PHP未设置支持SSL安全连接，不支持HTTPS的OpenID身份认证。');
						/*Your PHP installation does not support SSL, so it ' .
                      'will NOT be able to process HTTPS identity URLs and server URLs.*/
    }

    return $ok;
}

//header('Content-Type: ' . $r->contentType() . '; charset=us-ascii');
$title = 'OpenID Library 支持报告';/*OpenID Library Support Report*/
$out = $r->start($title) .
    $r->h1($title) .
    $r->p('你正准备使用JanRain PHP OpenID library，这个脚本将检测你的PHP环境。');
	/*This script checks your PHP installation to determine if you ' .
          'are set up to use the JanRain PHP OpenID library.*/

$body = '';

$_include = include 'Auth/OpenID.php';

if (!$_include) {
    $path = ini_get('include_path');
    $body .= $r->p(
        '没有找到OpenID库，请检查OpenID库是否在你的PHP include路径中。'.
		'你当前的PHP include路径为:');
	/*Cannot find the OpenID library. It must be in your PHP include ' .
        'path. Your PHP include path is currently:*/
    $body .= $r->pre($path);
} else {
    $status = array();

    $status[] = detect_math($r, $body);
    $status[] = detect_random($r, $body);
    $status[] = detect_stores($r, $body);
    $status[] = detect_fetcher($r, $body);
    $status[] = detect_xml($r, $body);

    $result = true;

    foreach ($status as $v) {
        if (!$v) {
            $result = false;
            break;
        }
    }

    if ($result) {
        $out .= $r->h2('配置全部完成!');/*Setup Complete!*/
	$out .= $r->p('你的系统已经支持使用OpenID Library了。');/*Your system should be ready to run the OpenID library.*/
    } else {
        $out .= $r->h2('配置未完成');/*Setup Incomplete*/
	$out .= $r->p('没有完成全部配置，在运行OpenID库之前，你需要更改你的某些系统设置。');
				/*Your system needs a few changes before it will be ready to run the OpenID library.*/
    }
}

$out .= $body . $r->end();
print $out;

?>
