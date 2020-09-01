<?php
namespace wangzhan\upload;

use think\Exception;
use think\facade\Config;
use think\facade\Db;
use wangzhan\Error;
class Fileupload {
	protected $file; 		//  上传的文件
    protected $engine; 		//  当前存储类
    protected $fileConfig;	//	当前配置

	/**
     * Filesystem constructor.
     * @param $file
     * @throws Exception
     */
    public function __construct($file = array(),$fileConfig = array()){
    	$this->fileConfig 		=	$fileConfig;
    	if (!$file) {
    		$file 				=	$_FILES;
    	}
        $this->file 			= 	$file;
        if (empty($this->file)) {
            return $this->error("未找到文件信息");
            // throw new Exception('未找到文件信息');
        }
    }

    /**
     * 执行普通上传 上传到当前服务器
     * @throws Exception
     */
    public function upload()
    {
        // 设置默认上传引擎
        if (empty($this->engine)) {
        	$this->config = $this->fileConfig['file'];
            $this->engine = $this->getEngineClass();
        }

        $this->engine->upload(); // 执行上传
        return $this->engine->getFileName();
    }


    /**
     * @throws Exception
     */
    private function getEngineClass()
    {
        $engineName 		= 	$this->config['type'];
        if (!$engineName) {
        	$engineName 	=	"File";
        }
        $classSpace 		= 	__NAMESPACE__."\\".ucfirst($engineName);
        if (!class_exists($classSpace)) {
            throw new Exception('未找到存储引擎类: ' . $engineName);
        }

        return new $classSpace($this->file,$this->config);
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