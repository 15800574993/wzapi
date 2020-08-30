<?php
namespace wangzhan;
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone','Asia/Shanghai');
use wangzhan\ip\IpLocation;
use wangzhan\other\Log;
/**
*   @author  王佩双 <250570889@qq.com>
*   @version 1.0
*   @var     公共函数库
*/
//+-------------------------------------------------------
//|     get_nonce_str               获取随机字符串
//+-------------------------------------------------------
//|     is_json                     判断字符串是否json格式
//+-------------------------------------------------------
//|     get_ip                      获取客户端真实IP
//+-------------------------------------------------------
//|     exe_ip_location             ip转地址接口
//+-------------------------------------------------------
//|     get_method                  获取当前网络请求方式
//+-------------------------------------------------------
//|     get_server                  获取指定服务器server所有参数
//+-------------------------------------------------------
//|     get_server_param            获取指定server参数 
//+-------------------------------------------------------
//|     get_http_type               获取当前网络协议
//+-------------------------------------------------------
//|     get_http                    获取当前服务器的域名地址 
//+-------------------------------------------------------
//|     get_post_param              获取get或者post数据
//+-------------------------------------------------------
//|     get_param                   获取请求参数返回json
//+-------------------------------------------------------
//|     get_post_input              过滤提交信息，防止被攻击
//+--------------------------------------------------------
//|     type_to_type                类型转换
//+--------------------------------------------------------
//|     get_post                    获取单个get或者post数据
//+--------------------------------------------------------
//|     get_post_header             优先检测请求头数据
//+--------------------------------------------------------
//|     arr_to_xml                  数组转xml
//+--------------------------------------------------------
//|     xml_to_arr                  xml转数组
//+--------------------------------------------------------
//|     return_json                 统一返回方法
//+--------------------------------------------------------
//|     check_param                 判断数据是否为空直接走return_json方法
//+--------------------------------------------------------
//|     get_tid                     生成唯一不重复订单号
//+--------------------------------------------------------
//|     get_uuid                    获取全球唯一标识【作为用户UUID】
//+--------------------------------------------------------
//|     get_rand_char               随机生成汉子
//+--------------------------------------------------------
//|     get_phone                   验证手机号是否正确 true/false
//+--------------------------------------------------------
//|     get_limit                   获取分页数据参数并组合
//+--------------------------------------------------------
//|     get_page                    返回分页数据
//+--------------------------------------------------------
//|     array_sort                  数组重新排序 
//+--------------------------------------------------------
//|     arr_key                     处理数组中指定字段
//+--------------------------------------------------------
//|     add_log                     写入日志
//+--------------------------------------------------------
//|     arr_query                   数组转get参数
//+--------------------------------------------------------
//|     line_tohump                 下划线转驼峰
//+--------------------------------------------------------
//|     tohump_line                 驼峰转下划线
//+---------------------------------------------------------
//|     mkdirs                      判断目录是否存在不存在添加
//+---------------------------------------------------------
//|     msectime                    返回当前毫秒时间戳
//+---------------------------------------------------------
//|     JSON_JSON                   将数组转json并且保留中文
//
class Func {
    /**
     * @var  存放请求的数据
     * @var [type]
     */
    private static $getPostParam;

    // 配置
    public static $config;
    // 请求地址
    public function __construct($config = array()){
        self::$config       =   $config;
    }
    // 更新当前配置
    public static function update_config($config) {
        self::$config       =   $config;
    } 

