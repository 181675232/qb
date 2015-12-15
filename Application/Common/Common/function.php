<?php
/**
 * 按josn方式输出通信数据
 * @param integer $code 状态码
 * @param string $message 提示信息
 * @param array $data 数据
 */
function json($code,$message='',$data=array()){
	if (!is_numeric($code)){
		return '';
	}

	$result = array(
			'code' => $code,
			'message' => $message,
			'data' => $data
	);

	echo json_encode($result);
	exit;

}

/**
 * 获取当前页面完整URL地址
 */
function geturl() {
	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

//计算两地之间距离
function  powc($lat_a,$lng_a,$lat_b,$lng_b){
	$pk = 180 / 3.1415926;
	$a1 = $lat_a / $pk;
	$a2 = $lng_a / $pk;
	$b1 = $lat_b / $pk;
	$b2 = $lng_b / $pk;

	$t1 = cos($a1) * cos($a2) * cos($b1) * cos($b2);
	$t2 = cos($a1) * sin($a2) * cos($b1) * sin($b2);
	$t3 = sin($a1) * sin($b1);
	$tt = acos($t1 + $t2 + $t3);
	$distance = round((6366000 * $tt));
	return $distance;
}
//数组分页
function array_page($array,$page,$count='15'){
	$page=(empty($page))?'1':$page; #判断当前页面是否为空 如果为空就表示为第一页面
	$start=($page-1)*$count; #计算每次分页的开始位置
	$totals=count($array);
	$pagedata=array();
	$pagedata=array_slice($array,$start,$count);
	return $pagedata;  #返回查询数据
}
//$curl post发送请求
function sendPostSMS($url,$data=array()){	
	
	$ch = curl_init();
	
	curl_setopt ($ch, CURLOPT_URL, $url);
	
	curl_setopt ($ch, CURLOPT_POST, 1);
	
	if($data != ''){
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$file_contents = curl_exec($ch);
	
	curl_close($ch);
	
	return $file_contents;
}
// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_code($code, $id = ''){ 
	$verify = new \Think\Verify(); 
	return $verify->check($code, $id);
}
//是否为空
function checkNull($_data) {
	if (trim($_data) == '') return true;
	return false;
}

//数据是否为数字
function checkNum($_data) {
	if (is_numeric($_data)) return true;
	return false;
}

//二维数组去重
function take($arr){
	$tmp_array = array();
	$new_array = array();
	foreach($arr as $k => $val){
		$hash = md5(json_encode($val));
		if (!in_array($hash, $tmp_array)) {
			$tmp_array[] = $hash;
			$new_array[] = $val;
		}
	}
	return $new_array;
}


//长度是否合法
function checkLength($_data, $_length, $_flag) {
	if ($_flag == 'min') {
		if (mb_strlen(trim($_data),'utf-8') < $_length) return true;
		return false;
	} elseif ($_flag == 'max') {
		if (mb_strlen(trim($_data),'utf-8') > $_length) return true;
		return false;
	} elseif ($_flag == 'equals') {
		if (mb_strlen(trim($_data),'utf-8') != $_length) return true;
		return false;
	} else {
		alertBack('EROOR：参数传递的错误，必须是min,max！');
	}
}
//验证电子邮件
function checkEmail($_data) {
	if (preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/',$_data)) return true;
	return false;
}

//验证手机
function checkPhone($_data) {
	if (preg_match('/^((1[3,5,8][0-9])|(14[5,7])|(17[0,6,7,8]))\d{8}$/',$_data)) return true;
	return false;
}

//账号
function checkUser($_data) {
	if (preg_match('/^[a-zA-Z0-9]+$/',$_data)) return true;
	return false;
}

//数据是否一致
function checkEquals($_data, $_otherdate) {
	if (trim($_data) == trim($_otherdate)) return true;
	return false;
}


//弹窗跳转
function alertLocation($_info, $_url) {
	if (!empty($_info)) {
		echo "<script type='text/javascript'>alert('$_info');location.href='$_url';</script>";
		exit();
	} else {
		header('Location:'.$_url);
		exit();
	}
}

//弹窗返回
function alertBack($_info) {
	echo "<script type='text/javascript'>alert('$_info');history.back();</script>";
	exit();
}

//弹窗返回刷新
function alertReplace($_info) {
	echo "<script type='text/javascript'>alert('$_info');location.replace(document.referrer);</script>";
	exit();
}

//不弹窗返回刷新
function jsReplace() {
	echo "<script type='text/javascript'>location.replace(document.referrer);</script>";
	exit();
}

//br换p
function nl2p($string, $line_breaks = true, $xml = true){
    // Remove existing HTML formatting to avoid double-wrapping things
    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);
     
    // It is conceivable that people might still want single line-breaks
    // without breaking into a new paragraph.
    if ($line_breaks == true)
        return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '<br'.($xml == true ? ' /' : '').'>'), trim($string)).'</p>';
    else
        return '<p>'.preg_replace("/([\n]{1,})/i", "</p>\n<p>", trim($string)).'</p>';
}

/**
 * 截取UTF-8编码下字符串的函数
 */
function sub_str($str, $length = 0, $append = true){
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength){
        return $str;
    }
    elseif ($length < 0)
    {
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr')){
        $newstr = mb_substr($str, 0, $length, 'utf-8');
    }
    elseif (function_exists('iconv_substr'))
    {
        $newstr = iconv_substr($str, 0, $length, 'utf-8');
    }
    else
    {
        //$newstr = trim_right(substr($str, 0, $length));
        $newstr = substr($str, 0, $length);
    }

   if ($append && $str != $newstr)
    {
        $newstr .= '...';
    }
    return $newstr;
}
//验证码

function yzm($phone)
{
	$vcodes = '';
	for($i=0;$i<6;$i++){$authnum=rand(1,9);$vcodes.=$authnum;}//生成验证码
	$username = 'caimantangcn';		//用户账号
	$password = 'caimantang123';	//密码
	$apikey = 'e1127a31a9dd2dee4ec9cc325da5b580';//密码
	$mobile	 = '86'.$phone;	//号手机码

	$content = '您的短信验证码是：'.$vcodes.'，本次验证码有效期为5分钟！【Q帮】';//内容
	// 		$this->session->set_userdata('time', time());
	// 		$this->session->set_userdata('mcode', $vcodes);//将content的值保存在session中
	$result = sendSMS($username,$password,$mobile,$content,$apikey);
	$data['code'] = $vcodes;
	return json('200','成功！',$data);
}
function sendSMS($username,$password,$mobile,$content,$apikey)
{
	$url = 'http://m.5c.com.cn/api/send/?';
	$data = array
	(
			'username'=>$username,					//用户账号
			'password'=>$password,				//密码
			'mobile'=>$mobile,					//号码
			'content'=>$content,				//内容
			'apikey'=>$apikey,				    //apikey
	);
	$result= curlSMS($url,$data);			//POST方式提交
	return $result;
}

function curlSMS($url,$post_fields=array()){
	$ch=curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3600); //60秒
	curl_setopt($ch, CURLOPT_HEADER,1);
	curl_setopt($ch, CURLOPT_REFERER,'http://www.yourdomain.com');
	curl_setopt($ch,CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_fields);
	$data = curl_exec($ch);
	curl_close($ch);
	$res = explode("\r\n\r\n",$data);
	return $res[2];
}