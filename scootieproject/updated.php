<?php
// updated.php — Update 1 แถวแบบ partial
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
$data = $in['data'] ?? $in['fields'] ?? null;
if ($pkValue===null || !is_array($data) || empty($data)) {
  http_response_code(400); echo json_encode(['error'=>'Invalid payload']); exit;
}
unset($data[$pk]); // ไม่ให้แก้ PK

$sets=[]; $types=''; $params=[];
foreach ($data as $col=>$val){
  $sets[]="`$col`=?"; 
  if (is_null($val))                               { $types.='s'; $params[] = null; }
  else if (is_numeric($val) && strpos((string)$val,'.')!==false) { $types.='d'; $params[]=(float)$val; }
  else if (is_numeric($val))                       { $types.='i'; $params[]=(int)$val; }
  else                                            { $types.='s'; $params[]=$val; }
}
if (!$sets){ echo json_encode(['ok'=>true,'affected'=>0]); exit; }

// type ของ PK
if ($pk==='License_Plate') $types.='s';
else if (is_numeric($pkValue)) $types.='i';
else $types.='s';
$params[]=$pkValue;

$sql = "UPDATE `$table` SET ".implode(',',$sets)." WHERE `$pk` = ?";
$stmt = $conn->prepare($sql);
if (!$stmt){ http_response_code(500); echo json_encode(['error'=>'Prepare failed','detail'=>$conn->error]); exit; }
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()){ http_response_code(409); echo json_encode(['error'=>'Update failed','detail'=>$stmt->error]); exit; }

echo json_encode(['ok'=>true,'affected'=>$stmt->affected_rows]);
