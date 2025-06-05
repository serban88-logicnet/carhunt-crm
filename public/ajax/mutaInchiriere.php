<?php
// File: public/ajax/mutaInchiriere.php
// Moves an existing booking to a different car, with the same overlap & document checks as createInchiriere.php.

require_once "../../app/bootstrap.php";
$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error','message'=>'Invalid request method']);
    exit;
}




$bookingId    = (int)$_POST['bookingId'];
$newCarId     = (int)$_POST['newCarId'];
$data_inceput = $_POST['startDate'];
$data_sfarsit = $_POST['endDate'];
$ora_inceput  = $_POST['startTime'];
$ora_sfarsit  = $_POST['endTime'];
$skipCheck    = isset($_POST['skipCheck'])    ? $_POST['skipCheck']    : 'false';
$skipCarCheck = isset($_POST['skipCarCheck']) ? $_POST['skipCarCheck'] : 'false';
$skipOverlap  = isset($_POST['skipOverlap'])  ? $_POST['skipOverlap']  : 'false';

// 1) fetch the booking we're moving
$query = "SELECT * FROM inchirieri WHERE id = :id";
$orig = $controller->generalModel->executeQueryPdo($query, [':id'=>$bookingId]);
if (!$orig) {
    echo json_encode(['status'=>'error','message'=>'Rezervare inexistentă']);
    exit;
}
$orig = $orig[0];



// 2) car-document checks (same as createInchiriere)
if ($skipCarCheck==='false') {
    $today = date('Y-m-d');
    $cquery = "SELECT m.*, ol.nume as marcata, m.observatii_marcaj 
               FROM masini m 
               LEFT JOIN other_lists ol ON ol.id=m.marcata_pentru 
               WHERE m.id=:mid";
    $car = $controller->generalModel->executeQueryPdo($cquery, [':mid'=>$newCarId])[0];
    function chk($d,$t,$e,$l){ if($d<$t) return "$l expirat\n"; if($d>$t && $d<$e) return "$l expiră în rezervare\n"; return '';}
    $msg = '';
    $msg .= chk($car->asigurare, $today, $data_sfarsit, 'Asigurare');
    $msg .= chk($car->vigneta,   $today, $data_sfarsit, 'Vigneta');
    $msg .= chk($car->itp,       $today, $data_sfarsit, 'ITP');
    $msg .= chk($car->data_ultima_revizie, $today, $data_sfarsit, 'Revizie');
    if (($car->km_actuali - $car->revizie_facuta)>10000) $msg.="Peste 10.000 km de la revizie\n";
    if (!empty($car->marcata)) $msg.="Mașina marcată pentru {$car->marcata}: {$car->observatii_marcaj}\n";
    if ($msg!=='') {
        echo json_encode(['status'=>'error','errorCode'=>'03','message'=>$msg]);
        exit;
    }
}

// 3) overlap check (date+time)
$newStart = strtotime("$data_inceput $ora_inceput");
$newEnd   = strtotime("$data_sfarsit $ora_sfarsit");
$oq = "SELECT * FROM inchirieri
       WHERE masina_id=:mid
         AND data_inceput<=:ds
         AND data_sfarsit>=:di
         AND id<>:bid
         AND status <> 32";
$params = [':mid'=>$newCarId,':di'=>$data_inceput,':ds'=>$data_sfarsit,':bid'=>$bookingId];
$others = $controller->generalModel->executeQueryPdo($oq,$params);

// dd($others);

$maxOvl=0;
foreach($others as $o){
  $s2=strtotime("$o->data_inceput $o->ora_inceput");
  $e2=strtotime("$o->data_sfarsit $o->ora_sfarsit");
  $ovlStart = max($newStart,$s2);
  $ovlEnd   = min($newEnd,$e2);
  $sec = max(0,$ovlEnd-$ovlStart);
  $hrs = $sec/3600;
  $maxOvl = max($maxOvl,$hrs);
}

// if not skipping and there is overlap, warn
if ($skipOverlap==='false' && $others) {
  if ($maxOvl>OVERLAPTIME) {
    echo json_encode([
      'status'=>'error','errorCode'=>'04',
      'message'=>"Suprapunere semnificativă (".round($maxOvl,1)." ore). Override?"
    ]);
    exit;
  }
  if (abs($maxOvl-OVERLAPTIME)<0.01) {
    echo json_encode([
      'status'=>'error','errorCode'=>'05',
      'message'=>"Exact ".OVERLAPTIME." ore suprapunere. Continui (ambele active)?"
    ]);
    exit;
  }
}

// if heavy overlap & skipOverlap, mark overwritten
if ($others && $skipOverlap==='true' && $maxOvl>OVERLAPTIME) {
  foreach($others as $o){
    $u1="UPDATE inchirieri SET status=32 WHERE id=:id";
    $controller->generalModel->executeQueryPdo($u1,[':id'=>$o->id]);
    if (!empty($o->oferta_id)) {
      $u2="UPDATE oferte SET status_oferta=16 WHERE id=:oid";
      $controller->generalModel->executeQueryPdo($u2,[':oid'=>$o->oferta_id]);
    }
  }
}

// 4) perform the move
$uq = "UPDATE inchirieri SET masina_id=:mid WHERE id=:bid";
if ($controller->generalModel->executeQueryPdo($uq,[':mid'=>$newCarId,':bid'=>$bookingId])) {
  echo json_encode(['status'=>'success']);
} else {
  echo json_encode(['status'=>'error','message'=>'Nu s-a putut muta rezervarea']);
}
