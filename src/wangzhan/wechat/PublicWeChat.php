<?php
namespace wangzhan\wechat;
use wangzhan\Http;
use wangzhan\Func;
use wangzhan\Error;
//+---------------------------------------------------------------
//|		get_wx_token 						获取token
//+---------------------------------------------------------------
//|		get_ticket 							获取微信ticket
//+---------------------------------------------------------------
//|		add_menu 							创建菜单
//+---------------------------------------------------------------
//|		get_menu_info 						获取菜单
//+---------------------------------------------------------------
//|		add_conditional 					创建个性化菜单
//+----------------------------------------------------------------
//|		del_conditional 					删除个性化菜单
//+----------------------------------------------------------------
//|		test_trymatch 						测试个性化菜单
//+----------------------------------------------------------------
//|		menu_get 							获取自定义菜单配置
//+----------------------------------------------------------------
//|		wx_authorize 						获取微信授权地址
//+---------------------------------------------------------------
//|		get_wx_code 						code 转 tokan和openid
//+---------------------------------------------------------------
//|		get_code_openid_info 				跟据code获取用户信息
//+---------------------------------------------------------------
//|		get_openid_info 					根据openid获取用户信息
//+---------------------------------------------------------------
//|		get_sign_package 					获取分享第三方配置
//+---------------------------------------------------------------
//|		pull_openid 						拉取用户openid
//+---------------------------------------------------------------
//|		send_user_msg 						发送客服文本消息
//+---------------------------------------------------------------
//|		upload_media_id 					上传临时素材
//+---------------------------------------------------------------
//|		get_media_get 						获取临时素材
//+---------------------------------------------------------------
//|		add_material						上传永久素材
//+---------------------------------------------------------------
//|		get_material 						获取永久素材
//+---------------------------------------------------------------
//|		del_material 						删除永久素材
//+---------------------------------------------------------------
//|		batchget_material 					获取永久素材列表
//+---------------------------------------------------------------
//|		add_tags_create 					创建标签
//+---------------------------------------------------------------
//|		get_tags 							获取标签
//+---------------------------------------------------------------
//
class PublicWeChat {
	// 微信网络请求
	private $url 	=	array(
		"token"			=>	"https://api.weixin.qq.com/cgi-bin/token?",				//	获取token
		"getticket" 	=>	"https://api.weixin.qq.com/cgi-bin/ticket/getticket",	//	获取ticket
		"create"		=>	"https://api.weixin.qq.com/cgi-bin/menu/create",		//	创建菜单
		"get_menu_info"	=>	"https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info",	//	获取菜单
		"add_conditional"=>	"https://api.weixin.qq.com/cgi-bin/menu/addconditional",		//	创建个性化菜单
		"del_conditional"=>	"https://api.weixin.qq.com/cgi-bin/menu/delconditional",		//	删除个性化菜单
		"test_trymatch"	=>	"https://api.weixin.qq.com/cgi-bin/menu/trymatch",				//	测试个性化菜单
		"menu_get"		=>	"https://api.weixin.qq.com/cgi-bin/menu/get",					//	获取自定义菜单配置
		"authorize"		=>	"https://open.weixin.qq.com/connect/oauth2/authorize",	//	授权地址
		"access_token"	=>	"https://api.weixin.qq.com/sns/oauth2/access_token",	//	code 转 tokan和openid
		"userinfo"		=>	"https://api.weixin.qq.com/sns/userinfo",				//	根据openid和授权token获取用户信息
		"info"			=>	"https://api.weixin.qq.com/cgi-bin/user/info",			//	openid获取用户信息
		"user_get"		=>	"https://api.weixin.qq.com/cgi-bin/user/get",			//	拉取用户openid
		"send"			=>	"https://api.weixin.qq.com/cgi-bin/message/custom/send",//	发送客服消息
		"media_upload"	=>	"https://api.weixin.qq.com/cgi-bin/media/upload",		// 上传 临时素材管理返回media_id
		"media_get"		=>	"https://api.weixin.qq.com/cgi-bin/media/get",			//	获取临时素材
		"add_material"	=>	"https://api.weixin.qq.com/cgi-bin/material/add_material",	//	上传永久素材
		"get_material"	=>	"https://api.weixin.qq.com/cgi-bin/material/get_material",	//	获取永久素材
		"del_material"	=>	"https://api.weixin.qq.com/cgi-bin/material/del_material",	//	删除永久素材
		"batchget_material"=>"https://api.weixin.qq.com/cgi-bin/material/batchget_material",// 获取永久素材列表
		"add_tags_create"=>	"https://api.weixin.qq.com/cgi-bin/tags/create",			//	创建标签
		"get_tags"		=>	"https://api.weixin.qq.com/cgi-bin/tags/get",				//	获取标签
	);
	// 配置
	private 		$config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 		=	$config;
    }
    // 更新配置
    public static function update_config($config = array()) {
    	$this->config 		=	$config;
    } 
   	/**
	 * @var    获取微信token
	 * @return [type] [description]
	 */
	public function get_wx_token() {

		// 检测是否配置token 如果配置实用配置的
		if ($this->config['token']){
			return $this->config['token'];
		}
		// 检测文件是否存在
		$cache_token 		= 	($this->config['cache_token'])?$this->config['cache_token']:__DIR__."/cache_token.json";
		if(!file_exists($cache_token)){
		    $myfile 		= 	fopen($cache_token, "w+") or $this->error("无法打开文件！");
			$txt 			= 	array("token"=>"","time"=>0);
			fwrite($myfile, json_encode($txt));
			fclose($myfile);
		}
	    $data    			= 	json_decode(file_get_contents($cache_token),true);
	    if ($data['time'] 	>= 	time()) {
	    	return $data['token'];
	    }
	    $param 				=	array();
	    $param["appid"]		=	$this->config['appid'];
	    $param["secret"]	=	$this->config['appsecret'];
	    $param['grant_type']=	"client_credential";
	    $ret 				=	Http::post_curl($this->url['token'],$param);
	    if ($ret['data']) {
	    	// 写入文件
	    	$myfile         = 	fopen($cache_token,'r+');
	    	$txt 			= 	array("token"=>$ret['data']['access_token'],"time"=>time()+($this->config['cache_token_time']));
	        fwrite($myfile,json_encode($txt));
	        fclose($myfile);
	        return $ret['data']['access_token'];
	    }
	    $this->error("无法获取token");
	}

	/**
    *   @var    获取ticket  
    **/
    public   function get_ticket() {
    	// 检测文件是否存在
		$cache_ticket 		= 	($this->config['cache_ticket'])?$this->config['cache_ticket']:__DIR__."/cache_ticket.json";
		if(!file_exists($cache_ticket)){
		    $myfile 		= 	fopen($cache_ticket, "w+") or $this->error("无法打开文件！");
			$txt 			= 	array("ticket"=>"","time"=>0);
			fwrite($myfile, json_encode($txt));
			fclose($myfile);
		}
	    $data    			= 	json_decode(file_get_contents($cache_ticket),true);
	    if ($data['time'] 	>= 	time()) {
	    	return $data['ticket'];
	    }
	    $param 					=	array();
	    $param['access_token']	=	$this->get_wx_token();
	    $param['type']			=	"jsapi";

	    $ret 				=	Http::post_curl($this->url['getticket'],$param);
	    if ($ret['data']) {
	    	// 写入文件
	    	$myfile         = 	fopen($cache_ticket,'r+');
	    	$txt 			= 	array(
	    							"ticket"=>$ret['data']['ticket'],
	    							"time"=>time()+($this->config['cache_ticket_time'])
	    						);
	        fwrite($myfile,json_encode($txt));
	        fclose($myfile);
	        return $ret['data']['ticket'];
	    }
	    $this->error("无法获取ticket"); 
    }

    /**
     * @var   上传菜单接口
     * @param [type] $data [description]
     */
    public  function add_menu($data) {
		$json 					= 	Func::JSON_JSON($data);
		$ret 					=	Http::get_curl($this->url['create']."?access_token=".$this->get_wx_token(),$json);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("上传菜单失败");
    }


    /**
     * @var    获取菜单接口
     * @return [type] [description]
     */
    public function get_menu_info() {
		$ret 					=	Http::get_curl($this->url['get_menu_info']."?access_token=".$this->get_wx_token(),array());
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取菜单失败");
    }

    /**
     * @var   创建个性化菜单
     */
    public function add_conditional($data,$matchrule) {
    	$data['matchrule']		=	$matchrule;
    	$json 					= 	Func::JSON_JSON($data);
		$ret 					=	Http::post_curl($this->url['add_conditional']."?access_token=".$this->get_wx_token(),$json);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("创建个性化菜单失败");
    }
    /**
     * @var  删除个性化菜单
     * @param  [type] $menuid [个性化菜单id]
     * @return [type]         [description]
     */
    public function del_conditional($menuid) {
    	$data 					=	array();
    	$data['menuid']			=	$menuid;
    	$json 					= 	Func::JSON_JSON($data);
		$ret 					=	Http::post_curl($this->url['del_conditional']."?access_token=".$this->get_wx_token(),$json);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("删除个性化菜单失败");
    }
    /**
     * @var    测试个性化菜单
     * @param  [type] $openid [description]
     * @return [type]         [description]
     */
    public function test_trymatch($openid) {
    	$data 					=	array();
    	$data['user_id']		=	$openid;
    	$json 					= 	Func::JSON_JSON($data);
		$ret 					=	Http::post_curl($this->url['test_trymatch']."?access_token=".$this->get_wx_token(),$json);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("测试个性化菜单失败");
    }

    /**
     * @var    获取自定义菜单配置
     * @return [type] [description]
     */
    public  function menu_get() {
    	$ret 					=	Http::get_curl($this->url['menu_get']."?access_token=".$this->get_wx_token(),array());
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("测试个性化菜单失败");
    }





	/**
	 * @var    微信授权地址生成
	 * @param  [type] $redirect_uri [description]
	 * @return [type]               [description]
	 */
	public  function wx_authorize($redirect_uri,$state = 123){
	  $url        = $this->url["authorize"]."?appid=".$this->config['appid']."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_login,snsapi_userinfo&state=".$state."#wechat_redirect";
	  return $url;
	}

	/**
	 * @var    获取微微信授权信息
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	public function get_wx_code($code) {
	  	$param 					=	array();
	  	$param['appid']			=	$this->config['appid'];
	  	$param['secret']		=	$this->config['appsecret'];
	  	$param['code']			=	$code;
	  	$param['grant_type']	=	"authorization_code";
		$ret 					=	Http::get_curl($this->url['access_token'],$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取失败");
	}
	/**
	 * @var    根据openid和授权sccess_token获取用户信息
	 * @param  [type] $openid [description]
	 * @return [type]       [description]
	 */
	public  function get_code_openid_info($code,$openid = "") {

		if (!$code && !$openid) {
			$this->error("参数值不能全部为空CODE|OPENID");
		}
		if ($openid) {
			return $this->get_openid_info($openid);
		}

		$param					=	$this->get_wx_code($code);
		if (!$param['openid']) {
			return $param;
		}
		$openid 				=	$param['openid'];
		$access_token 			=	$param['access_token'];
		$param 					=	array();
	  	$param['openid']		=	$openid;
	  	$param['access_token']	=	$access_token;
	  	$param['lang']			=	"zh_CN";
		$ret 					=	Http::get_curl($this->url['userinfo'],$param);
		
		

		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取失败");
	}

	/**
	 * @var    根据openid获取用户信息
	 * @param  [type] $openid [description]
	 * @return [type]       [description]
	 */
	public function get_openid_info($openid) {
		$param 						=	array();
		$param['access_token']		=	$this->get_wx_token();
		$param['openid']			=	$openid;
		$param['lang']				=	"zh_CN";
		$ret 						=	Http::get_curl($this->url['info'],$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取用户信息失败");
	}

	/**
    *   @author     王佩双 <250570889@qq.com>
    *   @var        获取微信分享信息  
    ***/
    public  function get_sign_package($url = ""){
        $nonceStr       = Func::get_nonce_str();
        $timestamp      = time();
        $jsapiTicket    = $this->get_ticket();
        $string         = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature      = sha1($string);
        return          array(
            "appId"         => config('app.WEIXINPAY_CONFIG')['appid'],
            "nonceStr"      => $nonceStr,
            "timestamp"     => $timestamp,
            "url"           => $url,
            "signature"     => $signature,
            "rawString"     => $string,
        );
    }

    /**
     * @var    拉取用户openid
     * @param  string $openid [description]
     * @return [type]         [description]
     */
    public function pull_openid($openid = "") {
    	$param 					=	array();
    	$param['access_token']	=	$this->get_wx_token();
    	if ($openid) {
    		$param['next_openid']=	$openid;
    	}
    	$ret 						=	Http::get_curl($this->url['user_get'],$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("拉取用户openid失败");
    }


    /**
	 * @var    发送客服文本消息
	 * @param  [type] $text  	[description]
	 * @param  [type] $openid 	[description]
	 * @return [type]         	[description]
	 */
	public function send_user_msg($openid,$text) {
	    $param = '{
	            "touser":"'.$openid.'",
	            "msgtype":"text",
	            "text":
	            {
	                 "content":"'.$text.'"
	            }
	    }';
	    $ret 						=	Http::get_curl($this->url['send']."?access_token=".$this->get_wx_token(),$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("发送客服文本消息失败");
	}

	/**
	 * @var    上传临时素材
	 * @param  [type] $path [路径]
	 * @param  [type] $type [类型  image ]
	 * @return [type]       [description]
	 */
	public function upload_media_id($path,$type) {
		$url 						=	$this->url['media_upload']."?access_token=".$this->get_wx_token()."&type=".$type;
		$param 						= 	array('media' => new \CURLFile(realpath($path)));
		$ret 						=	Http::post_curl($url,$param,array(CURLOPT_SAFE_UPLOAD=>true));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("上传临时素材失败");
	}

	/**
	 * @var    获取临时素材
	 * @return [type] [description]
	 */
	public function get_media_get($media_id) {
		$url 						=	$this->url['media_get']."?access_token=".$this->get_wx_token()."&media_id=".$media_id;
		$ret 						=	Http::get_curl($url,array(),array(),false);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取临时素材失败");
	}

	/**
	 * @var  上传永久素材
	 * @param  [type] $path [路径]
	 * @param  [type] $type [类型  image ]
	 * @return [type]       [description]
	 */
	public function add_material($path,$type) {
		$url 						=	$this->url['add_material']."?access_token=".$this->get_wx_token()."&type=".$type;
		$param 						= 	array('media' => new \CURLFile(realpath($path)));
		$ret 						=	Http::post_curl($url,$param,array(CURLOPT_SAFE_UPLOAD=>true));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("上传临时素材失败");
	}
	/**
	 * @var    获取永久素材
	 * @param  [type] $media_id [description]
	 * @return [type]           [description]
	 */
	public function get_material($media_id) {
		$url 						=	$this->url['get_material']."?access_token=".$this->get_wx_token();
		$param 						=	array();
		$param['media_id']			=	$media_id;
		$ret 						=	Http::get_curl($url,Func::JSON_JSON($param),array(),array(),false);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取永久素材失败");
	}
	/**
	 * @var    删除永久素材
	 * @param  [type] $media_id [description]
	 * @return [type]           [description]
	 */
	public function del_material($media_id) {
		$url 						=	$this->url['del_material']."?access_token=".$this->get_wx_token();
		$param 						=	array();
		$param['media_id']			=	$media_id;
		$ret 						=	Http::post_curl($url,Func::JSON_JSON($param));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("删除永久素材失败");
	}

	/**
	 * @var    获取永久素材列表
	 * @return [type] [description]
	 */
	public function batchget_material($type = "image",$offset = 0,$count=20) {
	    $url 						=	$this->url['batchget_material']."?access_token=".$this->get_wx_token();
		$param 						=	array();
		$param['type']				=	$type;
		$param['OFFSET']			=	$offset;
		$param['count']				=	$count;
		$ret 						=	Http::get_curl($url,Func::JSON_JSON($param));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取永久素材列表失败");
	}


	/**
	 * @var   创建标签
	 * @param [type] $name [description]
	 */
	public function add_tags_create($name) {
		$url 						=	$this->url['add_tags_create']."?access_token=".$this->get_wx_token();
		$param 						=	array();
		$param['tag']['name']		=	$name;
		$ret 						=	Http::post_curl($url,Func::JSON_JSON($param));
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("创建标签失败");

	}
	/**
	 * @var    获取标签
	 * @return [type] [description]
	 */
	public function get_tags() {
		$url 						=	$this->url['get_tags']."?access_token=".$this->get_wx_token();
		$param 						=	array();
		$param['access_token']		=	$this->get_wx_token();
		$ret 						=	Http::post_curl($url,$param);
		if ($ret['data']){
			return $ret['data'];
		}
		$this->error("获取标签失败");
	}



	/**
	 * @var    错误信息
	 * @param  [type] $text [description]
	 * @return [type]       [description]
	 */
	public function error($text) {
		return (new Error("微信公众号","1",$text));
	}

	
}