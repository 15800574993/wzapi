<?php
namespace wangzhan\tt;
use wangzhan\Http;
use wangzhan\Func;
use wangzhan\Error;
/**
 * @var   头条小程序相关
 */
//+---------------------------------------------------------------
//|		get_tt_token 						获取token
//+---------------------------------------------------------------
//|		exe_text 							文本检查
//+---------------------------------------------------------------
//|		exe_img 							图片检查
//+----------------------------------------------------------------
//|		login 								登陆接口
//+----------------------------------------------------------------
//|		qrcode 								获取二维码
//+----------------------------------------------------------------
class Ttapi {
	// 微信网络请求
	private $url 	=	array(
		"token"			=>	"https://developer.toutiao.com/api/apps/token?",				//	获取token
		"text"			=>	"https://developer.toutiao.com/api/v2/tags/text/antidirt",		//	敏感词检测
		"image"			=>	"https://developer.toutiao.com/api/v2/tags/image",				//	敏感图片检测
		"jscode2session"=>	"https://developer.toutiao.com/api/apps/jscode2session",
		"qrcode"		=>	"https://developer.toutiao.com/api/apps/qrcode",
	);
	
	// 配置
	private $config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 		=	$config;
		if (!$this->config['tt_appid'] || !$this->config['tt_appsecret']) {
	    	$this->error("缺少appid/appsecret配置");
	    }
    }

    /**
	 * @var    获取头条token
	 * @return [type] [description]
	 */
	public function get_tt_token() {
		// 检测是否配置token 如果配置实用配置的
		if ($this->config['tt_token']){
			return $this->config['tt_token'];
		}
		// 检测文件是否存在
		$tt_cache_token 		= 	($this->config['tt_cache_token'])?$this->config['tt_cache_token']:__DIR__."/tt_cache_token.json";
		if(!file_exists($tt_cache_token)){
		    $myfile 		= 	fopen($tt_cache_token, "w") or $this->error("无法打开文件！");
			$txt 			= 	array("token"=>"","time"=>0);
			fwrite($myfile, json_encode($txt));
			fclose($myfile);
		}
	    $data    			= 	json_decode(file_get_contents($tt_cache_token),true);
	    if ($data['time'] 	>= 	time()) {
	    	return $data['token'];
	    }
	    $param 				=	array();
	    $param["appid"]		=	$this->config['tt_appid'];
	    $param["secret"]	=	$this->config['tt_appsecret'];
	    $param['grant_type']=	"client_credential";

	    $ret 				=	Http::get_curl($this->url['token']."?".Func::arr_query($param),$param);
	    if (is_array($ret['data'])) {
	    	// 写入文件
	    	$myfile         = 	fopen($tt_cache_token,'r+');
	    	$txt 			= 	array("token"=>$ret['data']['access_token'],"time"=>time()+($this->config['cache_token_time']));
	        fwrite($myfile,json_encode($txt));
	        fclose($myfile);
	        return $ret['data']['access_token'];
	    }
	    $this->error("无法获取token");
	}


	/**
	 * @var    文本内容检测
	 * @param  [type] $text [需要检测的内容]
	 * @return [type]       [description]
	 */
	public function exe_text($text) {
		$param 					=	array();
		$param['tasks']			=	array(array('content'=>urldecode($text)));
		$headers 				=	array();
		$headers[] 				= 	'X-Token: '.$this->get_tt_token();
		$h[CURLOPT_HTTPHEADER]	=	$headers;
		$ret 					=	Http::post_curl($this->url['text'],$param,$h);
		if (($ret['data'])) {
	        return $ret['data'];
	    }
	    $this->error("无法获取token");
	}

	/**
	 * @var    图片容检测
	 * @param  [type] $text [需要检测的内容]
	 * @return [type]       [description]
	 */
	public function exe_img($img) {
		$param 					=	array();
		$param['tasks']			=	array(array('image'=>urldecode($img)));
		$headers 				=	array();
		$headers[] 				= 	'X-Token: '.$this->get_tt_token();
		$h[CURLOPT_HTTPHEADER]	=	$headers;
		$ret 					=	Http::post_curl($this->url['image'],$param,$h);

		if (($ret['data'])) {
	        return $ret['data'];
	    }
	    $this->error("无法获取token");
	}
	/**
	 * @var    登陆接口
	 * @param  [type] $code   [code]
	 * @param  [type] $ancode [ancode]
	 * @return [type]         [description]
	 */
	public  function login($code,$ancode) {
		$param					=	array();
        $param['appid']			=	$this->config['tt_appid'];
        $param['secret']		=	$this->config['tt_appsecret'];
        $param['code']			=	$code;
        $param['ancode']		=	$ancode;
        $ret 					=	Http::get_curl($this->url['jscode2session']."?".http_build_query($param),array());

		if (($ret['data'])) {
	        return $ret['data'];
	    }
        $this->error("无法获取数据");
	}

	/**
	 * @var    获取二维码
	 * @param  [type] $path    [路径]
	 * @param  [type] $arr     [参数数组]
	 * @param  [type] $appname [对应字节系 app]
	 * @return [type]          [description]
	 */
	public  function qrcode($path,$arr,$appname) {
		$param 					=	array();
		$param['access_token']	=	$this->get_tt_token();
		$param['appname']		=	$appname;
		$param['path']			=	urlencode($path."?".http_build_query($arr));

		$ret 					=	Http::get_curl($this->url['qrcode'],$param);
		if (($ret['data'])) {
	        return $ret['data'];
	    }
        $this->error("无法获取数据");
	
	}

	/**
	 * @var    错误信息
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	public function error($text) {
		return (new Error("字节小程序","3",$text));
	}

}