<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function authenticateMiddleware() {
    $headers = getallheaders();

    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            $key = new Key('secret_key', 'HS256');
            $decoded = JWT::decode($token, $key);

            // TODO: perform additional checks here, like expiration time
            return true;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
    }

    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
