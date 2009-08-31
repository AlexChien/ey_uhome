<?php
$succes ="Test success!"; 
$failed ="Test failed!";

if(!empty($_REQUEST['url']))
{	
	$handle = curl_init($_REQUEST['url']);

	if(!empty($_POST['trust'])){//如果选择了无论如何都信任对方的安全连接谈证书	
		curl_setopt($handle,CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($handle,CURLOPT_SSL_VERIFYHOST,0);
	}
	$contents = curl_exec($handle);
	curl_close($handle);
	
	echo $contents;
	echo "<hr/>";
	if(!empty($contents)){
		echo "<font color=green>".$succes."</font>";
	}else{
		echo "<font color=red>".$failed."</font>";
	}
}
?>
<body >
<hr/>
<form method="POST">
  <table align="center">
    <tr>
      <td> 测试链接：
        <input name="url" type="text" id="url" value="<?php echo @$_REQUEST['url'];?>" size="50"/>
        <input type="submit" name="button" id="button" value="测试" /></td>
    </tr>
    <tr>
      <td><input type="checkbox" name="trust" value="yes"/>
        无论如何都信任对方安全连接的证书，仅为HTTPS协议时有效。<br>
        如果您必须选择此项才可以测试成功，可能要添加根证书信任</td>
    </tr>
    <tr>
      <td>输入格式:http://yourname.openid.cn 或https://me.yahoo.com/yourname等</td>
    </tr>
    <tr>
      <td><font color="#0000FF">有部分站点在测试时总提示“not a valid OpenID”,可能是站点的防火墙将主动连接到外部的连接都拦截了。<br>
      用本工具可以进行测试。如果测试成功会有提示，并会在本网页开头显示所取网页的内容。</font></td>
    </tr>
  </table>
</form>
</body>
