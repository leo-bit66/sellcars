<?php

function isLoggedIn() {
    // Check if the 'user_id' session variable is set
    return isset($_SESSION['user_id']);
}

function redirectToLogin() {
    header("Location: /sellcars");
    exit();
}

function redirectToCustomersPage() {
    header("Location: /sellcars/customers-page");
    exit();
}