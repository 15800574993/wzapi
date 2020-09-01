<?php
namespace wangzhan\upload;
use wangzhan\Error;
use wangzhan\upload\qiniu\Storage\UploadManager;
use wangzhan\upload\qiniu\Auth;
class Qiniu{ 

	// 文件配置
    private  $config;
    // 上传服务器的文件信息
    private $file;
    // 文件信息
    public $filename;
    // 类信息
    private $ossInit;
    // 是否删除源文件
    private $delete;
    public function __construct($file,$config,$type = 0){
        $this->config       	=   $config['qiniu']; 
        $this->delete           =   $config['delete']; 
        if (!$type) {
        	$this->file 		=	(new File($file,$config))->upload()->getFileName();
        } else {
        	$this->file 		=	$file;
        }
    }

    /**
     * 执行上传
     * @return mixed
     * @throws Exception
     */
    public function upload() {
        $upManager          = new UploadManager();
        $auth               = new Auth($this->config['accessKey'], $this->config['secretKey']);
        $token              = $auth->uploadToken($this->config['bucket']);
        $ret                =   array();
        foreach ($this->file as $key => $value) {
            $data           =   $upManager->putFile($token, $this->config['key'].$value['name'], $value['path']);
            $arr            =   array();
            $arr['url']     =   $this->config['url'].$data["0"]['key'];
            $arr['path']    =   $value['path'];
            $arr['name']    =   $value['name'];
            $ret[]          =   $arr;
        }
        $this->filename     =   $ret;
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


}