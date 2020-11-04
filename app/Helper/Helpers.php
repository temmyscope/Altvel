<?php

use Seven\Vars\Strings;
use Jenssegers\Blade\Blade;

function curl($url)
{
    return new class ($url){
        protected $_curl = [
            'url' => '',
            'data' => [],
            'headers' => [],
            'time_out' => 200,
            'cookie_file' => '',
            'cookie_jar' => '',
            'method' => 'GET',
            'ret' => true,
        ];
        protected $_result, $_errors;
        function __construct($url)
        {
            $this->_curl['url'] = filter_var($url, FILTER_SANITIZE_URL);
        }
        public function setData(array $postdata)
        {
            $this->_curl['data'] = json_encode($postdata);
            return $this;
        }
        public function setHeaders($headers)
        {
            $this->_curl['headers'] = $headers;
            return $this;
        }
        public function setHeader($headers)
        {
            return $this->setHeaders($headers);
        }
        public function setSession($cookiefile)
        {
            $this->_curl['cookie_file'] = $cookiefile;
            return $this;
        }
        public function saveSession($cookiefile)
        {
            $this->_curl['cookie_jar'] = $cookiefile;
            return $this;
        }
        public function setMethod(string $method)
        {
            $this->_curl['method'] = strtoupper($method);
            return $this;
        }
        public function isReturnable(bool $val = true)
        {
            $this->_curl['ret'] = $val;
            return $this;
        }
        public function setTimeOut($time = 200)
        {
            $this->_curl['time_out'] = $time;
            return $this;
        }
        public function send()
        {
            array_push($this->_curl['headers'], 'Content-Type: application/json');
            $ch = curl_init($this->_curl['url']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->_curl['method']));
            if (!empty($this->_curl['data'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_curl['data']);
            }
            if (!empty($this->_curl['cookie_jar']) && !empty($this->_curl['cookie_file'])) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_curl['cookie_jar']);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_curl['cookie_file']);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->_curl['ret']);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_curl['time_out']);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->_curl['time_out']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_curl['headers']);
            $this->_result = curl_exec($ch);
            $this->_errors = curl_error($ch);
            curl_close($ch);
            //dnd($this->_result);
            if ($this->_errors) {
                return false;
            } else {
                return $this->_result;
            }
        }
        public function result()
        {
            return $this->_result;
        }
        public function errors()
        {
            return $this->_errors;
        }
    };
}

function app()
{
    return (new App\Providers\Application());
}

function dnd($var)
{
    echo "<pre>";
        var_dump($var);
    echo "<pre>";
    die();
}

