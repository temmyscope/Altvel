<?php
use Jenssegers\Blade\Blade;

function compareSpeed(...$args)
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
            echo "<pre style='background-color:black;text-color:green;>";
            echo "Time elapsed for testcase <b>{$key}</b> is {$time_elapsed}";
            echo "Memory used for testcase <b>{$key}</b> is {$memory_used}";
            echo "<pre>";
        }
    } else {
        throw new Exception("Testcases must be atleast 2", 1);
    }
}

function app()
{
    global $app;
    return $app;
}

function config(){
    return app()->config();
}

function app_url(){
    return app()->config()->get('APP_URL');
}

function dnd($var)
{
    echo "<pre style='background-color:black;color:green;font-size:2.8em;'>";
    var_dump($var);
    echo "<pre>";
    die();
}

function pusher($tokens = [], string $msg)
{
    $msg = [
        'title' => app()->config()->get('APP_NAME') . " Notification",
        'body'  => $msg,
        'icon'  => app()->config()->get('APP_PUSH_ICON')
    ];
    return curl('https://fcm.googleapis.com/fcm/send')
        ->setMethod('POST')->setHeaders([
            'Authorization: key=' . app()->config()->get('firebase_token'),
            'Content-Type: Application/json'
        ])->setData(['registration_ids' => $tokens_array, 'data' => $msg ])
        ->send();
}

function mailer($email, $subject, $message)
{
    $headers = implode("\r\n", [
        'From: ' . app()->config()->get('APP_NAME') . ' Team',
        'Reply-To: ' . app()->config()->get('app_email'),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'X-Priority: 3',
        'nX-MSmail-Priority: high'
    ]);
    if (mail($email, $subject, $message, $headers)) {
        return true;
    }
}

/**
*   @param formats may vary e.g. controllerName@endpoint; controllerName.endpoint; controllerName/endpoint;
*/
function route($var): string
{
    $var = str_replace('@', '/', $var);
    $var = str_replace('.', '/', $var);
    $var = str_ireplace('controller', '', $var);
    return app()->config()->get('APP_URL') . '/' . $var;
}

function view($view, $data = []): void
{
    $v = new class () extends Blade{
        public function __construct()
        {
            parent::__construct(config()->get('view'), config()->get('cache'));
        }
        public function rend($viewName, $data)
        {
            try {
                echo $this->render($viewName, $data);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    };
    if (!class_exists(Blade::class)) {
        dnd("You need to install jenssegers/blade library to use the 'view' helper");
    }
    $v->rend($view, $data);
}