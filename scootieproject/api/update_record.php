<?php
// ล้าง output buffer
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// ปิด error display
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Database connection
    $conn = mysqli_connect('localhost', 'root', '', 'scootiedb');
    
    if (!$conn) {
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, 'utf8mb4');
    
    // รับข้อมูล
    $table = isset($_GET['table']) ? $_GET['table'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    // ตรวจสอบข้อมูล
    if (empty($table) || empty($id) || empty($input)) {
        throw new Exception('Invalid input data');
    }
    
    // White list ของตารางที่อนุญาต
    $allowedTables = ['branch', 'account', 'employee', 'customer', 'scooter', 'promotion', 'rental', 'maintenance'];
    
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Invalid table name');
    }
    
    // กำหนด primary key สำหรับแต่ละตาราง
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
    
    // สร้าง SQL UPDATE
    $updates = [];
    foreach ($input as $key => $value) {
        // แปลง field name
        $columnName = str_replace(' ', '_', ucwords(str_replace('_', ' ', $key)));
        
        // ไม่อัพเดต primary key
        if ($columnName !== $primaryKey) {
            $escapedValue = mysqli_real_escape_string($conn, $value);
            
            // ถ้าเป็น NULL หรือ empty string ในบางกรณี
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