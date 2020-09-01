<?php
namespace wangzhan\other;

use wangzhan\Func;
use wangzhan\Error;
/*
 * 生成文档类 注意 | 这个是分割描述和参数的
 * @api 请求方式 后面跟着路径  | 和接口名称   
 * @apiVersion 代表版本号【后面所有】
 * @apiDescription  接口描述信息【后面所有】
 * @apiHeader {String|数据类型} TOKEN | 用户授权token 
 * @apiParam (输入参数：) {int}          [limit] | 每页数据条数（默认20） 【[代表是否必填]】
 * @apiReturn (返回的数据) {string}      arr.  | 返回的数据
 * @apiSuccessExample {json} 01 成功示例 后面跟json
 **/
class File{
	// 配置
	public $config;
	// 文档内容数据信息
    // name  接口名称    type提交方式  action提交地址   describe 接口描述    version 版本号 json 实例
    // header  头部信息   param 参数名称   describe 参数描述    type 参数类型  status 是否必填  remarks 参数说明
    // request 请求参数   param 参数名称   describe 参数描述    type 参数类型  status 是否必填  remarks 参数说明
    // return  返回数据   param 参数名称   describe 参数描述    type 参数类型  remarks 参数说明
	public $fileArr;
	// 错误信息
	public $msg;
	// 请求地址
    public $url             =   "http://api.api.com"; 
	public function __construct($config = array()){
		$this->config 		=	$config;
        if (array_key_exists("file_action_url",$config) && $config['file_action_url']) {
            $this->url      =   $config['file_action_url'];
        }
    }

    /**
     * @var   生成文档
     * @param [type] $FilePath   [生成文档文件 可以是目录或者文件地址或者数组文件地址]
     * @param string $hold_route [需要保存的文档路径 不填写不保存]
     */
    public function add_file($FilePath,$hold_route = "") {
        // 判断是否是数组
        if (is_array($FilePath)) {
            $this->exe_file_data($FilePath);
        } else {
            if(is_dir($FilePath)){
               $this->exe_dir($FilePath);
            } else {
                $this->exe_file_info($FilePath);
            }
        }
        if ($this->msg) {
            return $this->msg;
        }
        return $this->exe_file($hold_route);

    }

