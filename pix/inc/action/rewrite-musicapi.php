<?php
$type = $_GET['type'];
$source = $_GET['source'];
$id = $_GET['id'];

// 数据格式
if (in_array($type, ['song', 'list','album'])) {
    header('content-type: application/json; charset=utf-8;');
}

//允许跨站
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');



//密匙
if (AUTH_M) {
    $auth = isset($_GET['auth']) ? $_GET['auth'] : '';
    if (in_array($type, ['url', 'pic', 'lrc'])) {
        if ($auth == '' || $auth != music_auth($source . $type . $id)) {
            http_response_code(403);
            exit;
        }
    }
}

switch ($type)
{
case "pic":
    $res = cover($id, $source);
    break;
case "url":
    $res = song( $id, $source );
    break;
case "lrc":
    $res = lyric( $id, $source );
    break;
case "list":
    $res = music_lb_arr($source,$id,'list');
    break;    
case "album":
    $res = music_lb_arr($source,$id,'album');
    break;  
case "song":
    $res = song_arr($source,$id);
    break;    
default:
    return false;       
}



return_data($type, $res);
