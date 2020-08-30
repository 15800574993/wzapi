<?php
namespace wangzhan;
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone','Asia/Shanghai');

use wangzhan\Config;
use wangzhan\wechat\Weixin;
use wangzhan\Http;
use wangzhan\Func;
use wangzhan\ip\IpLocation;
use wangzhan\tt\Ttapi;
use wangzhan\other\MysqlModel;
use wangzhan\other\File;
use wangzhan\pay\Alipay;
use wangzhan\pay\Wxpay;
/**
*   @author  王佩双 <250570889@qq.com>
*   @version 1.0
*   @var     整合 
*   微信公众号/小程序/支付/发送包/转账/支付宝支付/oss/cos/七牛图片/短信/网络请求/日志/自动生成数据模型文件/和自动生成文档和一些常用文件操作方法
*/
class WzApi {
	/**
     * 所有配置实例
     * @var \config
     */
    protected $config;
    public 	  $MyConfig;
    public    $error;
    public static $wz_config;
	public function __construct($setConfig = array()){
		// 更新配置
     	$this->config 			=	new Config();
     	$this->config->setConfig($setConfig);
     	$this->MyConfig 		=	$this->config->getConfig();
        self::$wz_config        =   $this->config->getConfig();
        $this->loadClass();
    }
    /**
     * @var    静态存储配置数据
     * @return [type] [description]
     */
    public static function config() {
        return self::$wz_config; 
    }

    /**
     * @var    更新配置
     * @param  array  $setConfig [更新的数据]
     * @return [type]            [description]
     */
    public  function updateConfig($setConfig = array()) {
    	$this->config->setConfig($setConfig);
    	$this->MyConfig 		= 	$this->config->getConfig();
        self::$wz_config        =   $this->MyConfig;
    	return $this->MyConfig;
    }
    /**
     * @var 自动加载 11
     * @return [type] [description]
     */
    public function loadClass(){
        $xxxxx                 =   1;    
        require_once __DIR__."/function.php";      
    }


    // 获取微信相关操作  公众号  1 小程序 2
    public function getWechat() {
    	return (new Weixin($this->MyConfig));
    }
    // 获取网络请求  
    public function getHttp(){
    	return (new Http());
    }
    // 获取自定义方法
    public function getFunc() {
    	return (new Func($this->MyConfig));
    }

    // 获取字节小程序  字节小程序 3
    public function getTt() {
        return (new Ttapi($this->MyConfig));
    }
    // 支付宝相关操作  4
    public function getAlipay(){
        return (new Alipay());
    }
    // 微信支付相关操作  5
    public function getWxpay($notify_url = ""){
        return new_wxpay($notify_url);
    }
    // cos和oss相关操作七牛  6/7/8
    public function getOssCos(){
        return (new OssCos($this->MyConfig));
    }
    // 腾讯短信相关操作 9
    public function getSms(){
        return (new exeSms($this->MyConfig));
    }
    // 自动生成模型文件 
    public function getMysqlModel() {
        return (new MysqlModel($this->MyConfig));
    }
    // 自动生成文档文件 10
    public function getFile() {
        return (new File($this->MyConfig));
    }
}