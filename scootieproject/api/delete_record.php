<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_connect.php';
mysqli_set_charset($conn, 'utf8mb4');

$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($table) || empty($id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// White list
$allowedTables = ['branch', 'account', 'employee', 'customer', 'scooter', 'promotion', 'rental', 'maintenance'];

if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table']);
    exit();
}

// primary key 
$primaryKeys = [
    'branch' => 'Branch_ID',
    'account' => 'Account_ID',
    'employee' => 'Employee_ID',
    'customer' => 'Customer_ID',
    'scooter' => 'License_Plate',
    'promotion' => 'Promotion_ID',
    'rental' => 'Rental_ID',
    'maintenance' => 'Maintenance_ID'
];

$primaryKey = $primaryKeys[$table];
$escapedId = mysqli_real_escape_string($conn, $id);


//Delete (CRUD)
$sql = "DELETE FROM `$table` WHERE `$primaryKey` = '$escapedId'";

if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Record not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>