	/**
     * @var    随机生成字符串
     * @author 王佩双 <250570889@qq.com>
     * @param  integer $length [字符串长度]
     * @return string
     */
    public static function get_nonce_str($length = 16){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $nonceStr = "";
        for ($i = 0; $i < $length; $i++) {
            $nonceStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $nonceStr;
    }

  
    /**
     * @var    判断字符串是否为 Json 格式
     * @author 王佩双 <250570889@qq.com>
     * @param  integer $str     [字符串]
     * @return true/false
     */
    public static function is_json($str = '') {
        if (json_decode($str,true)) {
            return true;
        }
        return false;
    }

    /**
     * @var    获取客户端真实IP
     * @author 王佩双 <250570889@qq.com>
     * @return string
     */
    public static function get_ip(){  
        $ip             =   false;
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ips=explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if($ip){
                array_unshift($ips, $ip);
                $ip=FALSE;
            }
            for ($i=0; $i < count($ips); $i++){
                if(!eregi ('^(10│172.16│192.168).', $ips[$i])){
                    $ip=$ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    } 
    /**
     * @var    执行ip转地址/第三方类库转换
     * @author 王佩双 <250570889@qq.com>
     * @return array
     */
    public static function exe_ip_location($ip = "") {
        if(empty($ip)) $ip = self::get_ip();
        return (new IpLocation("qqwry.dat"))->getlocation($ip);
    }
   
    /**
     * @var    获取当前请求方式
     * @author 王佩双 <250570889@qq.com>
     * @return GET/POST
     */
    public static function get_method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @var    获取服务参数
     * @author 王佩双 <250570889@qq.com>
     * @return GET/POST
     */
    public static function get_server() {
        return $_SERVER;
    }
    /**
     * @var    获取指定服务器参数
     * @param  [type] $key   [键名]
     * @param  [type] $value [不存在的情况下默认值]
     * @return string
     */
    public static function get_server_param($key,$value = null) {
        $param          =   self::get_server();
        if (array_key_exists($key,$param)) {
            return $param[$key];
        }
        return $value;
    }
   /**
     * @var    获取当前是http还是https
     * @param  [type] $key   [键名]
     * @param  [type] $value [不存在的情况下默认值]
     * @return string
     */
    public static function get_http_type() {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type;
    }

    /**
     * @var    获取当前服务器的域名地址
     * @param  [type] $key   [键名]
     * @param  [type] $value [不存在的情况下默认值]
     * @return string
     */
    public static function get_http() {
        return self::get_http_type().$_SERVER['SERVER_NAME'];
    }
    /**
     * @var    获取客户端发送的数据
     * @return string
     */
    public static function get_post_param() {
        $param              =   array();
        $method             =   self::get_method();
        if ($method == "GET") {
            $param          =   $_GET;
        } else if ($method == "POST"){
            $param          =   array_merge($_GET,$_POST);
        } 
        $input          =   file_get_contents('php://input');
        $input          =   $input?json_decode($input,true):array();
        $param          =   array_merge($param,$input);
        return $param;
    }
    /**
     * @var    获取请求的参数返回json字符串
     * @return json
     */
    public static function get_param() {
        $param              =   self::get_post_param();
        return json_encode($param);
    }


    /**
     * @var    优先检测请求头数据
     * @param  [type] $name    [参数名称]
     * @param  [type] $header  [头部参数名称]
     * @param  [type] $default [默认值]
     * @return string
     */
    public static function get_post_header($name,$header,$default = null) {
      return array_key_exists($header,$_SERVER)?$_SERVER[$header]:self::get_post($name,$default);
    }

    /**
     * @var    过滤提交信息，防止被攻击
     * @param  [type] $str  [需要过滤的字符串]
     * @return string
     */
    public static function get_post_input($str) {
        $str    =   trim($str);
        $str    =   stripslashes($str);
        $str    =   htmlspecialchars($str);
        return $str;
    }
    /**
     * @var    类型转换
     * @param  [type] $str  [转换的字符串数据]
     * @param  string $type [转换的类型  [d/整型] [s/字符串] [s/浮点型] [ja/json字符串转数组]]
     * @return [type]       [description]
     */
    public static function type_to_type($str,$type = "") {
        switch ($type){
            case "d":
                $str        =   (int)$str;
              break;
            case "s":
                $str        =   (string)$str;
              break; 
            case "f":
                $str        =   (float)$str;
            case "ja":
                if (self::is_json($str)) {
                    $str    =   json_decode($str,true);
                } else {
                    $str    =   htmlspecialchars_decode($str);
                    $str    =   (self::is_json($str))?json_decode($str,true):array(); 
                }
              break;  
        }
        return $str;
    }

    /**
     * @var    获取参数信息
     * @param  [type] $key   [需要获取的键名]
     * @param  string $value [需要获取的值]
     * @return string
     */
    public static function get_post($key,$value = null) {
        if (!$param = self::$getPostParam) {
            $param              =   self::get_post_param();
            self::$getPostParam =   $param;
        }
        // 判断键是否有/ 
        $arr                    =   explode("/",$key); 
        $type                   =   "";
        if (count($arr) == 2) {
            $key                =   $arr['0'];
            $type               =   $arr['1'];
        }

        if (array_key_exists($key,$param)) {
            return self::type_to_type(self::get_post_input($param[$key]),$type);
        }
        return $value;
    }

    /**
     * @var 判断数组中是否包含某个值 如果不包含返回默认值
     * @param  [type]  $arr [数组]
     * @param  [type]  $key [键名]
     * @param  [type]  $val [默认值]
     * @return boolean      [description]
     */
    public static function is_param($arr,$key,$val = 0) {
        if (array_key_exists($key,$arr)) {
            return $arr[$key];
        }
        return $val;
    }

    /**
     * @var    数组转xml
     * @param  [type] $arr   [数组数据]
     * @return xml
     */
    public static function arr_to_xml($arr) {
        if(!is_array($arr) || count($arr) == 0) return '';
        $xml = self::arr_xml($arr,"<xml>")."</xml>";
        return $xml;
    }
    public static function arr_xml($arr,$xml = "") {
        foreach ($arr as $key=>$val) {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else if(is_array($val)){
                $xml .= (self::arr_xml($val,""));
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }

        return $xml;
    }
    /**
     * @var    xml转数组
     * @param  [type] $xml   [xml数据]
     * @return xml
     */
    public static function xml_to_arr($xml) {
        if($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    /**
    * @author     王佩双 <250570889@qq.com>
    * @version      1.0
    * @param        status      状态
    * @param        msg         提示数据
    * @param        results     对象
    * @param        data        数组数据
    * @param        info        分页
    * @param        type        返回的类型 1 json返回【客户端使用】  2 数组返回  3 返回xml【客户端使用】
    * @param        log_type    是否记录日志  
    * @param        user_id     记录日志的用户id
    * @return     返回json数据
    */
    public static function return_json($status = 0,$msg="",$results=array(),$data = array(),$info = "",$type = 1,$log_type = 0,$user_id = 0){ 
        if (!is_array($data) && !is_object($data)) {
            $data = array();
        }
        $results['data']    = $data;
        $array = array();
        $array['status']    = trim((string)$status);
        $array['msg']       = $msg;
        $array['results']   = $results;
        // 判断是否有分页数据
        if (is_array($info)) {
            $array['info']  = $info;
        }
        // 判断是否记录日志
        if ($log_type) {
            self::add_log($user_id,json_encode($array));
        } else {
            // 查询是否写入日志
            $config         =   self::$config;
            if ($config['return_json_status']) {
              self::add_log("",json_encode($array));
            }
        }
        if ($type == 2) {
            return $array;
        }
        if ($type == 3) {
            return self::arr_to_xml($array);
        }
        // 判断方法是否存在
        if(function_exists('halt')){
            echo (json_encode($array));halt();
        }else{
            exit(json_encode($array));
        }
        
    }


    /**
    * @var        
    * @author     王佩双 <250570889@qq.com>
    * @param        判断数据是否为空
    * @param        $name       参数值
    * @param        $default    默认值
    * @param        $error      不存在或者为空返回的数据信息
    * @param        $status     状态码
    * @return       返回获取到的值 
    */
    public static function check_param($name,$default = null,$error = "",$status = 0){
        if (!$param   = self::get_post($name,$default)) {
            self::return_json($status,$error);
        }
      return $param;
    }


    /**
    * @author  王佩双 <250570889@qq.com>
    * @var     生成唯一不重复订单号
    * @var     总数40  前面1-14   代表 当前年月日时分秒， 15-22 代表用户id   后面六位随机字符串
    * @param   $[user_id]           [标识id整型]
    * @param   $[prefix]            [前缀]
    */
    public static function get_tid($user_id = 0,$prefix = 0) {
      $user_id      =   sprintf('%010d',$user_id);
      $order_no     =   date('YmdHis').sprintf('%08d',$user_id).sprintf('%04d', rand(0, 999999));
      // 判断是否增加前缀
      if ($prefix) {
          $order_no =   $prefix.$order_no;
      }
      return $order_no;
    }

    /**
    * @author   王佩双 <250570889@qq.com>
    * @var      获取全球唯一标识【作为用户UUID】 一些特殊接口使用
    */
    public static function get_uuid(){
      return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff), 
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff), 
            mt_rand(0, 0xffff)
        );
    }

    /**
    * @author  王佩双 <250570889@qq.com>
    * @var     随机生成汉字
    * @var     num  汉字个数
    */
    public static function  get_rand_char($num = 3) {
        $b = '';
        for ($i=0; $i<$num; $i++) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(mt_rand(0xB0,0xD0)).chr(mt_rand(0xA1, 0xF0));
            // 转码
            $b .= iconv('GB2312', 'UTF-8', $a);
        }
        return $b;
    }

    /**
    *   @author     王佩双 <250570889@qq.com>
    *   @var 判断当前手机号是否是真的
    *   @param  $[phone]    [<手机号>]
    **/
    public static function get_phone($phone = 1) {
        $pattern = "/^(0|86|17951)?(19[0-9]|13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[0-9]|16[0-9])[0-9]{8}$/";
        if (!preg_match($pattern,$phone)) {
            return false;
        }
        return true;
    }
    /**
    * @author     王佩双 <250570889@qq.com>
    * @var 获取并组合好 分页数据 
    */
    public static function get_limit($type = 0) {
        $count              =  self::get_post(self::$config['count_name'].'/d',self::$config['page_count']);
        $page               =  self::get_post(self::$config['page_name'].'/d',1);
        $limit              = (intval($page)<=1) ? '0,'.$count : $count*($page-1).','.$count ;
        if ($type) {
            return explode(",",$limit);
        }
        return $limit;
    }
    /**
    *   @author     王佩双 <250570889@qq.com>
    *   @param      $[total]    [数据总数]
    *   @param      $[data]     [当前显示数]
    *   @var 获取并组合好 返回的分页数据 
    */
    public static function get_page($total,$data) {
        $count                =  self::get_post(self::$config['count_name'].'/d',self::$config['page_count']);
        $page                 =  self::get_post(self::$config['page_name'].'/d',1);
        $info['page']         = $page;
        $info['total_pages']  = ceil($total/$count);
        $info['total_count']  = $total;
        $info['total_results']= count($data);
        return $info;
    }

   /**
    * @var     数组重新排序
    * @param      $[arr]        [需要排序的数组]
    * @param      $[row]        [排序字段]
    * @param      $[type]       [1升序/0降序]
    * @var 获取并组合好 返回的分页数据 
    */
    public static function array_sort($arr,$row,$type = 0){
        if (!$arr) {
            return $arr;
        }
        $sort           =   array(  
            'direction' =>  ($type)?"SORT_ASC":"SORT_DESC",         //排序顺序标志 SORT_DESC 降序； SORT_ASC  升序  
            'field'     =>  $row,                                   //排序字段  
        );  
        $arrSort        =   array();  
        foreach($arr as $uniqid => $row){  
            foreach($row as $key=>$value){  
                $arrSort[$key][$uniqid] = $value;   
            }  
        }  
        if($sort['direction']){  
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);  
        }  
        return $arr;
    }


    /**
     * @var    处理数组中指定字段
     * @param  [type] $data  [完整的数组]
     * @param  [type] $field [查询的字段]
     * @return [type]        [description]
     */
    public static function arr_key($data,$field,$val  = null) {
      $ret    = array();
      foreach ($field as $key => $value) {
        $ret[$value]      =   array_key_exists($value,$data)?$data[$value]:$val;
      }
      return $ret;
    }
    /**
     * @var   写入日志类
     * @param [type] $fileName  [description]
     * @param [type] $user_name [description]
     * @param [type] $content   [description]
     */
    public static function add_log($user_name="", $content="") {
        if (!$user_name) {
            $user_name      =   self::$config['log_user_id'];
        }
        return (new Log(self::$config))->logWrite(date("H").".log",$user_name,$content);
    }

    /**
     * @var    数组转get参数
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public static function arr_query($arr) {
      return http_build_query($arr);
    }

    /**
     * @var    下划线转驼峰 字符串转化函数
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public static function line_tohump($str){
      $str = trim($str,'_');//去除前后下划线_
      $len = strlen($str);
      $out = strtoupper($str[0]);
      for ($i=1; $i<$len; $i++) {
          if(ord($str[$i]) == ord('_')){//如果当前是下划线，去除，并且下一位大写
              $out .= isset($str[$i+1])?strtoupper($str[$i+1]):'';
              $i++;
          }else{
              $out .= $str[$i];
          }
      }
      return $out;
    }
    /**
     * @var    驼峰转下划线 字符串函数
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public static function tohump_line($str){
      $len = strlen($str);
      $out = strtolower($str[0]);
      for ($i=1; $i<$len; $i++) {
          if(ord($str[$i]) >= ord('A') && (ord($str[$i]) <= ord('Z'))) {
              $out .= '_'.strtolower($str[$i]);
          }else{
              $out .= $str[$i];
          }
      }
      return $out;
    }

    /**
     * @var    判断目录是否存在不存在添加
     * @param  [type]  $dir  [description]
     * @param  integer $mode [description]
     * @return [type]        [description]
     */
    public static function mkdirs($dir, $mode = 0777){
      if(!is_dir($dir)){mkdir($dir, $mode, true);}
    }
    /**
     * @var    返回当前毫秒时间戳
     * @param  [type]  $dir  [description]
     * @param  integer $mode [description]
     * @return [type]        [description]
     */
    public static function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }




    /**
     * @var 将数组转json  并且保留中文
     * @param [type] $array [description]
     */
    public static function JSON_JSON($array) {
      self::arrayRecursive($array, 'urlencode', true);
      $json = json_encode($array);
      return urldecode($json);
    }
    /**
    *   @var 将数组转json  并且保留中文
    **/
    public static function arrayRecursive($array, $function, $apply_to_keys_also = false){
      static $recursive_counter = 0;
      if (++$recursive_counter > 2000) {
          die('possible deep recursion attack');
      }
      foreach ($array as $key => $value) {
          if (is_array($value)) {
              self::arrayRecursive($array[$key], $function, $apply_to_keys_also);
          } else {
              $array[$key] = $function($value);
          }

          if ($apply_to_keys_also && is_string($key)) {
              $new_key = $function($key);
              if ($new_key != $key) {
                  $array[$new_key] = $array[$key];
                  unset($array[$key]);
              }
          }
      }
      $recursive_counter--;
    }
 

 
 





}