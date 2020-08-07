<?php 
namespace App\Helpers;

use App\Providers\{Session, Request, Strings};

class HTML{

  /**
   * Prints HTML contents to view
   * functions @return void
  */

  public static function nav(){
    $app = app();
    $app_url = $app->get('APP_URL');
    $user_id = $app->get('CURRENT_USER_SESSION_NAME');

    $var = "<nav class='navbar navbar-expand-md navbar-light bg-white shadow-sm'>
            <div class='container'>
              <a class='navbar-brand' href='".$app_url."'>". $app->get('APP_NAME') ."</a>
              <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent' aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
                  <span class='navbar-toggler-icon'></span>
              </button>
              <div class='collapse navbar-collapse' id='navbarSupportedContent'>
                  <!-- Left Side Of Navbar -->
                  <ul class='navbar-nav mr-auto'>
                  </ul>";
        if(Session::exists($user_id) && (in_array('search', $app->get('USER_NAVBAR')) || in_array('Search', $app->get('USER_NAVBAR')))){
              $var .="<form class='form-inline my-2 my-md-0' method='post' action='".$app_url."/search' enctype='multipart/form-data' accept-charset='UTF-8'>";
              $var .="<input type='hidden' value='".Session::get('csrf')."' name='csrf'>";
              $var .="<input class='form-control' type='text' name='search' placeholder='Search' aria-label='Search' required></form>";
        }
              $var .="<!-- Right Side Of Navbar -->
                  <ul class='navbar-nav ml-auto'>";
                if(Session::exists($user_id)){
                  foreach($app->get('USER_NAVBAR') as $key => $value){
                    $key = ucfirst($key);
                    if ($key !== 'Search'){
                      if(is_array($value)){
                        $var .="<li class='nav-item dropdown show'><a id='navbarDropdown' class='nav-link dropdown-toggle' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' v-pre href='".$app_url."/#'> {$key} <span class='caret'></span></a>
                          <div class='dropdown-menu dropdown-menu-right' aria-labelledby='navbarDropdown'>";
                        foreach ($value as $k => $v) {
                            $url = strtolower($key).'/'.strtolower($v);
                             $var .= "<a class='dropdown-item' href='".$app_url."/{$url}'>{$k}</a>";  
                        }
                        $var .= "</div></li>";
                      }else{
                        $var .="<li class='nav-item'><a class='nav-link' href='".$app_url."/{$value}'> {$key} </a></li>";
                      }
                    }
                  }
                }else{
                  foreach($app->get('GUEST_NAVBAR') as $key => $value){
                    $var .="<li class='nav-item'><a class='nav-link' href='".$app_url."/{$value}'> {$key} </a></li>";
                  }
                }
           $var .="</ul></div></div></nav>";
           return $var;
  }

  public static function card($header = ''){
    return "<div class='card'><div class='card-header'>{$header}</div>".status()."<div class='card-body'>";
  }

  static public function closeCard(){
    echo "</div></div><br>";
  }

  public static function CsrfInput(){
    $csrf = Session::get('csrf');
    return "<br><input type='hidden' value='{$csrf}' name='csrf'>";
  }

  static public function paginate($perpage, $current=''){
    $page = Request::get('page');
    $current = ($current === true) ? true : $page;
    echo"<ul class='pagination' role='navigation'>";
    if($current == 1 || $current == null){
      echo"<li class='page-item'>
            <a class='page-link' href='?page=2' rel='next'>Next &raquo;</a>
      </li>";
    }elseif($current == 2){
      echo"<li class='page-item'>
            <a class='page-link' href='?page=1' rel='prev'>&laquo; Previous</a>
        </li><li class='page-item'>
            <a class='page-link' href='?page=3' rel='next'>Next &raquo;</a>
      </li>";
    }elseif ($current > 2) {
      $next = $current+1;
      $prev= $current -1;
      echo"<li class='page-item'>
            <a class='page-link' href='?page={$prev}' rel='prev'>&laquo; Previous</a>
        </li><li class='page-item'>
            <a class='page-link' href='?page={$next}' rel='next'>Next &raquo;</a>
      </li>";
    }elseif($current == true && $page != null){
      $var = $page - 1;
      echo"<li class='page-item'>
            <a class='page-link' href='?page={$var}' rel='next'>&laquo; Previous</a>
      </li>";
    }
    echo"</ul>";
  
  }

  public static function csrf(){
    $_SESSION["csrf"] = $_SESSION["csrf"] ?? Strings::fixed_length_token(16); 
  }

  public static function generateForm(string $endpoint, array $form, $id='', $extras=''){
  /*-----------------------------------------------------------------------------------------------------------|
  |$form = [ 'name' => [ 'type' => , 'rule' => , 'placeholder' => , 'label' => , 'maxlength'=>, 'value' => ] ];|
  |------------------------------------------------------------------------------------------------------------*/
    $var = "<form method='post' action='".app()->get('APP_URL')."/{$endpoint}' enctype='multipart/form-data' accept-charset='UTF-8' 
    id='{$id}' {$extras}>".self::CsrfInput();

    foreach($form as $key => $value){
      $placeholder = (isset($value['placeholder'])) ? 'placeholder="'.$value['placeholder'].'"' : '';
      $rule = $value['rule'] ?? '';
      $label = $value['label'] ?? ucwords(str_replace('_', ' ', $key));
      $val = $value['value'] ?? '';
      $type = $value['type'] ?? 'text';
      $maxlength = (isset($value['maxlength']) && is_numeric($value['maxlength'])) ? "maxlength='".$value['maxlength']."'" : '';

      switch (strtolower($type)) {
        case 'email':
        case 'number':
        case 'text':
        case 'password':
        case 'file':
          $var .="<div class='form-group row'>
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
          $var .="<div class='form-group row'>
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
          $var .="<div class='form-group row'>
            <label for='{$type}' class='col-md-4 col-form-label text-md-right'> {$label}: </label><div class='col-md-6'>";
            if (is_array($value['value'])) {
              foreach ($value['value'] as $k => $v) {
                $var .= "<input id='{$type}' type='{$type}' name='{$key}' value='{$v}' {$rule}>{$k} &nbsp;";
              }    
            }else{
                $var .= "<input id='{$type}' type='{$type}' name='{$key}' value='{$v}' {$rule}>{$k} &nbsp;";
            }                     
          $var .="</div>
            </div>";
          break;
        case 'select':
          $var .="<div class='form-group row'>
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
    return $var."</form>";
  }
}
?>