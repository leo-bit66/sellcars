# SellCars Customer Module

## Changes Log

### Version 1.1.0 (25 December, 2023)

- **Backend Refactoring:**
  - Index file now acts as the entry point, bootstrapping dependencies.
  - Incoming requests routed to corresponding controllers.
  - Middleware authorization implemented for protected resources.
  - Token and middleware authentication using Firebase PHP-JWT (requires Composer installation).

- **Endpoint URIs:**
  - Made more meaningful for improved API structure.

- **Frontend Code Cleanup:**
  - PHP code removed from views, now pure HTML for maintainability.


## Introduction
This web application is designed to serve the "SellCars" company, a car dealer, in managing their customer information efficiently. The application is built using PHP for the backend, JavaScript for the frontend, and MySQL as the database. The system is designed to enhance user experience and provide a user-friendly interface for managing customers, contact persons, and addresses.

## Project Overview
### Technologies Used

- Webserver: Apache
- Database: MySQL
- Backend: PHP 7+
- Frontend: Pure JavaScript
- DataTables library for sorting and searching (linked online).

### Features

- **UI Design:** Uniform color themes provide a cohesive brand experience. The project includes loading spinners, responsive buttons, logo, Favicon and confirmation modals for notices and user input.
- **Validation:** CSV file validation checks for file type, size, and basic structure. (Region-specific validations are not exhaustive due to assignment constraints.)
- **User Authentication:** Users are authenticated using email and password hashes stored securely in the database. Direct access to the "Customers page" without login redirects users to the login page.
- **Customers Page**: The main page displays customer information in a tabular format.
- **Customer Table**: Shows essential customer details and allows sorting, searching, deletion, and editing.
- **Upload Areas**: Enables users to upload CSV files for customers, contact persons, and addresses with size and format validation.

## Installation & Usage
1. Set up Apache and MySQL for the webserver and database.
2. Import the provided SQL data to generate the database with the necessary tables and mock users.
3. Access the login page (project root), and log in with the provided mock users.
4. Upon successful login, you will be redirected to the Customers page.
5. Use the "Upload Customers" functionality to import the provided sample customer CSV file from the "uploads" folder and populate the table.
6. Manage customer information through the responsive table.
7. Delete customers with confirmation alerts and initiate edits for a user-friendly experience.

## Notes
- The backend logic and frontend design are not thoroughly tested and may require additional refinement.
- The backend logic does not really adhere to REST principles, a lot of further improvements can be made for compliance.
- Validation checks for CSV files are not region-specific and focus on basic requirements.