<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_connect.php';
mysqli_set_charset($conn, 'utf8mb4');


$table = isset($_GET['table']) ? $_GET['table'] : '';
$input = json_decode(file_get_contents('php://input'), true);

if (empty($table) || empty($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// White list
$allowedTables = ['branch', 'account', 'employee', 'customer', 'scooter', 'promotion', 'rental', 'maintenance'];

if (!in_array($table, $allowedTables)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table']);
    exit();
}

// create SQL INSERT
$columns = [];
$values = [];

foreach ($input as $key => $value) {
    // convert field name จาก snake_case to column name
    $columnName = ucwords(str_replace('_', ' ', $key));
    $columnName = str_replace(' ', '_', $columnName);
    
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