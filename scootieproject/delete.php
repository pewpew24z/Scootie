<?php
// delete.php — Delete 1 แถวตาม PK
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require_once __DIR__ . '/db_connect.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
  http_response_code(500); echo json_encode(['error'=>'$conn not available']); exit;
}

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

function body(){ $j=json_decode(file_get_contents('php://input'), true); return is_array($j)?$j:$_POST; }
function pick($arr,$keys,$d=null){ foreach($keys as $k){ if(isset($arr[$k])) return $arr[$k]; } return $d; }

$in = body();
$keyOrTable = pick($in, ['table','t','key','tbl']);
if (!$keyOrTable) { http_response_code(400); echo json_encode(['error'=>'Missing table']); exit; }

$lower = strtolower($keyOrTable);
$meta = $WL[$lower] ?? ['table'=>$keyOrTable,'pk'=>pick($in,['pk','pkcol','pkname'],'id')];
$table = $meta['table']; $pk = $meta['pk'];

$pkValue = pick($in, ['pkValue','id',$pk]);
if ($pkValue===null) { http_response_code(400); echo json_encode(['error'=>'Missing pkValue']); exit; }

$sql = "DELETE FROM `$table` WHERE `$pk` = ?";
$stmt = $conn->prepare($sql);
if (!$stmt){ http_response_code(500); echo json_encode(['error'=>'Prepare failed','detail'=>$conn->error]); exit; }

if ($pk==='License_Plate') $stmt->bind_param('s', $pkValue);
else if (is_numeric($pkValue)) $stmt->bind_param('i', $pkValue);
else $stmt->bind_param('s', $pkValue);

if (!$stmt->execute()){
  http_response_code(409); echo json_encode(['error'=>'Delete failed (constraint?)','detail'=>$stmt->error]); exit;
}
echo json_encode(['ok'=>true,'affected'=>$stmt->affected_rows]);
