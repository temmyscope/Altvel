<?php

namespace App\Providers;

use Seven\File\{UploaderTrait, UploaderInterface};

class File implements UploaderInterface{

    use UploaderTrait;

    protected $destination = __DIR__.'/public/cdn';

    protected $allowedTypes = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'jpeg' => 'image/jpeg'
    ];

    protected $sizeLimit = 5024768;
    
}
