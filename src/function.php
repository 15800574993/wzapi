<?php
use wangzhan\WzApi;
use wangzhan\Func;
use wangzhan\wechat\PublicWeChat;
use wangzhan\wechat\AppWeChat;
use wangzhan\Http;
use wangzhan\tt\Ttapi;
use wangzhan\other\File;
use wangzhan\other\MysqlModel;
use wangzhan\Error;
use wangzhan\pay\Alipay;
use wangzhan\pay\Wxpay;
/**
*	@var 获取配置
**/
if (!function_exists("wz_config")) {
    function wz_config($key = "",$val = null) {
    	$config 			=	WzApi::config();
    	if ($key) {
    		return is_param($config,$key,$val);
    	}
    	return $config;
    }
}

/**
*	@var 错误信息
**/
if (!function_exists("wz_error")) {
    function wz_error($text) {
    	return (new Error("助手方法","11",$text));
    }
}

/**
*	@var get或者post网络请求
**/
if (!function_exists("get_post_curl")) {
    function get_post_curl($url,$type = "post", $data = array(), $http_opts = null,$is_transcoding = true) {
    	return (new Http())->get_post_curl($type,$url,$data,$http_opts,$is_transcoding);
    }
}
/**
 * @var  自动生成文档
 */
if (!function_exists("add_file")) {
    function add_file($FilePath,$hold_route = "") {
    	return (new File(wz_config()))->add_file($FilePath,$hold_route);
    }
}

/**
 * @var    自动生成模型文件
 * @param  [type]  [is_tp6] 	是否tp6 如果是tp6自动执行和获取数据 	
 * @param  [type]  [path]  		模型存放的路径	
 * @param  [type]  [columnss]  	数组表数据结构
 * @param  [type]  [prefix]  	表前缀
 * @param  [type]  [modelPath]  命名空间
 * @return [type]      [description]
 * 
 *	$tables 		=	array();
 *	foreach ($data as $key => $value) {
 * 		$table 		=	($value['Tables_in_xiaoshuo']);
 *		$tables[$table]	=	Db::query(" show COLUMNS from `".$table."`");
	 * 	}
	 * 	$this->wzapi->getMysqlModel()->exeAllModel($tables,"/www/web/xiaoshuo.caoyujie.com/app/model","cd_","app\\model");
 */
if (!function_exists("add_sql_model")) {
    function add_sql_model($is_tp6,$path,$prefix = "",$modelPath = "app\\model",$columnss = "") {
    	// 判断是否是tp6
    	if ($is_tp6) {
    		// 判断是否存在配置数据
    		if (!function_exists("config")){
    			wz_error("助手函数不存在config(),是否是thinkphp6");
    		}
    		$database 	=	(config("database.connections.mysql.database"));
    		$database 	=	($database)?$database:wz_error("数据库配置获取失败请检查/config/database.php里面的配置");
    		try {
    			$data	 	=	\think\facade\Db::query(" show tables from `".$database."`");
    		} catch (Exception $e) {
    			wz_error("数据库不存在[".$database."]");
    		}
    		$tables 		=	array();
			foreach ($data as $key => $value) {
				$table 		=	($value['Tables_in_'.$database]);
				$tables[$table]	=	\think\facade\Db::query(" show COLUMNS from `".$table."`");
			}
			$columnss 			=	$tables;
			$prefix 			=	(config("database.connections.mysql.prefix"));
    	}
    	return (new MysqlModel(wz_config()))->exeAllModel($columnss,$path,$prefix,$modelPath);
    
    }
}

