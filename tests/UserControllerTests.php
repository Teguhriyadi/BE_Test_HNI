<?php

use PHPUnit\Framework\TestCase;

require_once "api/controllers/UserController.php";
require_once "api/config/koneksi.php";

class UserControllerTest extends TestCase
{

    public function testLoginSuccess()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';

        ob_start();
        UserController::login();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertArrayHasKey('token', $response);
        $this->assertFalse($response['error']);
    }

    public function testLoginFailed()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'wrong@example.com';
        $_POST['password'] = 'wrongpassword';

        ob_start();
        UserController::login();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['error']);
        $this->assertEquals('User tidak ditemukan', $response['message']);
    }
}
