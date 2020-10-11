<?php
namespace App\Controllers;

use App\Auth;
use Sevens\Vars\Validation;
use App\Providers\{
	Notification, Strings, Session, Request
};

class AuthController extends Controller{

	public function indexEndPoint(){
		view('auth.index');
	}

	public function aboutEndPoint($request, $response){
		if( $this->request->isSecured() ){
			$this->request->validate([
				'email' => [ 'display' => 'E-mail', 'required' => true ],
				'feedback' => [ 'display' => 'FeedBack', 'required' => true],
			]);
			if($this->request->passed()){
				$contact = Auth::setTable('contact_us')->insert([ 'email' => post('email'),
					'feedback' => post('feedback'),	'created_at' => Strings::time_from_string()
				]);
				if(is_numeric($contact)){
					$this->request->status('success', 'We have received your message. Thanks.');
				}
			}
		}
		view('auth.about');	
	}

	public function registerEndPoint(Notification $notify){
		if($this->request->isSecured()){
			$this->request->validate([
				'email' => [ 'display' => 'E-mail', 'required' => true, 'valid_email' => true, 'unique' => true ],
				'password' => [ 'display' => 'Password', 'required' => true, 'min' => 8 ],
				'verify_password' => [ 'display' => 'Verify Password', 'required' => true, 'min' => 8, 'is_same_as' => 'password' ],
				'name' => [ 'display' => 'Name', 'required' => true]
			], 'users');
			$strings = new Strings();
			if($this->request->passed()){
				$key = Strings::limit( Strings::makeSafe(Strings::fixed_length_token(16).Strings::get_unique_name( post('email')), 140) );
				$reg = Auth::insert([
					'name' => post('name'),
					'email' => post('email'),
					'password' => Strings::hash( app()->get('APP_SALT') . post('password') ),
					'activation' => $key,
					'created_at' => Strings::time_from_string()
				]);
				if( is_numeric($reg) ){
					$notify->AccountCreated( post('email'), $key);
					$this->request->status('success', 'Your account has beeen created. check your e-mail to activate your account.');
					redirect('login');
				}
			}
		}
		view('auth.register');
	}

	public function activateEndPoint(...$args){
		if( Auth::update(['verified' => 'true'], [ 'email' => get('email'), 'activation' => $args[0] ]) ){ 
			$this->request->status('success', 'Your Account has been created and Activated. Please Login.');
			redirect('login');
		}else{
			redirect('errors/bad');
		}
	}

	public function loginEndPoint(Request $request){
		if($request->isSecured()){
			if( $request->validation([
				'email' => [ 'display' => 'E-mail', 'required' => true ],
				'password' => [ 'display' => 'Password', 'required' => true, 'min' => 8 ]
			])->passed() ){
				$user = Auth::findByEmail(post('email'));
				$password = app()->get('APP_SALT').post('password');
				if($user && Strings::verify_hash($password, $user->password)){
					$remember = (is_null(post('remember_me'))) ? false : true;
					$auth = new Auth((int) $user->id);
					$auth->login($user, $remember);
					resume();
				}else{
					$this->request->status('error', 'There is an error with your email or password.');
				}
			}
		}
		view('auth.login');
	}

	public function forgot_passwordEndPoint(Strings $strings, Notification $notify){
		if($this->request->isSecured()){
			$this->request->validate([
				'email' => [ 'display' => 'E-mail', 'required' => true ],
			]);
			if($this->request->passed() ){
				$user = Auth::findfirst([ 'email' => post('email') ]);
				if( !empty($user) ){
					$salt = app()->get('APP_SALT');
					$tmp_password = $strings->rand(random_int(8, 16));
					Users::update([ 'password' => Strings::hash($salt.$tmp_password) ], ['id' => $user->id ]);
					$notify->AccountCreated(post('email'),  $tmp_password );
					$this->request->status('success', 'A password reset link has been sent to your E-mail.');
				}else{
					$this->request->status('error', 'The E-mail entered does not exist.');
				}
			}
		}
		view('auth.forgot_password');
	}

	public function logoutEndPoint(){
		if(Auth::thisUser() !== NULL && Auth::thisUser()->logout())
			redirect('');
	}
}