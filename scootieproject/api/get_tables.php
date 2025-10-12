<?php
// ล้าง output buffer
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// ปิด error display
error_reporting(0);
ini_set('display_errors', 0);

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
    // Database configuration
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'scootiedb';
    
    // Create connection
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Set charset
    mysqli_set_charset($conn, 'utf8mb4');

    $tables = [];

    // Branch
    $result = mysqli_query($conn, "SELECT * FROM branch");
    if (!$result) {
        throw new Exception("Branch query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['branch'] = [
        'title' => 'Branch Table',
        'headers' => ['Branch_ID', 'Location', 'Phone_Number'],
        'rows' => $rows
    ];
    
    // Account
    $result = mysqli_query($conn, "SELECT Account_ID, Username, Password_Hash, Email FROM account");
    if (!$result) {
        throw new Exception("Account query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['account'] = [
        'title' => 'Account Table',
        'headers' => ['Account_ID', 'Username', 'Password_Hash', 'Email'],
        'rows' => $rows
    ];
    
    // Employee
    $result = mysqli_query($conn, "SELECT Employee_ID, Account_ID, Branch_ID, Name, Email, Phone_Number, Position, Salary FROM employee");
    if (!$result) {
        throw new Exception("Employee query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['employee'] = [
        'title' => 'Employee Table',
        'headers' => ['Employee_ID', 'Account_ID', 'Branch_ID', 'Name', 'Email', 'Phone_Number', 'Position', 'Salary'],
        'rows' => $rows
    ];
    
    // Customer
    $result = mysqli_query($conn, "SELECT Customer_ID, Account_ID, Name, Phone_Number, Email, Driver_License_Number, Membership_Level FROM customer");
    if (!$result) {
        throw new Exception("Customer query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['customer'] = [
        'title' => 'Customer Table',
        'headers' => ['Customer_ID', 'Account_ID', 'Name', 'Phone_Number', 'Email', 'Driver_License_Number', 'Membership_Level'],
        'rows' => $rows
    ];
    
    // Scooter
    $result = mysqli_query($conn, "SELECT * FROM scooter");
    if (!$result) {
        throw new Exception("Scooter query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['scooter'] = [
        'title' => 'Scooter Table',
        'headers' => ['License_Plate', 'Model_Name', 'Current_Branch_ID', 'Status', 'Daily_Rate'],
        'rows' => $rows
    ];
    
    // Promotion
    $result = mysqli_query($conn, "SELECT * FROM promotion");
    if (!$result) {
        throw new Exception("Promotion query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['promotion'] = [
        'title' => 'Promotion Table',
        'headers' => ['Promotion_ID', 'Promotion_Name', 'Description', 'Discount_Percentage', 'Start_Date', 'End_Date', 'Membership_Level_Required', 'Manager_ID'],
        'rows' => $rows
    ];
    
    // Rental
    $result = mysqli_query($conn, "SELECT * FROM rental");
    if (!$result) {
        throw new Exception("Rental query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['rental'] = [
        'title' => 'Rental Table',
        'headers' => ['Rental_ID', 'Customer_ID', 'License_Plate', 'Pickup_Branch_ID', 'Return_Branch_ID', 'Pickup_Date_Time', 'Return_Date_Time', 'Actual_Return_Date_Time', 'Initial_Total_Cost', 'Discount_Amount', 'Final_Total_Cost', 'Payment_Status', 'Promotion_ID'],
        'rows' => $rows
    ];
    
    // Maintenance
    $result = mysqli_query($conn, "SELECT * FROM maintenance");
    if (!$result) {
        throw new Exception("Maintenance query failed: " . mysqli_error($conn));
    }
    $rows = [];
    while($row = mysqli_fetch_row($result)) {
        $rows[] = $row;
    }
    $tables['maintenance'] = [
        'title' => 'Maintenance Table',
        'headers' => ['Maintenance_ID', 'License_Plate', 'Date', 'Description', 'Cost', 'Performed_By_Employee_ID'],
        'rows' => $rows
    ];
    
    // Close connection
    mysqli_close($conn);
    
    // ล้าง buffer และส่ง JSON เท่านั้น
    ob_clean();
    echo json_encode($tables, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // ล้าง buffer และส่ง error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}

// Flush output
ob_end_flush();
?>