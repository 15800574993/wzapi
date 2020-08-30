<?php
namespace wangzhan\other;

use wangzhan\Func;
/*
 * 日志操作类
 **/
class Log{
	// 配置
	private $config;
	// 请求地址
	public function __construct($config = array()){
		$this->config 		=	$config;
    }

    /**
     * @var    根据日志配置时间删除之前的日志
     * @param  [type] $logDir [description]
     * @return [type]         [description]
     */
    protected function logDirClear($logDir){
        if(is_dir($logDir)){
            $dirHandle 		= opendir($logDir);
            while(($dirName = readdir($dirHandle)) != false){
                $subDir 	= $logDir.'/'.$dirName;
                if($dirName == '.' || $dirName == '..'){
                    continue;
                }else{
                    $monthDate = date('Y-m-d', strtotime("- ".$this->config['log_day']." day",time()));
                    if(strtotime($monthDate) > strtotime($dirName)){
                        if(is_dir($subDir)){
                            $this->logFileClear($subDir);
                            rmdir($subDir);
                        }
                    }
                }
            }
            closedir($dirHandle);
        }
    }
    protected function logFileClear($fileDir){
        if(is_dir($fileDir)){
            $fileHandle = opendir($fileDir);
            while(($fileName = readdir($fileHandle)) != false){
                $subDir = $fileDir.'/'.$fileName;
                if($fileName == '.' || $fileName == '..'){
                    continue;
                }else{
                    if(is_dir($subDir)){
                        $this->logFileClear($subDir);
                        rmdir($subDir);
                    }else{
                        unlink($subDir);
                    }
                }
            }
            closedir($fileHandle);
        }
    }
    /*
     * 生成新日志
     * */
    public function logWrite($fileName, $user_name, $content){
    	$path 			=	($this->config['log_path'])?$this->config['log_path']:__DIR__."/wangzhanruntime/log/";
        $this->logDirClear($path);
        $now 			= 	date('Y-m-d');
        // 判断是否根据不同用户显示不同的文件夹
        if ($this->config['log_user_path']) {
        	$user_md5 	=	(is_numeric($user_name))?$user_name:md5($user_name);
        	$fileName 	=	$user_md5."_".$fileName;
        }
        $nowDir 		= 	$path.'/'.$now;
        Func::mkdirs($nowDir);
        // if(!is_dir($nowDir)){mkdir($nowDir, 0777, true);}
        $fileDir 		= 	$nowDir.'/'.$fileName;
        $fileContent 	=	"[".date("Y-m-d H:i:s")."] ".(Func::get_ip())." ".(Func::get_method())." ".Func::get_http()." ".Func::get_server_param("REQUEST_URI","").PHP_EOL;
        $fileContent 	.=	Func::get_param().PHP_EOL;
        if (is_object($content)) {
            $content    =   json_encode($content, JSON_FORCE_OBJECT);
        }
        $fileContent 	.= 	"用户【".$user_name.'】操作内容为：['.$content."]".PHP_EOL;
        file_put_contents($fileDir, $fileContent."---------------------------------------------------------------".PHP_EOL, FILE_APPEND);
        return true;
    }

}