<?php

/*

$httprequest = new httprequest();
$httprequest->seturl('http://localhost/');
$httprequest->setuseragent('');
$httprequest->send();
if ($httprequest->requestsuccess()) {
	echo $httprequest->gethttpdata();
} else {
	$errormsg = $httprequest->geterrormsg();
	throw new Exception('Curl reported error: ' . $errormsg);
}

*/

class httprequest {

	private $url = '';
	private $httpdata = '';
	private $headerdata = '';
	private $postdata = '';
	private $requestsuccess = false;
	private $statuscode = 0;
	private $curlerrormsg = '';
	private $cookiejar_file = '';
	private $useragent = '';
	private $referer = '';
	private $timeout = 30;
	private $timeout_conn = 30;
	private $redirect_max_follow = 0;
	private $returnheader = false;
	private $returnheaderonly = false;

	public function seturl($url) {
		$this->url = $url;
	}

	public function gethttpdata() {
		return $this->httpdata;
	}
	
	public function getheaderdata() {
		return $this->headerdata;
	}

	public function geterrormsg() {
		return $this->curlerrormsg;
	}

	public function requestsuccess() {
		return $this->requestsuccess;
	}

	public function setcookiesupport($cookiejar_file) {
		$this->cookiejar_file = $cookiejar_file;
	}

	public function setpostdata($postdata) {
		$this->postdata = $postdata;
	}

	public function setuseragent($useragent) {
		$this->useragent = $useragent;
	}

	public function setreferer($referer) {
		$this->referer = $referer;
	}

	public function settimeout($timeout) {
		$this->timeout = $timeout;
	}

	public function settimeoutconn() {
		$this->timeout_conn = $timeout;
	}

	public function setfollowlocation($redirect_max_follow) {
		$this->redirect_max_follow = $redirect_max_follow;
	}

	public function setreturnheader() {
		$this->returnheader = true;
	}

	public function setreturnheaderonly() {
		$this->returnheaderonly = true;
	}

	public function send() {

		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_FILE, $out);

		if ($this->returnheader) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		}

		if ($this->redirect_max_follow) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->redirect_max_follow);
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		}

		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_conn);

		if ($this->useragent) {
			curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
		}

		if ($this->referer) {
			curl_setopt($ch, CURLOPT_REFERER , $this->referer);
		}

		if ($this->cookiejar_file) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiejar);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiejar);
		}

		if ($this->returnheaderonly) {
			//curl_setopt( $ch, CURLOPT_CUSTOMREQUEST,"HEAD");
			curl_setopt($ch, CURLOPT_NOBODY, 1);
		}

		//if ($curl_ssl_cainfo_path) {
		//	curl_setopt($ch, CURLOPT_CAINFO, $curl_ssl_cainfo_path);
		//} else {
		//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		//}

		if ($this->postdata) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postdata);
		}

		$this->httpdata = curl_exec($ch);

		//If header data returned, split it from the body
		if ($this->returnheader) {

			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

			$this->headerdata = substr($this->httpdata, 0, $header_size - 4);
			$this->httpdata = substr($this->httpdata, $header_size);

		}

		$this->statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($this->statuscode == 200) {

			$this->requestsuccess = true;

		} else if (curl_errno($ch)) {

			$this->curlerrormsg = curl_error($ch);
			$this->requestsuccess = false;

		} else if ($this->statuscode != 200) {
			$this->curlerrormsg = curl_error($ch);

			if (!$this->curlerrormsg) {
				$this->curlerrormsg = 'Status Code: ' . $this->statuscode;
			}

			$this->requestsuccess = false;
		}

		curl_close($ch);

	}

}

?>