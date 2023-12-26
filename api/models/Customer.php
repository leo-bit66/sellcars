<?php

require_once __DIR__ . "/Database.php";

class Customer extends Database {

    public function getAllCustomers() {
        $query = "
            SELECT
                customers.id,
                contact_persons.first_name,
                contact_persons.last_name,
                addresses.company_name,
                addresses.country,
                CONCAT(addresses.zip, ' / ', addresses.city) AS zip_city,
                addresses.street
            FROM
                customers
            LEFT JOIN
                addresses ON customers.id = addresses.customer_id
            LEFT JOIN
                contact_persons ON customers.id = contact_persons.customer_id
        ";
        $result = $this->connection->query($query);

        if ($result) {
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = array(
                    'id' => $row['id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'company_name' => $row['company_name'],
                    'country' => $row['country'],
                    'zip_city' => $row['zip_city'],
                    'street' => $row['street'],
                );
            }

            $this->connection->close();
            return $data;
        } else {
            $this->connection->close();
            return [];
        }
    }

    public function getCustomerById($customerId) {
        $query = "
            SELECT
                c.*,
                cp.id AS contact_person_id,
                cp.first_name,
                cp.last_name,
                cp.email,
                cp.mobile_phone,
                cp.birth_date,
                a.id AS address_id,
                a.company_name,
                a.country,
                a.city,
                a.zip,
                a.fax,
                a.phone,
                a.street,
                a.email AS address_email
            FROM
                customers c
            LEFT JOIN
                contact_persons cp ON c.id = cp.customer_id
            LEFT JOIN
                addresses a ON c.id = a.customer_id
            WHERE
                c.id = $customerId
        ";

        $result = $this->connection->query($query);

        if ($result) {
            $customerData = [];

            while ($row = $result->fetch_assoc()) {
                $customerData['id'] = $row['id'];
                $customerData['type'] = $row['type'];
                $customerData['created_at'] = $row['created_at'];
                $customerData['updated_at'] = $row['updated_at'];

                $contactPersons[] = [
                    'id' => $row['contact_person_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'email' => $row['email'],
                    'mobile_phone' => $row['mobile_phone'],
                    'birth_date' => $row['birth_date'],
                ];

                // Addresses (array of objects)
                $addresses[] = [
                    'id' => $row['address_id'],
                    'company_name' => $row['company_name'],
                    'country' => $row['country'],
                    'city' => $row['city'],
                    'zip' => $row['zip'],
                    'fax' => $row['fax'],
                    'phone' => $row['phone'],
                    'street' => $row['street'],
                    'email' => $row['address_email'],
                ];
            }

            $customerData['contact_persons'] = $contactPersons;
            $customerData['addresses'] = $addresses;

            $this->connection->close();
            return $customerData;
        } else {
            $this->connection->close();
            return false;
        }
    }

    public function createCustomer($data) {
        try {
            $intnr = $this->conn->real_escape_string(trim($data[0]));
            $type = $this->conn->real_escape_string(trim($data[1]));

            $existingCustomerQuery = "SELECT * FROM customers WHERE intnr = '$intnr'";
            $existingCustomerResult = $this->conn->query($existingCustomerQuery);

            if ($existingCustomerResult->num_rows > 0) {
                return [
                    'status' => 'error',
                    'message' => 'Customer with the same intnr already exists',
                ];
            } else {
                $query = "INSERT INTO customers (intnr, type) VALUES ('$intnr', '$type')";
                if ($this->conn->query($query) !== true) {
                    throw new Exception('Error inserting data into customers: ' . $this->conn->error);
                }
                $customer_id = $this->conn->insert_id;

                $query = "INSERT INTO contact_persons (customer_id, first_name, last_name, email, mobile_phone, birth_date) VALUES ('$customer_id', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]')";
                if ($this->conn->query($query) !== true) {
                    throw new Exception('Error inserting data into contact_persons: ' . $this->conn->error);
                }

                $query = "INSERT INTO addresses (customer_id, company_name, country, city, zip, fax, phone, street, email) VALUES ('$customer_id', '$data[7]', '$data[8]', '$data[9]', '$data[10]', '$data[11]', '$data[12]', '$data[13]', '$data[14]')";
                if ($this->conn->query($query) !== true) {
                    throw new Exception('Error inserting data into addresses: ' . $this->conn->error);
                }

                return [
                    'status' => 'success',
                    'message' => 'Customer created successfully',
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        } finally {
            $this->conn->close();
        }
    }

    public function updateCustomer($customerId, $data) {
        try {
            // Business logic for updating customer

            $updatedFirstName = $data['firstname'];
            $updatedLastName = $data['lastname'];

            // Use prepared statements for database operations
            $updateContactPersonsQuery = "UPDATE contact_persons SET first_name = ?, last_name = ? WHERE customer_id = ?";
            if (!$this->execute($updateContactPersonsQuery, [$updatedFirstName, $updatedLastName, $customerId])) {
                return ['success' => false, 'message' => 'Error updating contact persons data'];
            }

            $updatedCompany = $data['company'];
            $updatedCountry = $data['country'];
            $updatedZipCity = $data['zipcity'];
            $updatedAddress = $data['address'];
            list($updatedZip, $updatedCity) = explode(' / ', $updatedZipCity);

            $updateAddressesQuery = "UPDATE addresses SET company_name = ?, country = ?, zip = ?, city = ?, street = ? WHERE customer_id = ?";
            if (!$this->execute($updateAddressesQuery, [$updatedCompany, $updatedCountry, $updatedZip, $updatedCity, $updatedAddress, $customerId])) {
                return ['success' => false, 'message' => 'Error updating addresses data'];
            }
            
            $updateUpdatedAtQuery = "UPDATE customers SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            if (!$this->execute($updateUpdatedAtQuery, [$customerId])) {
                return ['success' => false, 'message' => 'Error updating updated_at'];
            }

            return ['success' => true, 'message' => 'Changes saved successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteCustomer($customerId) {
        try {
            // Business logic for deleting customer

            $deleteCustomerQuery = "DELETE FROM customers WHERE id = ?";
            $deleteContactPersonsQuery = "DELETE FROM contact_persons WHERE customer_id = ?";
            $deleteAddressesQuery = "DELETE FROM addresses WHERE customer_id = ?";

            // Use prepared statements for database operations
            $customerDeleted = $this->execute($deleteCustomerQuery, [$customerId]);
            $contactPersonsDeleted = $this->execute($deleteContactPersonsQuery, [$customerId]);
            $addressesDeleted = $this->execute($deleteAddressesQuery, [$customerId]);

            // Check if all queries were successful
            if ($customerDeleted && $contactPersonsDeleted && $addressesDeleted) {
                $result = ['success' => true, 'message' => 'Customer deleted successfully'];
            } else {
                $result = ['success' => false, 'message' => 'Error deleting customer'];
            }

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}
