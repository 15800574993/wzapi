<?php
namespace wangzhan\pay;
use wangzhan\pay\Ali\Service\AlipayTradeService;
use wangzhan\pay\Ali\Aop\request\AlipayTradeAppPayRequest;
use wangzhan\pay\Ali\Buildermodel\AlipayTradeQueryContentBuilder;
use wangzhan\pay\Ali\Buildermodel\AlipayTradeRefundContentBuilder;
use wangzhan\pay\Ali\Buildermodel\AlipayTradeFastpayRefundQueryContentBuilder;
use wangzhan\pay\Ali\Buildermodel\AlipayTradeWapPayContentBuilder;
use wangzhan\Error;

// 支付宝支付
class Alipay {
	// 付费类型
	private 	$payType;
	/**
	 * @var [支付配置]
	 * @param  $[allpay] [<app_id>] 				支付宝的app_id
	 * @param  $[allpay] [<charset>] 				编码
	 * @param  $[allpay] [<sign_type>] 				加密方式
	 * @param  $[allpay] [<gatewayUrl>] 			网关
	 * @param  $[allpay] [<merchant_private_key>] 	支付宝私钥
	 * @param  $[allpay] [<alipay_public_key>] 		支付宝公钥
	 * @param  $[allpay] [<return_url>] 			同步回调地址
	 * @param  $[allpay] [<notify_url>] 			异步回调地址
	 */
	public 		$config 	= [
        'curl_proxy_host' 	=> '0.0.0.0',
        'curl_proxy_post' 	=> 0,
        'charset'         	=> 'UTF-8',
        'sign_type'       	=> 'RSA2'
    ];

    // 订单数据/查询数据
    public $payData 		= [];

