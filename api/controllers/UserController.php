<?php
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../middleware/Auth.php';

use Middleware\Auth;

class UserController
{
    public static function login()
    {
        global $conn;
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        $response = [];

        if (!isset($data['email']) || empty(trim($data['email']))) {
            echo json_encode(["error" => true, "message" => "Email wajib diisi!"]);
            exit();
        }

        if (!isset($data['password']) || empty(trim($data['password']))) {
            echo json_encode(["error" => true, "message" => "Password wajib diisi!"]);
            exit();
        }

        $email = trim($data['email']);
        $password = trim($data['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => true, "message" => "Format email tidak valid!"]);
            exit();
        }

        $email = mysqli_real_escape_string($conn, $email);
        $query = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $token = Auth::generateJWT($user['id'], $user['role']);

                echo json_encode([
                    "error" => false,
                    "message" => "Login berhasil!",
                    "token" => $token,
                    "user" => [
                        "id" => $user['id'],
                        "name" => $user['name'],
                        "email" => $user['email'],
                        "role" => $user['role']
                    ]
                ]);
            } else {
                echo json_encode(["error" => true, "message" => "Password salah"]);
            }
        } else {
            echo json_encode(["error" => true, "message" => "User tidak ditemukan"]);
        }
    }

    public static function getAllUsers()
    {
        global $conn;
        Auth::verifyToken();

        $query = "SELECT id, name, email, role FROM users WHERE deleted_at IS NULL";
        $result = $conn->query($query);

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode($users);
    }

    public static function getUserById($id)
    {
        global $conn;

        $id = mysqli_real_escape_string($conn, $id);

        $query = "SELECT id, name, email, role FROM users WHERE id = '$id' LIMIT 1";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User tidak ditemukan"]);
        }
    }

    public static function createUser()
    {
        global $conn;
        $data = json_decode(file_get_contents("php://input"), true);

        $name = isset($data['name']) ? trim($data['name']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        $role = isset($data['role']) ? trim($data['role']) : '';

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            http_response_code(400);
            echo json_encode(["message" => "Semua field harus diisi"]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["message" => "Format email tidak valid"]);
            return;
        }

        $emailCheckQuery = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
        $emailCheckResult = $conn->query($emailCheckQuery);
        if ($emailCheckResult->num_rows > 0) {
            http_response_code(400);
            echo json_encode(["message" => "Email sudah terdaftar"]);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(["message" => "Password harus minimal 6 karakter"]);
            return;
        }

        $allowedRoles = ['admin', 'user'];
        if (!in_array(strtolower($role), $allowedRoles)) {
            http_response_code(400);
            echo json_encode(["message" => "Role harus 'admin' atau 'user'"]);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', '$role')";
        $result = $conn->query($query);

        if ($result) {
            http_response_code(201);
            echo json_encode(["message" => "User berhasil dibuat"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Gagal membuat user"]);
        }
    }

    public static function updateUser($id)
    {
        global $conn;
        Auth::verifyToken();

        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['name'];
        $email = $data['email'];
        $role = $data['role'];

        $query = "UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id";

        if ($conn->query($query)) {
            echo json_encode(["message" => "User berhasil diupdate"]);
        } else {
            echo json_encode(["message" => "Gagal mengupdate user"]);
        }
    }

    public static function deleteUser($id)
    {
        global $conn;
        Auth::verifyToken();

        $query = "UPDATE users SET deleted_at=NOW() WHERE id=$id";

        if ($conn->query($query)) {
            echo json_encode(["message" => "User berhasil dihapus"]);
        } else {
            echo json_encode(["message" => "Gagal menghapus user"]);
        }
    }
}
