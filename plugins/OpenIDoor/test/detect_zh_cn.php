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
    $out .= $r->h2('����֧��');//'Math support'
    $ext = Auth_OpenID_detectMathLibrary(Auth_OpenID_math_extensions());
    if (!isset($ext['extension']) || !isset($ext['class'])) {
        $out .= $r->p('��ǰPHP��������������֧��(big integer math)����SSL��ȫ���ӵ�OpenID����İ�ȫ�Խ��޷���֤');
					 /*'Your PHP installation does not include big integer math ' .
					   'support. This support is required if you wish to run a ' .
					   'secure OpenID server without using SSL.'*/

        $out .= $r->p('Ҫʹ��OpenID Library,������ѡ��:');/*'To use this library, you have a few options:'*/

        $gmp_lnk = $r->link('http://www.php.net/manual/en/ref.gmp.php', 'GMP');
        $bc_lnk = $r->link('http://www.php.net/manual/en/ref.bc.php', 'bcmath');
        $out .= $r->ol(array(
			'��װ ' .$gmp_lnk .' PHP ��չ', // 'Install the ' . $gmp_lnk . ' PHP extension',       
			'��װ ' .$bc_lnk . 'PHP ��չ',//'Install the ' . $bc_lnk . ' PHP extension',           
			'������վ���ǵ͵ȼ��İ�ȫ����' .
			'����Auth/OpenID/BigMath.php�ж����Auth_OpenID_setNoMathSupport(),',
			'OpenID Library��������ʹ�ã�����OpenID����İ�ȫ�Խ�ȡ��վ�㣨���ӣ�����'.
			'�����ֻ����һ��Consumer(������)֧�ֹ��ܣ����Ѿ��ܹ���ȫ�������ˡ�'));
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
            $out .= $r->p('PHP�����Ѿ�����BCMATCH֧�֣�����֧��С��ģ�ļ���(Ӧ��)�����Ǹ���ȷ�������ģ�ؼ���(Ӧ��)�������Ҫ����֧��GMP��չ');
		/*'Your PHP installation has bcmath support. This is ' .
          'adequate for small-scale use, but can be CPU-intensive. ' .
          'You may want to look into installing the GMP extension.'*/

            $lnk = $r->link('http://www.php.net/manual/en/ref.gmp.php');

           /* $out .= $r->p('See ' . $lnk .' for more information ' .
                          'about the GMP extension.');*/
			$out .= $r->p('�鿴 ' . $lnk .' ��ø������GMP ��չ֧�ֵ���Ϣ ');
            break;
        case 'gmp':
           /* $out .= $r->p('Your PHP installation has gmp support. Good.');*/
			$out .= $r->p('�ܺã����PHP�Ѿ�����֧����GMP��չ��');
            break;
        default:
            $class = $ext['class'];
            $lib = new $class();
            $one = $lib->init(1);
            $two = $lib->add($one, $one);
            $t = $lib->toString($two);

            /*$out .= $r->p('Uh-oh. I do not know about the ' .
                          $ext['extension'] . ' extension!');*/	
			$out .= $r->p('Uh-oh. û�й��� ' .
                          $ext['extension'] . ' ��չ����Ϣ');

            /*if ($t != '2') {
                $out .= $r->p('It looks like it is broken. 1 + 1 = ' .
                  var_export($t, false));				
                return false;
            } else {
                $out .= $r->p('But it seems to be able to add one and one.');
            }*/
			if ($t != '2') {
                $out .= $r->p('�������. 1 + 1 = ' .
                  var_export($t, false));				
                return false;
            } else {
                $out .= $r->p('�������ƺ��ܹ���һ���');
            }
        }
        return true; // Math library is OK
    }
}

