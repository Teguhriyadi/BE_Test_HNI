<?php

namespace Middleware;

require __DIR__ . '/../../vendor/autoload.php';

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private static $secret_key = "supersecret";

    public static function generateJWT($user_id, $role)
    {
        $payload = [
            "user_id" => $user_id,
            "role" => $role,
            "exp" => time() + (60 * 60)
        ];
        return JWT::encode($payload, self::$secret_key, 'HS256');
    }

    private static function getHeadersFromServer()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }


    public static function verifyToken()
    {
        $headers = function_exists('getallheaders') ? getallheaders() : self::getHeadersFromServer();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            die(json_encode(["message" => "Token diperlukan"]));
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        try {
            return JWT::decode($token, new Key(self::$secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            die(json_encode(["message" => "Token tidak valid"]));
        }
    }
}
