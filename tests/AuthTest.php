<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../api/middleware/Auth.php";

use Middleware\Auth;

class AuthTest extends TestCase
{

    public function testGenerateJWT()
    {
        $token = Auth::generateJWT(1, 'admin');
        $this->assertNotEmpty($token);
    }

    public function testVerifyToken()
    {
        $token = Auth::generateJWT(1, 'admin');

        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer $token";

        function getallheaders()
        {
            return [
                "Authorization" => $_SERVER['HTTP_AUTHORIZATION']
            ];
        }

        $decoded = Auth::verifyToken();

        $this->assertEquals(1, $decoded->user_id);
        $this->assertEquals('admin', $decoded->role);
    }
}