function detect_random($r, &$out)
{
    /*$out .= $r->h2('Cryptographic-quality randomness source');*/
	$out .= $r->h2('���������������Դ');
    if (Auth_OpenID_RAND_SOURCE === null) {
        /*$out .= $r->p('Using (insecure) pseudorandom number source, because ' .
                      'Auth_OpenID_RAND_SOURCE has been defined as null.');*/
	      $out .= $r->p('��ΪAuth_OpenID_RAND_SOURCE�Ѿ�������Ϊnull,�ʲ���α�����Դ');
        return false;
    }

    /*$msg = 'The library will try to access ' . Auth_OpenID_RAND_SOURCE
        . ' as a source of random data. ';*/
	$msg = 'OpenID Library��ʹ�� ' . Auth_OpenID_RAND_SOURCE
        . ' ��Ϊ�����Դ����. ';

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
        $msg .= '�����˳� ';
		/*$msg .= 'It seems to exist ';*/
        if ($dataok) {
            /*$msg .= 'and be readable. Here is some hex data: ' . */ 
			$msg .= '������һЩ�ɶ���16��������: ' .
                bin2hex($data) . '.';
        } else {
            /*$msg .= 'but reading data failed.';���Ƕ�ȡ����ʧ��*/
			$msg .= '��ȡ����ʧ��.';
        }
        if ($size) {
            /*$msg .= ' This is a ' . $size . ' byte file. Unless you know ' .
                'what you are doing, it is likely that you are making a ' .
                'mistake by using a regular file as a randomness source.';*/
			/*����һ�� XX byte��С���ļ�,������֪��������ʲô��������ʹ��һ���̶����ĵ���Ϊ�����Դ*/
			$msg .= ' ����һ����СΪ ' . $size . ' Byte ���ļ�. �����棺�㽫ʹ��һ���й��ɵġ�ȷ�����ĵ�(regular file)��Ϊ�����Դ';
        }
    } else {
        /*$msg .= Auth_OpenID_RAND_SOURCE .
            ' could not be opened. This could be because of restrictions on' .
            ' your PHP environment or that randomness source may not exist' .
            ' on this platform.';*/
		$msg .= Auth_OpenID_RAND_SOURCE .
            ' �޷��򿪣����������PHP���ã�������PHP���ô�����߸�����Դû���˳�';
        if (IS_WINDOWS) {
            /*$msg .= ' You seem to be running Windows. This library does not' .
                ' have access to a good source of randomness on Windows.';*/
			$msg .= ' ������ʹ��windowsϵͳ��OpenID��û�ܻ��һ���������������Դ';
        }
        $ok = false;
    }

    $out .= $r->p($msg);

    if (!$ok) {
        $out .= $r->p('����һ�������Դ��������Auth_OpenID_RAND_SOURCEΪԴ·��'.
					'������ϵͳ��֧����������ã�OpenID Library����ʹ�õͰ�ȫ�����α�����ģʽ��'.
					'Ҫʹ��α�����ģʽ��������Auth_OpenID_RAND_SOURCEΪNull');
		$out .= $r->p('����������:');
        $out .= $r->pre(php_uname());
        $out .= $r->p('����Դ��Ч��'.
					  '��Unixƽ̨������MacOS X�� �볢������Ϊ/dev/random �Լ�/dev/urandom');
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
	$out .= $r->h2('���ݴ洢');//'Data storage'

    $found = array();
    foreach (array('sqlite', 'mysql', 'pgsql') as $dbext) {
        if (extension_loaded($dbext) || @dl($dbext . '.' . PHP_SHLIB_SUFFIX)) {
            $found[] = $dbext;
        }
    }
    if (count($found) == 0) {
        $text = '��ǰPHP�������ò�֧��SQL���ݿ⣬����֧����鿴PHP�ֲ�.';
	/*if (count($found) == 0) {
        $text = 'No SQL database support was found in this PHP ' .
            'installation. See the PHP manual if you need to ' .
            'use an SQL database.';*/
    } else {
        $text = '���ҵ���֧�� ';//Support was found for
        if (count($found) == 1) {
            $text .= $found[0] . '.';
        } else {
            $last = array_pop($found);
            $text .= implode(', ', $found) . ' �� ' . $last . '.';//'and'
        }
	$text = $r->b($text);
    }
    $text .= 'OpenID Library���õ�֧��MySQL��PostgreSql��SQLite�����������Լ���ͨ���ļ��洢�� ' .
        'PEAR DB��OpenID Library֧�����ݿ�ı�����չ��';
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
		$out .= $r->p('ʹ��SQLite���ݿ�,��Ҫ ' . $web_user . ' Ȩ�ޡ�');//��������������������������������������������
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
		$out .= $r->p('FileSystem Store����SQLite֧����ζ��' . $lnk . ' ����Ч�ģ� ' .
                      '���ݽ��洢������λ���У�');
        $out .= $r->pre(var_export($basedir_str, true));
    } else {
       /* $out .= $r->p('The ' . $r->b($r->tt('open_basedir')) . ' configuration restriction ' .
		      'is not in effect.');*/
		$out .= $r->p($r->b($r->tt('open_basedir')) . ' ��Ч������' );
    }

    /*$out .= $r->p('If you are using the filesystem store, your ' .
                  'data directory must be readable and writable by ' .
                  $web_user . ' and not availabe over the Web.');*/
	$out .= $r->p('�ļ��޷���д��ʹ��FileSystem Store������ ' . $web_user . ' Ȩ�ޡ�');
    return true;
}

