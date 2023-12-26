<?php

class CSVValidator {

    public function validateCSVRow($row, $rowNumber, $columnIndexes) {
        $errors = [];

        if (isset($columnIndexes['intnr']) && !$this->validateIntnr($row[$columnIndexes['intnr']])) {
            $errors['intnr'] = 'Invalid intnr format';
        }

        if (isset($columnIndexes['type']) && !$this->validateType($row[$columnIndexes['type']])) {
            $errors['type'] = 'Invalid type';
        }

        if (isset($columnIndexes['firstname']) && !$this->validateName($row[$columnIndexes['first_name']])) {
            $errors['firstname'] = 'Invalid Firstname';
        }

        if (isset($columnIndexes['lastname']) && !$this->validateName($row[$columnIndexes['last_name']])) {
            $errors['lastname'] = 'Invalid Lastname';
        }

        if (isset($columnIndexes['email']) && !$this->validateEmail($row[$columnIndexes['email']])) {
            $errors['email'] = 'Invalid E-Mail format';
        }

        if (isset($columnIndexes['mobile_phone']) && !$this->validateMobilePhone($row[$columnIndexes['mobile_phone']])) {
            $errors['mobile_phone'] = 'Invalid mobile phone format';
        }

        if (isset($columnIndexes['birth_date']) && !$this->validateBirthDate($row[$columnIndexes['birth_date']])) {
            $errors['birth_date'] = 'Invalid birth date format';
        }

        if (isset($columnIndexes['company_name']) && isset($columnIndexes['type']) && !$this->validateCompanyName($row[$columnIndexes['company_name']], $row[$columnIndexes['type']])) {
            $errors['company_name'] = 'Invalid company name format';
        }

        if (isset($columnIndexes['country']) && isset($columnIndexes['city']) && isset($columnIndexes['zip']) && !$this->validateLocation($row[$columnIndexes['country']], $row[$columnIndexes['city']], $row[$columnIndexes['zip']])) {
            $errors['location'] = 'Invalid country, city, or zip format';
        }

        if (isset($columnIndexes['fax']) && isset($columnIndexes['type']) && !$this->validateContactNumber($row[$columnIndexes['fax']], $row[$columnIndexes['type']])) {
            $errors['fax'] = 'Invalid fax number';
        }

        if (isset($columnIndexes['phone']) && isset($columnIndexes['type']) && !$this->validateContactNumber($row[$columnIndexes['phone']], $row[$columnIndexes['type']])) {
            $errors['phone'] = 'Invalid company phone number';
        }

        if (isset($columnIndexes['email2']) && isset($columnIndexes['type']) && !$this->validateContactEmail($row[$columnIndexes['email2']], $row[$columnIndexes['type']])) {
            $errors['email2'] = 'Invalid company E-Mail format';
        }

        if (isset($columnIndexes['street']) && !$this->validateStreet($row[$columnIndexes['street']])) {
            $errors['street'] = 'Invalid Street format';
        }
        
        return $errors;
    }

    public function validateIntnr($intnr) {
        return (bool) preg_match('/^c-\d{5}$/i', $intnr);
    }

    public function validateType($type) {
        $validTypes = ['private', 'company', 'dealer'];
        return in_array(strtolower($type), $validTypes);
    }

    public function validateName($value) {
        return (bool) preg_match('/^[a-zA-Z]{1,50}$/', $value);
    }

    public function validateEmail($value) {
        return ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL));
    }

    public function validateMobilePhone($value) {
        return ($value !== '' && preg_match('/^\d{1,20}$/', $value));
    }

    public function validateBirthDate($value) {
        return ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value));
    }

    public function validateCompanyName($value, $type) {
        $validTypes = ['company', 'dealer'];
        if (in_array(strtolower($type), $validTypes)) {
            return ($value !== '' && preg_match('/^[a-zA-Z\s]{1,50}$/', $value));
        }
        return true; // If the type is not 'company' or 'dealer', return true (considered valid)
    }

    public function validateLocation($country, $city, $zip) {
        return preg_match('/^[a-zA-Z\s]{1,50}$/', $country) &&
                preg_match('/^[a-zA-Z\s]{1,50}$/', $city) &&
                ($zip !== '');
    }

    public function validateContactNumber($value, $type) {
        $validTypes = ['company', 'dealer'];
        if (in_array(strtolower($type), $validTypes)) {
            return ($value !== '' && preg_match('/^\d{1,20}$/', $value));
        }
        return true; // If the type is not 'company' or 'dealer', return true (considered valid)
    }

    public function validateContactEmail($value, $type) {
        $validTypes = ['company', 'dealer'];
        // Check if the type is valid before applying the validation
        if (in_array(strtolower($type), $validTypes)) {
            return ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL));
        }
        return true; // If the type is not 'company' or 'dealer', return true (considered valid)
    }

    public function validateStreet($value) {
        return preg_match('/^[a-zA-Z0-9\s]{1,100}$/', $value);
    }

}