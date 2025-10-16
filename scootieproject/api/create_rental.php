<?php

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
    mysqli_set_charset($conn, 'utf8mb4');

    // Get POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Validate required fields
    $required = ['customerId', 'licensePlate', 'pickupBranchId', 'returnBranchId', 
                 'pickupDateTime', 'returnDateTime', 'initialTotalCost', 
                 'discountAmount', 'finalTotalCost'];
    
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Prepare SQL statement
    $sql = "INSERT INTO rental (
        Customer_ID, 
        License_Plate, 
        Pickup_Branch_ID, 
        Return_Branch_ID, 
        Pickup_Date_Time, 
        Return_Date_Time, 
        Initial_Total_Cost, 
        Discount_Amount, 
        Final_Total_Cost, 
        Payment_Status, 
        Promotion_ID
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)";

    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }

    // Bind parameters - promotionId can be null
    $promotionId = isset($data['promotionId']) && $data['promotionId'] !== '' ? $data['promotionId'] : null;
    
    mysqli_stmt_bind_param($stmt, 'ssssssddss', 
        $data['customerId'],
        $data['licensePlate'],
        $data['pickupBranchId'],
        $data['returnBranchId'],
        $data['pickupDateTime'],
        $data['returnDateTime'],
        $data['initialTotalCost'],
        $data['discountAmount'],
        $data['finalTotalCost'],
        $promotionId
    );

    // Execute
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create rental: ' . mysqli_stmt_error($stmt));
    }

    $rentalId = mysqli_insert_id($conn);

    // Update scooter status to 'Rented'
    $updateSql = "UPDATE scooter SET Status = 'Rented' WHERE License_Plate = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);
    
    if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, 's', $data['licensePlate']);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'rentalId' => $rentalId,
        'message' => 'Rental created successfully'
    ]);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

ob_end_flush();
?>