<?php

namespace App\Providers;

use Seven\Vars\{Strings, Validation};
use Seven\File\Uploader;
use Symfony\Component\HttpFoundation\Response;

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
        $request = new \StdClass();
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $request->input = function (string $var, mixed $value = null) use ($data) {
            return $data[$var] ?? $value;
        };
        $request->header = function(string $var){
            return $_SERVER[$var] ?? NULL;
        };
        $request->bearerToken = function(string $authKey="HTTP_AUTHORIZATION"){
            $auth = strtok($_SERVER[$authKey] ?? "", "Bearer");
            return trim($auth);
        };
        $request->get = function(string $var){
            return $_GET[$var] ?? NULL;
        };
        $request->upload = function(string $var){
            $config = (new Application())->config();
            return (new Uploader(
                $config->get('cdn'),
                $config->get('ALLOWED_UPLOAD_TYPES'),
                $config->get('UPLOAD_LIMIT')
            ))->upload($var);
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
            public function send($response, int $code = 200, $headers = [])
            {
                return $this->response->setStatusCode($code)->setContent($response)->send();
            }
            public function sendAndCache($response, int $code = 200, $timeInSeconds)
            {
                if ($code === 200) {
                    return $this->response->setStatusCode($code)->setContent($response)
                        ->setTtl($timeInSeconds)->send();
                }
                return $this->response->setStatusCode($code)->setContent($response)->send();
            }
        };
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
        return $this->string->timeFromString($str, $this->config()->get('APP_TIMEZONE'));
    }
}
