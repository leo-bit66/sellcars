<?php

require_once __DIR__ . "/Database.php";

class User extends Database {

    public function getUserByEmail($email) {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param('s', $email); // 's' indicates a string parameter
            $stmt->execute();
            $result = $stmt->get_result();

            // Fetch the user as an associative array
            return $result->fetch_assoc();
        } catch (mysqli_sql_exception $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    public function validatePassword($inputPassword, $hashedPassword) {
        return md5($inputPassword) === $hashedPassword;

        // must implement comprehensive password validation,for example, using password_verify functions:
        // return password_verify($inputPassword, $hashedPassword);
    }

}
