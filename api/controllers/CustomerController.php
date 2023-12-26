<?php

class CustomerController {

    private $customerHandler;
    private $userHandler;

    public function __construct() {
        $this->customerHandler = new Customer();
        $this->userHandler = new User();
    }

    public function showCustomersPage() {
        //   if ($this->isLoggedIn()) {
        require_once __DIR__ . "/../../frontend/customers.html";
        //     exit();
        //  }
    }

    public function getCustomer($id) {
        $customerData = $this->customerHandler->getCustomerById($id);

        if ($customerData !== false) {
            $this->jsonResponse($customerData);
        } else {
            $this->jsonResponse(['error' => 'Customer not found'], 404);
        }
    }

    public function getAllCustomers() {
        $customersData = $this->customerHandler->getAllCustomers();
        $this->jsonResponse($customersData);
    }

    public function updateCustomer($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $this->customerHandler->updateCustomer($id, $data);
        if ($result['success']) {
            $this->jsonResponse($result);
        } else {
            $this->jsonResponse($result, 500); // Internal Server Error
        }
    }

    public function deleteCustomer($id) {
        $result = $this->customerHandler->deleteCustomer($id);
        if ($result['success']) {
            $this->jsonResponse($result);
        } else {
            $this->jsonResponse($result, 500);
        }
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

}
