<?php
namespace wangzhan;
/**
 * @var   网络请求类
 */
class Http {
    /**
     * @var    静态方法动态调用
     * @param  string  $type           [类型 post/get]
     * @param  [type]  $url            [description]
     * @param  array   $data           [description]
     * @param  [type]  $http_opts      [description]
     * @param  boolean $is_transcoding [description]
     * @return [type]                  [description]
     */
    public function get_post_curl($type = "post",$url, $data = array(), $http_opts = null,$is_transcoding = true) {
        return ($type == "post")?self::post_curl($url,$data,$http_opts):get_curl($url,$data,$http_opts,$is_transcoding);
    }
    /**
     * @var    post 请求
     * @param  [type] $url       [请求地址]
     * @param  [type] $data      [请求参数]
     * @param  [type] $http_opts [curl_setopt()参数]
     * @return [type]            [description]
     */
    public static function post_curl($url, $data, $http_opts = null ) {
        $curl_handler                   =               curl_init(); //初始化一个curl对象
        $options                        =               array(
            // PHP取回的URL地址。可以在用curl_init()函数初始化时设置这个选项
            CURLOPT_URL                 =>              $url,
            // 在启用CURLOPT_RETURNTRANSFER时候将获取数据返回
            CURLOPT_RETURNTRANSFER      =>              1,
            //最大延迟秒数
            CURLOPT_CONNECTTIMEOUT      =>              15,
            // 把一个头包含在输出中，设置这个选项为一个非零值。
            CURLOPT_HEADER	            =>              false,
            // 在HTTP请求中包含一个”user-agent”头的字符串
            CURLOPT_USERAGENT           =>              self::userAgent(),
            // 如果你想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值。这个POST是普通的 application/x-www-from-urlencoded 类型，多数被HTML表单使用。
            CURLOPT_POST                =>              TRUE,
            // 传递一个作为HTTP “POST”操作的所有数据的字符串。
            CURLOPT_POSTFIELDS          =>              $data
        );
        if (is_array($http_opts)) {
            // 接口参数
            foreach ($http_opts as $key => $value) {
                $options[$key] = $value;
            }
        }
 
        curl_setopt_array($curl_handler, $options);
        // 运行cURL，请求网页获取URL站点内容 
        $curl_result                    =               curl_exec($curl_handler); 
        //获取最后一次收到的HTTP状态码
        $curl_http_status               =               curl_getinfo($curl_handler,CURLINFO_HTTP_CODE); 
        $curl_http_info                 =               curl_getinfo($curl_handler);
        if ($curl_result == false) {
            $error                      =               curl_error($curl_handler);
            curl_close($curl_handler);
            return array('status' => $curl_http_status, 'message' => $error,'http_info' => $curl_http_info);
        }
        // 关闭一个curl会话
        curl_close($curl_handler);  
        //自动获取字符串编码函数
        $encode                         =               mb_detect_encoding($curl_result, array('ASCII', 'UTF-8','GB2312', 'GBK', 'BIG5')); //进行编码识别
        // if ($encode != 'UTF-8') {
        //     $curl_result = iconv($encode, 'UTF-8', $curl_result);
        // }
        $result                         =               json_decode($curl_result, true);
        if (is_null($result)) {
            $result                     =               $curl_result;
        }
        return array('status' => $curl_http_status, 'message' => 'ok', 'data' => $result,'http_info' => $curl_http_info);
    }
    /**
     * @var    get    请求
     * @param  [type] $url       [请求地址]
     * @param  [type] $data      [请求参数]
     * @param  [type] $http_opts [curl_setopt()参数]
     * @return [type]            [description]
     */
    public static function get_curl($url,$data = array(),$http_opts = null, $is_transcoding = true ) {
        $curl_handler                   =               curl_init();
        $options = array(
            CURLOPT_URL                 =>              $url,  // 请求地址
            CURLOPT_RETURNTRANSFER      =>              1,
            CURLOPT_CONNECTTIMEOUT      =>              15,
            CURLOPT_HEADER			    =>              false,
            CURLOPT_USERAGENT           =>              self::userAgent(),
            CURLOPT_POSTFIELDS          =>              $data
        );
        if (is_array($http_opts)) {
            foreach ($http_opts as $key => $value){
                $options[$key] = $value;
            }
        }
        
        curl_setopt_array($curl_handler, $options);
        $curl_result                    =               curl_exec($curl_handler);
        $curl_http_status               =               curl_getinfo($curl_handler,CURLINFO_HTTP_CODE);
        $curl_http_info                 =               curl_getinfo($curl_handler);
        if ($curl_result === false) {
            $error = curl_error($curl_handler);
            curl_close($curl_handler);
            return array('status' => $curl_http_status, 'message' => $error,'http_info' => $curl_http_info);
        }
        if ($is_transcoding) {
            $encode = mb_detect_encoding($curl_result, array('ASCII', 'UTF-8','GB2312', 'GBK', 'BIG5'));
            // if ($encode != 'UTF-8') {
            //     $curl_result = iconv($encode, 'UTF-8', $curl_result);
            // }
        }
 
        $result = json_decode($curl_result,true);
        if (is_null($result) || empty($result)) {
            $result = $curl_result;
        }
        curl_close($curl_handler);
        return array('status' => $curl_http_status, 'data' => $result,'http_info' => $curl_http_info);
    }

    //
    public static function request_check($url, $data = null, $headers,$is_gbk=false) {  
        $curl = curl_init();  
        curl_setopt($curl, CURLOPT_URL, $url);  
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);  
        if (! empty($data)) {  
            curl_setopt($curl, CURLOPT_POST, 1);  
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
        }  
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);  
        $output = curl_exec($curl);  
        curl_close($curl);  
        if($is_gbk){
            $output = mb_convert_encoding($output, "UTF-8", "GBK");
        }
        return json_decode($output, true);  
    }

    /**
     * @var    请求头用户代理信息
     * @return [type] [description]
     */
    public  static function userAgent() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return  'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.114 Safari/537.36';
    }
 
}