    public function handle() {
        
    	switch ($this->payType){
    		// app支付
			case "AliApp":
				return $this->AliApp();
			  break;  
			// 支付宝h5 支付
			case "AliH5":
				return $this->AliH5();
			  break; 
			// 二维码支付
			case "AliQrpay":
			  	return $this->AliQrpay();
			  break;
			// 查询订单
			case "AliQuery":
			  	return $this->AliQuery();
			  break;
			// 退款
			case "AliRefund":
			  	return $this->AliRefund();
			  break;
			// 查询退款信息
			case "AliRefundQuery":
			  	return $this->AliRefundQuery();
			  break;
			// 支付宝web跳转支付同步跳转
			case "AliWeb":
			  	return $this->AliWeb();
			  break;
			// 支付宝回掉
			case "NotifyAli":
				return $this->NotifyAli();
			  break;
			default:
                $this->error("支付宝支付类型不存在");
		}
    }
    /**
     * @var    
     * @param  [type] $payType [支付方式]
     * @param  [type] $config  [支付配置]
     * @param  [type] $payData [支付参数]
     * @return [type]          [description]
     */
    public function pay($payType, $config = array(), $payData){
    	$this->payType 				=	$payType;
    	$this->config 				=	$config;
    	$this->payData 				=	$payData;
    	return $this->handle();
    }
    /**
     * @var 支付宝app支付
     */
    public function AliApp() {
    	$order      = [
            'subject'      => $this->payData['subject'],
            'out_trade_no' => $this->payData['out_trade_no'],
            'total_amount' => $this->payData['amount'],
            'product_code' => 'QUICK_MSECURITY_PAY'
        ];
        $builder    = new AlipayTradeAppPayRequest();
        $notify_url = $this->payData['notify_url'] ?? $this->config['notify_url'];
        $builder->setNotifyUrl($notify_url);
        $builder->setBizContent(json_encode($order));
        $service  = new AlipayTradeService($this->config);
        $response = $service->app($builder, $notify_url);
        return $response;
    }
    /**
     * @var 支付宝H5支付
     */
    public function AliH5() {
    	$builder = new AlipayTradeWapPayContentBuilder();
        $builder->setBody($this->payData['subject']);
        $builder->setSubject($this->payData['subject']);
        $builder->setOutTradeNo($this->payData['out_trade_no']);
        $builder->setTotalAmount($this->payData['amount']);
        //$builder->setTimeExpress($timeout_express);
        $pay        = new AlipayTradeService($this->config);
        $notify_url = $this->payData['notify_url'] ?? $this->config['notify_url'];
        $pay->wapPay($builder, $this->payData['return_url'], $notify_url);
        exit;
    }
    /**
     * @var 支付宝二位码支付
     */
    public function AliQrpay() {
    	$builder = new AlipayTradePrecreateContentBuilder();
        $builder->setOutTradeNo($this->payData['out_trade_no']);
        $builder->setTotalAmount($this->payData['amount']);
        $builder->setTimeExpress("5m");
        $builder->setSubject($this->payData['subject']);
        $notify_url = $this->payData['notify_url'] ?? $this->config['notify_url'];
        $builder->setNotifyUrl($notify_url);
        $builder->setDisablePayChinnels('pcredit,pcreditpayInstallment,creditCard,creditCardExpress,creditCardCartoon');
        $qrPay  = new AlipayTradeService($this->config);
        $result = $qrPay->qrPay($builder);
        $res    = $result->getResponse();
        if ($result->getTradeStatus() !== 'SUCCESS') {
            $this->error($res);
        }

        return $res->qr_code;
    }
    /**
     * @var 支付宝查询订单
     */
    public  function AliQuery() {
    	$builder = new AlipayTradeQueryContentBuilder();
        if ($this->payData['out_trade_no']) {
            $builder->setOutTradeNo($this->payData['out_trade_no']);
        } elseif ($this->payData['trade_no']) {
            $builder->setTradeNo($this->payData['trade_no']);
        } else {
            $this->error("订单号与交易号不能同时为空");
        }
        $aop    = new AlipayTradeService($this->config);
        $result = $aop->Query($builder);
        if (!isset($result->code)){
            $this->error("查询失败");
        }
        if ($result->code != '10000') {
            $this->error($result->sub_msg);
        }
        return json_decode(json_encode($result), true);
    }
	/**
	 * @var 支付宝退款
	 */
    public function AliRefund() {
    	$builder = new AlipayTradeRefundContentBuilder();
        if (isset($this->payData['out_trade_no'])) {
            $builder->setOutTradeNo($this->payData['out_trade_no']);
        }
        if (isset($this->payData['trade_no'])) {
            $builder->setTradeNo($this->payData['trade_no']);
        }

        if (!isset($this->payData['out_trade_no']) && !isset($this->payData['trade_no'])) {
            $this->error("订单号与微信交易号不能同时为空");
        }
        $builder->setRefundAmount($this->payData['refund_fee']);
        $builder->setOutRequestNo($this->payData['refund_no']);
        $aop    = new AlipayTradeService($this->config);
        $result = $aop->Refund($builder);
        if (!isset($result->code)){
            $this->error("退款申请失败");
        }
        if ($result->code != '10000'){
            $this->error($result->msg . $result->sub_msg);
        }
        return  [
            'out_trade_no'  => $result->out_trade_no,         //商户订单号码
            'trade_no'      => $result->trade_no,             //商户订单号码
            'out_refund_no' => $this->payData['refund_no'],     //商户提交的退款单号
            'refund_id'     => $result->trade_no,             //微信退款单号
            'refund_fee'    => $result->refund_fee,           //退款金额
        ];
    }
    /**
	 * @var 查询退款信息
	 */
    public function AliRefundQuery() {
    	$builder                   = new AlipayTradeFastpayRefundQueryContentBuilder();
        $out_trade_no           = $this->payData['out_trade_no'] ?? $this->payData['trade_no'];
        if (!$out_trade_no) {
            $this->error("订单号与支付宝交易号不能同时为空");
        }
        $out_refund_no = $this->payData['out_refund_no'] ?? $this->payData['refund_no'];
        if (!$out_refund_no) {
            $this->error("退款单号与支付宝退款交易号不能同时为空");
        }
        $builder->setOutTradeNo($out_trade_no);
        $builder->setOutRequestNo($out_refund_no);
        $aop    = new AlipayTradeService($this->config);
        $result = $aop->refundQuery($builder);

        if (!isset($result->alipay_trade_fastpay_refund_query_response->code)) {
            $this->error("查询失败");
        }
        $result = $result->alipay_trade_fastpay_refund_query_response;
        if ($result->code != '10000'){
            $this->error($result->msg . $result->sub_msg);
        }
        return json_decode(json_encode($result), true);
    }
    /**
	 * @var 支付宝异步回调
	 */
    public function NotifyAli() {
        $service = new AlipayTradeService($this->config);
        $result  = $service->check($this->payData);
        if ($result) {
            if ($this->payData['trade_status'] == 'TRADE_SUCCESS' || $this->payData['trade_status'] == 'TRADE_FINISHED') {
            	return true;
            }
        }
        return false;
    }
    public function error($text) {
        return (new Error("支付宝操作","4",$text));
    }

}