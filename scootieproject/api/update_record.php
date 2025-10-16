<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {

    require_once __DIR__ . '/db_connect.php';
    mysqli_set_charset($conn, 'utf8mb4');
    
    $table = isset($_GET['table']) ? $_GET['table'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($table) || empty($id) || empty($input)) {
        throw new Exception('Invalid input data');
    }
    
    // White list
    $allowedTables = ['branch', 'account', 'employee', 'customer', 'scooter', 'promotion', 'rental', 'maintenance'];
    
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Invalid table name');
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
    
    // create SQL UPDATE
    $updates = [];
    foreach ($input as $key => $value) {
        // convert field name
        $columnName = str_replace(' ', '_', ucwords(str_replace('_', ' ', $key)));
        
        if ($columnName !== $primaryKey) {
            $escapedValue = mysqli_real_escape_string($conn, $value);
            
            if ($value === null || $value === '') {
                $updates[] = "`$columnName` = NULL";
            } else {
                $updates[] = "`$columnName` = '$escapedValue'";
            }
        }
    }
    
    if (empty($updates)) {
        throw new Exception('No fields to update');
    }
    
    $updateStr = implode(', ', $updates);
    $escapedId = mysqli_real_escape_string($conn, $id);
    
    $sql = "UPDATE `$table` SET $updateStr WHERE `$primaryKey` = '$escapedId'";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) >= 0) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
        } else {
            throw new Exception('Record not found or no changes made');
        }
    } else {
        throw new Exception(mysqli_error($conn));
    }
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();
?>