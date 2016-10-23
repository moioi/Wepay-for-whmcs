<?php
function wepay_config() {
    $configarray = array(
        "FriendlyName"  => array(
            "Type"  => "System",
            "Value" => "WePay"
        ),
        "account"  => array(
            "FriendlyName" => "商户手机号",
            "Type"         => "text",
            "Size"         => "32",
        ),
        "key" => array(
            "FriendlyName" => "安全检验码",
            "Type"         => "text",
            "Size"         => "32",
        ),
        "mchid" => array(
            "FriendlyName" => "商户号",
            "Type"         => "text",
            "Size"         => "32",
        ),
    );

    return $configarray;
}

function wepay_form($params) {

    $systemurl          = $params['systemurl'];
    $invoiceid          = $params['invoiceid'];
    $amount             = $params['amount']; 
    $account            = $params['account'];
    $mchid              = $params['mchid'];
    $type               = '2';
    $name               = $mchid . "|" . $invoiceid;
    $key                = $params['key'];
    $sign               = array();
    $sign['mchid']      = $mchid;
    $sign['account']    = $account;
    $sign['cny']        = $amount;
    $sign['type']       = $type;
    $sign['trade']      = $name;

    foreach ($sign as $k=>$v)
    {
        $o.= "$k=".urlencode($v)."&";
    }

    $sign = md5(substr($o,0,-1).$key);
    unset($arrPostInfo);
    $arrPostInfo  = array(
        "mchid"   => $mchid,
        "account" => $account,
        "type"    => $type,
        "cny"     => $amount,
        "trade"   => $name,
        "sign"    => $sign
        );

    $url = 'http://v2.ukoi.net/gateways/pay.php';//请求的url地址
     
    $ch = curl_init();//打开
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arrPostInfo);
    $response  = curl_exec($ch);
    curl_close($ch);//关闭
    $result = json_decode($response,true);
	$code = '<div class="wepay"><div id="wepayimg" style="border: 1px solid #AAA;border-radius: 4px;overflow: hidden;margin-bottom: 5px;"><img class="img-responsive pad" src="http://qr.liantu.com/api.php?m=10&text='.$result['qrlink'].'" style=""></div>';
	$code_ajax = '<a href="" target="_blank" id="wepayDiv" class="btn btn-success btn-block">扫描二维码进行支付</a></div>';
	$code_ajax = $code_ajax.'
<!--微信支付ajax跳转-->
	<script>
    //设置每隔 5000 毫秒执行一次 load() 方法
    setInterval(function(){load()}, 5000);
    function load(){
        var xmlhttp;
        if (window.XMLHttpRequest){
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }else{
            // code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                trade_state=xmlhttp.responseText;
                if(trade_state=="SUCCESS"){
                    document.getElementById("wepayimg").style.display="none";
                    document.getElementById("wepayDiv").innerHTML="支付成功";
                    //延迟 2 秒执行 tz() 方法
                    setTimeout(function(){tz()}, 5000);
                    function tz(){
                        window.location.href="'.$systemurl.'/viewinvoice.php?id='.$invoiceid.'";
                    }
                }
            }
        }
        //invoice_status.php 文件返回订单状态，通过订单状态确定支付状态
        xmlhttp.open("get","'.$systemurl.'/modules/gateways/wepay/invoice_status.php?invoiceid='.$invoiceid.'",true);
        //下面这句话必须有
        //把标签/值对添加到要发送的头文件。
        //xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        //xmlhttp.send("out_trade_no=002111");
        xmlhttp.send();
    }
</script>';
	
	$code = $code.$code_ajax;
    $n1 = $_SERVER['PHP_SELF'];
    if(stristr($n1,'viewinvoice')){
        return $code;
    }else{
        return '<img style="width: 150px" src="'.$systemurl.'/modules/gateways/wepay/wechat.png" alt="微信支付" />';
    }

}

function wepay_link($params) {
    return wepay_form($params);
}

?>
