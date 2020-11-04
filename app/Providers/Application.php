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

    public function cookie()
    {
        return new class (){
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
                $this->set($name, '', time() - 3600);
            }
        };
    }

    public function session()
    {
        return new class (){
            public function exists($name)
            {
                return (isset($_SESSION[$name])) ? true : false ;
            }
            public function get($name)
            {
                return $_SESSION[$name] ?? null;
            }
            public function set($name, $value)
            {
                return $_SESSION[$name] = $value;
            }
            public function delete($name)
            {
                if ($this->exists($name)) {
                    unset($_SESSION[$name]);
                }
            }
            public function destroy()
            {
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                }
                session_destroy();
            }
        };
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
        $request->userAgent = function () {
            return preg_replace($regx = '/\/[a-zA-Z0-9.]*/', '', $uagent = $_SERVER['HTTP_USER_AGENT'] ?? "");
        };
        $request->htmlSanitize = function (string $input) {
            return  htmlentities($input, ENT_QUOTES, 'UTF-8');
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
            }
            public function json(mixed $response, int $code = 200, $headers = [])
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
        return $this->config()->get('APP_URL');
    }

    public function decrypt(string $str): string
    {
        return $this->string->decrypt($str);
    }

    public function encrypt(string $str): string
    {
        return $this->string->encrypt($str);
    }

    public function config()
    {
        return new class (){
            public function __construct()
            {
                $this->config = include __DIR__ . '/../../config/app.php';
            }
            public function get(string $var)
            {
                return $this->config[$var] ?? $_ENV[$var] ?? null;
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

    public function compareSpeed(...$args)
    {
        if (count($args) > 1) {
            foreach ($args as $key => $value) {
                $time_start = microtime(true);
                $mem_start = memory_get_usage(true);
                for ($i = 0; $i <= 10000; $i++) {
                    call_user_func_array($args[$key]['function'], $args[$key]['parameters']);
                }
                $mem_end = memory_get_usage(true);
                $time_end = microtime(true);
                $time_elapsed = $time_end - $time_start;
                $memory_used = $mem_end - $mem_start;
                echo "<pre>";
                echo "Time elapsed for testcase <b>{$key}</b> is {$time_elapsed}";
                echo "Memory used for testcase <b>{$key}</b> is {$memory_used}";
                echo "<pre>";
            }
        } else {
            throw new Exception("Testcases must be atleast 2", 1);
        }
    }
}
