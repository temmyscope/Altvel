<?php

namespace App;

use Seven\Model\Model;
use App\Http\{StateFulAuth, StateLessAuth};

class Auth extends Model
{

    public $id;
    protected static $table = 'users';
    protected static $fulltext = [];

    public function __construct($user = '')
    {
        global $app;        
        if ($user != '' and is_int($user)) {
            $this->id = $user;
        }
    }

    
}
