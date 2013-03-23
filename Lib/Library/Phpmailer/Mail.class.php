<?php

define("DEBUG_SEND", 1);
define("DEBUG_RCPT", 2);

class Smtp_Mail
{

	
	var $from;	//送信人
	var $to;
	var $subject;
	var $body;
	
	var $stmp			=	'smtp.exmail.qq.com';		//SMTP服务器
	var $port			=	25;							//SMTP服务端口号
	var $auth			=	false;						//是否允许身份验证
	var $username		=	'me@meside.com.cn';			//SMTP用户账号
	var $password		=	'm12345678';				//STMP用户密码
	var $debug			=	true;						//调试信息
	
	var $issend			=	false;						//发信状态（成功true，失败false）
	var $fp				=	null;						//套接字连接句柄
	
	function Smtp_Mail($mailinfo, $mailconf = null){
		
		if(isset($mailinfo['to']))			$this->to			= $mailinfo['to'];
		if(isset($mailinfo['from']))		$this->from			= $mailinfo['from'];
		if(isset($mailinfo['subject']))		$this->subject		= $mailinfo['subject'];
		if(isset($mailinfo['body']))		$this->body			= $mailinfo['body'];
		
		if(isset($mailinfo['smtp']))		$this->smtp			= $mailinfo['smtp'];
		if(isset($mailinfo['port']))		$this->port			= $mailinfo['port'];
		if(isset($mailinfo['auth']))		$this->auth			= $mailinfo['auth'];
		if(isset($mailinfo['username']))	$this->username		= $mailinfo['username'];
		if(isset($mailinfo['password']))	$this->password		= $mailinfo['password'];
		if(isset($mailinfo['debug']))		$this->debug		= $mailinfo['debug'];
	}
	
	function Send(){
		
		//连接服务器
		$this->connect();
		
		//向服务器发出请求
		if($this->auth){
			$this->_put('HELO', 'taodoor.com', 250);
			$this->_put('AUTH LOGIN', '', 334);
			$this->_put(base64_encode($this->username), '', 334);
			$this->_put(base64_encode($this->password), '', 235);
		}else{
			$this->_put('HELO', 'taodoor.com', 250);
		}
		
		//送信人
		$this->_put('MAIL FROM:', "<{$this->from}>", 250);
		
		//收件人
		if(!is_array($this->to)){
			$this->to = array($this->to);
		}
		
		//循环处理邮件地址
		foreach($this->to as $key=>$to){
			$to = trim($to);
			if($this->checkMail($to)){
				$this->_put('RCPT TO:', "<{$to}>", 250);
				$this->to[$key] = $to;
			}else{
				unset($this->to[$key]);
			}
		}
		
		//邮件正文
		$this->_put('DATA', '', 354);
		$stream = "From: " .$this->from. "\r\n";
		$stream .= "To: \"" .jooin('", "', $this->to). "\" \r\n";
		$stream .= "Subject: " .$this->subject. "\r\n";
		$stream .= "Date: " .date('r'). "\r\n";
		
		$stream .= "Mime-Version: 1.0\r\n";
		$stream .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
		$stream .= rtrim($this->body, ".\n\r");
		$stream .= "\r\n.";
		$this->_put($stream, '', 250);
		
		//退出
		$this->_put('QUIT', '', 221);
		
		$this->issend = true;
		@fclose($this->fp);
	}
	
	function stat(){
		return $this->issend;
	}
	
	function connect(){
		$this->fp = fsockopen($his->smtp, $this->port, $errno, $errstr, 30) or die("<p>SMTP服务器连接失败！</p>");
		$rcpt = fgets($this->fp);
		$this->_debug($rcpt, DEBUG_RCPT);
		
		if(substr($rcpt,0,3) != 220){
			die("<p>SMTP服务器遇到错误！</p>");
		}
	}
	
	function _put($command, $args, $code){
		
		$send = (empty($args))? $command . "\r\n" : $command . ' ' . $args . "\r\n";
		
		if(is_resource($this->fp)){
			fputs($this->fp, $send);
			$rcpt = fgets($this->fp);
			$this->_debug($send, DEBUG_SEND);
			$this->_debug($rcpt, DEBUG_RCPT);
			if(substr($rcpt, 0, 3) != $code){
				die("<p>邮件发送失败（希望的响应值为`$code`)!</p>");
			}
		}else{
			die("<p>远程STMP服务器关闭！</p>");
		}
		
	}
	
	function _debug($data, $type){
		if($this->debug == true){
			echo "<pre>";
			$data = htmlspecialchars(trim($data));
			switch($type){
				case DEBUG_RCPT:
					echo "<font color=#00F>-></font>" .$data;
				break;
				case DEBUG_SEND;
					echo "<font color=#00F>-></font><b>" .$data. "</b>";
				break;
			}
		}
	}
	
	function checkMail($mail){
		return preg_match("/^[\w-]+@[\w-]+(\.[\w-]+){0,3}$/", $mail);
	}
	
}

?>