function detect_xml($r, &$out)
{
    global $__Auth_Yadis_xml_extensions;

    $out .= $r->h2('XML ֧��');/*XML Support*/

    // Try to get an XML extension.
    $ext = Auth_Yadis_getXMLParser();

    /*if ($ext !== null) {
        $out .= $r->p('XML parsing support is present using the '.
                      $r->b(get_class($ext)).' interface.');*/
	if ($ext !== null) {
        $out .= $r->p('����ʹ�ýӿ� '.
                      $r->b(get_class($ext)).' ����XML.');
        return true;
    } else {
        /*$out .= $r->p('XML parsing support is absent; please install one '.
                      'of the following PHP extensions:');*/
		$out .= $r->p('XML�޷��������볢�԰�װ�������ȡPHP��Ӧ����չ:');
        foreach ($__Auth_Yadis_xml_extensions as $name => $cls) {
            $out .= "<li>" . $r->b($name) . "</li>";
        }
        return false;
    }
}

function detect_fetcher($r, &$out)
{
    $out .= $r->h2('HTTP ��ȡ');//HTTP Fetching

    $result = @include 'Auth/Yadis/Yadis.php';

    if (!$result) {
        $out .= $r->p('�޷���ȡyadis.php����,�޷����HTTP��ȡ����');/*Yadis code unavailable; could not test fetcher support.*/
	return false;
    }

    if (Auth_Yadis_Yadis::curlPresent()) {
        $out .= $r->p('��ǰPHP����֧��libcurl(�������ò�ͬ��Э�����Ӻ͹�ͨ��ͬ�ķ�����)��');/*This PHP installation has support for libcurl. Good.*/
    } else {
        /*$out .= $r->p('This PHP installation does not have support for ' .
                      'libcurl. CURL is not required but is recommended. '.
                      'The OpenID library will use an fsockopen()-based fetcher.');
        $lnk = $r->link('http://us3.php.net/manual/en/ref.curl.php');
        $out .= $r->p('See ' . $lnk . ' about enabling the libcurl support ' .
                      'for PHP.');*/
		$out .= $r->p('��ǰPHP������֧��libcurl��' .
						'Libcurl֧�ֲ���OpenID Library����ģ�' .
						'�����Ƽ�����֧��Libcurl��' .
						'OpenID Library��ʹ��fsockopen()-based (����Զ�̶�ȡ)��');
        $lnk = $r->link('http://us3.php.net/manual/en/ref.curl.php');
        $out .= $r->p('������ ' . $lnk . ' �鿴�������libcurl֧�֡�');
    }

    $ok = true;
    $fetcher = Auth_Yadis_Yadis::getHTTPFetcher();
    $fetch_url = 'http://www.openidenabled.com/resources/php-fetch-test';
    $expected_url = $fetch_url . '.txt';
    $result = $fetcher->get($fetch_url);

    if (isset($result)) {
        $parts = array('һ��HTTP��������ɡ�');/*An HTTP request was completed.*/
        // list ($code, $url, $data) = $result;
        if ($result->status != '200' && $result->status != '206') {
            $ok = false;
            $parts[] = $r->b(
                sprintf(
					'��ǰHTTP״̬Ϊ %s ��Ԥ��״̬Ϊcode(200 or 206)',$result->status));
                    /*'Got %s instead of the expected HTTP status ' .
                    'code (200 or 206).', $result->status));*/
        }

        $url = $result->final_url;
        if ($url != $expected_url) {
            $ok = false;
            if ($url == $fetch_url) {
                $msg = 'û���ض����URL���ӡ�';/*The redirected URL was not returned.*/
            } else {
                $msg = '��Ч��URL���ӣ� <' . $url . '>.';/*An unexpected URL was returned:*/
            }
            $parts[] = $r->b($msg);
        }

        $data = $result->body;
        if ($data != 'Hello World!') {
            $ok = false;
            $parts[] = $r->b('��Ч�ķ������ݡ�');/*Unexpected data was returned.*/
        }
        $out .= $r->p(implode(' ', $parts));
    } else {
        $ok = false;
        $out .= $r->p('ȡ�� URL ' . $lnk . ' ʧ��!');/*Fetching URL ' . $lnk . ' failed!*/
    }

    if ($fetcher->supportsSSL()) {
        $out .= $r->p('���PHP�Ѿ�֧��SSL��ȫ���ӣ��ܹ�֧��HTTPS��OpenID�����֤��');
						/*Your PHP installation appears to support SSL, so it ' .
                      'will be able to process HTTPS identity URLs and server URLs.*/
    } else {
        $out .= $r->p('���PHPδ����֧��SSL��ȫ���ӣ���֧��HTTPS��OpenID�����֤��');
						/*Your PHP installation does not support SSL, so it ' .
                      'will NOT be able to process HTTPS identity URLs and server URLs.*/
    }

    return $ok;
}