function resume()
{
    global $app;
    $rdr = app()->config()->get('REDIRECT');
    if ($app->session()->exists($rdr)) {
        $route = $app->session()->get($rdr);
        $app->session()->delete($rdr);
        redirect(app()->config()->get('APP_URL'), $route);
    } else {
        redirect('home');
    }
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

function status()
{
    global $app;
    if ($app->session()->exists('errors')) {
        $html = "<div><ul class='alert alert-danger'>";
        $errors = $app->session()->get('errors');
        foreach ($errors as $error) {
            if (is_array($error)) {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error[0] . '</li><br/>';
            } else {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error . '</li><br/>';
            }
        }
        $html .= '</ul></div>';
        $app->session()->delete('errors');
        return $html;
    }
    if ($app->session()->exists('warnings')) {
        $html = "<div><ul class='alert alert-warning'>";
        $errors = $app->session()->get('warnings');
        foreach ($errors as $error) {
            if (is_array($error)) {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error[0] . '</li><br/>';
            } else {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error . '</li><br/>';
            }
        }
        $html .= '</ul></div>';
        $app->session()->delete('warnings');
        return $html;
    }
    if ($app->session()->exists('success')) {
        $html = "<div><ul class='alert alert-success'>";
        $errors = $app->session()->get('success');
        foreach ($errors as $error) {
            if (is_array($error)) {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error[0] . '</li><br/>';
            } else {
                $html .= '<li style="list-style: none;text-align: center; color: white;">' . $error . '</li><br/>';
            }
        }
        $html .= '</ul></div>';
        $app->session()->delete('success');
        return $html;
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
            parent::__construct(app()->get('view'), app()->get('cache'));
        }
        public function rend($viewName, $data = [])
        {
            try {
                echo $this->render($viewName, [ 'dataSource' => $data ]);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    };
    $v->rend($view, $data);
}

function html()
{
    return new Class (){
        public static function generateForm(string $endpoint, array $form, $id = '', $extras = '')
        {
            /*-----------------------------------------------------------------------------------------------------------|
            |$form = [ 'name' => [ 'type' => , 'rule' => , 'placeholder' => , 'label' => , 'maxlength'=>, 'value' => ] ];|
            |------------------------------------------------------------------------------------------------------------*/
            $csrf = $_SESSION["csrf"] = $_SESSION["csrf"] ?? Strings::fixed_length_token(16);
            $var = "<form method='post' action='" . app()->config()->get('APP_URL') .
                  "/{$endpoint}' enctype='multipart/form-data' accept-charset='UTF-8' 
                  id='{$id}' {$extras}><br><input type='hidden' value='{$csrf}' name='csrf'>";

            foreach ($form as $key => $value) {
                $placeholder = (isset($value['placeholder'])) ? 'placeholder="' . $value['placeholder'] . '"' : '';
                $rule = $value['rule'] ?? '';
                $label = $value['label'] ?? ucwords(str_replace('_', ' ', $key));
                $val = $value['value'] ?? '';
                $type = $value['type'] ?? 'text';
                $maxlength = (isset($value['maxlength']) && is_numeric($value['maxlength'])) ? "maxlength='" . $value['maxlength'] . "'" : '';

                switch (strtolower($type)) {
                    case 'email':
                    case 'number':
                    case 'text':
                    case 'password':
                    case 'file':
                        $var .= "<div class='form-group row'>
                    <label for='{$type}' class='col-md-4 col-form-label text-md-right'> {$label}: </label>
                    <div class='col-md-6'>
        <input id='{$type}' type='{$type}' class='form-control' name='{$key}' {$placeholder} {$maxlength} {$rule} value='{$val}'>                        
                    </div>
                    </div>";
                        break;
                    case 'hidden':
                        $var .= "<input type='hidden' value='{$val}' name='{$key}'> ";
                        break;
                    case 'submit':
                        $displayName = ucfirst($label);
                        $var .= "<div class='form-group row'>
                    <div class='col-md-8 offset-md-4'>
                      <button type='submit' class='btn btn-primary' {$rule}> {$displayName} </button> 
                    </div>
                  </div>";
                        break;
                    case 'rememberme':
                    case 'remember_me':
                        $var .= "<div class='form-group row'>
                      <div class='col-md-6 offset-md-4'>
                          <div class='form-check'>
                              <input class='form-check-input' type='checkbox' name='remember_me' id='remember' >

                              <label class='form-check-label' for='remember'>
                                  Remember Me
                              </label>
                          </div>
                      </div>
                  </div><br/>
                  ";
                        break;
                    case 'textarea':
                        $var .= "<div class='form-group row'>
                    <label for='{$key}' class='col-md-4 col-form-label text-md-right'> {$label}: </label>
                    <div class='col-md-6'><textarea name='{$key}' class='form-control' {$placeholder} {$rule}>{$val}</textarea></div>
                  </div>";
                        break;

                    case 'checkbox':
                    case 'radio':
                        $var .= "<div class='form-group row'>
                    <label for='{$type}' class='col-md-4 col-form-label text-md-right'> {$label}: </label><div class='col-md-6'>";
                        if (is_array($value['value'])) {
                            foreach ($value['value'] as $k => $v) {
                                $var .= "<input id='{$type}' type='{$type}' name='{$key}' value='{$v}' {$rule}>{$k} &nbsp;";
                            }
                        } else {
                            $var .= "<input id='{$type}' type='{$type}' name='{$key}' value='{$v}' {$rule}>{$k} &nbsp;";
                        }
                        $var .= "</div>
                    </div>";
                        break;
                    case 'select':
                        $var .= "<div class='form-group row'>
                    <label for='{$type}' class='col-md-4 col-form-label text-md-right'> {$label}: </label>
                    <div class='col-md-6'>
                    <select name='{$key}' class='form-control' $rule>";
                        if (is_array($value['value'])) {
                            foreach ($value['value'] as $k => $v) {
                                $var .= "<option id='{$type}' value='{$v}'> {$k} </option><br>";
                            }
                        }
                        $var .= "</select></div>
                    </div>";
                        break;
                }
            }
            return $var . "</form>";
        }
    };
}
