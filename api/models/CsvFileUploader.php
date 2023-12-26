<?php

require_once __DIR__ . "/Database.php";
include __DIR__ . '/../includes/CSVValidator.php';

class CSVFileUploader extends Database {

    public function uploadCustomers($csvFile) {
        return $this->processCSVFile($csvFile, ['intnr', 'type', 'contact_persons[0].first_name', 'contact_persons[0].last_name', 'contact_persons[0].email',
                    'contact_persons[0].mobile_phone', 'contact_persons[0].birth_date', 'addresses[0].company_name', 'addresses[0].country', 'addresses[0].city',
                    'addresses[0].zip', 'addresses[0].fax', 'addresses[0].phone', 'addresses[0].street', 'addresses[0].email'], 'uploadCustomers');
    }

    public function uploadAddresses($csvFile) {
        return $this->processCSVFile($csvFile, ['intnr', 'addresses.company_name', 'addresses.country', 'addresses.city', 'addresses.zip', 'addresses.fax', 'addresses.phone', 'addresses.street', 'addresses.email'], 'uploadAddresses');
    }

    public function uploadContactPersons($csvFile) {
        return $this->processCSVFile($csvFile, ['intnr', 'contact_persons.first_name', 'contact_persons.last_name', 'contact_persons.email', 'contact_persons.mobile_phone', 'contact_persons.birth_date'], 'uploadContactPersons');
    }

    private function processCSVFile($csvFile, $requiredColumns, $uploadType) {
        // Read CSV file
        $file = fopen($csvFile, "r");
        $header = fgetcsv($file);

        // Validate header columns
        if ($header !== $requiredColumns) {
            $errorMessage = 'Invalid CSV file. Please check the column headers.';
            return $this->prepareErrorResponse($errorMessage);
        }

        $csvValidator = new CSVValidator();
        $existingCustomers = array(); // Initialize an array to store existing customers
        $newRecordInserted = false;
        $headerFormatted = $this->formatHeader($header);
        $columnIndexes = array_flip($headerFormatted);
        $rowNumber = 0;

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;
            $data = array_map('trim', $row);
            $errors = $csvValidator->validateCSVRow($data, $rowNumber, $columnIndexes);

            if (!empty($errors)) {
                $errorMessage = "Validation failed for row $rowNumber<br>" . implode("<br>", $errors) . "<br>No records created.";
                return $this->prepareErrorResponse($errorMessage);
            }

            // Check if customer with the same intnr already exists
            $intnr = $data[0];
            $existingCustomerQuery = "SELECT * FROM customers WHERE intnr = '$intnr'";
            $existingCustomerResult = $this->connection->query($existingCustomerQuery);

            if ($uploadType === 'uploadCustomers') {
                if ($existingCustomerResult->num_rows > 0) {
                    // Customer with the same intnr already exists, add it to the list
                    $existingCustomers[] = $existingCustomerResult->fetch_assoc();
                } else {
                    $this->insertCustomerData($data, $columnIndexes);
                    // New record is inserted
                    $newRecordInserted = true;
                }
            } elseif ($uploadType === 'uploadAddresses') {

                if ($existingCustomerResult->num_rows > 0) {

                    $customerData = $existingCustomerResult->fetch_assoc();

                    $id = $customerData['id'];
                    // Insert data into customers table
                    $this->insertAddressData($data, $id, $columnIndexes);
                    $newRecordInserted = true;
                } else {
                    // Customer does not exist
                    return $this->prepareErrorResponse("Customer does not exist for intnr: $intnr");
                }
            } elseif ($uploadType === 'uploadContactPersons') {

                if ($existingCustomerResult->num_rows > 0) {

                    $customerData = $existingCustomerResult->fetch_assoc();

                    // Get the customer_id
                    $id = $customerData['id'];
                    $this->insertContactPersonData($data, $id, $columnIndexes);
                    $newRecordInserted = true;
                } else {
                    return $this->prepareErrorResponse("Customer does not exist for intnr: $intnr");
                }
            }
        }

        fclose($file);
        // Close MySQL connection
        $this->connection->close();

        $response = $this->prepareResponse($existingCustomers, $newRecordInserted);
        return $response;
    }

    private function insertCustomerData($data, $columnIndexes) {
        $query = "INSERT INTO customers (intnr, type) VALUES (?, ?)";
        $this->execute($query, [$data[0], $data[1]]);
        $customer_id = $this->getInsertId();

        $this->insertContactPersonData($data, $customer_id, $columnIndexes);

        $this->insertAddressData($data, $customer_id, $columnIndexes);
    }

    private function insertContactPersonData($data, $customer_id, $columnIndexes) {
        $query = "INSERT INTO contact_persons (customer_id, first_name, last_name, email, mobile_phone, birth_date)"
                . " VALUES (?, ?, ?, ?, ?, ?)";
        $this->execute($query, [$customer_id, $data[$columnIndexes['first_name']], $data[$columnIndexes['last_name']], $data[$columnIndexes['email']], $data[$columnIndexes['mobile_phone']], $data[$columnIndexes['birth_date']]]);
    }

    private function insertAddressData($data, $customer_id, $columnIndexes) {
        $query = "INSERT INTO addresses (customer_id, company_name, country, city, zip, fax, phone, street, email)"
                . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->execute($query, [$customer_id, $data[$columnIndexes['company_name']], $data[$columnIndexes['country']], $data[$columnIndexes['city']], $data[$columnIndexes['zip']], $data[$columnIndexes['fax']], $data[$columnIndexes['phone']], $data[$columnIndexes['street']], $data[$columnIndexes['email2']]]);
    }

    private function respondWithError($errorMessage) {
        echo json_encode(["status" => "error", 'message' => $errorMessage]);
        exit();
    }

    private function prepareErrorResponse($errorMessage) {
        return ['status' => 'error', 'message' => $errorMessage];
    }

    private function sendErrorResponse($errorMessage) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
        exit();
    }

    private function prepareResponse($existingCustomers, $newRecordInserted) {
        $response = [];
        if ($newRecordInserted) {
            if (!empty($existingCustomers)) {
                $response['status'] = 'partial_success';
                $response['message'] = 'Data uploaded with some skipped records.';
                $response['existing_customers'] = array_column($existingCustomers, 'intnr'); // Extract intnr values
            } else {
                $response['status'] = 'success';
                $response['message'] = 'Data uploaded successfully!';
            }
        } else {
            $response['status'] = 'no_records_uploaded';
            $response['message'] = 'No new records uploaded. All records already exist.';
        }
        return $response;
    }

    private function formatHeader($header) {
        $headerFormatted = array_map(function ($item) {
            $parts = explode('.', $item);
            $lastPart = end($parts);

            if (in_array('addresses', $parts) && strpos(end($parts), 'email') !== false) {
                $item = str_replace('addresses.', '', $item);
                return $item . '2';
            }

            return $lastPart;
        }, $header);

        if (end($headerFormatted) === 'email') {
            $headerFormatted[count($headerFormatted) - 1] = 'email2';
        }
        return $headerFormatted;
    }

}
