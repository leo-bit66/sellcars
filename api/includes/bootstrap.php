<?php

define("BASE_PATH", __DIR__ . "/../");
// include main db configuration file 
require_once BASE_PATH . "/config/db_config.php";
// include the controller files
require_once BASE_PATH . "/controllers/CustomerController.php";
require_once BASE_PATH . "/controllers/LoginController.php";
require_once BASE_PATH . "/controllers/UploadController.php";
// include the model files
require_once BASE_PATH . "/models/User.php";
require_once BASE_PATH . "/models/Customer.php";
require_once BASE_PATH . "/models/CsvFileUploader.php";
require_once BASE_PATH . "includes/middleware.php";
require_once BASE_PATH . "routes/api.php";
require_once BASE_PATH . "/vendor/autoload.php";