    /**
     * @var    检测文件信息
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function exe_file_info($path) {
    	if(!file_exists($path)){
    		$this->msg 		.=	"当前文件不存在".$path;
            return $this->error();
		}
        //将整个文件内容读入到一个字符串中
        $str                =   file_get_contents($path);
        $str                =   str_replace("\r\n","<br />",$str);
        // 提取/***/  
        $byteData           =   $this->exeByteData($str);
        // 分解数据写入fileArr数据里面
        if (!$byteData) {
            return false;
        }
        $this->ExeFileArr($byteData);
        return true;
    }
    /**
     * @var    传入文件路径  
     * @param  [type] $data [路径数组]
     * @return [type]       [description]
     */
    public function exe_file_data($data) {
        if (!is_array($data)) {
            $this->msg      =   "请传入数组文件绝对路径";
            return $this->error();
        }
    	foreach ($data as $key => $value) {
    		$this->exe_file_info($value);
    	}

    	return $this->return_data();
    }
    /**
     * @var    传入目录
     * @return [type] [description]
     */
    public function exe_dir($dir) {
        $end        =   substr($dir, -1);
        if ($end != "/") {
            $dir    =   $dir."/";
        }
        if(!is_dir($dir)){
           $this->msg       .=  "目录不存在".$dir;
           return $this->error();
        }
        // 扫描目录下的所有文件
        $filename = scandir($dir);
        // 定义一个数组接收文件名
        $conname = array();
        foreach($filename as $k=>$v){
            // 跳过两个特殊目录   continue跳出循环
            if($v=="." || $v==".." || $v == ""){
                continue;
            }
            if (is_file($dir.$v)) {
                $conname[]    =   $dir.$v;
            }    
        }
        return $this->exe_file_data($conname);
    }


    /**
     * 返回的用户数据
     * @return [type] [description]
     */
    public function return_data() {
    	// 检测是否存在错误信息  如果存在先返回错误信息
    	if ($this->msg) {
    		return false;
    	}
    	return ($this);
    }

    /**
     * @var    生成文档    文档存放的路径
     * @return [type] [description]
     */
    public function exe_file($dir = "") {
        // 判断是否生成文件 如果传路径就会自动生成
        if ($dir) {
            $end        =   substr($dir, -1);
            if ($end != "/") {
                $dir    =   $dir."/";
            }

            if(!is_dir($dir)){
               $this->msg       .=  "目录不存在".$dir;
               return $this->error();
            }
        }
        
        $html               =   $this->startHtml();
        if (!$this->fileArr) {
            $this->msg       .=  "数据不存在";
            return $this->error();
        }
        // 生成标签名称
        $li             =   '<div class="docs-nav" id="main-nav-bar" style="top: 15px; position: fixed;"><ul id="list"> ';
        foreach ($this->fileArr as $key => $value) {
            if ($key == 0) {
                $li     .=   '<li class="on" href="#doc'.$key.'"><a href="#doc'.$key.'">'.$key.".".$value['name'].'</a></li>';
            } else {
                $li     .=   '<li class="" href="#doc'.$key.'"><a href="#doc'.$key.'">'.$key.".".$value['name'].'</a></li>';
            }
            
        }
        $html           .=  $li;
        $html           .=  '</ul></div><div class="docs-main"> ';
        // 生成接口代码
        foreach ($this->fileArr as $key => $value) {
            if (!array_key_exists("name",$value)) {
                $value['name']     =   "无";
            }

            $html       .= '<div class="docs-mod" id="doc'.$key.'"> ';
            $html       .= '<div class="docs-hd"> '.$key.".".$value['name'];
            $html       .= '</div>  <div class="docs-bd"> ';
            $html       .= '<div class="docs-sub">提交方式： '.$value['type'];
            $html       .= '</div> <div class="docs-sub">提交地址：'.$this->url;
            if (array_key_exists("action",$value)) {
                $html   .= '</div> <div class="docs-sub">接入网关：'.$value["action"];
            }
            if (array_key_exists("version",$value)) {
               $html     .= '</div> <div class="docs-sub">接口版本：'.$value["version"];
            }
            if (array_key_exists("describe",$value)) {
               $html     .= '</div> <div class="docs-sub">接口描述：'.$value["describe"];
            }
            $html       .= '</div><table>';


            // 判断header
            if (array_key_exists("header",$value)) {
                $html   .=      '<div class="docs-sub">HEADER参数：</div> ';
                $html   .=      $this->ExeTableTitle();
                $html   .=      $this->ExeTableData($value['header'],"1"); 
            }
            // 判断请求参数
            if (array_key_exists("request",$value)) {
                $html   .=      '<div class="docs-sub">REQUEST参数：</div><table> ';
                $html   .=      $this->ExeTableTitle();
                $html   .=      $this->ExeTableData($value['request'],"2"); 
            }
            // 判断返回参数
            if (array_key_exists("return",$value)) {
                $html   .=      '<div class="docs-sub">返回参数：</div> <table>';
                $html   .=      $this->ExeTableTitle();
                $html   .=      $this->ExeTableData($value['return'],"2"); 
            }
            $html       .=  "</div>";
            // 返回实例
            if (array_key_exists("json",$value)) {
                $html       .= '<div class="docs-hd">成功实例</div> ';
                $html       .= '<div class="docs-bd"><pre>'.$value['json'].'</pre></div> ';
            } 

        }
        $html       .=  '</div> </div></div> </div> </body></html><script type="text/javascript">window.onpopstate=function(event){var maodian=document.location.hash;var oUl=document.getElementById("list");var Lis=oUl.getElementsByTagName("li");for(var i=0;i<Lis.length;i++){Lis[i].className="";}maodian=maodian.replace("#doc","");　
Lis[maodian].className="on";var num=(60 * maodian)-300;oUl.scrollTop = num;};</script><script type="text/javascript">var height=Number((document.body.clientHeight)*0.9);var oUl=document.getElementById("list");oUl.style["overflow"]="auto";oUl.style["overflow-x"]="hidden";oUl.style["height"]=height+"px";</script>';

        // //判断目录存在否，不存在则创建目录
        if ($dir) {
            if (!is_dir($dir."doc/")){  
                $res        =   mkdir(iconv("UTF-8", "GBK", $dir."doc/"),0777,true); 
                if (!$res){
                    $this->msg  =   "文件夹权限不足";
                    return $this->error();
                }
            }
            $DocFile            = fopen($dir."doc/"."index.html", "w") or return $this->error("没有写入权限");
            fwrite($DocFile, $html);
            fclose($DocFile);
        }
        


        return $html;
      

    }

    /**
     * @var    分解注释数据
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function exeByteData($str) {
        preg_match_all('/\/\*(.*?)\*\//',$str,$m);
        $data               =   $m['0'];
        $ret                =   array();
        foreach ($data as $key => $value) {
            // 判断是否是正在的接口文档
            if (strstr($value, '@api') && strstr($value, '@apiSuccessExample') && (strstr($value, '@apiVersion')
             || strstr($value, '@apiDescription') || strstr($value, '@apiParam') || strstr($value, '@apiReturn')) ) { 
                $value          =   str_replace("/*","",$value);
                $value          =   str_replace("*/","",$value);
                $value          =   str_replace("*","",$value);
                $apiData        =   explode("<br />",$value);
                $ret[]          =   $apiData;
            }
        }
        return $ret;
    }

    // 分解数据写入fileArr数据里面
    public function ExeFileArr($data) {
        foreach ($data as $key => $value) {
            $file               =   array(); 
            foreach ($value as $k => $v) {
                // 把多个空格转成单个空格
                $v              =   $this->merge_spaces($v);
                // 过滤请求方式和接口名称
                if (strstr($v, '@api ')) {
                    // 去除制定字符串
                    $v          =   str_replace("@api ","",$v);
                    // 过滤请求方式
                    preg_match('/{(.*?)}/',$v,$type);
                    $file['type']           =   "未知";
                    if ($type){
                       if (is_array($type)) {
                            $file['type']   =   $type['0']; 
                       } else {
                            $file['type']   =   $type; 
                       }
                       
                       $v              =   str_replace($type,"",$v);
                    }

                    // 判断用户习惯 | 或者空格
                    $arr                =   $this->ExeXg($v);
                    if (count($arr) >= 2) {
                        $file['action'] =   $arr['0'];
                        $file['name']   =   $arr['1'];
                    }
                }
                // 过滤版本号
                if (strstr($v, '@apiVersion ')) {
                    $file['version']    =   trim(str_replace("@apiVersion ","",$v));
                }
                // 过滤描述信息
                if (strstr($v, '@apiDescription ')) {
                    $file['describe']    =   trim(str_replace("@apiDescription ","",$v));
                }
                // 过滤请求头部信息
                if (strstr($v, '@apiHeader ')) {
                    $header              =   array();
                    $v                   =   trim(str_replace("@apiHeader ","",$v));
                    // 过滤请求方式
                    preg_match('/{(.*?)}/',$v,$type);
                    if ($type){
                       if (is_array($type)) {
                            $header['type']   =   $type['0']; 
                       } else {
                            $header['type']   =   $type; 
                       }
                       $v              =   str_replace($type,"",$v);
                    }
                     // 判断用户习惯 | 或者空格
                    $arr                =   $this->ExeXg($v);
                    if (count($arr) >= 2) {
                        preg_match('/\[(.*?)\]/',$arr['0'],$status);
                        $header['status']       =  "是";
                        if ($status) {
                            $header['status']   =  "否";
                        } 

                        $header['param']    =   $this->exeLr($arr['0']);
                        $header['describe'] =   $arr['1'];
                    }
                    $file['header'][]    =   $header;
                }
                // 过滤请求参数
                if (strstr($v, '@apiParam ')) {
                    $request                  =   array();
                    $v                        =   trim(str_replace("@apiParam ","",$v));
                    $v                        =   (str_replace("(输入参数) ","",$v));
                    $v                        =   (str_replace("(输入参数：) ","",$v));
                    // 过滤请求方式
                    preg_match('/{(.*?)}/',$v,$type);
                    if ($type){
                       if (is_array($type)) {
                            $request['type']   =   $type['0']; 
                       } else {
                            $request['type']   =   $type; 
                       }
                       $v              =   str_replace($type,"",$v);
                    }

                     // 判断用户习惯 | 或者空格
                    $arr                =   $this->ExeXg($v);
                    if (count($arr) >= 2) {
                        preg_match('/\[(.*?)\]/',$arr['0'],$status);
                        $request['status']       =  "是";
                        if ($status) {
                            $request['status']   =  "否";
                        } 

                        $request['param']    =   $this->exeLr($arr['0']);
                        $request['describe'] =   $arr['1'];
                    }
                    $file['request'][]    =   $request;
                }
                // 过滤返回的数据
                if (strstr($v, '@apiReturn ')) {
                    $return                   =   array();
                    $v                        =   trim(str_replace("@apiReturn ","",$v));
                    $v                        =   (str_replace("(返回的数据) ","",$v));

                    // 过滤返回类型
                    preg_match('/{(.*?)}/',$v,$type);
                    if ($type){
                       if (is_array($type)) {
                            $return['type']   =   $type['0']; 
                       } else {
                            $return['type']   =   $type; 
                       }
                       $v              =   str_replace($type,"",$v);
                    }

                    // $arr               =   explode("|",trim($v));
                    $arr                    =   $this->ExeXg($v);
                    if (count($arr) >= 2) {
                        $return['param']    =   $arr['0'];
                        $return['describe'] =   $arr['1'];
                    }
                    $file['return'][]    =   $return;
                    
                }

            }
            // 提取字符串中的json数据  
            $str            =   implode(",",$value);
            if (strstr($str, '@apiSuccessExample')) {  
                // 提取 @apiSuccessExample 之后的数据
                $true            =   false;
                $json            =   "";
                foreach ($value as $k => $v) {
                    if ($true) {
                        $json    .= $v.PHP_EOL;
                    }
                    if (strstr($v, '@apiSuccessExample')) {
                        $true       =   true;
                        // 防止开发者写多个这里只验证最后一个
                        $json       =   "";
                    }
                    
                }
                $start              =   strpos($json,"{");
                $end                =   (strrpos($json,"}"));
                $jsonTrue           =   substr($json,$start,$end);
                // 判断如果是一个完整的json 则返回完整的 非完整的返回原格式
                if (json_decode($jsonTrue,true)){
                    $file['json']   =   json_encode(json_decode($jsonTrue,true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                } else {
                    $file['json']   =   $json;
                }
            }

            // 判断格式是否正确  正确写入数据
            $this->is_fileArr($file); 
        }

        return true;
    }


    // 多个空格转成单个空格
    public function merge_spaces($string){
        return preg_replace("/\s(?=\s)/","\\1",$string);
    }
    // 判断格式是否正确  正确写入数据
    public function is_fileArr($file) {
        // 判断动作是否存在
        if (!$file['action']) {
            $this->msg      .=  "当前请求的地址不存在或者为空".PHP_EOL;
            return $this->error();
        }
        if (!$file['name']) {
            $this->msg      .=  "当前请求的接口名称不存在或者为空".PHP_EOL;
            return $this->error();
        }
        $fileArr                =   $this->fileArr;
        $fileArr[]              =   $file;
        $this->fileArr          =   $fileArr;
        return true;
    }


    /**
     * @var    开始的字符串
     * @return [type] [description]
     */
    public function startHtml() {
$str = <<<"STARTHTML"
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
  <meta http-equiv="X-UA-Compatible" content="IE=edge" /> 
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" /> 
  <meta name="applicable-device" content="pc,mobile" /> 
  <meta name="description" content="简单快捷" /> 
  <meta name="keywords" content="API开发文档,wzapi自动生成" /> 
  <title>API开发文档 - wzapi自动生成</title> 
  <style>
    .body,input,button,textarea,select{font-size:16px;line-height:1.75;color:#4a5875}a:hover{text-decoration:none}ul{overflow:hidden}ul li{list-style:none}.clear:after{content:'.';display:block;height:0;clear:both;visibility:hidden}.wrapper{width:1200px;margin:0 auto}.allcenter{display:flex;justify-content:center;align-items:center}.header{height:80px;color:#666;font-size:14px;position:fixed;z-index:999;border-bottom:1px solid #eaeaea;background-color:#fff;-webkit-transition:all .2s;-o-transition:all .2s;transition:all .2s;-webkit-box-shadow:0 0 20px rgba(0,0,0,.1);box-shadow:0 0 20px rgba(0,0,0,.1);width:100%}.header a{color:#666}.header .wrapper{height:auto}.header .logo{padding-top:15px;float:left}.header .logo img{margin-top:10px}.header .nav-wrap{float:right;position:relative}.header .nav{float:right;line-height:80px;margin-right:197px}.header .nav ul{overflow:visible}.header .nav li{float:left;margin-left:10px;position:relative;padding:0 15px;cursor:pointer;list-style:none}.header .btns .on a{color:#00ffde;border-color:#00ffde}.menu-item{position:relative;font-weight:normal}.current_page_item a{color:#3397fd}.current_page_item i{color:#00ffde}.current-menu-parent strong:before{content:'';position:absolute;left:0;right:0;bottom:-10px;height:2px;background:#3397fd}.current_page_item strong:before{content:'';position:absolute;left:0;right:0;bottom:-10px;height:2px;background:#3397fd}.xh-nav li strong{position:relative;font-weight:normal}.header .nav li:hover dl{display:block}.header .nav i{padding-left:10px;font-size:14px}.header .btns{margin-top:20px;line-height:38px;position:absolute;right:0;top:0}.header .btns li{float:left;margin-left:10px;cursor:pointer}.header .btns a{display:inline-block;width:88px;height:38px;border:1px solid #3397fd;text-align:center;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;color:#3397fd}.header .reg:hover a{background:#fff;color:#3e7bf8}.banner{margin-top:80px;height:150px;background:url(images/banner-5.jpg) no-repeat center center #4481f6;text-align:center;color:#fff;font-size:18px;background-size:100%}.banner h2{padding-top:55px;font-size:30px}.banner p{letter-spacing:10px;font-size:16px}.guide-nv{border-bottom:#dcdcdc solid 2px;height:80px;line-height:80px;margin-top:20px;font-size:24px;color:#4a5875}.guide-nv li{float:left}.guide-nv a{width:235px;display:inline-block;border-bottom:transparent solid 2px;text-align:center;height:78px;line-height:80px;font-size:18px}.guide-nv li.on a{border-bottom-color:#6e94ff}.guide-mn{height:auto;overflow:hidden}.guide-l{width:235px;float:left;padding-top:30px}.guide-l li{margin-bottom:15px}.guide-l a{display:block;height:65px;color:#4a5875;font-size:18px;text-align:center;border-radius:5px;line-height:65px}.guide-l li a:hover{background-color:#eee}.guide-l li.on a{background:#6e94ff;color:#fff}.guide-r{width:900px;float:right}.guide-rhd{border-bottom:#dcdcdc solid 1px;padding:30px 0 20px 0;font-size:20px;color:#4a5875}.guide-rbd{font-size:18px;line-height:52px;padding:30px 0}.guide-rbd p{margin-bottom:60px;font-size:14px}.guide-rbd a{color:#6e94ff;margin-right:20px}.clear_fix{clear:both}.news_list{width:1000px;margin:0 auto;padding:40px 0}.news_list .item{border-bottom:1px solid #e4e8f3;list-style:none;padding:40px 0;clear:both}.news_list .img{float:left}.news_list .img img{display:block;width:300px;height:200px}.news_list .txt{float:right;width:660px}.news_list .txt h2{font-size:24px;font-weight:400;line-height:34px;height:34px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.news_list .txt h2 a{color:#000;text-decoration:none}.news_list .txt p{height:124px;font-size:14px;line-height:24px;margin:10px 0 12px;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:5;overflow:hidden;text-overflow:ellipsis}.news_list .txt p a{color:#999;text-decoration:none}.news_list .txt span{font-size:14px;color:#333;line-height:20px}.news_detail{width:1000px;margin:30px auto;background:#f8f8f8;padding:40px 30px}.news_detail h1{font-size:30px;font-weight:400;color:#333;text-align:center;line-height:42px}.news_detail h2,.news_detail h3,.news_detail h4,.news_detail h5{margin:20px 0}.news_detail .subtitle{font-size:14px;color:#999;text-align:center;text-indent:0;line-height:20px;margin-top:20px}.page_body{padding:30px 0}.news_detail p{font-size:14px;color:#666;line-height:28px;margin-top:10px}.news_detail img{text-align:center;max-width:100%;margin:15px auto}.news_detail ul,.news_detail ol{margin:30px 0 30px 30px;line-height:28px}.news_detail ul li,.news_detail ol li{list-style:disc}.footer{padding:40px 0 30px 0;background:#394f7e;color:#fff;height:auto;overflow:hidden;font-size:16px}.footer a{color:#d7e2ff}.footer a:hover{color:#fff}.footer .iconfont{margin-right:20px}.footer dl{float:left;width:260px}.footer dl:first-child{width:310px}.footer dl:last-child{width:auto}.footer dt{font-size:16px;border-bottom:#a8b6d9 solid 1px;display:inline-block;padding-bottom:5px;margin-bottom:10px}
.footer dd{margin-top:10px}.footer dd a{font-size:12px}.copyright{background:#2f4471;color:#fff;padding:15px 0;text-align:center;font-size:12px}.copyright a{color:#f8f8f8}.docs{height:auto;overflow:hidden}.docs-nav{width:235px;padding-top:30px;margin-bottom:160px;float:left}.docs-nav li{margin-bottom:10px}.docs-nav li a{display:block;height:50px;color:#4a5875;font-size:16px;border-radius:5px;line-height:50px;padding-left:20%}.docs-nav li a:hover{background-color:#eee}.docs-nav li.on a{background:#4481f6;color:#fff;border-bottom-color:#4481f6}.docs-main{width:900px;float:right}.docs-hd{border-bottom:#dcdcdc solid 1px;padding:30px 0 10px 0;font-size:20px;color:#4a5875}.docs-bd{font-size:14px;line-height:28px;padding:15px 0;color:#666}.docs-bd table a{color:#4481f6}.docs-bd p{margin-bottom:160px}.docs-bd a{color:#4481f6!important}.hbanner{margin-top:80px;width:100%;height:650px;background:url(images/banner.jpg) no-repeat center #3e7bf8;background-size:cover;color:#fff;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei"}.g-txt{float:left;line-height:1.4;padding-top:120px;width:539px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei"}.g-txt h2{position:relative;padding-left:20px;font-size:36px;font-weight:bold}.g-txt h2:before{content:'';position:absolute;left:0;top:8px;width:5px;height:36px;background:#fff}.g-txt h2 font{font-size:30px;font-weight:normal}.g-txt p{padding-top:40px;font-size:16px;line-height:30px}.g-img{padding-top:98px;float:right}.g-home-btn{margin-top:67px;display:inline-block;width:230px;height:60px;line-height:60px;text-align:center;font-size:24px;font-family:"微软雅黑";color:#008aff;background:#fff;-webkit-box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:solid 1px #fff}.hnav{margin-top:35px}.hnav ul{padding:40px 0;background:#fff;-webkit-box-shadow:0 1px 30px 0 rgba(27,103,229,0.1);box-shadow:0 1px 30px 0 rgba(27,103,229,0.1);-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px}.hnav li{float:left;width:33.33%;border-right:1px solid #e5e5e5;-moz-transition:all .5s;-webkit-transition:all .5s;transition:all .5s}.hnav li:hover{-moz-transform:translateY(-20px);-webkit-transform:translateY(-20px);transform:translateY(-20px)}.hnav li:last-child{border:0}.hnav li .img{float:left;padding:25px 30px 0 30px}.hnav li .txt{float:left;color:#6d7589}.hnav li .txt h2{font-size:16px;line-height:30px}.hnav li .txt p{padding-top:20px;font-size:14px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei";line-height:25px;color:#8a95b2}.hhelp{padding:60px 0 90px 0}.g-hd{text-align:center;color:#515c7a}.g-hd h2{font-size:30px}.g-hd p{font-size:16px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei"}.hhelp-bd{margin-top:85px}.hhelp-bd ul{overflow:visible}.hhelp-bd li{float:left;width:20%;text-align:center;-moz-transition:all .5s;-webkit-transition:all .5s;transition:all .5s}.hhelp-bd li img{width:125px;height:125px;border-radius:100%;background-color:#f5f5f5}.hhelp-bd li:hover{-moz-transform:translateY(-20px);-webkit-transform:translateY(-20px);transform:translateY(-20px)}.hhelp-bd .txt h2{margin-top:30px;font-size:16px;color:#4a5875}.hhelp-bd .txt h4{padding-top:20px;font-size:14px;color:#8d95a4}.hpro{padding:85px 0 100px 0;background:#f6f9fe}.hpro-bd{margin-top:95px}.hpro-bd ul{height:380px;overflow:visible;display:flex;align-items:center;justify-content:center}.hpro-bd li img{max-width:100%}.hpro-bd li{margin-right:25px;width:280px;height:390px;background:#fff;text-align:center;-webkit-box-shadow:0 5px 20px 0 rgba(145,145,145,0.05);box-shadow:0 5px 20px 0 rgba(145,145,145,0.05);-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;border:solid 1px #fff;-moz-transition:all .5s ease;-webkit-transition:all .5s ease;transition:all .5s ease}.hpro-bd li:hover{-moz-transform:translateY(-50px);-webkit-transform:translateY(-50px);transform:translateY(-50px);-webkit-box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);box-shadow:0 20px 30px 0 rgba(62,123,248,0.3)}.hpro-bd li:last-child{margin-right:0}.hpro-bd li .img{margin-top:34px}.hpro-bd li .txt{position:relative;line-height:30px;color:#515c7a}.hpro-bd .txt:before{content:'';position:absolute;left:50%;top:55px;margin-left:-25px;width:50px;height:2px;background:#ff8b88}.hpro-bd li .txt h2{padding:20px 0 30px 0;font-size:20px}.hpro-bd li .txt p{font-size:14px;color:#8a95b2}.hpro-bd2{margin-top:55px}.hpro-bd2 ul{height:380px;overflow:visible;display:flex;align-items:center;justify-content:center}.hpro-bd2 li img{max-width:100%;border-top-left-radius:5px;border-top-right-radius:5px}
.hpro-bd2 li{margin-right:35px;width:280px;height:340px;background:#fff;-webkit-box-shadow:0 5px 20px 0 rgba(145,145,145,0.05);box-shadow:0 5px 20px 0 rgba(145,145,145,0.05);-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;border:solid 1px #fff;-moz-transition:all .5s ease;-webkit-transition:all .5s ease;transition:all .5s ease;cursor:pointer}.hpro-bd2 li:hover{-moz-transform:translateY(-50px);-webkit-transform:translateY(-50px);transform:translateY(-50px);-webkit-box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);box-shadow:0 20px 30px 0 rgba(62,123,248,0.3)}.hpro-bd2 li:last-child{margin-right:0}.hpro-bd2 li .img{margin-top:0}.hpro-bd2 li .txt{position:relative;line-height:30px;color:#515c7a}.hpro-bd2 li .txt h2{padding:10px;height:40px;line-height:30px;font-size:20px;overflow:hidden;text-align:left}.hpro-bd2 li .txt p{font-size:14px;color:#8a95b2;line-height:22px;padding:10px;text-align:left}.line-ffb573 .txt:before{background:#ffb573}.line-47e7c4 .txt:before{background:#47e7c4}.line-6e94ff .txt:before{background:#6e94ff}.pay{padding:120px 0 110px}.pay .pay-txt{margin-top:35px;line-height:30px;font-size:14px;letter-spacing:normal;color:#8a95b2}.pay-bd{margin-top:85px;text-align:center}.pay ul{clear:both;margin-bottom:-66px}.pay li{padding:31px 0;margin-bottom:66px;float:left;width:260px;margin-right:30px;text-align:center;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;border:solid 1px #e5e5e5;-moz-transition:all .5s;-webkit-transition:all .5s;transition:all .5s;opacity:.8}.pay li:last-child{margin-right:0}.pay li:hover{border-color:#1b92fa}.pay-btn{margin-top:72px}.g-btn{display:inline-block;width:200px;height:60px;line-height:60px;font-size:28px;color:#fff;text-align:center;background-image:linear-gradient(#1b92fa,#1b92fa),linear-gradient(#57b1fb,#57b1fb);-webkit-box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);box-shadow:0 20px 30px 0 rgba(62,123,248,0.3);-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;-moz-transition:all .5s;-webkit-transition:all .5s;transition:all .5s;opacity:.8}.g-btn:hover{opacity:1;color:#fff}.hview{overflow:hidden;padding:140px 0 120px}.even{background:#f6f9fe}.even .img{float:right}.even .txt{float:left}.odd .img{float:left}.odd .txt{float:right}.btn-zx{margin-top:40px}.btn-jr{margin-top:20px}.line-27{font-size:26px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei";color:#8690a3}.line-27:before{content:'';margin-right:15px;display:inline-block;width:2px;height:27px;background:#47e7c4;vertical-align:-4px}.hview-txt ul{margin-top:40px}.hview-txt li{position:relative;padding-left:45px;margin-bottom:25px;font-size:14px;line-height:28px;color:#8d95a4}.hview-txt li .icon-xuanzhongzhuangtai{position:absolute;left:0;top:0;font-size:21px;color:#49a8fb}.hpartner{height:760px;background:url(images/5f7a2b457b3a99822b044e4cae811023.jpg) no-repeat center #00baff;background-size:cover}.hpart-hd{padding-top:80px;color:#fff}.hpart-bd{position:relative;margin-top:60px}.hpart-list{position:relative}.hpart-list:before{content:'';position:absolute;left:50%;bottom:-31px;margin-left:-544px;width:1090px;height:363px;background:#fff;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;opacity:.7}.list-wrper{width:100%;-webkit-border-radius:10px;-moz-border-radius:10px;border-radius:10px;overflow:hidden}.hpart-list-wrapper{*zoom:1;-webkit-backface-visibility:hidden;-webkit-transform-style:preserve-3d;transition:all 1s ease;background:#fff}.hpart-list .hpart-list-item{float:left;width:1200px;background:#fff;-webkit-border-radius:10px;-moz-border-radius:10px;border-radius:10px}.hpart-list .hpart-list-item dl{padding:58px 78px;height:380px;overflow:hidden}.hpart-list .hpart-list-item dd{float:left;width:20%;margin-bottom:20px;text-align:left}.hpart-list .hpart-list-item a{display:block}.slider-nav{position:absolute;bottom:30px;margin-left:50px}.slider-nav__item{width:10px;height:6px;float:left;clear:none;display:block;margin:0 10px;background:#a6b0c9;border-radius:3px;transition:width .5s}.slider-nav__item--current{width:30px;background:#8dacf9}.hpart-prev,.hpart-next{position:absolute;left:-100px;top:50%;margin:-30px 0 -50px 0;width:100px;height:100px;text-align:center;cursor:pointer}.hpart-next{left:inherit;right:-100px}.hpart-prev i,.hpart-next i{font-size:42px;color:#fff}.hstart{position:relative;padding:100px 0 160px;background:url(images/img_25.png) no-repeat bottom;overflow:hidden}.hstart-txt:before{content:'';position:absolute;top:50%;left:0;margin-top:-36px;width:6px;height:86px;background:#47e7c4}.hstart-txt{position:relative;padding-left:30px}.hstart-txt h2{font-size:30px;color:#38a0fb}.hstart-txt p{font-size:16px;color:#515c7a}.hstart-btn{position:absolute;top:50%;right:21%;margin-top:-75px}.hstart-btn a{width:268px;height:80px;line-height:80px;color:#fff}.sidebar{position:fixed;bottom:50%;right:0;z-index:999}
.sidebar li{margin-bottom:12px}.sidebar a{display:block;width:54px;height:54px;line-height:54px;background:#49a8fb;text-align:center;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;opacity:.7}.sidebar a:hover{opacity:1}.sidebar i{font-size:30px;color:#fff}.about{padding:80px 0 110px 0}.about-hd{text-align:left}.about-bd{margin-top:40px;line-height:30px;font-size:16px;font-family:'宋体';color:#8d95a4}.about-hd h2:before{content:'';display:inline-block;margin-right:30px;width:2px;height:42px;background:#ff8b88;vertical-align:middle}.contact{padding:60px 0 85px 0;background:#f9f9f9}.contact-bd{padding:67px 30px 0;overflow:hidden}.contact-bd ul{margin-top:60px}.contact-bd li{position:relative;margin-bottom:10px;padding-left:163px;font-size:16px;color:#515c7a}.contact-bd span{position:absolute;top:0;left:0}.contact-bd i{font-size:23px;margin-right:34px;vertical-align:middle}.contact-bd .txt{float:left;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei"}.contact-bd .img{float:left;margin:35px 0 0 219px;-webkit-border-radius:10px;-moz-border-radius:10px;border-radius:10px;-webkit-box-shadow:0 0 56px 5px #d2d3d5;box-shadow:0 0 56px 5px #d2d3d5}.contact-bd img{display:block;border:5px solid #fff;-webkit-border-radius:10px;-moz-border-radius:10px;border-radius:10px}.channel{padding:137px 0}.channel-bd{margin-top:60px;overflow:hidden}.channel-bd .txt{float:right}.channel-bd .img{float:left}.channel-bd .txt{width:560px}.g-bd .btn{margin-top:60px;margin-left:25px;width:300px;background-color:#6e94ff;background-image:none;-webkit-box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);box-shadow:0 20px 30px 0 rgba(41,59,236,0.3)}.g-bd .txt p{margin-top:30px;padding-left:25px;font-size:16px;font-family:"宋体";color:#8d95a4}.traffic{padding:90px 0;background:#f9f9f9}.traffic-bd{margin-top:22px}.traffic-bd .txt,.traffic-bd .img{float:left}.traffic-bd .txt{margin:90px 35px 0 0;width:585px;line-height:30px}.sdk{padding:127px 0 56px 0;background:#f9f9f9}.sdk ul{overflow:visible;margin-right:-77px}.sdk li{position:relative;margin:0 77px 136px 0;float:left;width:348px;height:178px;background:#fff;-webkit-box-shadow:0 10px 14px 1px rgba(110,110,110,0.05);box-shadow:0 10px 14px 1px rgba(110,110,110,0.05);-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;cursor:pointer}.sdk .txt{padding:60px 0 0 45px;font-size:18px;color:#4a5875}.sdk .img{position:absolute;top:53px;right:37px}.sdk .btn{position:absolute;left:50%;bottom:-30px;margin-left:-64px}.sdk .btn a{display:block;width:129px;height:57px;line-height:57px;text-align:center;font-size:22px;color:#fff;background:#6e94ff;-webkit-box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px}.demo{padding:133px 0;background:#f9f9f9;overflow:hidden}.demo-txt{float:left;font-size:46px;color:#4a5875}.demo-img{float:right}.demo1 .demo-img{padding-right:200px;-webkit-border-radius:20px;-moz-border-radius:20px;border-radius:20px}.demo1 img{border:5px solid #fff;-webkit-box-shadow:0 10px 14px 1px rgba(110,110,110,0.1);box-shadow:0 10px 14px 1px rgba(110,110,110,0.1);-webkit-border-radius:20px;-moz-border-radius:20px;border-radius:20px}.demo-txt h2{position:relative;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei";padding-left:17px;line-height:1.5;font-size:30px}.demo-txt h2 font{font-style:oblique;color:#3d7bf8}.demo-txt h2:before{content:'';position:absolute;top:15%;left:0;width:2px;height:102px;background:#6e94ff;vertical-align:middle}.demo-txt p{margin-top:30px;font-size:14px;line-height:30px;color:#8d95a4}.demo2{background:url(images/img_46.png) no-repeat center bottom}.demo2 .demo-txt h2:before{height:42px;top:23%}.demo2 .demo-txt{padding-top:25px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei"}.demo2 .demo-img{float:left;padding-right:75px}.demo2 .btn{margin-top:60px;width:300px;height:80px;line-height:80px;background:#6e94ff;-webkit-box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);-webkit-border-radius:20px;-moz-border-radius:20px;border-radius:20px}.faq-view{margin-top:-80px;height:387px;background:url(images/banner-4.jpg) no-repeat center #4481f6;text-align:center}.faq-srch{position:relative;margin-top:95px;display:inline-block;text-align:center}.faq-srch .txt,.faq-srch .btn{border:0;background:0}.faq-srch .txt{padding:0 110px 0 34px;width:770px;height:60px;line-height:60px;font-size:20px;background:#fff;-webkit-box-shadow:0 10px 14px 1px rgba(35,25,168,0.1);box-shadow:0 10px 14px 1px rgba(35,25,168,0.1);-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px}::-webkit-input-placeholder{color:#d3d7df}
:-moz-placeholder{color:#d3d7df}::-moz-placeholder{color:#d3d7df}:-ms-input-placeholder{color:#d3d7df}.faq-srch .btn{position:absolute;top:0;right:0;width:110px;height:60px;line-height:60px;text-align:center;color:#fff;background:#6e94ff;-webkit-border-top-right-radius:5px;-moz-border-radius-topright:5px;border-top-right-radius:5px;-webkit-border-bottom-right-radius:5px;-moz-border-radius-bottomright:5px;border-bottom-right-radius:5px;cursor:pointer;opacity:.8;-webkit-transition:all .5s;-moz-transition:all .5s;transition:all .5s}.faq-srch .btn:hover{opacity:1}.faq-nav{margin-top:80px}.faq-nav ul{overflow:visible}.faq-nav li{float:left;width:16.6%}.faq-nav li a{position:relative;display:inline-block;font-size:24px;color:#fff}.faq-nav li:hover a:before{content:'';position:absolute;left:0;right:0;bottom:-10px;height:2px;background:#fff}.faq-nav li.on a:before{content:'';position:absolute;left:0;right:0;bottom:-10px;height:2px;background:#fff}.faq-list{padding:66px 0;overflow:hidden}.faq-listbg{background:#fcfcfc}.faq-list dl{position:relative;float:left;padding-left:15px;width:50%}.faq-list dt{font-size:24px;color:#4a5875}.faq-list dl:before{content:'';position:absolute;left:0;top:7%;width:2px;height:22px;background:#ff8b88;vertical-align:middle}.faq-list dt{margin-bottom:30px}.faq-list dd a{line-height:35px;font-size:18px;color:#8d95a4}.faq-list dd:hover a{text-decoration:underline}.faq-list .bj-ffb573:before{background-color:#ffb573}.faq-list .bj-ffd74c:before{background-color:#ffd74c}.faq-list .bj-47e7c4:before{background-color:#47e7c4}.faq-list .bj-4ac4fd:before{background-color:#4ac4fd}.faq-list .bj-f19ec2:before{background-color:#f19ec2}.faq-chat{padding:130px 0 0}.faq-pd{padding-bottom:80px}.faq-chat .txt{text-align:center}.faq-img{background:url(images/img_36.png) no-repeat center bottom}.faq-chat h2{font-size:24px;color:#4a5875}.faq-chat h4{margin-top:20px;font-size:20px;color:#8d95a4}.g-btn-2{margin-top:40px;display:inline-block;width:300px;height:80px;line-height:80px;background:#6e94ff;font-size:32px;color:#fff;text-align:center;-webkit-box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);box-shadow:0 20px 30px 0 rgba(41,59,236,0.3);-webkit-border-radius:10px;-moz-border-radius:10px;border-radius:10px;-moz-transition:all .5s;-webkit-transition:all .5s;transition:all .5s;opacity:.8}.g-btn-2:hover{opacity:1}.faqd-hd{padding:55px 0 10px;font-size:24px;border-bottom:2px solid #dcdcdc;color:#4a5875}.faqd-hd:before{height:22px;background:#ff8b88}.faqd-bd{margin-top:50px;padding:0 45px;font-size:18px;color:#8d95a4}.faqd-bd img{margin-top:35px}.probanner{margin-top:-80px;width:100%;height:600px;background:url(images/banner-5.jpg) no-repeat center #3d7bf8;background-size:cover;color:#fff}.probanner-r{margin-left:100px;float:left}.pro-pay{padding:90px 0 124px}.pro-bd{margin-top:70px;overflow:hidden}.pro-pay-l{margin-top:80px;float:left}.pro-pay-r{float:right}.g-describe{margin-top:50px;font-size:14px;font-family:"Lucida Grande","Microsoft JhengHei","Microsoft YaHei";line-height:30px;color:#8d95a4}.pro-pay-r ul{margin-top:100px;overflow:visible}.pro-pay-r li{float:left;width:25%;text-align:center;cursor:pointer;transition:all .5s}.pro-pay-r li:hover{-webkit-transform:translateY(-30px);-moz-transform:translateY(-30px);transform:translateY(-30px)}.pro-pay-r li:hover span{color:#5bb1fb}.pro-pay-r li span{display:block;margin-top:17px;font-size:16px;color:#4a5875}.pro-sys{padding:70px 0 90px;background:#f7f9ff}.pro-sys .pro-txt{margin-top:70px;line-height:30px;font-size:14px;font-family:"微软雅黑";letter-spacing:normal;color:#8d95a4}p.pro-txt{font-size:14px}.pro-sys-l,.pro-sys-r{float:left}.pro-sys-l li a{display:block;width:239px;height:120px;line-height:120px;font-size:20px;color:#5f697f;background:#f8f9fc;text-align:center}.pro-sys-l li.on a{background:#57b1fb;color:#fff}.pro-sys-l li.on i{color:#fff}.pro-sys-l li i{margin-right:30px;font-size:30px;color:#5f697f}.pro-sys-r{position:relative;width:959px;height:600px;background:#fff}.pro-sys-r-none{display:none}.pro-sys-r .img{margin-top:55px;text-align:center}.pro-sys-r .txt{padding:0 0 0 80px;margin-top:-35px}.pro-sys-r .txt h2{font-size:20px;font-family:"微软雅黑";color:#515c7a}.pro-sys-r .txt p{margin-top:10px;font-size:14px;line-height:36px}.pro-sys-r .btn{position:absolute;right:103px;bottom:60px}.pro-df{padding:110px 0 100px;background:#fff}.pro-df .pro-bd{overflow:visible}.pro-df-l{padding-bottom:20px}.pro-df-l .bj-4ac4fd:before{background:#4ac4fd}.pro-df-l .btn{margin-top:60px}.pro-qr{padding:80px 0 67px;background:#f7f9ff}.pro-qr .pro-bd{margin-top:20px}.pro-qr-l{margin-top:100px}.pro-qr-l .btn{margin-top:88px}.pro-qr-r{padding-left:60px}.xh-nav ul{display:none;position:absolute;left:0;top:80px;background:#fff;border-radius:5px;width:145px;text-align:center;padding:15px 0;box-shadow:rgba(0,0,0,0.1) 0 5px 5px;z-index:11}
.xh-nav li:hover>ul{display:block}.sub-menu li{margin:0!important;padding:0!important;width:145px;height:50px;line-height:50px}.sub-menu li a{color:#485774;font-size:14px;display:block;word-break:keep-all;text-align:center}pre{display:block;padding:9.5px;margin:0 0 10px;font-size:13px;line-height:1.42857143;color:#333;word-break:break-all;word-wrap:break-word;background-color:#f5f5f5;border:1px solid #ccc;border-radius:4px;padding:20px;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}code{padding:2px 4px;font-size:90%;color:#c7254e;background-color:#f9f2f4;border-radius:4px}*{margin:0;padding:0;-webkit-appearance:none;box-sizing:border-box}html,body{overflow-x:hidden;background-color:#fff}@media only screen and (max-width:1210px){html,body{font-size:14px}img{max-width:100%}.wrapper{margin:0 3%;width:auto}.header{height:65px}.bj-3d7bf8{padding-bottom:80px}.header .btns{position:relative;margin-top:0;text-align:center}.header .btns ul{text-align:center}.header .btns li{float:none;display:inline-block;margin-left:0;text-align:center;padding:10px 0}.header .nav-wrap{position:absolute;left:0;right:0;top:65px;background:#3e7af8;z-index:9;display:none;box-shadow:0 5px 15px 0 rgba(41,59,236,0.6)}.header .nav{float:none;line-height:50px;margin-right:0}.header .nav li{display:block;width:100%;float:none;text-align:center;font-size:16px;padding:0;margin-left:0;border-bottom:1px dashed rgba(255,255,255,0.3)}.header .nav .on strong:before{display:none}.header .nav dl{width:100%;position:relative;top:0}.gh{float:right;height:25px;width:23px;margin-right:5px;margin-top:20px;position:relative;transition:all .5s cubic-bezier(0.7,0,0.3,1) 0s;-webkit-transition:all .5s cubic-bezier(0.7,0,0.3,1) 0s;-ms-transition:all .5s cubic-bezier(0.7,0,0.3,1) 0s;cursor:pointer}.gh.selected{transform:rotate(90deg)}.gh a{background-color:#fff;display:block;height:3px;margin-top:-1px;position:relative;top:50%;transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;-webkit-transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;-ms-transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;width:100%;border-radius:3px}.gh a:after,.gh a:before{background-color:#fff;content:"";display:block;height:3px;left:0;position:absolute;transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;-webkit-transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;-ms-transition:all .3s cubic-bezier(0.7,0,0.3,1) 0s;width:100%;border-radius:3px}.gh a:after{top:9px}.gh a:before{top:-8px}.gh.selected a:after,.gh.selected a:before{top:0}.gh.selected a:before{transform:translateY(0px) rotate(-45deg);-webkit-transform:translateY(0px) rotate(-45deg);-ms-transform:translateY(0px) rotate(-45deg)}.gh.selected a:after{transform:translateY(0px) rotate(45deg);-webkit-transform:translateY(0px) rotate(45deg);-ms-transform:translateY(0px) rotate(45deg)}.gh.selected a{background-color:transparent!important}.sidebar{right:10px}.hbanner{position:relative;height:500px}.g-txt{float:none;padding:0 3%;position:relative;z-index:1;width:auto;text-align:center;padding-top:130px}.g-txt h2{text-align:center;line-height:1.2;padding-left:0;font-size:24px;position:relative}.g-txt h2:after{position:absolute;left:50%;margin-left:-25px;bottom:-20px;content:"";width:50px;height:2px;background-color:#47e7c4}.g-txt h2 font{line-height:1.3;font-size:24px}.g-txt h2:before{display:none}.g-txt p{text-align:center;font-size:16px;padding-top:40px;max-width:420px;margin:0 auto;opacity:.8;line-height:1.6}.g-home-btn{margin-top:50px;font-size:18px;width:180px;height:45px;line-height:45px}.g-img{float:none;padding-top:0;position:absolute;left:0;right:0;text-align:center;bottom:0}.g-img img{opacity:.4}.hnav{margin-top:0}.hnav .wrapper{margin:0}.hnav li{width:33.33%;text-align:center;border-right:0;position:relative}.hnav li:after{position:absolute;right:0;top:0;content:"";width:1px;height:30px;background-color:#f1f1f1}.hnav li:last-child:after{display:none}.hnav li .img{float:none;padding:0}.hnav li .img img{width:45px}.hnav li .txt{float:none;max-width:160px;display:inline-block}.hnav li .txt h2{font-size:16px}.hnav li .txt br{display:none}.hnav li .txt p{font-size:12px;padding-top:10px;margin:0 10px;line-height:1.6}.g-hd h2{font-size:20px}.about-hd h2:before{height:25px;margin-right:20px}.g-hd p{font-size:15px;letter-spacing:1px;line-height:1.3}.hhelp,.hpro,.pay{padding:40px 0}.hhelp-bd,.hpro-bd,.pay-bd{margin-top:30px}.hnav{position:relative;z-index:2}.hhelp{background:#fff}.hhelp-bd ul{height:auto;overflow:hidden}.hhelp-bd .txt h2{font-size:15px}.hhelp-bd .txt h4{padding-top:5px;font-size:12px}.hhelp-bd li{width:50%;margin-bottom:10px}.hhelp-bd li.s{display:block}.hpro-bd ul{height:auto;overflow:hidden}.hpro-bd li{width:47%;margin:0 1%;margin-bottom:2%;height:220px}.hpro-bd li .img{margin-top:18px}.hpro-bd li .img img{width:60px}.hpro-bd li .txt h2{font-size:16px;padding:10px 0 30px}.hpro-bd .txt:before{top:50%;width:40px;margin-left:-20px;height:1px}
.hpro-bd li .txt h4{font-size:14px;line-height:1.5}.pay .pay-txt{max-width:420px;margin:30px auto 0;font-size:16px;line-height:1.5}.pay .pay-txt br{display:none}.pay li{width:47%;margin:0 1%;margin-bottom:15px;padding:20px 0 18px}.pay li .img img{width:75px}.pay li .txt{font-size:14px}.g-btn{height:45px;line-height:45px;font-size:16px}.hview{padding:50px 0}.line-27,.pro-sys-r .txt h2{font-size:18px}.line-27:before{height:20px;vertical-align:-2px}.hview .img{width:63%}.hview.even .img{margin-right:-30%}.hview.odd .img{margin-left:-30%;text-align:right}.hview-txt{width:60%}.hview-txt ul{margin-top:25px}.hview-txt li{position:relative;padding-left:30px;font-size:14px;line-height:1.8;margin-bottom:15px}.hview-txt li br{display:none}.hview-txt li .icon-xuanzhongzhuangtai{position:absolute;left:0;top:0;margin-top:-4px;font-size:18px}.btn-zx,.btn-jr{width:150px;display:block;margin:0 auto;margin-top:10px}.hstart{padding:40px 0}.hpartner{height:auto;padding-bottom:40px}.hpart-hd{padding-top:40px}.hpart-bd{margin-top:30px}.hpart-list li{width:auto}.hstart-txt{padding-left:20px}.hstart-txt h2{font-size:18px}.hstart-txt:before{width:3px;height:30px;margin-top:-15px}.hstart-txt p{font-size:12px;line-height:1.4}.hstart-btn{position:relative;top:0;right:0;margin-top:0;margin-top:35px;text-align:center}.hstart-btn a{height:45px;width:200px;line-height:45px}.hpart-prev,.hpart-next{display:none}.hpart-list:before{width:90%;margin-left:-45%;height:50px;bottom:-15px}.hpart-list .hpart-list-item dl{padding:20px 20px;height:auto}.footer{font-size:14px;padding-top:20px}.footer dl{display:none}.footer dt{font-size:16px}.footer .s{float:left;display:block}.footer dl:first-child{width:auto}.footer .iconfont{margin-right:5px}.footer .s:last-child{float:right}.faq-view{height:360px}.faq-srch{margin-top:80px}.faq-srch .txt{width:720px;box-sizing:border-box;height:50px;line-height:50px;font-size:16px;padding-left:15px}.faq-srch .btn{height:50px;line-height:50px}.faq-nav{margin-top:110px}.faq-nav li a{font-size:16px}.faq-nav li.on a:before{height:1px}.faq-list{padding:30px 0 0}.faq-list dl{margin-bottom:20px;box-sizing:border-box}.faq-list dt{margin-bottom:10px;font-size:18px}.faq-list dl:before{height:16px;top:8px}.faq-list dd a{font-size:14px;line-height:1.5}.faq-chat{padding-top:50px;padding-bottom:50px}.faq-chat h2{font-size:20px}.faq-chat h4{font-size:18px;margin-top:8px}.g-btn-2{width:200px;height:45px;line-height:45px;font-size:18px;margin-top:30px}.faqd-hd{padding-top:30px}.faqd-bd{margin-top:30px;font-size:14px;padding:0 10px}.faqd-bd img{margin-top:20px}.probanner{position:relative;height:500px}.g-img{font-size:0;overflow:hidden}.g-img img{position:relative;margin-bottom:-16%}.probanner-r{margin-left:0;display:none}.probanner-r img{width:300px}.pro-pay,.pro-sys,.pro-df,.pro-qr,.about,.contact,.channel,.traffic,.sdk,.login1{padding:40px 0}.pro-pay .wrapper{margin:0}.pro-bd,.pro-sys .pro-txt{margin-top:30px}.pro-pay-l{width:40%;margin-left:-50px;text-align:right;box-sizing:border-box;margin-top:0}.pro-pay-r{width:60%}.g-describe{margin-top:20px;font-size:14px;line-height:1.5}.g-describe br{display:none}.pro-pay-r ul{margin-top:30px}.pro-pay-r li{width:20%}.pro-pay-r li img{width:80px}.pro-pay-r li span{margin-top:8px;font-size:14px}.pro-sys .pro-txt{font-size:14px;line-height:1.5;max-width:600px;margin-left:auto;margin-right:auto}.pro-sys .pro-txt br{display:none}.pro-sys-l,.pro-sys-r{float:none;width:auto}.pro-sys-l li{width:20%;float:left;text-align:center}.pro-sys-l li a{display:block;width:auto;font-size:16px;line-height:1.2;padding:15px 0;height:auto}.pro-sys-l li i{display:block;margin-right:0}.pro-sys-r{padding:30px 10px;height:auto}.pro-sys-r .img{margin-top:0}.pro-sys-r .txt{padding:0 10px;width:60%;float:left}.pro-sys-r .txt p{font-size:14px;line-height:1.5}.pro-sys-r .btn{position:relative;right:0;bottom:0;margin-top:20px}.pro-df-l{width:60%}.pro-df-l .btn,.pro-qr-l .btn{margin-top:20px;margin-left:auto;margin-right:auto}.pro-df-r,.odd .pro-qr-r{width:40%}.pro-qr-r{padding-left:0}.pro-qr-l{margin-top:50px;width:60%}.login-hd h2{font-size:20px}.login-btn .btn{font-size:16px}.about-bd{font-size:14px;line-height:1.5;margin-top:20px}.contact-bd{padding:30px 0;text-align:center}.contact-bd ul,.channel-bd{margin-top:20px}.contact-bd li,.g-bd .txt p{font-size:16px}.contact-bd .txt{display:inline-block;float:none;text-align:left}.contact-bd .img{margin-left:50px;margin-top:0;display:inline-block;float:none}.channel-bd .img,.traffic-bd .img{width:40%}.channel-bd .txt,.traffic-bd .txt{width:55%;margin-left:5%}.traffic-bd .txt{margin-left:0;margin-right:5%;margin-top:20px}.g-bd .txt p{padding-left:0}.g-bd .btn{margin-top:30px}.contact-bd li,.g-bd .txt p{font-size:14px}.contact-bd li{padding-left:120px;line-height:1.5}.contact-bd span{line-height:1.5;vertical-align:top;margin-top:-6px}.contact-bd i{margin-right:15px}.docs-nav,.guide-l{width:200px}.docs-nav li a,.guide-l li a{height:45px;line-height:45px;font-size:18px}.docs-main,.guide-r{width:calc(100% - 220px)}
.docs-hd,.guide-rhd{font-size:18px;padding-bottom:10px}.docs-bd{font-size:14px;line-height:1.5;padding:15px 0}.guide-rbd{padding:20px 0}.guide-rbd p{font-size:14px;line-height:1.5;margin-bottom:15px}.demo{padding:50px 0;text-align:center}.demo-txt h2:before{display:none}.demo-txt,.demo-img,.demo2 .demo-img{float:none;width:auto}.demo1 .demo-img{padding-top:20px}.demo1 .demo-img,.demo2 .demo-img{padding-right:0}.demo-txt h2{font-size:22px}.demo-txt p{margin-top:10px;font-size:16px}.demo2 .btn{width:200px;height:45px;line-height:45px;margin-top:30px}.banner h2{font-size:26px}.banner p{letter-spacing:5px}.sdk ul{margin-right:0}.sdk li{width:29%;box-sizing:border-box;margin:0 2%;margin-bottom:45px;text-align:center;height:145px}.sdk .txt{padding-left:0;display:inline-block;font-size:18px}.sdk .img{position:relative;top:0;right:0;display:inline-block;vertical-align:middle;margin-left:10px;width:70px}.sdk .btn{margin-left:-50px}.sdk .btn a{width:100px;height:45px;line-height:45px;font-size:18px}.guide-nv{height:auto;line-height:normal;overflow:hidden;border-width:1px}.guide-nv a{width:auto;padding:0 30px;font-size:18px;height:40px;line-height:40px}.login-hd p{padding:20px 0 30px}.gtpwd-img img{width:80px}.gtpwd-txt h2{padding-top:25px;padding-bottom:40px;font-size:20px}.gtpwd-txt p{padding-bottom:50px;font-size:16px}}@media only screen and (max-width:768px){.hview.even .img,.hview.odd .img,.pro-pay-l{margin-right:0;width:auto;float:none;text-align:center;margin-left:0}.hview.even .img img,.hview.odd .img img,.pro-pay-l img{width:80%}.hview-txt,.pro-pay-r{float:none;width:auto;margin-top:25px}.pro-pay-r{margin-left:3%;margin-right:3%}.faq-srch{width:100%}.faq-srch .txt{width:100%}.faq-nav{margin-top:60px}.faq-nav li{width:33.33%;padding:10px 0}.faq-list dl{margin-bottom:20px;float:none;width:auto}.probanner-r img{width:200px}.pro-pay-r li{width:25%}.pro-pay-r li span{font-size:12px}.pro-sys-l li a{font-size:12px}.pro-sys-l li i{font-size:24px}.pro-df-r,.odd .pro-qr-r,.pro-df-l,.pro-qr-l{width:auto;float:none}.pro-df-r,.odd .pro-qr-r{width:80%;margin:0 auto}.pro-qr-l{margin-top:10px}.login2{padding:40px 0}.login-wp{padding:30px;margin:0 3%;display:block}.login-bd{height:auto;overflow:hidden;margin-top:10px}.login2 .txt,.login1 .txt{width:100%;box-sizing:border-box;height:45px;line-height:45px;margin-top:5px;font-size:14px;-webkit-appearance:none;border-radius:0;appearance:none}.login2 .txt-gt{margin-bottom:60px}.gtpwd{height:65px;line-height:65px}.gtpwd-l>label{height:75px}.login-bd li{position:relative}.login-bd img{position:absolute;right:0;margin-top:10px;height:45px}.login-bd p{padding:20px 0 0}.login-btn .btn{width:90%;height:40px;line-height:40px;margin-bottom:30px;box-shadow:0 9px 10px 0 rgba(41,59,236,0.3)}.contact-bd .txt{display:block;float:none}.contact-bd .img{margin-left:0;margin-top:30px}.contact-bd .img img{width:200px}.channel-bd .img,.traffic-bd .img,.channel-bd .txt,.traffic-bd .txt{float:none;width:auto;margin:0}.channel-bd .img,.traffic-bd .img{width:80%;margin:30px auto 0}.g-bd .btn{width:200px;display:block;margin-left:auto;margin-right:auto}.docs-nav,.guide-l{width:auto;float:none;margin-bottom:0}.docs-nav li,.guide-l li{display:block;width:33%;float:left;margin-bottom:0}.docs-nav li a,.guide-l li a{font-size:12px}.docs-main,.guide-r{float:none;width:auto}.guide-rhd{padding-top:15px}.sdk li{width:46%;height:135px}.sdk .txt{display:block;padding-top:20px}.sdk .img{margin-left:0;margin-top:10px;text-align:center}.guide-nv a{font-size:16px}.pro-df-l{margin-bottom:30px}.banner{height:255px}.sdk .btn{bottom:-18px}.sdk .btn a{height:40px;line-height:40px;font-size:16px}.pro-sys-r .img img{width:80%}.pro-sys-r .txt{float:none;width:auto;margin-top:0}.pro-sys-r .txt p br{display:none}.pro-sys-r .btn{text-align:center}.pro-df-l .btn,.pro-qr-l .btn{display:block}.line-27,.pro-sys-r .txt h2{font-size:16px}.line-27:before{vertical-align:-5px}.hpro-bd2 ul{display:block;height:auto}.hpro-bd2 li{width:100%}.news_detail{width:100%}}.docs-bd div.pp{margin-bottom:8px}.docs-bd th{border:#dcdcdc solid 1px;padding:5px 10px;font-weight:bold}.docs-bd table{border-collapse:collapse;width:100%}.docs-bd table tr td{border:#dcdcdc solid 1px;padding:5px 10px}
  </style> 
 </head>
 <body> 
  <div class="wrapper"> 
   <div class="docs"> 
STARTHTML;

    return $str;

    }

    /**
     * @var   获取表格头部数据
     * @param [type] $title [description]
     */
    public  function ExeTableTitle() {
        $html           =   "";
        $html       .= '<thead><tr>';
        $html       .= '<th style="width:25%">参数名称</th> ';
        // $html       .= '<th style="text-align: left;width:15%">参数含义</th> ';
        $html       .= '<th style="text-align: left;width:20%">字段类型</th> ';
        $html       .= '<th style="text-align: left;width:10%">是否必填</th> ';
        $html       .= '<th style="text-align: left;width:45%">参数说明</th> ';
        $html       .= '</tr> </thead>';
        return $html;

    }
    // 数组组合
    public function ExeTableData($data,$type) {
        $html       =   "<tbody>";
        foreach ($data as $key => $value) {
            if (!array_key_exists("param",$value)) {
                $value['param']     =   "无";
            }
            if (!array_key_exists("describe",$value)) {
                $value['describe']     =   "无";
            }
            if (!array_key_exists("type",$value)) {
                $value['type']     =   "无";
            }
            if (!array_key_exists("status",$value)) {
                $value['status']     =   "无";
            }
            if (!array_key_exists("remarks",$value)) {
                $value['remarks']     =   "无";
            }
            $html   .=  "<tr><td>".$value['param']."</td> ";
            // $html   .=  '<td style="text-align: left;">'.$value['describe'].'</td> ';
            $html   .=  '<td style="text-align: left;">'.$value['type'].'</td> ';
            $html   .=  '<td style="text-align: left;">'.$value['status'].'</td> ';
            $html   .=  '<td style="text-align: left;">'.$value['describe'].'</td> ';
            $html   .=  '</tr> ';
        }
        $html       .=  '</tbody></table>';
        return $html;
    }


    // 判断用户习惯 | 或者空格
    public function ExeXg($v) {
        $v                  =   trim($v);
        if (strstr($v, '|')) {
            $arr           =   explode("|",($v));
        } else {
            $arr           =   explode(" ",($v));
            // 判断是否大于2  大于2 的话把后面所有全部赋值给1
            if (count($arr) > 2) {
                $myArr     =    array();
                foreach ($arr as $key => $value) {
                    if ($key == 0) {
                        $myArr["0"]    =   $value;
                    } else {
                        $myArr["1"]    .=   $value;
                    }
                }

                $arr        =   $myArr;
            }
        }

        return $arr;
    }
    // 过滤 [] 字符
    public function exeLr($param) {
        return str_replace("]","",str_replace("[","",$param));
    }



    /**
     * @var    错误信息
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public function error($text = "") {
        if (!$text) {
            $text       =   $this->msg;
        }
        return (new Error("自动生成文档","10",$text));
    }
}