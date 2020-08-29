<?php
namespace wangzhan\pay\Wx;
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone','Asia/Shanghai');
/**
*   @author  王佩双 <250570889@qq.com> 11
*   @version 1.0
*   @var     微信商户支付整合类
*/
class Weixinpay {
    // 定义配置项
    private $appid;
    private $appsecret;
    private $mch_id;
    private $key;
    private $notify_url;

    /**
    *   @var 构造函数设置初始值
    **/
    public function __construct($appid,$appsecret,$mch_id,$key,$notify_url){
        $this->appid=$appid;
        $this->appsecret=$appsecret;
        $this->mch_id=$mch_id;
        $this->key=$key;
        $this->notify_url=$notify_url;
    }

    /**
     * 统一下单
     * @param  $order 订单 必须包含支付所需要的参数 
     * @var    body(产品描述)
     * @var    total_fee(订单金额)
     * @var    out_trade_no(订单号)
     * @var    product_id(产品id)
     * @var    trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function unifiedOrder($order){

        // 获取配置项
        $config=array(
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'nonce_str'=>'test',
            'spbill_create_ip'=>'192.168.0.1',
            'notify_url'=>$this->notify_url
        );

        // 合并配置数据和订单数据
        $data=array_merge($order,$config);
        // 生成签名
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            die(json_encode($result));
        }
        $result['sign']=$sign;
        $result['nonce_str']='test';
        return $result;
    }


    /**
     * 查询订单
     * @param  $out_trade_no  传入的订单id
     */
    public function orderquery($out_trade_no){
        // 获取配置项
        $config=array(
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'out_trade_no'=>$out_trade_no,
            'nonce_str'=>'test',
            );

        // 合并配置数据和订单数据
        $data=array_merge($config);
        // 生成签名
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/orderquery';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            die(json_encode($result));
        }
        $result['sign']=$sign;
        $result['nonce_str']='test';
        return $result;
    }


    /**
     * 验证
     * @return array 返回数组格式的notify数据
     */
    public function notify(){
        // 获取xml
        $xml=file_get_contents('php://input', 'r'); 
        // 转成php数组
        $data=$this->toArray($xml);
        // 保存原sign
        $data_sign=$data['sign'];
        // sign不参与签名
        unset($data['sign']);
        $sign=$this->makeSign($data);
        // 判断签名是否正确  判断支付状态
        if ($sign===$data_sign && $data['return_code']=='SUCCESS' && $data['result_code']=='SUCCESS') {
            $result=$data;
        }else{
            $result=false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function toXml($data){
        if(!is_array($data) || count($data) <= 0){
            throw new WxPayException("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($data as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function makeSign($data){
        // 去空
        $data=array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a=http_build_query($data);
        $string_a=urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp=$string_a."&key=".$this->key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result=strtoupper($sign);
        return $result;
    }

    /**
     * 将xml转为array
     * @param  string $xml xml字符串
     * @return array       转换得到的数组
     */
    public function toArray($xml){   
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $result;
    }

    /**
     * 生成授权url
     * @return string url地址
     */
    public function makeUrl($out_trade_no,$redirect_uri){
        $redirect_uri=urlencode($redirect_uri);
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state='.$out_trade_no.'#wechat_redirect';
        return $url;
    }


    /**
     * 获取jssdk需要用到的数据
     * @return array jssdk需要用到的数据
     */
    public function getParameters($code,$body,$total_fee,$out_trade_no,$product_id,$openid = ""){
        /**
        * @var 判断是否传入openid 如果没有从新获取
        ***/
        if (!$openid) {
          // code 组合获取prepay_id的url
          $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code';
          $result=file_get_contents($url);    // curl获取prepay_id
          $result=json_decode($result,true);  // $result=curl_get_contents($url);
          $openid=$result['openid'];
        }
        
        // 订单数据
        $order=array(
            'body'        =>$body,        // 商品描述（需要根据自己的业务修改）
            'total_fee'   =>$total_fee,   // 订单金额  以(分)为单位（需要根据自己的业务修改）
            'out_trade_no'=>$out_trade_no,// 订单号（需要根据自己的业务修改）
            'product_id'  =>$product_id,  // 商品id（需要根据自己的业务修改）
            'trade_type'  =>'JSAPI',      // JSAPI公众号支付
            'openid'      =>$openid       // 获取到的openid
        );

        // 统一下单 获取prepay_id
        $unified_order=$this->unifiedOrder($order);
        // 获取当前时间戳
        $time=time();
        // 组合jssdk需要用到的数据
        $data=array(
            'appId'     =>$this->appid,                             //  appid
            'timeStamp' =>strval($time),                            //  时间戳
            'nonceStr'  =>$unified_order['nonce_str'],              //  随机字符串
            'package'   =>'prepay_id='.$unified_order['prepay_id'], //  预支付交易会话标识
            'signType'  =>'MD5'                                     //  加密方式
        );
        // 生成签名
        $data['paySign']=$this->makeSign($data);
        return $data;
    }

    /**
     * 生成支付二维码
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function pay($order){
        $result=$this->unifiedOrder($order);
        $decodeurl=urldecode($result['code_url']);
        // qrcode($decodeurl);  这里需要生成二维码  待处理 2018-06-26
    }
//================================微信退款 2018-07-26   漾程式写的微信退款===============================
    /**
    *   @var order_id      退款订单号
    *   @var pays          订单总金额
    *   @var pay           需要退款的金额  
    **/
    public function WeixinRefund($order_id = 0,$pays = 1,$pay = 1) {
        date_default_timezone_set("Asia/Shanghai");

        $appid      = $this->appid;
        $mch_id     = $this->mch_id;
        $text       = "text";
        $order_i    = $order_id;
        $order_id   = $order_id;
        $op_user_id = $this->mch_id;
        $key        = $this->key;
        $total_fee  = (int)$pays;
        $refund_fee = (int)$pay;

        $ref= strtoupper(md5("appid=".$appid."&mch_id=".$mch_id."&nonce_str=".$text."&op_user_id=".$op_user_id
              . "&out_refund_no=".$order_i."&out_trade_no=".$order_id."&refund_fee=".$refund_fee."&total_fee=".$total_fee
              . "&key=".$key));               //sign加密MD5
       $refund=array(
          'appid'         =>$appid,       //应用ID，固定
          'mch_id'        =>$mch_id,      //商户号，固定
          'nonce_str'     =>$text,        //随机字符串
          'op_user_id'    =>$op_user_id,  //操作员
          'out_refund_no' =>$order_i,     //商户内部唯一退款单号
          'out_trade_no'  =>$order_id,    //商户订单号,pay_sn码 1.1二选一,微信生成的订单号，在支付通知中有返回
          'refund_fee'    =>$refund_fee,  //退款金额
          'total_fee'     =>$total_fee,   //总金额
          'sign'          =>$ref          //签名
       );
       $url="https://api.mch.weixin.qq.com/secapi/pay/refund";//微信退款地址，post请求
       $xml=$this->arrayToXml($refund);
       $ch=curl_init();
       curl_setopt($ch,CURLOPT_URL,$url);
       curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
       curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);//证书检查
       curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
       curl_setopt($ch,CURLOPT_SSLCERT,dirname(__FILE__).'/cert/apiclient_cert.pem');
       curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
       curl_setopt($ch,CURLOPT_SSLKEY,dirname(__FILE__).'/cert/apiclient_key.pem');
       curl_setopt($ch,CURLOPT_POST,1);
       curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);

       $data=curl_exec($ch);
       if($data){                         //返回来的是xml格式需要转换成数组再提取值，用来做更新
          curl_close($ch);
          return $data;
       }else{
          $error=curl_errno($ch);
          return $error;
          curl_close($ch);
       }
         
    }

    public function arrayToXml($arr){
       $xml = "<root>";
       foreach ($arr as $key=>$val){
          if(is_array($val)){
             $xml.="<".$key.">".arrayToXml($val)."</".$key.">";
          }else{
             $xml.="<".$key.">".$val."</".$key.">";
          }
       }
       $xml.="</root>";
       return $xml ;
    }





}
