<?php
namespace wangzhan;
class Config {
	// 微信appid
	protected $config 	=	[
		// 微信公众号appid
		"appid"				=>	"",
		// 微信公众号appsecret
		"appsecret"			=>	"",
		// 小程序appid
		"app_appid"			=>	"",
		// 微信小程序appsecret
		"app_appsecret"		=>	"",
		//微信公众号原始ID
		"wx_id"				=>	"",
		// 微信商户mch_id
		"mch_id"			=>	"",
		//微信商户key
		"key"				=>	"",
		// 微信商户平台 apiclient_cert.pem
		"apiclient_cert"	=>	"",
		// 微信商户平台 apiclient_key.pem
		"apiclient_key"		=>	"",
		// 微信商户平台 apiclient_cert.p12
		"apiclient_ca"		=>	"",
		// token 如果这个值存在优先使用这个值得token
		// 这样方便之前的用户管理token 如果不存在根据缓存位置和时间进行缓存
		"token"				=>	"",
		// token 缓存的文件位置
		"cache_token"		=>	"",
		// token 缓存时间
		"cache_token_time"	=>	7200,
		// 小程序token
		"app_token"			=>	"",
		// 小程序token 缓存的文件位置
		"app_cache_token"	=>	"",
		// 小程序token 缓存时间
		"app_cache_token_time"	=>	7200,
		// ticket 缓存的文件位置
		"cache_ticket"		=>	"",
		// ticket 缓存时间
		"cache_ticket_time"	=>	5,
		// 日志保存的时长天数
		"log_day"			=>	0,
		// 日志保存的位置
		"log_path"			=>	"",
		// 是否根据不同用户生成不同日志文件夹 如果用户是存数字文件夹名称就是数字否则是名称的md5值
		"log_user_path"		=>	0,
		// 记录日志的用户id
		"log_user_id"		=>	0,
		// 是否开启return_json 日志记录
		"return_json_status"=>	1,
		// 分页数据每页字段名称
		"page_name"			=>	"page",
		// 分页数据每页默认数量
		"page_count"		=>	10,
		// 分页数据煤业显示数量名称
		"count_name"		=>	"count",
		// 头条appid 		
		"tt_appid"			=>	'',
		// 头条appsecret
		"tt_appsecret"		=>	"",
		// 头条token
		"tt_token"			=>	"",
		// 头条token 缓存的文件位置
		"tt_cache_token"	=>	"",
		// 头条token 缓存时间
		"tt_cache_token_time"=>	7200,
		// 自动生成文件的接口域名
		"file_action_url"	=>  "",
		// 支付宝相关配置
		"ali_pay"			=>	[
			//	支付宝的app_id
			"app_id"				=>	"",
			// 编码
			"charset"				=>	"UTF-8",
			// 加密方式
			"sign_type"				=>	"RSA2",
			// 网关
			"gatewayUrl"			=>	"",
			// 支付宝私钥
			"merchant_private_key"	=>	"",
			// 支付宝公钥
			"alipay_public_key"		=>	"",
			// 同步回调地址
			"return_url"			=>	"",
			// 异步回调地址
			"notify_url"			=>	"",
		],		
		// 微信支付配置 如果不存在自动获取上面的配置 为了更好的配合app支付增加的
		'wx_pay'          	=> [
			// 微信支付appid 不存在自动获取 上一层的 appid/app_appid
	        'appid'                 => '',    
	        // 微信支付appsecret 不存在自动获取 上一层的 appsecret/app_appsecret         
	        'appsecret'             => '',   
	        // 微信支付mch_id 不存在自动获取 上一层的 mch_id 
	        'mch_id'                => '',
	        // 微信支付key 不存在自动获取 上一层的 key
	        'key'                   => '',
	        // 回掉地址
	        "notify_url"			=> "",
	    ],
	    // 文件上传
	    'file' 				=>[
	    	"type"					=>	"Oss",		//	上传配置   默认上传当前服务器 或者 File/Oss/Cos/Qiniu
	    	"path"					=>	"",			//	本地服务器保存路径
	    	"max"					=>	20000000,	//	文件大小 
	    	"file_type"				=>	[			//	可以上传的文件类型
	    		"image/jpg","image/png","image/jpeg"
	    	],
	    	"url" 					=>	"http://www.baidu.com/",			//  域名
	    	"delete"				=>	0,									//	是否删除服务器上传文件
	    	'oss'					=>	[
			    'accessId'     			=> '',
			    'accessSecret' 			=> '',
			    'bucket'       			=> '',
			    'endpoint'     			=> '',
			    "key"					=>	"text/",		//	上传文件位置
			    'url'          			=> '',
	    	],
	    	"qiniu" 				=>	[
	    		"accessKey"				=>	"",
	    		"secretKey"				=>	"",
	    		"bucket"				=>	"",
	    		"key"					=>	"",		//	上传文件位置
	    		"url"					=>	"",
	    	],
	    	"cos"					=>	[
	    		"secretId"				=>	"",		//	"云 API 密钥 SecretId";
	    		"secretKey"				=>	"",		//	"云 API 密钥 SecretKey";
	    		"bucket"				=>	"",		//	存储桶名称 格式：BucketName-APPID
	    		"key"					=>	"",		//	上传文件位置
	    		"region"				=>	"",		//	设置一个默认的存储桶地域
	    		"url"					=>	"https://res.appgan.com/",
	    	],

	    ],



	];
	/**
	 * @var 	设置配置
	 * @param 	config  		单个键名或者数组
	 */
	public function setConfig($config,$value = "") {
		if (!is_array($config))  {
			$this->setKeyValue($config,$value);
			return $this;
		}
		foreach ($config as $key => $val) {
			$this->setKeyValue($key,$val);
		}
		return $this;
	}
	/**
	 * @var    获取配置
	 * @param  string $key [配置名]
	 * @return [type]      [description]
	 */
	public function getConfig($key = "") {
		if ($key) {
			if (!array_key_exists($key,$this->config)) {
				return "";
			}
			return $this->config[$key];
		}
		return $this->config;
	}
	/**
	 * @var   单个设置配置
	 * @param [type] $key [配置名]
	 * @param [type] $val [配置值]
	 */
	public function setKeyValue($key,$val) {
		return $this->config[$key]	=	$val;
	}


	



}
