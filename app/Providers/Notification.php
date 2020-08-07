<?php
namespace App\Providers;

class Notification{

	public function __construct()
	{
		$this->app = app();
	}

	public function AccountActivated($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Account Activation', $message);
	}

	public function AccountBreach($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Account Security Breach', $message);	
	}

	public function AccountCreated($email, $key){
		$message = "Welcome to ".$this->app->get('APP_NAME').", Your account has been created. 
		Activate your account by clicking on this link: ".$this->app->get('APP_URL')."/activate/{$key}"."?email={$email}".
		"\n This is an automated message system, please do not reply.";
		$this->EmailNotification($email, 'Account Creation Success', $message);
	}

	public function AccountDeletion($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Account Delete Request', $message);
	}

	public function DeviceChanged($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Device Changed', $message);
	}	

	public function ForgotPassword($email, $new){
		$message = <<< __MAIL__
		A forgot password request has been issued for the account connected to this email.
		Your password is {$new} . If this is a false alarm, kindly ignore and delete this mail immediately.
__MAIL__;
		$this->EmailNotification($email, 'Forgot Password Request', $message);
	}		

	public function EmailChange($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Email Change Request', $message);
	}

	public function messageReceived($email){
		$message = <<< __MAIL__

__MAIL__;
		$this->EmailNotification($email, 'Email Change Request', $message);
	}

	final public function PWAsend(array $tokens_array, string $note){
		$msg = [ 
			'title' => $this->app->get('APP_NAME')." Notification",
			'body'	=> $note,
			'icon'	=> $this->app->get('firebase_token')
		];
		if ($this->PWANotification(['registration_ids' => $tokens_array, 'data' => $msg ])){
			return true;
		}
		return false;
	}

	private function PWANotification($payload){
		$header= [ 
			'Authorization: key='.$this->app->get('firebase_token'), 
			'Content-Type: Application/json' 
		];
		$curl= curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL 			=> "https://fcm.googleapis.com/fcm/send",
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_CUSTOMREQUEST	=> "POST",
			CURLOPT_POSTFIELDS		=> json_encode($payload),
			CURLOPT_HTTPHEADER		=> $header
		));
		$response= curl_exec($curl);
		$err= curl_error($curl);
		curl_close($curl);
		
		if(!$err){
			return $response;
		}else{
			return false;
		}
	}

	public function EmailNotification($email, $subject='', $message){
		$subject = $this->app->get('APP_NAME') . ' Notification: ' . $subject;
		$headers = implode("\r\n", [
			'From: '. $this->app->get('APP_NAME') .' Team',
			'Reply-To: '.$this->app->get('app_email'),
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'X-Priority: 3',
			'nX-MSmail-Priority: high'
		]);
		if(mail($email, $subject, $message, $headers)){
			return true;
		}
	}
}