<?php
// read.php — JSON: {fields, rows}
// ใช้ $conn จาก db_connect.php เท่านั้น
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500); echo json_encode(['error'=>'$conn not available']); exit;
}

// map key -> real table / pk
$WL = [
  'branch'=>['table'=>'Branch','pk'=>'Branch_ID'],
  'account'=>['table'=>'Account','pk'=>'Account_ID'],
  'customer'=>['table'=>'Customer','pk'=>'Customer_ID'],
  'employee'=>['table'=>'Employee','pk'=>'Employee_ID'],
  'scooter'=>['table'=>'Scooter','pk'=>'License_Plate'],
  'promotion'=>['table'=>'Promotion','pk'=>'Promotion_ID'],
  'rental'=>['table'=>'Rental','pk'=>'Rental_ID'],
  'maintenance'=>['table'=>'Maintenance','pk'=>'Maintenance_ID'],
];

function pick($arr, $keys, $default=null){ foreach($keys as $k){ if(isset($arr[$k])) return $arr[$k]; } return $default; }

$src = $_GET;
$keyOrTable = pick($src, ['table','t','key','tbl']);
if (!$keyOrTable) { http_response_code(400); echo json_encode(['error'=>'Missing table']); exit; }

$lower = strtolower($keyOrTable);
if (isset($WL[$lower])) { $table = $WL[$lower]['table']; }
else { $table = $keyOrTable; } // เผื่อส่งชื่อจริงมาแล้ว

$limit  = max(1, min(1000, (int)pick($src, ['limit'], 500)));
$offset = max(0, (int)pick($src, ['offset'], 0));

$q = $conn->query("SELECT * FROM `$table` LIMIT $limit OFFSET $offset");
if (!$q) { http_response_code(500); echo json_encode(['error'=>'Query failed','detail'=>$conn->error]); exit; }

$rows = [];
while ($r = $q->fetch_assoc()) $rows[] = $r;

$fields = [];
$m = $conn->query("SHOW COLUMNS FROM `$table`");
while ($f = $m->fetch_assoc()) $fields[] = $f['Field'];

echo json_encode(['fields'=>$fields, 'rows'=>$rows]);
