<?php
namespace wxpay;
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('date.timezone','Asia/Shanghai');
/**
 *  @var 微信红包类
 *  @var 2018-11-29   根据官方文档和一些网上资料整合
 *  @var 王佩双 <250570889@qq.com>
 */
CLASS Wxhongbao {
	private $mch_id;                      //  微信商户平台ID
    private $wxappid;                     //  微信公众号appid
    private $client_ip;                   //  服务器ip  必须在商户后台设置成为白名单才可以
    private $apikey;       				  //  微信商户密钥
    private $total_num  = 1;              //  发放人数 单独发放固定 1
    private $nick_name;                   //  红包商户名称
    private $send_name;        			  //  红包派发者名称
    private $wishing;                     //  红包祝福语
    private $act_name;                    //  活动名称
    private $remark;                      //  备注信息
    private $nonce_str  = "";             //  随机字符串
    private $mch_billno;                  //  生成的订单id
    private $re_openid;           		  //  接收方的openID
    private $total_amount = 1 ;           //  红包金额，单位 分
    private $min_value    = 1;            //  最小金额
    private $max_value    = 1;            //  根据接口要求，上述3值必须一致
    private $apiclient_cert;              //  微信商户平台 apiclient_cert.pem
    private $apiclient_key;               //  微信商户平台 apiclient_key.pem
    private $apiclient_ca;                //  微信商户平台 apiclient_cert.p12
    private $wxhb_inited;                 //  记录红包是否准备好
    private $api_hb_single = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";     // 普通红包

     function __construct($data){
        $this->wxhb_inited = false; 
        $this->apiclient_cert = getcwd() . "/cert/apiclient_cert.pem";
        $this->apiclient_key  = getcwd() . "/cert/apiclient_key.pem";
        $this->apiclient_ca   = getcwd() .  "/cert/apiclient_cert.p12";
        $this->mch_id		  = $data['mch_id'];
        $this->wxappid		  = $data['wxappid'];
        $this->client_ip 	  = $data['client_ip'];
        $this->apikey         = $data['apikey'];
        $this->nick_name 	  = $data['nick_name'];
        $this->send_name 	  = $data['send_name'];
        $this->wishing 		  = $data['wishing'];
        $this->act_name		  = $data['act_name'];
        $this->remark 		  = $data['remark'];
        $this->re_openid      = $data['re_openid'];
        $this->mch_billno     = $data['mch_billno'];
    }
   

    /**
     * 生成红包
     * @param  $amount 金额分
     */
    public function newhb($amount){

        if(!is_numeric($amount)){
            $this->return_error("金额参数错误");
            exit;
        }elseif($amount<100){
        	$this->return_error("金额太小");
            exit;
        }elseif($amount>20000){
            $this->return_error("金额太大");
            exit;
        }

        $this->gen_nonce_str();//构造随机字串
        // $this->gen_mch_billno();//构造订单号
        $this->setAmount($amount);
        $this->wxhb_inited = true; //标记微信红包已经初始化完毕可以发送

    }
    /**
     * 发出红包
     * 构造签名
     * 注意第二参数，单发时不要改动！
     * @return xml
     */
    public function send(){
        $url = $this->api_hb_single;
        $total_num = 1;
        if(!$this->wxhb_inited) {
            $this->return_error("红包未准备好");//未初始化完成
            exit;
        }
        $this->total_num = $total_num;
        $this->gen_Sign(); //生成签名
        //构造提交的数据        
        $xml = $this->genXMLParam();

        file_put_contents("hbxml.debug",$xml);

        //提交xml
        $ch = curl_init();     
        curl_setopt($ch,CURLOPT_TIMEOUT,10);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);        
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSLCERT,$this->apiclient_cert);        
        curl_setopt($ch,CURLOPT_SSLKEY,$this->apiclient_key);
        curl_setopt($ch,CURLOPT_CAINFO,$this->apiclient_ca);       
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $data = curl_exec($ch);

        $data = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $data;

    }
    /**
     * 设置红包金额
     * @var  $price 单位 分
     */
    public function setAmount($price){
        $this->total_amount = (int)$price;
        $this->min_value = (int)$price;
        $this->max_value = (int)$price;
    }
    // // 生成随机订单
    // private function gen_mch_billno(){
    //     //生成一个长度10，的阿拉伯数字随机字符串
    //     $rnd_num = array('0','1','2','3','4','5','6','7','8','9');
    //     $rndstr = "";
    //     while(strlen($rndstr)<10){
    //         $rndstr .= $rnd_num[array_rand($rnd_num)];    
    //     }
    //     $this->mch_billno = $this->mch_id.date("Ymd").$rndstr;
    // }
    // 生成随机数
    private function gen_nonce_str(){
        $this->nonce_str = strtoupper(md5(mt_rand().time())); //确保不重复而已
    }

    // 生成签名
    private function gen_Sign(){
        unset($param); 
        //其实应该用key重排一次 right?
        $param["act_name"]=$this->act_name;
        if($this->total_num==1){ //这些是裂变红包用不上的参数，会导致签名错误
            $param["client_ip"]=$this->client_ip;
            $param["max_value"]=$this->max_value;
            $param["min_value"]=$this->min_value;
            $param["nick_name"]=$this->nick_name;
        }
        $param["mch_billno"]  = $this->mch_billno;        
        $param["mch_id"]	  = $this->mch_id;        
        $param["nonce_str"]   = $this->nonce_str;        
        $param["re_openid"]   = $this->re_openid;
        $param["remark"]	  = $this->remark;        
        $param["send_name"]   = $this->send_name;
        $param["total_amount"]= $this->total_amount;
        $param["total_num"]   = $this->total_num;       
        $param["wishing"]     = $this->wishing;
        $param["wxappid"]	  = $this->wxappid;
        ksort($param); //按照键名排序
        $sign_raw = "";
        foreach($param as $k => $v){
            $sign_raw .= $k."=".$v."&";
        }
        $sign_raw .= "key=".$this->apikey;
        $this->sign = strtoupper(md5($sign_raw));

    }
    public function genXMLParam(){
      	$xml = "<xml>
         		<act_name><![CDATA[".$this->act_name."]]></act_name> 
          		<client_ip><![CDATA[".$this->client_ip."]]></client_ip> 
          		<max_value>".$this->max_value."</max_value> 
           		<mch_billno>".$this->mch_billno."</mch_billno> 
           		<mch_id>".$this->mch_id."</mch_id>
           		<min_value>".$this->min_value."</min_value> 
           		<nick_name><![CDATA[".$this->nick_name."]]></nick_name>  
           		<nonce_str>".$this->nonce_str."</nonce_str>
            	<re_openid>".$this->re_openid."</re_openid> 
            	<remark><![CDATA[".$this->remark."]]></remark>
             	<send_name><![CDATA[".$this->send_name."]]></send_name>
             	<total_amount>".$this->total_amount."</total_amount> 
             	<total_num>".$this->total_num."</total_num>
             	<wishing><![CDATA[".$this->wishing."]]></wishing>
             	<wxappid>".$this->wxappid."</wxappid>
             	<sign>".$this->sign."</sign>  
        	</xml>";
        return $xml;
    }
    /**
    *	@var 返回信息
    **/ 
    private function return_error($text) {
    	$ret['return_code'] = "SUCCESS";
    	$ret['return_msg']  = $text;
    	$ret['result_code'] = "ERROR";
    	$ret['err_code']    = "ERROR";
    	$ret['err_code_des']= "ERROR";
    	return $ret;
    }

}