// ================================ 	支付宝支付   		======================
// 获取支付宝支付配置参数
if (!function_exists("ali_config"))  {
	function ali_config($key = "",$val = "") {
		$ali_pay 				=	(wz_config("ali_pay"));
		if ($key) {
    		return is_param($ali_pay,$key,$val);
    	}
		return $ali_pay;
	}
}
// 支付宝通用参数
if (!function_exists("ali_pay"))  {
	function ali_pay($type,$data) {
		return (new Alipay())->pay($type,ali_config(),$data);
	}
}
// 支付宝app支付
if (!function_exists("ali_app"))  {
	function ali_app($data) {
		return ali_pay("AliApp",$data);
	}
}
// 支付宝H5支付
if (!function_exists("ali_h5"))  {
	function ali_h5($data) {
		return ali_pay("AliH5",$data);
	}
}
// 支付宝二维码支付
if (!function_exists("ali_qr_pay"))  {
	function ali_qr_pay($data) {
		return ali_pay("AliQrpay",$data);
	}
}
// 支付宝查询订单
if (!function_exists("ali_query"))  {
	function ali_query($data) {
		return ali_pay("AliQuery",$data);
	}
}
// 支付宝退款
if (!function_exists("ali_qrfund"))  {
	function ali_qrfund($data) {
		return ali_pay("AliRefund",$data);
	}
}
// 支付宝退款查询
if (!function_exists("ali_qrfund_query"))  {
	function ali_qrfund_query($data) {
		return ali_pay("AliRefundQuery",$data);
	}
}
// 支付宝web跳转支付同步跳转
if (!function_exists("ali_web"))  {
	function ali_web($data) {
		return ali_pay("AliWeb",$data);
	}
}
// 支付宝回掉
if (!function_exists("ali_notify"))  {
	function ali_notify($data) {
		return ali_pay("NotifyAli",$data);
	}
}

// ================================ 	微信支付   		======================
if (!function_exists("wxpay_config"))  {
	function wxpay_config($key = "",$val = "") {
		$wx_pay 					= 	wz_config("wx_pay");
		if (!is_param($wx_pay,"appid",null)) {
			$wx_pay['appid']		=	wz_config("appid")?:wz_config("app_appid");
		}
		if (!is_param($wx_pay,"appsecret",null)) {
			$wx_pay['appsecret']	=	wz_config("appsecret")?:wz_config("app_appsecret");
		}
		if (!is_param($wx_pay,"mch_id",null)) {
			$wx_pay['mch_id']		=	wz_config("mch_id");
		}
		if (!is_param($wx_pay,"key",null)) {
			$wx_pay['key']			=	wz_config("key");
		}
		if ($key) {
    		return is_param($wx_pay,$key,$val);
    	}
		return $wx_pay;

	}
}
// new 微信配置
if (!function_exists("new_wxpay"))  {
	function new_wxpay($notify_url) {
		$appid 			=	wxpay_config("appid");
		$appsecret 		=	wxpay_config("appsecret");
		$mch_id 		=	wxpay_config("mch_id");
		$key 			=	wxpay_config("key");
		$notify_url 	=	$notify_url?$notify_url:wxpay_config("notify_url");
  		return new Wxpay($appid,$appsecret,$mch_id,$key,$notify_url);
	}
}
/**
 * 统一下单
 * @param  $order 订单 必须包含支付所需要的参数 
 * @var    body(产品描述)
 * @var    total_fee(订单金额)
 * @var    out_trade_no(订单号)
 * @var    product_id(产品id)
 * @var    trade_type(类型：JSAPI，NATIVE，APP)
 */
if (!function_exists("wxpay_unified_order"))  {
	function wxpay_unified_order($order,$notify_url = "") {
  		$wxpay 			=	new_wxpay($notify_url);
  		return $wxpay->unifiedOrder($order);
	}
}
/**
 * 小程序或者公众号支付
 */
if(!function_exists("wxpay_jssdk")) {
	function wxpay_jssdk($data,$notify_url = "") {
  		$wxpay 			=	new_wxpay($notify_url);

  		$body 			=	is_param($data,"body",null)?$data['body']:wz_error("微信支付参数body不能为空");
  		$total_fee 		=	is_param($data,"total_fee",null)?$data['total_fee']:wz_error("微信支付金额错误total_fee");
  		$out_trade_no 	=	is_param($data,"out_trade_no",null)?$data['out_trade_no']:wz_error("微信支付商户订单号错误out_trade_no");
  		$product_id 	=	is_param($data,"product_id",null)?$data['product_id']:wz_error("微信支付错误product_id");
  		$openid 		=	is_param($data,"openid",null)?$data['openid']:wz_error("微信支付错误openid");
		
		return $wxpay->getParameters("",$body,$total_fee,$out_trade_no,$product_id,$openid);
	}
}
/**
 * 微信回掉
 */
