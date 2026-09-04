<?php
// 音乐函数

function bg_music_autoload(){
    $playlistid = get_op('play_id');
    $type = get_op('mu_type');
    $source = get_op('mu_source');
    $data = get_music_data( $playlistid, $type, $source );
    echo $data ? $data : '0';
    exit();

}
add_action('wp_ajax_nopriv_bg_music_autoload', 'bg_music_autoload');
add_action('wp_ajax_bg_music_autoload', 'bg_music_autoload');

//文章音乐
function posts_music_autoload(){
    $pid = isset($_POST['pid']) ? $_POST['pid'] : '';
    $html= '';
    $meta = get_post_meta( $pid, '_pix_posts_options', true );
    if(isset($meta)){
        $type =  $meta['mus_type'];
        $source = $meta['mus_source'];
        $play_id = $meta['plays_id'];
        $on = $meta['mu_on'];
    }
   
    if($on){
        $data = get_music_data( $play_id, $type, $source );  
        echo  $data;   
    } 
    
    exit();
}
add_action('wp_ajax_nopriv_posts_music_autoload', 'posts_music_autoload');
add_action('wp_ajax_posts_music_autoload', 'posts_music_autoload');


//获取api json
function music_remote_get($url){
    $response = wp_remote_get($url);
    if ( is_array( $response ) && !is_wp_error($response) && $response['response']['code'] == '200' ) {
        $header = $response['headers']; // array of http header lines
        $body = $response['body']; // use the content
    }

    return $body;
}

//输出歌曲列表专辑数据  从远程api或本地获取数据
function get_music_data( $id, $type, $source ) {
    $api_url = get_op('pix_mu_api');
    $url = music_api_url($type,$id,$source);
    if($api_url) {
        $res = music_remote_get($url);
    } else {
        $res = music_lb_arr($source,$id,$type);
    }

    return $res;
}

//获取单曲音乐数据 从远程api或本地获取数据
function get_music_info($id,$source){
    $api_url = get_op('pix_mu_api');
    $url = music_api_url('song',$id,$source);

    if($api_url) {
        $res = music_remote_get($url);
    } else {
        $res = song_arr($source,$id);
    }

    return $res;
}

//播放器HTML
function pixplay_box(){
    $state = get_op('bgm_open') ? 'on' : 'off';
    $list = '';
    $html = '';
    if(get_op('bgm_open')){
        $list = '<a class="m_list"><i class="ri-bar-chart-horizontal-line"></i></a>';
    }
    $html = '<div class="player_mod" state="'.$state.'">
                <div class="player_hand"></div>
                <div class="top">
                    <div class="left">
                        <div class="m_cover"><img src="'.THEME_DEFAULT_URL.'"></div>
                        <div class="m_info">
                            <div class="m_tt"><h2>加载中..</h2><small>作者..</small></div>
                            <div class="timer"><div class="current_time">00:00</div><span>/</span><div class="total_time">00:00</div></div>
                        </div>
                    </div>

                    <div class="pl_btn">
                        <a class="m_prev"><i class="ri-skip-back-fill"></i></a>
                        <a class="m_play"><i class="ri-play-circle-fill"></i></a>
                        <a class="m_next"><i class="ri-skip-forward-fill"></i></a>
                        '.$list.'
                        <a class="m_volume"><i class="ri-volume-down-fill"></i></a>
                        <div class="musci_list_box" uk-dropdown="mode: click;offset:11;toggle:.m_list;pos:top-center;boundary: .bgm_box;flip: false; stretch: x;animation:uk-animation-slide-bottom-small"></div>
                        <div class="volume_box" uk-dropdown="mode: hover;offset:6;toggle:.m_volume;pos:top-center;animation:uk-animation-slide-bottom-small"><div class="vo_bar"><div class="vo_size"></div></div></div>
                    </div>
                    
                </div>
                <div class="tool">
                    <div class="player_bar"><div class="progress"><div class="player_dot"></div></div></div>
                </div>
                
            </div>';
    return $html;       
}