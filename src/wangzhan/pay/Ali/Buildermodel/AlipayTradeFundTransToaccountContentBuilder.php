<?php

namespace wangzhan\pay\Ali\Buildermodel;
/* *
 * 功能：支付宝电脑网站支付查询接口(alipay.trade.query)接口业务参数封装
 * 版本：2.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 */


class AlipayTradeFundTransToaccountContentBuilder
{

    // 商户订单号.
    private $out_biz_no;

    // 支付宝交易号
    private $payee_type;

    private $payee_account;

    private $amount;

    private $payer_show_name;//付款方姓名,付款方姓名（最长支持100个英文/50个汉字）。显示在收款方的账单详情页。如果该字段不传，则默认显示付款方的支付宝认证姓名或单位名称

    private $payee_real_name;//收款方真实姓名,收款方真实姓名（最长支持100个英文/50个汉字）。如果本参数不为空，则会校验该账户在支付宝登记的实名是否与收款方真实姓名一致。

    private $remark;

    private $bizContent = NULL;

    /**
     * @return mixed
     */
    public function getOutBizNo()
    {
        return $this->out_biz_no;
    }

    /**
     * @param mixed $out_biz_no
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setOutBizNo($out_biz_no)
    {
        $this->out_biz_no = $out_biz_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayeeType()
    {
        return $this->payee_type;
    }

    /**
     * @param mixed $payee_type
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setPayeeType($payee_type)
    {
        $this->payee_type = $payee_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayeeAccount()
    {
        return $this->payee_account;
    }

    /**
     * @param mixed $payee_account
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setPayeeAccount($payee_account)
    {
        $this->payee_account = $payee_account;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayerShowName()
    {
        return $this->payer_show_name;
    }

    /**
     * @param mixed $payer_show_name
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setPayerShowName($payer_show_name)
    {
        $this->payer_show_name = $payer_show_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayeeRealName()
    {
        return $this->payee_real_name;
    }

    /**
     * @param mixed $payee_real_name
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setPayeeRealName($payee_real_name)
    {
        $this->payee_real_name = $payee_real_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param mixed $remark
     * @return AlipayTradeFundTransToaccountContentBuilder
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
        return $this;
    }//收款方真实姓名,转账备注（支持200个英文/100个汉字）。当付款方为企业账户，且转账金额达到（大于等于）50000元，remark不能为空。收款方可见，会展示在收款用户的收支详情中。

    public function getBizContent()
    {
        if (is_null($this->bizContent)) {
            $this->out_biz_no && $this->bizContent['out_biz_no'] = $this->out_biz_no;
            $this->payee_type && $this->bizContent['payee_type'] = $this->payee_type;
            $this->payee_account && $this->bizContent['payee_account'] = $this->payee_account;
            $this->amount && $this->bizContent['amount'] = $this->amount;
            $this->payer_show_name && $this->bizContent['payer_show_name'] = $this->payer_show_name;
            $this->payee_real_name && $this->bizContent['payee_real_name'] = $this->payee_real_name;
            $this->remark && $this->bizContent['remark'] = $this->remark;
            $this->bizContent && $this->bizContent['bizContent'] = $this->bizContent;
        }

        return json_encode($this->bizContent, JSON_UNESCAPED_UNICODE);
    }
}