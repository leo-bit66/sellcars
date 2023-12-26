<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function validateLogin() {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'];
        $password = $data['password'];

        $user = $this->userModel->getUserByEmail($username);

        if (!isset($user)) {
            // User not found
            $this->jsonResponse(['status' => 'error', 'message' => 'User not found']);
        } elseif ($this->userModel->validatePassword(trim($password), $user['password_hash'])) {
            // Valid login
            $this->generateTokenResponse($user);
        } else {
            // Invalid password
            $this->jsonResponse(['status' => 'error', 'message' => 'Invalid password']);
        }
    }

    public function showLoginForm() {
        require_once __DIR__ . "/../../frontend/login.html";
    }

    public function getCurrentUser() {

        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);

            try {
                $decoded = $this->decodeToken($token);
                $user = $this->userModel->getUserByEmail($decoded->username);

                if ($user) {
                    $userData = [
                        'customerName' => $user['first_name'] . ' ' . $user['last_name'],
                        'lastLogin' => $user['updated_at'],
                    ];
                    $this->jsonResponse($userData);
                } else {
                    $this->jsonResponse(['message' => 'User not found'], 404);
                }
            } catch (\Exception $e) {
                $this->jsonResponse(['message' => 'Invalid token'], 401);
            }
        } else {
            $this->jsonResponse(['message' => 'Authorization header not present'], 401);
        }
    }

    private function generateTokenResponse($user) {
        $jwtPayload = [
            "user_id" => $user['id'],
            "username" => $user['email'], // Assuming email is unique
            "role" => "user"
        ];

        $jwt = JWT::encode($jwtPayload, 'secret_key', 'HS256');
        $this->jsonResponse(['status' => 'success', 'token' => $jwt, 'redirect' => '/sellcars/customers-page']);
    }

    private function decodeToken($token) {
        $key = new Key('secret_key', 'HS256');
        return JWT::decode($token, $key);
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        // exit();
    }

    private function isLoggedIn() {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);

            try {
                $this->decodeToken($token);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

}
