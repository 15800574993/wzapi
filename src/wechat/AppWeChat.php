<?php
namespace wangzhan\wechat;
use wangzhan\Http;
use wangzhan\Func;
use wangzhan\Error;
//+---------------------------------------------------------------
//|		get_code_openid 				code转openid和sessionid
//+---------------------------------------------------------------
//|		get_wx_app_token 				获取微信token
//+---------------------------------------------------------------
//|		wx_decrypt 						小程序信息解密
//+---------------------------------------------------------------
//|		createwxaqrcode 				获取小程序二维码，正常二维码，永久有效，有数量限制
//+---------------------------------------------------------------
//|		getwxacode 						获取小程序二维码，小程序二维码，永久有效，有数量限制
//+---------------------------------------------------------------
//|		getwxacodeunlimit 				获取小程序二维码，永久有效，无数量限制，有参数长度限制
//+--------------------------------------------------------------
//|		img_sec_check 					图片安全检查
//+--------------------------------------------------------------
//|		msg_sec_check 					文本安全检查
//+--------------------------------------------------------------

class AppWeChat {
	// 微信网络请求
	private $url 	=	array(
		"jscode2session"		=>	"https://api.weixin.qq.com/sns/jscode2session",				//	code转openid
		"token"					=>	"https://api.weixin.qq.com/cgi-bin/token",					//	token
		"createwxaqrcode"		=>	"https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode",	//	获取小程序二维码，适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制
		"getwxacode"			=>	"https://api.weixin.qq.com/wxa/getwxacode",					//	获取小程序码，适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制
		"getwxacodeunlimit"		=>	"https://api.weixin.qq.com/wxa/getwxacodeunlimit",			//	获取小程序码，适用于需要的码数量极多的业务场景。通过该接口生成的小程序码，永久有效，数量暂无限制
		"img_sec_check"			=>	"https://api.weixin.qq.com/wxa/img_sec_check",				//	小程序图片安全
		"msg_sec_check"			=>	"https://api.weixin.qq.com/wxa/msg_sec_check",				//	小程序内容安全
	);

	
	// 配置
	private $config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 			=	$config;
    }
    /**
     * @var    code转openid接口
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function get_code_openid($code) {
		$url 					=	$this->url['jscode2session'];
		$param 					=	array();
		$param['appid']			=	$this->config['app_appid'];
		$param['secret']		=	$this->config['app_appsecret'];
		$param['js_code']		=	$code;
		$param['grant_type']	=	"authorization_code";
		$ret 					=	Http::get_curl($this->url['jscode2session'],$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取openid失败");
    }

    /**
	 * @var    获取微信token
	 * @return [type] [description]
	 */
	public function get_wx_app_token() {
		// 检测是否配置token 如果配置实用配置的
		if ($this->config['app_token']){
			return $this->config['app_token'];
		}
		// 检测文件是否存在
		$app_cache_token 		= 	($this->config['app_cache_token'])?$this->config['app_cache_token']:__DIR__."/app_cache_token.json";
		if(!file_exists($app_cache_token)){
		    $myfile 		= 	fopen($app_cache_token, "w+") or $this->error("无法打开文件！");
			$txt 			= 	array("token"=>"","time"=>0);
			fwrite($myfile, json_encode($txt));
			fclose($myfile);
		}
	    $data    			= 	json_decode(file_get_contents($app_cache_token),true);
	    if ($data['time'] 	>= 	time()) {
	    	return $data['token'];
	    }
	    $param 				=	array();
	    $param["appid"]		=	$this->config['app_appid'];
	    $param["secret"]	=	$this->config['app_appsecret'];
	    $param['grant_type']=	"client_credential";
	    $ret 				=	Http::post_curl($this->url['token'],$param);
	    if ($ret['data']) {
	    	if (!array_key_exists("access_token",$ret['data'])) {
	    		$this->error($ret['data']['errmsg']);
	    	}
	    	// 写入文件
	    	$myfile         = 	fopen($app_cache_token,'r+');
	    	$txt 			= 	array("token"=>$ret['data']['access_token'],"time"=>time()+($this->config['app_cache_token_time']));
	        fwrite($myfile,json_encode($txt));
	        fclose($myfile);
	        return $ret['data']['access_token'];
	    }
	    $this->error("无法获取token");
	}

	/**
	 * @var    小程序解密接口
	 * @param  string $data        [encryptedData]
	 * @param  string $session_key [session_key]
	 * @param  string $iv          [iv]
	 * @return [type]              [description]
	 */
	public function wx_decrypt($data='',$session_key='',$iv=''){

	    $str  = urldecode($data);
	    $str  = str_replace(' ','+',$str);
	    $str  = base64_decode($str);
	    $keys = base64_decode($session_key);
	    $iv   = urldecode($iv);
	    $iv   = str_replace(' ','+',$iv);
	    $iv   = base64_decode($iv);
	    $decrypted_string = openssl_decrypt($str,"AES-128-CBC",$keys,OPENSSL_RAW_DATA,$iv);
	    if ($decrypted_string) {
	        return json_decode($decrypted_string,true);
	    } else {
	        return false;
	    }
	}

	/**
	 * @var 获取小程序二维码，正常二维码，永久有效，有数量限制
	 * @param  [type]  $path  [路径和参数]
	 * @param  integer $width [二维码宽度]
	 * @return [type]         [description]
	 */
	public function createwxaqrcode($path,$width = 430) {
		$param 						=	array();
		$param['path']				=	$path;
		$param['width']				=	($width <= 300)?300:$width;
		$ret 						=	Http::get_curl($this->url['createwxaqrcode']."?access_token=".$this->get_wx_app_token(),Func::JSON_JSON($param));
		if ($ret['data']){
			return 'data:image/png;base64,'.base64_encode($ret['data']);
		}
		$this->error("获取小程序二维码失败");
	}
	/**
	 * @var 获取小程序二维码，小程序二维码，永久有效，有数量限制
	 * @param  [type]  $path  [路径和参数]
	 * @param  integer $width [二维码宽度]
	 * @return [type]         [description]
	 */
	public function getwxacode($path,$width = 430) {
		$param 						=	array();
		$param['path']				=	$path;
		$param['width']				=	($width <= 300)?300:$width;
		$ret 						=	Http::get_curl($this->url['getwxacode']."?access_token=".$this->get_wx_app_token(),Func::JSON_JSON($param));
		if ($ret['data']){
			return 'data:image/png;base64,'.base64_encode($ret['data']);
		}
		$this->error("获取小程序二维码失败");
	}
	/**
	 * @var    获取小程序二维码，小程序二维码，永久有效，无数量限制，有参数长度限制
	 * @param  [type] $page  [路径]
	 * @param  [type] $width [宽度]
	 * @param  string $scene [参数]
	 * @return [type]        [description]
	 */
	public function getwxacodeunlimit($page,$width,$scene = "") {
		if (!$scene) {
			$this->error("获取小程序二维码失败");
		}
		$param 						=	array();
		if ($page) {
			$param['page']			=	$page;
		}
		$param['scene']				=	$scene;
		$ret 						=	Http::post_curl($this->url['getwxacodeunlimit']."?access_token=".$this->get_wx_app_token(),Func::JSON_JSON($param));
		if ($ret['data']){
			return 'data:image/png;base64,'.base64_encode($ret['data']);
		}
		$this->error("获取小程序二维码失败");
	}
	/**
	 * @var    图片安全检测
	 * @param  [type] $path [图片路径]
	 * @return [type]       [description]
	 */
	public function img_sec_check($path) {
		if(!file_exists($path)){
		    $this->error("图片地址错误");
		}
		$url 						=	$this->url['img_sec_check']."?access_token=".$this->get_wx_app_token();
		$param 						= 	array('media' => new \CURLFile(realpath($path)));
		$ret 						=	Http::post_curl($url,$param,array(CURLOPT_SAFE_UPLOAD=>true));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("图片检测失败");
	}
	/**
	 * @var    文本安全检测
	 * @param  [type] $path [图片路径]
	 * @return [type]       [description]
	 */
	public function msg_sec_check($text) {
		$url 						=	$this->url['msg_sec_check']."?access_token=".$this->get_wx_app_token();
		$param 						= 	'{"content":"'.$text.'"}';
		$ret 						=	Http::post_curl($url,$param,array(CURLOPT_SAFE_UPLOAD=>true));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("文本安全检测失败");
	}

	/**
	 * @var    错误信息
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	public function error($text) {
		return (new Error("微信小程序","2",$text));
	}


    

}