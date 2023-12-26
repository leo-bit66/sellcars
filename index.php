<?php

// Include necessary files
require __DIR__ . "/api/includes/bootstrap.php";

// Extract HTTP method and request URI
$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base_url = "/sellcars";
// Remove the base URL from the request URI
$uri = str_replace($base_url, '', $request_uri);

// Create a new Router instance
$router = new Router($request_method, $uri);

// Register middleware
$router->middleware('auth', 'authenticateMiddleware');

$router->get('/', 'LoginController@showLoginForm');
$router->get('/index.php', 'LoginController@showLoginForm');
$router->post('/login', 'LoginController@validateLogin');

// Define routes with middleware
$router->get('/customers-page', 'CustomerController@showCustomersPage');
$router->get('/customers-page/customers', 'CustomerController@getAllCustomers', ['auth']);
$router->get('/login/user', 'LoginController@getCurrentUser', ['auth']);
$router->get('/customers-page/{id}', 'CustomerController@getCustomer', ['auth']);
$router->put('/customers-page/{id}', 'CustomerController@updateCustomer', ['auth']);
$router->delete('/customers-page/{id}', 'CustomerController@deleteCustomer', ['auth']);

// Additional routes for uploads
$router->post('/uploads/customers', 'UploadController@uploadCustomers', ['auth']);
$router->post('/uploads/addresses', 'UploadController@uploadAddresses', ['auth']);
$router->post('/uploads/contactpersons', 'UploadController@uploadContactPersons', ['auth']);


// Handle the incoming request
$router->resolve();