//header('Content-Type: ' . $r->contentType() . '; charset=us-ascii');
$title = 'OpenID Library ֧�ֱ���';/*OpenID Library Support Report*/
$out = $r->start($title) .
    $r->h1($title) .
    $r->p('����׼��ʹ��JanRain PHP OpenID library������ű���������PHP������');
	/*This script checks your PHP installation to determine if you ' .
          'are set up to use the JanRain PHP OpenID library.*/

$body = '';

$_include = include 'Auth/OpenID.php';

if (!$_include) {
    $path = ini_get('include_path');
    $body .= $r->p(
        'û���ҵ�OpenID�⣬����OpenID���Ƿ������PHP include·���С�'.
		'�㵱ǰ��PHP include·��Ϊ:');
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
        $out .= $r->h2('����ȫ�����!');/*Setup Complete!*/
	$out .= $r->p('���ϵͳ�Ѿ�֧��ʹ��OpenID Library�ˡ�');/*Your system should be ready to run the OpenID library.*/
    } else {
        $out .= $r->h2('����δ���');/*Setup Incomplete*/
	$out .= $r->p('û�����ȫ�����ã�������OpenID��֮ǰ������Ҫ�������ĳЩϵͳ���á�');
				/*Your system needs a few changes before it will be ready to run the OpenID library.*/
    }
}

$out .= $body . $r->end();
print $out;

?>
