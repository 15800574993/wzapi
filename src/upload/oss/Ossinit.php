<?php
namespace wangzhan\upload\oss;
use wangzhan\Error;
use wangzhan\upload\oss\Core\OssException;
use wangzhan\upload\oss\OssClient;
class Ossinit{ 

	protected $file; 		//  上传的文件
    protected $engine; 		//  当前存储类
    protected $config;		//	当前配置

    /**
     * Aliyun constructor.
     * @param $file
     * @param $config
     */
    public function __construct($file, $config){
        $this->file 	= $file;
        $this->config 	= $config['oss'];
    }

    /**
     * 执行上传
     * @return mixed|null
     * @throws OssException
     */
    public function upload()
    {
        // 实例化OSS
        $ossClient 			= 	new OssClient($this->config['accessId'], $this->config['accessSecret'], $this->config['endpoint']);
        $ret 				=	array();
        foreach ($this->file as $key => $value) {
        	$data   		=	$ossClient->uploadFile($this->config['bucket'], $this->config['key'].$value['name'], $value['path']);
        	$arr 			=	array();
        	$arr['url']		=	$this->config['url'].$value['name'];
        	$arr['path']	=	$value['path'];
        	$arr['name']	=	$value['name'];
        	$arr['oss_url']	=	$data['oss-request-url'];
        	$ret[]			=	$arr;
        }
        return $ret;
    }
}