if (!function_exists("wxpay_notify"))  {
	function wxpay_notify($data = "",$notify_url = "") {
		$wxpay 			=	new_wxpay($notify_url);
  		return $wxpay->notify($data);
	}
}
// 查询订单
if (!function_exists("wxpay_query"))  {
	function wxpay_query($out_trade_no) {
		$wxpay 			=	new_wxpay($notify_url);
  		return $wxpay->orderquery($out_trade_no);
	}
}
	





// ================================ 	微信公众号相关 		======================
// 微信公众号类
if (!function_exists("wx"))  {
	function wx() {
		return new PublicWeChat(wz_config());
	}
}

/**
 * @var    获取微信token
 * @return [type] [description]
 */
if (!function_exists("get_wx_token"))  {
	function get_wx_token() {
		return wx()->get_wx_token();
	}
}

/**
 * @var    获取微信ticket
 * @return [type] [description]
 */
if (!function_exists("get_wx_ticket"))  {
	function get_wx_ticket() {
		return wx()->get_ticket();
	}
}
/**
 * @var   上传菜单接口
 * @param [type] $data [description]
 */
if (!function_exists("add_wx_menu"))  {
	function add_wx_menu($data) {
		return wx()->add_menu($data);
	}
}

/**
 * @var   获取菜单接口
 * @param [type] $data [description]
 */
if (!function_exists("get_wx_menu_info"))  {
	function get_wx_menu_info() {
		return wx()->get_menu_info();
	}
}
/**
 * @var   创建个性化菜单
 */
if (!function_exists("add_wx_conditional"))  {
	function add_wx_conditional($data,$matchrule) {
		return wx()->get_menu_info($data,$matchrule);
	}
}
/**
 * @var  删除个性化菜单
 * @param  [type] $menuid [个性化菜单id]
 * @return [type]         [description]
 */
if (!function_exists("del_wx_conditional"))  {
	function del_wx_conditional($menuid) {
		return wx()->del_conditional($menuid);
	}
}
/**
 * @var    测试用户个性化菜单
 * @param  [type] $openid [description]
 * @return [type]         [description]
 */
if (!function_exists("test_wx_trymatch"))  {
	function test_wx_trymatch($openid) {
		return wx()->test_trymatch($openid);
	}
}

/**
* @var    获取自定义菜单配置
* @return [type] [description]
*/
if (!function_exists("menu_wx_get"))  {
	function menu_wx_get() {
		return wx()->menu_get();
	}
}

/**
* @var    微信授权地址生成
* @param  [type] $redirect_uri [description]
* @return [type]               [description]
*/
if (!function_exists("wx_authorize"))  {
	function wx_authorize($redirect_uri,$state = 123) {
		return wx()->wx_authorize($redirect_uri,$state);
	}
}
/**
 * @var    获取微微信授权信息
 * @param  [type] $code [description]
 * @return [type]       [description]
 */
if (!function_exists("get_wx_code"))  {
	function get_wx_code($code) {
		return wx()->get_wx_code($code);
	}
}
/**
 * @var    根据openid或者code获取用户信息
 * @param  [type] $openid [description]
 * @return [type]       [description]
 */
if (!function_exists("get_wx_code_openid_info"))  {
	function get_wx_code_openid_info($openid = "",$code = "") {

		return wx()->get_code_openid_info($code,$openid);
	}
}
/**
 * @var    根据openid获取用户信息
 * @param  [type] $openid [description]
 * @return [type]       [description]
 */
if (!function_exists("get_wx_openid_info"))  {
	function get_wx_openid_info($openid) {
		return wx()->get_openid_info($openid);
	}
}


/**
*   @author     王佩双 <250570889@qq.com>
*   @var        获取微信分享信息  
***/
if (!function_exists("get_wx_sign_package"))  {
	function get_wx_sign_package($url = "") {
		return wx()->get_sign_package($url);
	}
}
/**
 * @var    拉取用户openid
 * @param  string $openid [description]
 * @return [type]         [description]
 */
if (!function_exists("pull_wx_openid"))  {
	function pull_wx_openid($openid = "") {
		return wx()->pull_openid($openid);
	}
}

/**
 * @var    发送客服文本消息
 * @param  [type] $text  	[description]
 * @param  [type] $openid 	[description]
 * @return [type]         	[description]
 */
