<?php

class UploadController {

    private $csvFileUploader;

    public function __construct() {
        $this->csvFileUploader = new CSVFileUploader();
    }

    public function uploadCustomers() {
        $this->handleFileUpload('customers');
    }

    public function uploadAddresses() {
        $this->handleFileUpload('addresses');
    }

    public function uploadContactPersons() {
        $this->handleFileUpload('contact_persons');
    }

    private function handleFileUpload($uploadType) {
        try {
            // Validate upload type
            $allowedTypes = ['customers', 'contact_persons', 'addresses'];
            if (!in_array($uploadType, $allowedTypes)) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Invalid upload type.'], 400);
                return;
            }

            // Check if the file is set and not empty
            if (!isset($_FILES["csvFile"]) || empty($_FILES["csvFile"]["name"])) {
                $this->jsonResponse(['status' => 'error', 'message' => 'No file uploaded or the file is empty.'], 400);
                return;
            }

            $csvFile = $_FILES["csvFile"]["tmp_name"];

            // Handle file upload based on the specified type
            switch ($uploadType) {
                case 'customers':
                    $response = $this->csvFileUploader->uploadCustomers($csvFile);
                    break;
                case 'contact_persons':
                    $response = $this->csvFileUploader->uploadContactPersons($csvFile);
                    break;
                case 'addresses':
                    $response = $this->csvFileUploader->uploadAddresses($csvFile);
                    break;
                default:
                    $this->jsonResponse(['status' => 'error', 'message' => 'Invalid upload type.'], 400);
                    return;
            }

            $statusCode = ($response['status'] === 'error') ? 400 : 200;
            // Return success or error response
            $this->jsonResponse($response, $statusCode);
        } catch (Exception $e) {
            // Return error response
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        //exit();
    }

    public function showUploadPage() {
        // TODO
        // ...
    }

}
