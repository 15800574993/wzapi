<?php
namespace wangzhan\wechat;
use wangzhan\wechat\PublicWeChat;
use wangzhan\wechat\AppWeChat;


class Weixin {
	// 配置
	private $config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 		=	$config;
    }
    /**
     * @var    操作微信公众号
     * @return [type] [description]
     */
    public function get_public_wechat() {
    	return (new PublicWeChat($this->config));
    }

    /**
     * @var    操作微信小程序
     * @return [type] [description]
     */
    public function get_app_wechat() {
    	return (new AppWeChat($this->config));
    }


}