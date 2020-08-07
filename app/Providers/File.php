<?php
namespace App\Providers;

use Seven\File\UploaderTrait;

class File
{
	use UploaderTrait;

    protected $_dest;

    protected $_allowed  = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'jpeg' => 'image/jpeg'
    ];
    protected $_limit = 5024768;

    public function __construct()
    {
        $this->_dest = app()->get('cdn');
    }

    public function uploader($var, $dim = []): array
    {
        [$status, $value, $type, $size ] = $this->upload($var, $dim);
        return ['status' => $status, 'value' => str_replace($this->_dest, app()->get('APP_CDN').'/', $value), 'type' => $type, 'size' => $size ];
    }

    public static function saveJson(string $name, array $data, $dir = "")
    {
        return file_put_contents( app()->get('cache').'/'.$name.'.json', json_encode($data));
    }
}