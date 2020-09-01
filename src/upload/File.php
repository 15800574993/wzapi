<?php
namespace wangzhan\upload;
use wangzhan\Error;
class File{

    //设定属性：保存允许上传的MIME类型
    private  $types = array('image/jpg', 'image/jpeg', 'image/pjpeg');
    // 上传数据的配置
    private  $config;
    // 上传的资源文件
    private $file;
    // 文件名称
    public $filename;
    public function __construct($file,$config){
        if (!$file) {
            $file           =   $_FILES;
        }
        $this->file         =   $file;
        $this->config       =   $config; 
    }
    // 上传
    public function upload() {
        return $this->uploadOne($this->file,$this->config['path'],$this->config['max']);
    }
    // 获取当前上传文件的数据信息
    public function getFileName() {
        return  $this->filename;
    }


    /**
     * @desc 单文件上传
     * @param string $file,上传文件信息数组
     * @param string $path,上传路径
     * @param int $max = 2M,最大上传大小
     * @return bool|string,成功返回文件名，失败返回false
     */
    public function uploadOne($file, $path, $max = 2000000){
        
        if (!is_dir($path)) {
            return $this->error("存储路径不存在！");
        }
        $filename           =   array();
        foreach ($file as $key => $value) {
            $name           =   $key;
            
            if (!in_array($file[$name]['type'], $this->config['file_type'])) {
                return $this->error("当前上传的文件类型不允许！");
            }
            //判定业务大小
            if ($file[$name]['size'] > $max) {
                return $this->error('当前上传的文件超过允许的大小！当前允许的大小是：' . (string)($max / 1000000) . 'M');
            }
            $imgname        =   $file[$name]['name'];
            $tmp            =   $file[$name]['tmp_name'];
            $filepath       =   $path;
            is_dir($filepath) OR mkdir($filepath, 0777, true);                  // 判断文件是否存在 不存在直接创建 存在跳过
            $rand           =   $this->getRandomName($file[$name]['name']);
            $file_sessage   =   $filepath.$rand;
            if(move_uploaded_file($tmp,$file_sessage)){
                $ret['name']  =   $rand;
                $ret['path']  =   $file_sessage;
                $ret['url']   =   $this->config['url'].$rand;   
                $filename[] = $ret;
                
            }
        }

        $this->filename     =   $filename;
        return $this;
        
    }
   
    /**
     * @desc 获取随机文件名
     * @param string $filename,文件原名
     * @param string $prefix,前缀
     * @return string,返回新文件名
     */
    public function getRandomName($filename, $prefix = 'image'){
        //取出源文件后缀
        $ext = strrchr($filename, '.');
        //构建新名字
        $new_name = $prefix . date('YmdHis');
        //增加随机字符（6位大写字母）
        for ($i = 0; $i < 6; $i++) {
            $new_name .= chr(mt_rand(65, 90));
        }
        //返回最终结果
        return md5($new_name) . $ext;
    }


    /**
     * @var    错误信息
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function error($text) {
        return (new Error("文件上传","6",$text));
    }
}