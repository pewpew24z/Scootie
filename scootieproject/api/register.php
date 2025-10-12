<?php
header('Content-Type: application/json; charset=utf-8');
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

// รับข้อมูลจาก POST
$input = json_decode(file_get_contents('php://input'), true);

// ตรวจสอบข้อมูล
if (empty($input)) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit();
}

// ดึงข้อมูลจาก input
$name = isset($input['name']) ? trim($input['name']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$license = isset($input['license']) ? trim($input['license']) : '';
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

// Validate ข้อมูล
if (empty($name)) {
    echo json_encode(['success' => false, 'error' => 'Full name is required']);
    exit();
}

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Phone number is required']);
    exit();
}

if (empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Email is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

if (empty($license)) {
    echo json_encode(['success' => false, 'error' => 'Driver license number is required']);
    exit();
}

if (empty($username)) {
    echo json_encode(['success' => false, 'error' => 'Username is required']);
    exit();
}

if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Password is required']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
    exit();
}

// ตรวจสอบว่า username ซ้ำหรือไม่
$checkUsername = "SELECT Account_ID FROM account WHERE Username = ?";
$stmt = mysqli_prepare($conn, $checkUsername);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'error' => 'Username already exists']);
    exit();
}
mysqli_stmt_close($stmt);

// ตรวจสอบว่า email ซ้ำหรือไม่
$checkEmail = "SELECT Account_ID FROM account WHERE Email = ?";
$stmt = mysqli_prepare($conn, $checkEmail);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'error' => 'Email already exists']);
    exit();
}
mysqli_stmt_close($stmt);

// ตรวจสอบว่า phone ซ้ำหรือไม่
$checkPhone = "SELECT Customer_ID FROM customer WHERE Phone_Number = ?";
$stmt = mysqli_prepare($conn, $checkPhone);
mysqli_stmt_bind_param($stmt, "s", $phone);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'error' => 'Phone number already exists']);
    exit();
}
mysqli_stmt_close($stmt);

// ตรวจสอบว่า license ซ้ำหรือไม่
$checkLicense = "SELECT Customer_ID FROM customer WHERE Driver_License_Number = ?";
$stmt = mysqli_prepare($conn, $checkLicense);
mysqli_stmt_bind_param($stmt, "s", $license);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'error' => 'Driver license number already exists']);
    exit();
}
mysqli_stmt_close($stmt);

// เริ่ม Transaction
mysqli_begin_transaction($conn);

try {
    // 1. Insert ลง Account table
    $passwordHash = $password; // ในระบบจริงควรใช้ password_hash($password, PASSWORD_DEFAULT)
    
    $insertAccount = "INSERT INTO account (Username, Password_Hash, Email) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertAccount);
    mysqli_stmt_bind_param($stmt, "sss", $username, $passwordHash, $email);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create account');
    }
    
    $accountId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // 2. Insert ลง Customer table
    $membershipLevel = 'Regular'; // Default เป็น Regular
    
    $insertCustomer = "INSERT INTO customer (Account_ID, Name, Phone_Number, Email, Driver_License_Number, Membership_Level) 
                       VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertCustomer);
    mysqli_stmt_bind_param($stmt, "isssss", $accountId, $name, $phone, $email, $license, $membershipLevel);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create customer profile');
    }
    
    mysqli_stmt_close($stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! Please login.',
        'account_id' => $accountId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

mysqli_close($conn);
?>