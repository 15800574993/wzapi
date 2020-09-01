<?php
namespace wangzhan\upload;
use wangzhan\Error;
use wangzhan\upload\oss\Ossinit;
class Oss{ 

	// 文件配置
    private  $config;
    // 上传服务器的文件信息
    private $file;
    // 文件信息
    public $filename;
    // 类信息
    private $ossInit;
    public function __construct($file,$config,$type = 0){
        $this->config       	=   $config; 
        $this->delete           =   $config['delete'];
        if (!$type) {
        	$this->file 		=	(new File($file,$config))->upload()->getFileName();
        } else {
        	$this->file 		=	$file;
        }

        $this->ossInit          =   (new ossInit($this->file,$config));
        
    }
    // 上传oss
    public function upload() {
        $this->filename         =   $this->ossInit->upload();
    	return $this;
    }

    // 获取当前上传文件的数据信息
    public function getFileName() {
        // 判断是否删除本地文件
        if ($this->delete) {
            foreach ($this->file as $key => $value) {
                unlink($value['path']);
            }
            
        }
        return  $this->filename;
    }
	/**
     * @var    错误信息
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function error($text) {
        return (new Error("文件上传","7",$text));
    }
}