<?php

class Database {

    protected $connection = null;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->connection = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE_NAME);

            if ($this->connection->connect_error) {
                throw new Exception("Could not connect to database: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8"); // Set the character set if needed
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function select($query = "", $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            $result = $stmt->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return false;
    }

    public function execute($query = "", $params = []) {
        try {
            $stmt = $this->executeStatement($query, $params);
            $stmt->close();
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function executeStatement($query = "", $params = []) {
        try {
            $stmt = $this->connection->prepare($query);

            if ($stmt === false) {
                throw new Exception("Unable to prepare statement: " . $this->connection->error);
            }
            if ($params) {
                $this->bindParams($stmt, $params);
            }
            $stmt->execute();

            if ($stmt->errno !== 0) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }
            
            return $stmt;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function bindParams($stmt, $params) {
        $types = "";
        $bindParams = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= "i";
            } elseif (is_float($param)) {
                $types .= "d";
            } else {
                $types .= "s";
            }

            $bindParams[] = $param;
        }

        $stmt->bind_param($types, ...$bindParams);
    }

    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    public function getInsertId() {
        return $this->connection->insert_id;
    }

    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

}
