<?php
// 独立于主题,所有第三方音乐接口集合到这里,也可独立作为API页面

// 接入meting api
require THEME_DIR . '/inc/pix-meting.php';
use Metowolf\Meting;

// 设置cookie
/*if ($server == 'netease') {
    $api->cookie('os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; MUSIC_U=****** ; __remember_me=true');
}*/

//音乐api密匙
define('AUTH_M', get_op('mu_api_key_on') ? get_op('mu_api_key_on') : false);
define('MUSIC_AUTH', get_op('pix_mu_api_key') ? get_op('pix_mu_api_key') : 'pix-music');
function music_auth($name)
{
    return hash_hmac('sha1', $name, MUSIC_AUTH);
}

function music_cookie($source,$API){
    //$API = new \Metowolf\Meting($source);
    $netease = get_op('netease_cookie');
    $tencent = get_op('tencent_cookie');
    $kugou = get_op('kugou_cookie');
    $kuwo = get_op('kuwo_cookie');
    switch ($source)
        {
        case "netease":
            if($netease){
                $API->cookie($netease);
            }
            break;
        case "tencent":
            if($tencent){
                $API->cookie($tencent);
            }
            break;
        case "kuwo":
            if($kuwo){
                $API->cookie($kuwo);
            }
            break;
        case "kugou":
            if($kugou){
                $API->cookie($kugou);
            }
            break;    
        }
}


//获取歌单
function data_playlist( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    $result = json_decode( $API->format(true)->playlist($id) );

    return $result;
}

//专辑
function data_album( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    $result = json_decode( $API->format(true)->album($id) );

    return $result;
}

function songdata( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    $result = $API->format(true)->song($id);
    $data = json_decode($result);
  
    return $data;
}

//获取歌曲链接
function song( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    music_cookie($source,$API);
    $result = $API->format(true)->url($id);
    $data = json_decode($result);
    $url = str_replace('http://', 'https://', $data->url);

    
    return $url;
}

//获取歌词
function lyric( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    $result = $API->format(true)->lyric($id);
    $data = json_decode($result);

    return $data->lyric;
}

//获取封面
function cover( $id, $source ) {
    $API = new \Metowolf\Meting($source);
    $result = $API->format(true)->pic($id);
    $data = json_decode($result);

    return $data->url;
}

//返回api数组
function music_api_return($type,$source,$id){
    switch ( $type ) {
        case 'list':
            $data = data_playlist($id, $source);
            break;
        case 'album':
            $data = data_album($id, $source);
            break;
        case 'song':
            $data = songdata($id, $source);
            break;    
    }

    return $data;
}

//音乐列表接口
function music_lb_arr($source,$id,$type){
    switch ( $type ) {
        case 'list':
            $data = data_playlist($id, $source);
            break;
        case 'album':
            $data = data_album($id, $source);
            break;   
    }

    if ( !empty($data) && is_array($data) ) {
        $res = array();
        foreach ($data as $key => $song) {
           
        $audio_list = array(
                'title' => $song->name,
                'artist' => $song->artist,
                'pid' => music_api_url('pic',$song->pic_id,$source),
                'mid' => music_api_url('url',$song->url_id,$source),
                'lid' => music_api_url('lrc',$song->lyric_id,$source),
                'source' => $source
        );
         
        array_push($res,$audio_list);       
            
        }

        return json_encode($res);
        exit();
    }
}

//单曲接口
function song_arr($source,$id){
    $data = songdata( $id, $source );

    if ( !empty($data) && is_array($data) ) {
        $arr = array(
            'title' => $data[0]->name,
            'author' => $data[0]->artist[0],
            'cover' => music_api_url('pic',$data[0]->pic_id,$source),
            'url' => music_api_url('url',$data[0]->url_id,$source),
        );

        return json_encode($arr);
        exit();
    }
}


//音乐api链接获取
function music_api_url($type,$id,$source){
    $auth = '';
    if(in_array($type, ['url', 'pic', 'lrc'])){
        $auth = AUTH_M ? '&auth=' . music_auth($source . $type . $id) : '';
    }

    $api_url = get_op('pix_mu_api');
    
    $mu_url = $api_url ? $api_url : home_url();

    return $mu_url.'/musicapi/?type='.$type.'&id='.$id.'&source='.$source.$auth;   
}


//api重定向
function return_data($type, $data)
{
    if (in_array($type, ['url', 'pic'])) {
        header('Location: ' . $data);
    } else {
        echo $data;
    }
    exit;
}