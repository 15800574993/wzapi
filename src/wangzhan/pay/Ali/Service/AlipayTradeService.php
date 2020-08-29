<?php

namespace wangzhan\pay\Ali\Service;

use \Exception;
use wangzhan\pay\Ali\Aop\request\AlipayTradeWapPayRequest;
use wangzhan\pay\Ali\Aop\request\AlipayTradePagePayRequest;
use wangzhan\pay\Ali\Aop\request\AlipayTradeQueryRequest;
use wangzhan\pay\Ali\Aop\request\AlipayTradeRefundRequest;
use wangzhan\pay\Ali\Aop\request\AlipayTradeFastpayRefundQueryRequest;
use wangzhan\pay\Ali\Aop\request\AlipayFundTransToaccountTransferRequest;
use wangzhan\pay\Ali\Aop\request\AlipayTradePrecreateRequest;
use wangzhan\pay\Ali\Aop\request\AlipayF2FPrecreateResult;
use wangzhan\pay\Ali\Aop\AopClient;

/* *
 * 功能：支付宝手机网站alipay.trade.close (统一收单交易关闭接口)业务参数封装
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */

class AlipayTradeService
{

    //支付宝网关地址
    public $gateway_url = "https://openapi.alipay.com/gateway.do";

    //支付宝公钥
    public $alipay_public_key;

    //商户私钥
    public $private_key;

    //应用id
    public $appid;

    //编码格式
    public $charset = "UTF-8";

    public $token = NULL;

    public $notify_url = '';

    //返回数据格式
    public $format = "json";

    //签名方式
    public $signtype = "RSA2";

    function __construct($alipay_config)
    {
        $this->gateway_url       = $alipay_config['gatewayUrl'];
        $this->appid             = $alipay_config['app_id'];
        $this->private_key       = $alipay_config['merchant_private_key'];
        $this->alipay_public_key = $alipay_config['alipay_public_key'];
        $this->charset           = $alipay_config['charset'];
        $this->signtype          = $alipay_config['sign_type'];
        $this->notify_url        = $alipay_config['notify_url'];

        if (empty($this->appid) || trim($this->appid) == "") {
            throw new Exception("appid should not be NULL!");
        }
        if (empty($this->private_key) || trim($this->private_key) == "") {
            throw new Exception("private_key should not be NULL!");
        }
        if (empty($this->alipay_public_key) || trim($this->alipay_public_key) == "") {
            throw new Exception("alipay_public_key should not be NULL!");
        }
        if (empty($this->charset) || trim($this->charset) == "") {
            throw new Exception("charset should not be NULL!");
        }
        if (empty($this->gateway_url) || trim($this->gateway_url) == "") {
            throw new Exception("gateway_url should not be NULL!");
        }

    }

    function app($builder, $notify_url = '')
    {
        $aop                     = new AopClient ();
        $aop->gatewayUrl         = $this->gateway_url;
        $aop->appId              = $this->appid;
        $aop->rsaPrivateKey      = $this->private_key;
        $aop->format             = $this->format;
        $aop->charset            = $this->charset;
        $aop->apiVersion         = "1.0";
        $aop->signType           = 'RSA2';
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $response                = $aop->sdkExecute($builder);
        return $response;

    }

    /**
     * alipay.trade.wap.pay
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @param $return_url 同步跳转地址，公网可访问
     * @param $notify_url 异步通知地址，公网可以访问
     * @return $response 支付宝返回的信息
     */
    function wapPay($builder, $return_url, $notify_url)
    {

        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);

