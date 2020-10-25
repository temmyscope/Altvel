<?php

namespace App\Providers;

use Seven\Vars\{Strings, Validation};
use Symfony\Component\HttpFoundation\{Request, Response};

class Application
{
  
    public function __construct()
    {
        if (!getenv('APP_DEBUG')) {
            $this->setLogger();
        }
        $this->string = new Strings(getenv('APP_ALG'), getenv('APP_SALT'), getenv('APP_IV'));
    }

    private function setLogger()
    {
        ini_set("log_errors", true);
        ini_set("error_log", __DIR__ . '/../../error.log');
    }

    public function cookie($value='')
    {
        return new class(){
            public function exists($name)
            {
                return (isset($_COOKIE[$name])) ? true : false ;
            }

            public function get($name)
            {
                return $_COOKIE[$name] ?? null;
            }

            public function set($name, $value)
            {
                $time = time() + (app()->get('REMEMBER_ME_COOKIE_EXPIRY'));
                if (setcookie($name, $value, $time, '/')) {
                    return true;
                }
                return false;
            }

            public function delete($name)
            {
                self::set($name, '', time() - 3600);
            }
        };
    }

    public function session($value='')
    {
        return new class(){
            public static function exists($name)
            {
                return (isset($_SESSION[$name])) ? true : false ;
            }
            public static function get($name)
            {
                return $_SESSION[$name] ?? null;
            }
            public static function set($name, $value)
            {
                return $_SESSION[$name] = $value;
            }
            public static function delete($name)
            {
                if (self::exists($name)) {
                    unset($_SESSION[$name]);
                }
            }
            public static function destroy()
            {
                $_SESSION = array();
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                }
                session_destroy();
            }
        };
    }

    public function file($value='')
    {
        return ;
    }

    public function request()
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true) ?? $_REQUEST;
        $request->input = function (string $var, mixed $value = null) use ($data) {
            return $data[$var] ?? $value;
        };
            $request->has = function (string $var) use ($data) {
                return isset($data[$var]) ? true : false;
            };
            $request->validate = function (array $rules) use ($data) {
                return Validation::init($data)->rules($rules);
            };
            $request->all = function () use ($data) {
                return $data;
            };
            $request->userAgent = function () use () {
                return preg_replace($regx='/\/[a-zA-Z0-9.]*/', '', $uagent = $_SERVER['HTTP_USER_AGENT']);
            };
            return $request;
    }

    public function response()
    {
            return new class (){
                public function __construct()
                {
                    $this->response = new Response();
                }
                public function send(mixed $response, int $code = 200, $headers = [])
                {
                        return $this->response->setStatusCode($code)->setContent($response)->send();
                }public function json(mixed $response, int $code = 200, $headers = [])
                {
                        return $this->send($response, $code, $headers);
                }
                public function sendAndCache(mixed $response, int $code = 200, $timeInSeconds)
                {
                    if ($code === 200) {
                            return $this->response->setStatusCode($code)->setContent($response)
                            ->setTtl($timeInSeconds)->send();
                    }
                    return $this->response->setStatusCode($code)->setContent($response)->send();
                }
            };
    }

    public function url(): string
    {
            return $this->config->get('APP_URL');
    }

    public function decrypt(string $str): string
    {
        return $this->string->decrypt($str);
    }

    public function encrypt(string $str): string
    {
        return $this->string->encrypt($str);
    }

    public function config($config_array = [])
    {
        return new class ($config_array){
            public function __construct($config_array)
            {
                $this->config = require __DIR__ . '/../../config/app.php';
                $this->config = array_merge($this->config, $config_array);
            }
            public function get(string $var)
            {
                return $this->config[$var] ?? null;
            }
            public function all()
            {
                return $this->config;
            }
        };
    }

    public function dateTime(string $str = 'now')
    {
        return $this->string->time_from_string($str, $this->config()->get('APP_TIMEZONE'));
    }
}
