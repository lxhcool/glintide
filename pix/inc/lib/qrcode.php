<?php 
//qrcode 二维码
require_once 'phpqrcode.php';
$url = urldecode($_GET['data']); // remember to sanitize that - it is user input!
ob_start();
QRcode::png($url,false,QR_ECLEVEL_M,6,2);
$data = ob_get_contents();
ob_end_clean();

$imageString = base64_encode($data);
header("content-type:application/json; charset=utf-8");
return 'data:image/jpeg;base64,' . $imageString;