if (!function_exists("send_wx_user_msg"))  {
	function send_wx_user_msg($openid,$text) {
		return wx()->send_user_msg($openid,$text);
	}
}
/**
 * @var    上传临时素材
 * @param  [type] $path [路径]
 * @param  [type] $type [类型  image ]
 * @return [type]       [description]
 */
if (!function_exists("upload_wx_media_id"))  {
	function upload_wx_media_id($path,$type) {
		return wx()->upload_media_id($path,$type);
	}
}
/**
 * @var    获取临时素材
 * @return [type] [description]
 */
if (!function_exists("get_wx_media_get"))  {
	function get_wx_media_get($media_id) {
		return wx()->get_media_get($media_id);
	}
}

/**
 * @var  上传永久素材
 * @param  [type] $path [路径]
 * @param  [type] $type [类型  image ]
 * @return [type]       [description]
 */
if (!function_exists("add_wx_material"))  {
	function add_wx_material($path,$type) {
		return wx()->add_material($path,$type);
	}
}

/**
 * @var    获取永久素材
 * @param  [type] $media_id [description]
 * @return [type]           [description]
 */
if (!function_exists("get_wx_material"))  {
	function get_wx_material($media_id) {
		return wx()->get_material($media_id);
	}
}

/**
 * @var    删除永久素材
 * @param  [type] $media_id [description]
 * @return [type]           [description]
 */
if (!function_exists("del_wx_material"))  {
	function del_wx_material($media_id) {
		return wx()->del_material($media_id);
	}
}

/**
 * @var    获取永久素材列表
 * @return [type] [description]
 */
if (!function_exists("batchget_wx_material"))  {
	function batchget_wx_material($type = "image",$offset = 0,$count=20) {
		return wx()->batchget_material($type,$offset,$count);
	}
}

/**
 * @var   创建标签
 * @param [type] $name [description]
 */
if (!function_exists("add_wx_tags_create"))  {
	function add_wx_tags_create($name) {
		return wx()->add_tags_create($name);
	}
}

/**
 * @var    获取标签
 * @return [type] [description]
 */
if (!function_exists("get_wx_tags"))  {
	function get_wx_tags() {
		return wx()->get_tags();
	}
}
// ================================ 	微信小程序 			======================
// 微信小程序类
if (!function_exists("wxapp"))  {
	function wxapp() {
		return new AppWeChat(wz_config());
	}
}

/**
 * @var    code转openid接口
 * @param  [type] $code [description]
 * @return [type]       [description]
 */
if (!function_exists("get_wxapp_code_openid"))  {
	function get_wxapp_code_openid($code) {
		return wxapp()->get_code_openid($code);
	}
}

/**
 * @var    获取微信token
 * @return [type] [description]
 */
if (!function_exists("get_wxapp_token"))  {
	function get_wxapp_token() {
		return wxapp()->get_wx_app_token();
	}
}
/**
 * @var    小程序解密接口
 * @param  string $data        [encryptedData]
 * @param  string $session_key [session_key]
 * @param  string $iv          [iv]
 * @return [type]              [description]
 */
if (!function_exists("wxapp_decrypt"))  {
	function wxapp_decrypt($data='',$session_key='',$iv='') {
		return wxapp()->wx_decrypt($data,$session_key,$iv);
	}
}

/**
 * @var 获取小程序二维码，正常二维码，永久有效，有数量限制
 * @param  [type]  $path  [路径和参数]
 * @param  integer $width [二维码宽度]
 * @return [type]         [description]
 */
if (!function_exists("create_wxapp_qrcode"))  {
	function create_wxapp_qrcode($path,$width = 430) {
		return wxapp()->createwxaqrcode($path,$width);
	}
}

/**
 * @var 获取小程序二维码，小程序二维码，永久有效，有数量限制
 * @param  [type]  $path  [路径和参数]
 * @param  integer $width [二维码宽度]
 * @return [type]         [description]
 */
if (!function_exists("get_wxapp_acode"))  {
	function get_wxapp_acode($path,$width = 430) {
		return wxapp()->getwxacode($path,$width);
	}
}

/**
 * @var    获取小程序二维码，小程序二维码，永久有效，无数量限制，有参数长度限制
 * @param  [type] $page  [路径]
 * @param  [type] $width [宽度]
 * @param  string $scene [参数]
 * @return [type]        [description]
 */