        $request = new AlipayTradeWapPayRequest();

        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request, true);
        // $response = $response->alipay_trade_wap_pay_response;
        return $response;
    }

    function transfer($builder)
    {
        $biz_content = $builder->getBizContent();
        //$this->writeLog($biz_content);

        $request = new AlipayFundTransToaccountTransferRequest();

        $request->setBizContent($biz_content);
        $response = $this->aopclientRequestExecute($request);
        return $response;
    }

    /**
     * alipay.trade.page.pay
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @param $return_url 同步跳转地址，公网可以访问
     * @param $notify_url 异步通知地址，公网可以访问
     * @return $response 支付宝返回的信息
     */
    function pagePay($builder, $return_url, $notify_url)
    {

        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);

        $request = new AlipayTradePagePayRequest();

        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request, true);
        // $response = $response->alipay_trade_wap_pay_response;
        return $response;
    }

    //当面付2.0预下单(生成二维码,带轮询)
    public function qrPay($req)
    {

        $bizContent = $req->getBizContent();
        //$this->writeLog($bizContent);

        $request = new AlipayTradePrecreateRequest();
        $request->setBizContent($bizContent);
        //$request->setNotifyUrl($this->notify_url);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_precreate_response;

        $result = new AlipayF2FPrecreateResult($response);
        if (!empty($response) && ("10000" == $response->code)) {
            $result->setTradeStatus("SUCCESS");
        } else {
            $result->setTradeStatus("FAILED");
        }

        return $result;

    }

    function aopclientRequestExecute($request, $ispage = false)
    {

        $aop                     = new AopClient ();
        $aop->gatewayUrl         = $this->gateway_url;
        $aop->appId              = $this->appid;
        $aop->rsaPrivateKey      = $this->private_key;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $aop->apiVersion         = "1.0";
        $aop->postCharset        = $this->charset;
        $aop->format             = $this->format;
        $aop->signType           = $this->signtype;
        // 开启页面信息输出
        $aop->debugInfo = true;
        if ($ispage) {
            $result = $aop->pageExecute($request, "post");
            echo $result;
        } else {
            $result = $aop->Execute($request);
        }

        //打开后，将报文写入log文件
        //$this->writeLog("response: " . var_export($result, true));
        return $result;
    }

    /**
     * alipay.trade.query (统一收单线下交易查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function Query($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);
        $request = new AlipayTradeQueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_query_response;
        return $response;
    }

    /**
     * alipay.trade.refund (统一收单交易退款接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function Refund($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);
        $request = new AlipayTradeRefundRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_refund_response;
        return $response;
    }

    /**
     * alipay.trade.close (统一收单交易关闭接口)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function Close($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);
        $request = new AlipayTradeCloseRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_close_response;
        return $response;
    }

    /**
     * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function refundQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);
        $request = new AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        return $response;
    }

    /**
     * alipay.data.dataservice.bill.downloadurl.query (查询对账单下载地址)
     * @param $builder 业务参数，使用buildmodel中的对象生成。
     * @return $response 支付宝返回的信息
     */
    function downloadurlQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        //打印业务参数
        //$this->writeLog($biz_content);
        $request = new alipaydatadataservicebilldownloadurlqueryRequest();
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_data_dataservice_bill_downloadurl_query_response;
        return $response;
    }

    /**
     * 验签方法
     * @param $arr 验签支付宝返回的信息，使用支付宝公钥。
     * @return boolean
     */
    function check($arr)
    {
        $aop                     = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result                  = $aop->rsaCheckV1($arr, $this->alipay_public_key, $this->signtype);
        return $result;
    }

    //请确保项目文件有可写权限，不然打印不了日志。
    function writeLog($text)
    {
        // $text=iconv("GBK", "UTF-8//IGNORE", $text);
        //$text = characet ( $text );
        file_put_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "./../../log.txt", date("Y-m-d H:i:s") . "  " . $text . "\r\n", FILE_APPEND);
    }


    /** *利用google api生成二维码图片
     * $content：二维码内容参数
     * $size：生成二维码的尺寸，宽度和高度的值
     * $lev：可选参数，纠错等级
     * $margin：生成的二维码离边框的距离
     */
    function create_erweima($content, $size = '200', $lev = 'L', $margin = '0')
    {
        $content = urlencode($content);
        $image   = '<img src="http://chart.apis.google.com/chart?chs=' . $size . 'x' . $size . '&amp;cht=qr&chld=' . $lev . '|' . $margin . '&amp;chl=' . $content . '"  widht="' . $size . '" height="' . $size . '" />';
        return $image;
    }
}