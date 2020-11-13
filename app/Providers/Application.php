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
        $request = new class(){
            public function __construct(){
                $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            }
            public function all()
            {
                return $this->data;
            }
            public function input(string $var, mixed $value = null)
            {
                return $this->data[$var] ?? $value;
            }
            public function header(string $var)
            {
                return $_SERVER[$var] ?? NULL;
            }
            public function bearerToken(string $authKey="HTTP_AUTHORIZATION")
            {
                $auth = strtok($_SERVER[$authKey] ?? "", "Bearer");
                return trim($auth);
            }
            public function get(string $var)
            {
                return $_GET[$var] ?? NULL;
            }
            public function has(string $var)
            {
                return isset($data[$var]) ? true : false;
            }
            public function upload(string $var)
            {
                $config = (new Application())->config();
                return (new Uploader(
                    $config->get('cdn'),
                    $config->get('ALLOWED_UPLOAD_TYPES'),
                    $config->get('UPLOAD_LIMIT')
                ))->upload($var);
            }
            public function validate(array $rules)
            {
                return Validation::init($this->data)->rules($rules);
            }
            public function htmlSanitize(string $input)
            {
                return  htmlentities($input, ENT_QUOTES, 'UTF-8');
            }
            public function userAgent(string $var = "")
            {
               return preg_replace($regx = '/\/[a-zA-Z0-9.]*/', '', $uagent = $_SERVER['HTTP_USER_AGENT'] ?? $var);
            }
        };
        return $request;
    }

    public function response()
    {
        return new class (){
            public function __construct(){}
            public function send($response, $statusCode = 200, $headers = [])
            {
                foreach ($headers as $key => $value) {
                    header("{$key}: {$value}");       
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code($statusCode);
                echo json_encode($response, JSON_PRETTY_PRINT);
            }
            public function sendAndCache($response, $statusCode = 200, $timeInSeconds)
            {
                if ($timeInSeconds > 0 && ($statusCode ==200 || $statusCode ==201)) {
                    header("Cache-Control: no-transform,public,max-age={$timeInSeconds},s-maxage={$timeInSeconds}");
                }
                header('Content-Type: application/json; charset=utf-8');
                http_response_code($status_code);
                echo json_encode($data, JSON_PRETTY_PRINT);
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