if (!function_exists("get_wxapp_acodeunlimit"))  {
	function get_wxapp_acodeunlimit($page,$width,$scene = "") {
		return wxapp()->getwxacodeunlimit($page,$width,$scene);
	}
}

/**
 * @var    图片安全检测
 * @param  [type] $path [图片路径]
 * @return [type]       [description]
 */
if (!function_exists("img_wxapp_sec_check"))  {
	function img_wxapp_sec_check($path) {
		return wxapp()->img_sec_check($path);
	}
}
/**
 * @var    文本安全检测
 * @param  [type] $path [图片路径]
 * @return [type]       [description]
 */
if (!function_exists("msg_wxapp_sec_check"))  {
	function msg_wxapp_sec_check($text) {
		return wxapp()->msg_sec_check($text);
	}
}










// ================================ 	字节小程序 			======================
// 字节小程序类
if (!function_exists("ttapp"))  {
	function ttapp() {
		return new Ttapi(wz_config());
	}
}

/**
 * @var    获取头条token
 * @return [type] [description]
 */
if (!function_exists("get_tt_token"))  {
	function get_tt_token() {
		return ttapp()->get_tt_token();
	}
}

/**
 * @var    文本内容检测
 * @param  [type] $text [需要检测的内容]
 * @return [type]       [description]
 */
if (!function_exists("exe_tt_text"))  {
	function exe_tt_text($text) {
		return ttapp()->exe_text($text);
	}
}

/**
 * @var    图片容检测
 * @param  [type] $text [需要检测的内容]
 * @return [type]       [description]
 */
if (!function_exists("exe_tt_img"))  {
	function exe_tt_img($img) {
		return ttapp()->exe_img($img);
	}
}

/**
 * @var    登陆接口
 * @param  [type] $code   [code]
 * @param  [type] $ancode [ancode]
 * @return [type]         [description]
 */
if (!function_exists("tt_login"))  {
	function tt_login($code,$ancode) {
		return ttapp()->login($code,$ancode);
	}
}
/**
 * @var    获取二维码
 * @param  [type] $path    [路径]
 * @param  [type] $arr     [参数数组]
 * @param  [type] $appname [对应字节系 app]
 * @return [type]          [description]
 */
if (!function_exists("tt_qrcode"))  {
	function tt_qrcode($path,$arr,$appname) {
		return ttapp()->qrcode($path,$arr,$appname);
	}
}





// ================================ 	自定义方法 			======================
if (!function_exists("update_func_config")) {
	function update_func_config() {
		return (Func::update_config(wz_config()));
	}
}
/**
 * @var    随机生成字符串
 * @author 王佩双 <250570889@qq.com>
 * @param  integer $length [字符串长度]
 * @return string
 */
if (!function_exists("get_nonce_str")) {
    function get_nonce_str($length = 16) {
    	return  Func::get_nonce_str($length);
    }
}
/**
 * @var    判断字符串是否为 Json 格式
 * @author 王佩双 <250570889@qq.com>
 * @param  integer $str     [字符串]
 * @return true/false
 */
if (!function_exists("is_json")) {
	function is_json($str = '') {
		return  Func::is_json($str);
	}
}

/**
 * @var    获取客户端真实IP
 * @author 王佩双 <250570889@qq.com>
 * @return string
 */
if (!function_exists("get_ip")) {
	function get_ip() {
		return  Func::get_ip();
	}
}

/**
* @var    执行ip转地址/第三方类库转换
* @author 王佩双 <250570889@qq.com>
* @return array
*/
if (!function_exists("exe_ip_location")) {
	function exe_ip_location($ip = "") {
		return  Func::exe_ip_location($ip);
	}
}


/**
 * @var    获取当前请求方式
 * @author 王佩双 <250570889@qq.com>
 * @return GET/POST
 */
if (!function_exists("get_method")) {
	function get_method() {
		return  Func::get_method();
	}
}

/**
 * @var    获取服务参数
 * @author 王佩双 <250570889@qq.com>
 * @return GET/POST
 */
if (!function_exists("get_server")) {
	function get_server() {
		return  Func::get_server();
	}
}
/**
 * @var    获取指定服务器参数
 * @param  [type] $key   [键名]
 * @param  [type] $value [不存在的情况下默认值]
 * @return string
 */
