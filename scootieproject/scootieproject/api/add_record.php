<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'scootiedb');

if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

mysqli_set_charset($conn, 'utf8mb4');

// รับข้อมูล
$table = isset($_GET['table']) ? $_GET['table'] : '';
$input = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบข้อมูล
if (empty($table) || empty($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// White list ของตารางที่อนุญาต
$allowedTables = ['branch', 'account', 'employee', 'customer', 'scooter', 'promotion', 'rental', 'maintenance'];

if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table']);
    exit();
}

// สร้าง SQL INSERT
$columns = [];
$values = [];

foreach ($input as $key => $value) {
    // แปลง field name จาก snake_case เป็น column name
    $columnName = ucwords(str_replace('_', ' ', $key));
    $columnName = str_replace(' ', '_', $columnName);
    
    // เพิ่มทุก column ที่ส่งมา ไม่มีการข้ามเลย
    $columns[] = "`$columnName`";
    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
}

if (empty($columns)) {
    echo json_encode(['success' => false, 'error' => 'No fields to insert']);
    exit();
}

$columnsStr = implode(', ', $columns);
$valuesStr = implode(', ', $values);

$sql = "INSERT INTO `$table` ($columnsStr) VALUES ($valuesStr)";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Record added successfully', 'id' => mysqli_insert_id($conn)]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>