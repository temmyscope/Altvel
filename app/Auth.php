<?php

namespace App;

use App\Providers\{
    Application
};
use Seven\Model\Model;

class Auth extends Model
{

    public $id;
    protected static $table = 'users';
    protected static $fulltext = [];

    public function __construct($user = '')
    {
        global $app;        if ($user != '' and is_int($user)) {
            $this->id = $user;
        }
    }

    public static function FindByEmail($email)
    {
        return static::setTable('users')->findFirst(['email' => $email]);
    }

    public static function thisUser()
    {
        $app = app();
        if (Session::exists($app->get('CURRENT_USER_SESSION_NAME'))) {
            $u = new Auth(Session::get($app->get('CURRENT_USER_SESSION_NAME')));
            return $u;
        }
    }

    public function login($user, $rememberMe)
    {
        $app = app();
        Session::set($app->get('CURRENT_USER_SESSION_NAME'), $this->id);
        if ($rememberMe) {
            $hash = Strings::limit(Strings::rand_token(), 224);
            $user_agent = Session::uagent_no_version();
            Cookie::set($app->get('REMEMBER_ME_COOKIE_NAME'), $hash);
            static::setTable('user_sessions')->softDelete([
                'user_id' => $this->id, 'user_agent' => $user_agent
            ]);
            static::setTable('user_sessions')->insert([
                        'session' => $hash, 'user_agent' => $user_agent, 'user_id' => $this->id
            ]);
        }
    }

    public static function loginUserFromCookie()
    {
        $userSession = (new Auth())->getFromCookie();
        if (isset($userSession->user_id) && (int)$userSession->user_id > 0) {
            $user = new self((int) $userSession->user_id);
            $user->login(self::setTable('users')->findfirst([ 'id' => $userSession->user_id ]), true);
        }
    }
 
    public function logout()
    {
        $app =  app();
        Session::delete($app->get('CURRENT_USER_SESSION_NAME'));
        Session::destroy();
        if (Cookie::exists($app->get('REMEMBER_ME_COOKIE_NAME'))) {
            Cookie::delete($app->get('REMEMBER_ME_COOKIE_NAME'));
            $this->del();
        }
        return true;
    }

    public function getFromCookie()
    {
        $app =  app();
        if (COOKIE::exists($app->get('REMEMBER_ME_COOKIE_NAME'))) {
            return static::setTable('user_sessions')->findfirst([
                'user_agent' => Session::uagent_no_version(),
                'session' => Cookie::get($app->get('REMEMBER_ME_COOKIE_NAME'))
            ]);
        }
    }

    public function del()
    {
        $data = static::setTable('user_sessions')->findfirst([
             'user_agent' => Session::uagent_no_version(),
             'session' => COOKIE::get(app()->get('REMEMBER_ME_COOKIE_NAME'))
        ]);
        return static::setTable('user_sessions')->delete(['id' => $data->id]);
    }
}
