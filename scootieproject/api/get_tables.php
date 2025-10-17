<?php
// ล้าง output buffer
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/db_connect.php';

    $tables = [];

    // Branch
    $result = $conn->query("SELECT * FROM branch");
    if (!$result) {
        throw new Exception("Branch query failed: " . $conn->error);
    }
    $tables['branch'] = [
        'title' => 'Branch Table',
        'headers' => ['Branch_ID', 'Location', 'Phone_Number'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Account
    $result = $conn->query("SELECT Account_ID, Username, Password_Hash, Email FROM account");
    if (!$result) {
        throw new Exception("Account query failed: " . $conn->error);
    }
    $tables['account'] = [
        'title' => 'Account Table',
        'headers' => ['Account_ID', 'Username', 'Password_Hash', 'Email'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Employee
    $result = $conn->query("SELECT Employee_ID, Account_ID, Branch_ID, Name, Email, Phone_Number, Position, Salary FROM employee");
    if (!$result) {
        throw new Exception("Employee query failed: " . $conn->error);
    }
    $tables['employee'] = [
        'title' => 'Employee Table',
        'headers' => ['Employee_ID', 'Account_ID', 'Branch_ID', 'Name', 'Email', 'Phone_Number', 'Position', 'Salary'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Customer
    $result = $conn->query("SELECT Customer_ID, Account_ID, Name, Phone_Number, Email, Driver_License_Number, Membership_Level FROM customer");
    if (!$result) {
        throw new Exception("Customer query failed: " . $conn->error);
    }
    $tables['customer'] = [
        'title' => 'Customer Table',
        'headers' => ['Customer_ID', 'Account_ID', 'Name', 'Phone_Number', 'Email', 'Driver_License_Number', 'Membership_Level'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Scooter
    $result = $conn->query("SELECT * FROM scooter");
    if (!$result) {
        throw new Exception("Scooter query failed: " . $conn->error);
    }
    $tables['scooter'] = [
        'title' => 'Scooter Table',
        'headers' => ['License_Plate', 'Model_Name', 'Current_Branch_ID', 'Status', 'Daily_Rate'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Promotion
    $result = $conn->query("SELECT * FROM promotion");
    if (!$result) {
        throw new Exception("Promotion query failed: " . $conn->error);
    }
    $tables['promotion'] = [
        'title' => 'Promotion Table',
        'headers' => ['Promotion_ID', 'Promotion_Name', 'Description', 'Discount_Percentage', 'Start_Date', 'End_Date', 'Membership_Level_Required', 'Manager_ID'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Rental
    $result = $conn->query("SELECT * FROM rental");
    if (!$result) {
        throw new Exception("Rental query failed: " . $conn->error);
    }
    $tables['rental'] = [
        'title' => 'Rental Table',
        'headers' => ['Rental_ID', 'Customer_ID', 'License_Plate', 'Pickup_Branch_ID', 'Return_Branch_ID', 'Pickup_Date_Time', 'Return_Date_Time', 'Actual_Return_Date_Time', 'Initial_Total_Cost', 'Discount_Amount', 'Final_Total_Cost', 'Payment_Status', 'Promotion_ID'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // Maintenance
    $result = $conn->query("SELECT * FROM maintenance");
    if (!$result) {
        throw new Exception("Maintenance query failed: " . $conn->error);
    }
    $tables['maintenance'] = [
        'title' => 'Maintenance Table',
        'headers' => ['Maintenance_ID', 'License_Plate', 'Date', 'Description', 'Cost', 'Performed_By_Employee_ID'],
        'rows' => $result->fetch_all(MYSQLI_NUM)
    ];
    
    // send JSON
    ob_clean();
    echo json_encode($tables, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage(),
        'success' => false
    ]);
}

ob_end_flush();
?>