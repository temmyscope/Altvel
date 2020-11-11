<?php

namespace App\Http;

use Seven\Vars\Strings;

trait StateFulAuth
{



    public static function FindByEmail($email)
    {
        return static::setTable('users')->findFirst(['email' => $email]);
    }

    public static function thisUser()
    {
        global $app;
        $app =  $app->config();
        if (Session::exists($app->get('CURRENT_USER_SESSION_NAME'))) {
            $u = new Auth(Session::get($app->get('CURRENT_USER_SESSION_NAME')));
            return $u;
        }
    }

    public function login($user, $rememberMe)
    {
        global $app;
        $app =  $app->config();
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
        global $app;
        $app =  $app->config();
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
        global $app;
        $app =  $app->config();
        if (COOKIE::exists($app->get('REMEMBER_ME_COOKIE_NAME'))) {
            return static::setTable('user_sessions')->findfirst([
                'user_agent' => Session::uagent_no_version(),
                'session' => Cookie::get($app->get('REMEMBER_ME_COOKIE_NAME'))
            ]);
        }
    }

    public function del()
    {
        global $app;
        $data = static::setTable('user_sessions')->findfirst([
             'user_agent' => Session::uagent_no_version(),
             'session' => COOKIE::get($app->config()->get('REMEMBER_ME_COOKIE_NAME'))
        ]);
        return static::setTable('user_sessions')->delete(['id' => $data->id]);
    }
}
