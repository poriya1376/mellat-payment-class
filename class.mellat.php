<?php
//http://www.webhostingtalk.ir/f148/121231/
/**
 * Mellat Payment API
 * Author: Mohammad Mahdi Mahdi Sahebi
 * Date: 1393-05-31
 * email: msahebi--at--gmail.com
 **/
class MellatAPI {
	var	$namespace   = 'http://interfaces.core.sw.bps.com/';
	var $WSDL        = 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl'; ## PayMode (WebService)
	var $ACTION      = 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat'; ## PayMode (Form)
	var $callBackUrl = 'http://www.your-domain.com/callback.php';

	var $client;
	var $terminalId;
	var $username;
	var $password;

	var $errors = array();

	public function __construct($terminalId = null, $username = null, $password = null, $callbackURL = null) {	
		$this->terminalId = isset($terminalId) && !empty($terminalId) ? ($terminalId) : null;
		$this->username   = isset($username)   && !empty($username)   ? ($username)   : null;
		$this->password   = isset($password)   && !empty($password)   ? ($password)   : null;
		$this->password   = isset($password)   && !empty($password)   ? ($password)   : null;
		$this->callBackUrl= isset($callbackURL)&& !empty($callbackURL)? ($callbackURL): null;
	}

	public function setConnection(){
		try { 
			$this->client = @new SoapClient($this->WSDL);
		} catch (Exception $e) { 
			die($e->getMessage()); 
		}

		return $this->client;
	}

	public function bpPayRequest($orderId, $amount, $additionalData, $payerId){
		$parameters = array(
							'terminalId' 	 => $this->terminalId,
							'userName' 		 => $this->username,
							'userPassword' 	 => $this->password,
							'orderId' 		 => $orderId,
							'amount' 		 => $amount,
							'localDate' 	 => date("Ymd"),
							'localTime' 	 => date("His"),
							'additionalData' => $additionalData,
							'callBackUrl' 	 => $this->callBackUrl,
							'payerId' 		 => 0
							);
		$result = -1;
		try { 
			$result = $this->client->bpPayRequest($parameters, $this->namespace);
		} catch (Exception $e) { 
			die($e->getMessage()); 
		}

		$resultStr = $result->return;
		$res = @explode (',',$resultStr);
		if(is_array($res)){
		}else{
			$res[0] = $resultStr;
			$res[1] = $this->getErrors($resultStr);
		}
		return $res;
	}

	public function bpVerifyRequest($saleOrderId, $SaleReferenceId, $type = 'verify'){
		$parameters = array(
						'terminalId'      => $this->terminalId,
						'userName'        => $this->username,
						'userPassword'    => $this->password,
						'orderId'         => $saleOrderId,
						'saleOrderId'     => $saleOrderId,
						'saleReferenceId' => $SaleReferenceId
					  );
		$result = -1;
		try { 
			if($type == 'verify'){
				$result = $this->client->bpVerifyRequest($parameters, $this->namespace);
			}else
			if($type == 'settle'){
				$result = $this->client->bpSettleRequest($parameters, $this->namespace);
			}
		} catch (Exception $e) { 
			die($e->getMessage()); 
		}


		$resultStr = $result->return;
		$res = @explode (',',$resultStr);

		$ret = array();
		if(is_array($res)){
			$ret = $res;
		}else{
			$ret[0] = $resultStr;
			$ret[1] = $this->getErrors($resultStr);
		}
		return $ret;
	}

	public function redirectToBank($refid){
		$var = '
				<form method="POST" action="'.$this->ACTION.'" target="_self" id="payform">
					<input type="hidden" name="RefId" value="'.$refid.'">
				</form>
				<script type="text/javascript">
					document.getElementById("payform").submit();
				</script>
				';
		return $var;
	}

	public function setErrors($errData = '', $note = '', $errNote = 'خطای سیستم') {
		## ErrData
		$errData = is_array($errData) ? implode(", ", $errData) : $errData;

		if (!empty($errData)) {
			$this->errors[] = $errNote . ' :: ' . $errData . (!empty($note) ? ' - ' . $note : '');
		}
	}

	public function getErrors($errCode = '') {
		switch($errCode) {
			case 11: $err = "شماره کارت معتبر نیست"; break;
			case 12: $err = "موجودی کافی نیست"; break;
			case 13: $err = "رمز دوم شما صحیح نیست"; break;
			case 14: $err = "دفعات مجاز ورود رمز بیش از حد است"; break;
			case 15: $err = "کارت معتبر نیست"; break;
			case 16: $err = "دفعات برداشت وجه بیش از حد مجاز است"; break;
			case 17: $err = "کاربر از انجام تراکنش منصرف شده است"; break;
			case 18: $err = "تاریخ انقضای کارت گذشته است"; break;
			case 19: $err = "مبلغ برداشت وجه بیش از حد مجاز است"; break;
			case 21: $err = "پذیرنده معتبر نیست"; break;
			case 23: $err = "خطای امنیتی رخ داده است"; break;
			case 24: $err = "اطلاعات کاربری پذیرنده معتبر نیست"; break;
			case 25: $err = "مبلغ نامعتبر است"; break;
			case 31: $err = "پاسخ نامعتبر است"; break;
			case 32: $err = "فرمت اطلاعات وارد شده صحیح نیست"; break;
			case 33: $err = "حساب نامعتبر است"; break;
			case 34: $err = "خطای سیستمی"; break;
			case 35: $err = "تاریخ نامعتبر است"; break;
			case 41: $err = "شماره درخواست تکراری است"; break;
			case 42: $err = "تراکنش Sale یافت نشد"; break;
			case 43: $err = "قبلا درخواست Verify داده شده است"; break;
			case 44: $err = "درخواست Verify یافت نشد"; break;
			case 45: $err = "تراکنش Settle شده است"; break;
			case 46: $err = "تراکنش Settle نشده است"; break;
			case 47: $err = "تراکنش Settle یافت نشد"; break;
			case 48: $err = "تراکنش Reverse شده است"; break;
			case 49: $err = "تراکنش Refund یافت نشد"; break;
			case 51: $err = "تراکنش تکراری است"; break;
			case 54: $err = "تراکنش مرجع موجود نیست"; break;
			case 55: $err = "تراکنش نامعتبر است"; break;
			case 61: $err = "خطا در واریز"; break;
			case 111: $err = "صادر کننده کارت نامعتبر است"; break;
			case 112: $err = "خطای سوییچ صادر کننده کارت"; break;
			case 113: $err = "پاسخی از صادر کننده کارت دریافت نشد"; break;
			case 114: $err = "دارنده کارت مجاز به انجام این تراکنش نمی باشد"; break;
			case 412: $err = "شناسه قبض نادرست است"; break;
			case 413: $err = "شناسه پرداخت نادرست است"; break;
			case 414: $err = "سازمان صادر کننده قبض معتبر نیست"; break;
			case 415: $err = "زمان جلسه کاری به پایان رسیده است"; break;
			case 416: $err = "خطا در ثبت اطلاعات"; break;
			case 417: $err = "شناسه پرداخت کننده نامعتبر است"; break;
			case 418: $err = "اشکال در تعریف اطلاعات مشتری"; break;
			case 419: $err = "تعداد دفعات ورود اطلاعات بیش از حد مجاز است"; break;
			case 421: $err = "IP سرور معتبر نیست"; break;
			default:  $err = ""; break;
		}
		return $err;
	}
}
?>