if (!function_exists("get_server_param")) {
	function get_server_param($key,$value = null) {
		return  Func::get_server_param($key,$value);
	}
}

/**
 * @var    获取当前是http还是https
 * @param  [type] $key   [键名]
 * @param  [type] $value [不存在的情况下默认值]
 * @return string
 */
if (!function_exists("get_http_type")) {
	function get_http_type() {
		return  Func::get_http_type();
	}
}

/**
 * @var    获取客户端发送的数据
 * @return string
 */
if (!function_exists("get_post_param")) {
	function get_post_param() {
		return Func::get_post_param();
	}
}
/**
 * @var    获取请求的参数返回json字符串
 * @return json
 */
if (!function_exists("get_param")) {
	function get_param() {
		return Func::get_param();
	}
}
/**
 * @var    优先检测请求头数据
 * @param  [type] $name    [参数名称]
 * @param  [type] $header  [头部参数名称]
 * @param  [type] $default [默认值]
 * @return string
 */
if (!function_exists("get_post_header")) {
	function get_post_header($name,$header,$default = null) {
		return Func::get_post_header($name,$header,$default);
	}
}

/**
 * @var    过滤提交信息，防止被攻击
 * @param  [type] $str  [需要过滤的字符串]
 * @return string
 */
if (!function_exists("get_post_input")) {
	function get_post_input($str) {
		return Func::get_post_input($str);
	}
}
/**
 * @var    类型转换
 * @param  [type] $str  [转换的字符串数据]
 * @param  string $type [转换的类型  [d/整型] [s/字符串] [s/浮点型] [ja/json字符串转数组]]
 * @return [type]       [description]
 */
if (!function_exists("type_to_type")) {
	function type_to_type($str,$type = "d") {
		return Func::type_to_type($str,$type);
	}
}

/**
 * @var    获取参数信息
 * @param  [type] $key   [需要获取的键名]
 * @param  string $value [需要获取的值]
 * @return string
 */
if (!function_exists("get_post")) {
	function get_post($key,$value = null) {
		return Func::get_post($key,$value);
	}
}


/**
 * @var 判断数组中是否包含某个值 如果不包含返回默认值
 * @param  [type]  $arr [数组]
 * @param  [type]  $key [键名]
 * @param  [type]  $val [默认值]
 * @return boolean      [description]
 */
if (!function_exists("is_param")) {
	function is_param($arr,$key,$val = 0) {
		return Func::is_param($arr,$key,$val);
	}
}

/**
 * @var    数组转xml
 * @param  [type] $arr   [数组数据]
 * @return xml
 */
if (!function_exists("arr_to_xml")) {
	function arr_to_xml($arr) {
		return Func::arr_to_xml($arr);
	}
}

/**
 * @var    xml转数组
 * @param  [type] $xml   [xml数据]
 * @return xml
 */
if (!function_exists("xml_to_arr")) {
	function xml_to_arr($xml) {
		return Func::xml_to_arr($xml);
	}
}
/**
* @author     王佩双 <250570889@qq.com>
* @version      1.0
* @param        status      状态
* @param        msg         提示数据
* @param        results     对象
* @param        data        数组数据
* @param        info        分页
* @param        type        返回的类型 1 json返回【客户端使用】  2 数组返回  3 返回xml【客户端使用】
* @param        log_type    是否记录日志  
* @param        user_id     记录日志的用户id
* @return     返回json数据
*/
if (!function_exists("return_json")) {
	function return_json($status = 0,$msg="",$results=array(),$data = array(),$info = "",$type = 1,$log_type = 0,$user_id = 0){
		return Func::return_json($status,$msg,$results,$data,$info,$type,$log_type,$user_id);
	}
}
/**
* @var        
* @author     王佩双 <250570889@qq.com>
* @param        判断数据是否为空
* @param        $name       参数值
* @param        $default    默认值
* @param        $error      不存在或者为空返回的数据信息
* @param        $status     状态码
* @return       返回获取到的值 
*/
if (!function_exists("check_param")) {
	function check_param($name,$default = null,$error = "",$status = 0) {
		return Func::check_param($name,$default,$error,$status);
	}
}

