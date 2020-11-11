<?php

namespace App\Http;

use Firebase\JWT\JWT;

trait StateLessAuth
{

    public static function generateUserToken(object $user, bool $long = false): string
    {
        //time of token issued at
        $iat = time();

        //not before in seconds
        $nbf = $iat + 5;

        //expiry time of token in seconds
        $exp = ( $long === true ) ? $iat + 2592000 : $iat + 9000;

        $token = [
          "iat" => $iat, "nbf" => $nbf, "exp" => $exp, "user_id" => $user->id
        ];
        return self::encryptToken($token);
    }

    public static function encryptToken(array $data): string
    {
        global $app;
        return JWT::encode($data, env('JWT_SECRET'), env('JWT_ALG'));
    }

    public static function getValuesFromToken($token)
    {
        try {
            $data = JWT::decode($token, env('JWT_SECRET'), [ env('JWT_ALG') ]);
        } catch (\Exception $e) {
            $data = [];
        }
        return $data;
    }

    public static function isValid($token): bool
    {
        $decoded = self::decomposeToken($token);
        if (!empty($decoded)) {
            return true;
        }
        return false;
    }
    
}