/**
* @author  王佩双 <250570889@qq.com>
* @var     生成唯一不重复订单号
* @var     总数40  前面1-14   代表 当前年月日时分秒， 15-22 代表用户id   后面六位随机字符串
* @param   $[user_id]           [标识id整型]
* @param   $[prefix]            [前缀]
*/
if (!function_exists("get_tid")) {
	function get_tid($user_id = 0,$prefix = 0) {
		return Func::get_tid($user_id,$prefix);
	}
}

/**
* @author   王佩双 <250570889@qq.com>
* @var      获取全球唯一标识【作为用户UUID】 一些特殊接口使用
*/
if (!function_exists("get_uuid")) {
	function get_uuid() {
		return Func::get_uuid();
	}
}

/**
* @author  王佩双 <250570889@qq.com>
* @var     随机生成汉字
* @var     num  汉字个数
*/
if (!function_exists("get_rand_char")) {
	function get_rand_char($num = 3) {
		return Func::get_rand_char($num);
	}
}

/**
*   @author     王佩双 <250570889@qq.com>
*   @var 判断当前手机号是否是真的
*   @param  $[phone]    [<手机号>]
**/
if (!function_exists("get_phone")) {
	function get_phone($phone = 1) {
		return Func::get_phone($phone);
	}
}

/**
* @author     王佩双 <250570889@qq.com>
* @var 获取并组合好 分页数据 
*/
if (!function_exists("get_limit")) {
	function get_limit($type  = 0) {
		update_func_config();
		return Func::get_limit($type);
	}
}

/**
*   @author     王佩双 <250570889@qq.com>
*   @param      $[total]    [数据总数]
*   @param      $[data]     [当前显示数]
*   @var 获取并组合好 返回的分页数据 
*/
if (!function_exists("get_page")) {
	function get_page($total,$data) {
		update_func_config();
		return Func::get_page($total,$data);
	}
}
/**
* @var     数组重新排序
* @param      $[arr]        [需要排序的数组]
* @param      $[row]        [排序字段]
* @param      $[type]       [1升序/0降序]
* @var 获取并组合好 返回的分页数据 
*/
if (!function_exists("array_sort")) {
	function array_sort($arr,$row,$type = 0) {
		return Func::array_sort($arr,$row,$type);
	}
}

/**
* @var    获取数组中指定字段
* @param  [type] $data  [完整的数组]
* @param  [type] $field [查询的字段]
* @param  [type] $val   [不存在返回的数据]
* @return [type]        [description]
*/
if (!function_exists("arr_key")) {
	function arr_key($data,$field,$val  = null) {
		return Func::arr_key($data,$field,$val);
	}
}

/**
 * @var   写入日志类
 * @param [type] $fileName  [description]
 * @param [type] $user_name [description]
 * @param [type] $content   [description]
 */
if (!function_exists("add_log")) {
	function add_log($user_name="", $content="") {
		update_func_config();
		return Func::add_log($user_name,$content);
	}
}

/**
 * @var    数组转get参数
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
if (!function_exists("arr_query")) {
	function arr_query($arr) {
		return Func::arr_query($arr);
	}
}
/**
 * @var    下划线转驼峰 字符串转化函数
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
if (!function_exists("line_tohump")) {
	function line_tohump($str) {
		return Func::line_tohump($str);
	}
}
/**
 * @var    驼峰转下划线 字符串函数
 * @param  [type] $arr [description]
 * @return [type]      [description]
 */
if (!function_exists("tohump_line")) {
	function tohump_line($str){
		return Func::tohump_line($str);
	}
}
/**
 * @var    判断目录是否存在不存在添加
 * @param  [type]  $dir  [description]
 * @param  integer $mode [description]
 * @return [type]        [description]
 */
if (!function_exists("mkdirs")) {
	function mkdirs($dir, $mode = 0777){
		return Func::mkdirs($dir, $mode);
	}
}
/**
 * @var    返回当前毫秒时间戳
 * @param  [type]  $dir  [description]
 * @param  integer $mode [description]
 * @return [type]        [description]
 */
if (!function_exists("msectime")) {
	function msectime() {
		return Func::msectime();
	}
}
/**
 * @var 将数组转json  并且保留中文
 * @param [type] $array [description]
 */
if (!function_exists("json_json")) {
	function json_json($array) {
		return Func::json_json();
	}
}























