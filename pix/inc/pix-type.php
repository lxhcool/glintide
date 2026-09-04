<?php
function get_moment_type_content(){
    global $post;
    $output = '';
    $type = get_post_meta($post->ID,'moment_type',true);
    switch ($type)
        {
        case 'image':
            $output = get_gallery();
            break;
        case 'card':
            $output = get_m_card();
            break;
        case 'audio':
            $output = get_m_audio();
            break;  
        case 'video':
            $output = get_m_video();
            break;       
        default:
            $output = get_gallery();
        }

        return $output;
}


//卡片------------------------------------------------------
function head_append_meta() {
    $metas = [
        'og:title'     => get_the_title(),
        'og:site_name' => get_bloginfo( 'name' ),
        'og:type'      => 'website',
    ];
    if ( is_single() || is_page() ) {
        $type = get_post_type();
        $metas['og:url']         = get_permalink();
        $metas['og:description'] = get_the_excerpt();

        
        $meta_image_url = cst_get_thum( get_the_ID(), 'large');
        if ( $meta_image_url ) {
            $metas['og:image'] = $meta_image_url;
        }
        
        $metas['og:type'] = get_post_type();

        if($type == 'moment'){
            $metas['og:title'] = get_the_title() ? get_the_title() : get_the_excerpt();
            $moment_img = THEME_URL.'/img/banner.jpg';
            $lists = get_post_meta(get_the_ID(),'moment_ga',true);
            if(is_array($lists) && !empty($lists)){
                $moment_img = $lists[0]['thum'];
            }
            $metas['og:image'] = $moment_img;
        }
    }
    foreach ( $metas as $key => $value ) {
        echo '<meta property="' . $key . '" content="' . $value . '" />' . "\n";
    }
}

add_action( 'wp_head', 'head_append_meta', 1 );   

//获取卡片信息
function get_card_by_url($data){
    $preg = "/^http(s)?:\\/\\/.+/";
    $pid = $data;
    if ( preg_match($preg,$data) ) {
        $pid = url_to_postid($data);
    }   

    if($pid){
        $type = get_post_type($pid);
        $meta = array();
        $meta['title'] = get_the_title($pid);
        $meta['url'] = get_permalink($pid);
        $meta['des'] = get_the_excerpt($pid);
        $meta['pid'] = $pid;
        $meta_image_url = cst_get_thum( $pid, 'medium');
        if ( $meta_image_url ) {
            $meta['image'] = $meta_image_url;
        }
    
        if($type == 'moment'){
            $meta['title'] = '片刻';
            $moment_img = THEME_URL.'/img/banner.jpg';
                $lists = get_post_meta($pid,'moment_ga',true);
                if(is_array($lists) && !empty($lists)){
                    $moment_img = $lists[0]['thum'];
                }
                $meta['image'] = $moment_img;
        }

        return $meta;
    } else {
        return false;
    }
    
}

//卡片编辑
function card_type_box(){
    $output = '';
    $output = '<div class="add_card_box">
                    <div class="edit_card_box">
                        <div class="tips"># 输入本站网址,自动生成链接卡片 <span>支持文章, 片刻, 页面等网址</span></div>
                        <div class="edit_content">
                            <input type="text" placeholder="本站网址" name="moment_card_link" id="moment_card_link" required="required">
                            <a class="push_card">生成</a>
                        </div>
                    </div>
                    <div class="show_card"><div class="card_sortble" uk-sortable="handle: .moment_card_item"></div></div>
                </div>';
    echo $output;  
    exit();         
}
add_action('wp_ajax_nopriv_card_type_box', 'card_type_box');
add_action('wp_ajax_card_type_box', 'card_type_box');



function get_moment_card(){
    $card_url = esc_url($_POST['card_url']);
    $arr = get_card_by_url($card_url);
    $html = '';

    if(is_array($arr)){
        $html = '<div class="moment_card_item" pid="'.$arr['pid'].'">
                    <a>
                        <div class="left"><img src="'.$arr['image'].'"></div>
                        <div class="right"><h4>'.$arr['title'].'</h4><div class="content">'.$arr['des'].'</div></div>
                        <span class="de_card"><i class="ri-close-line"></i></span>
                    </a>
                </div>';
        $res = array('html' => $html,'state' => '1');        
    } else {
        $html = '未知错误';
        $res = array('html' => $html,'state' => '0');  
    }

    echo json_encode($res);
    exit();

}
add_action('wp_ajax_nopriv_get_moment_card', 'get_moment_card');
add_action('wp_ajax_get_moment_card', 'get_moment_card');

function get_m_card(){
    global $post;
    $pid = $post->ID;
    $lists = get_post_meta($pid,'moment_card',true);
    $html = '';
    if(is_array($lists) && !empty($lists)){
        foreach($lists as $index => $list){
                $arr = get_card_by_url($list);
                $html .= '<div class="moment_card_item loop_card_item" pid="'.$list.'">
                            <a href="'.$arr['url'].'" target="_blank">
                                <div class="left"><img src="'.$arr['image'].'"></div>
                                <div class="right"><h4>'.$arr['title'].'</h4><div class="content">'.$arr['des'].'</div></div>
                            </a>
                        </div>';             
        }
        return '<div class="card_list">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }
 
}   

//音乐编辑---------------------------------------------------------------------------------------------------------------------------------
function audio_type_box(){
    $output = '';
    $output = '<div class="add_audio_box">
                    <div class="edit_audio_box">
                        <div class="audio_choose" uk-switcher="animation: uk-animation-fade">
                            <li class="local"><a href="#" class="audio_c_btn" au_type="local">本地</a></li>
                            <li class="netease"><a href="#" class="audio_c_btn" au_type="netease">网易云</a></li>
                            <li class="tencent"><a href="#" class="audio_c_btn" au_type="tencent">QQ音乐</a></li>
                            <li class="kugou"><a href="#" class="audio_c_btn" au_type="kugou">酷狗</a></li>
                            <li class="kuwo"><a href="#" class="audio_c_btn" au_type="kuwo">酷我</a></li>
                        </div>
                        <div class="tips"># 请插入封面,歌名以及歌曲外链 <span></span></div>
                        <div class="edit_content audio_type uk-switcher">
                            <div class="loacl_audio">
                                <div class="audio_left m_media_left">
                                    <i class="ri-add-line"></i>
                                    <input type="file" name="moment_img_up" id="moment_img_up" accept="image/jpg,image/jpeg,image/png,image/gif,image/webp" multiple="multiple" title="上传封面">
                                </div>
                                <div class="audio_right">
                                    <div class="audio_meta">
                                        <input type="text" placeholder="歌名" name="moment_audio_name" id="moment_audio_name" class="required" required="required">
                                        <input type="text" placeholder="歌手(选填)" name="moment_audio_author" id="moment_audio_author">
                                    </div>
                                    <input type="text" placeholder="歌曲外链" name="moment_audio_url" id="moment_audio_url" class="required" required="required">
                                </div>
                            </div>
                            <div class="netease_audio type_audio_text"></div>
                            <div class="tencent_audio type_audio_text"></div>
                            <div class="kugou_audio type_audio_text"></div>
                            <div class="kuwo_audio type_audio_text"></div>
                        </div>
                    </div>
                </div>';
    echo $output;  
    exit();         
}
add_action('wp_ajax_nopriv_audio_type_box', 'audio_type_box');
add_action('wp_ajax_audio_type_box', 'audio_type_box');

function moment_cover_upload(){
    $valid_formats = array("jpg", "png", "gif", "webp", "jpeg"); 
    $wp_upload_dir = wp_upload_dir();
    $path = $wp_upload_dir['path'] . '/';
    $img_size = 5;
    $max_file_size = 1024000 * $img_size; 
    $files = $_FILES['moment_img_up'];
    
    if(!empty($files)){
        if(get_user_role('administrator') || get_user_role('author')){
            $filename = $files['name'];
            $file_type = strtolower($files['type']);
            $extension = pathinfo( $filename, PATHINFO_EXTENSION );
            $new_filename = pix_generate_random_code( 20 )  . '.' . $extension;

            if ( $files['error'] == 0 ) {

                if(!in_array( strtolower( $extension ), $valid_formats )){
                    $msg = array('code'=>'1','msg'=>'文件格式错误！');
                    //exit();
                } else if($files['size'] > $max_file_size){
                    $msg = array('code'=>'1','msg'=>'图片最大'.$img_size.'MB');
                    //exit();
                } else {
                if( move_uploaded_file( $files["tmp_name"], $path.$new_filename )){
                    $new_file = $path.$new_filename;
                    $filetype = wp_check_filetype( basename( $filename ), null );
                    $wp_upload_dir = wp_upload_dir();
                        $attachment = array(
                            'guid'           => $wp_upload_dir['url'] . '/' . basename( $new_file ),
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                            'post_content'   => '',
                            'post_status'    => 'inherit'
                        );
                    $attach_id = wp_insert_attachment( $attachment, $new_file);
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );
                           
                    // Generate meta data
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                   // $attach_url = wp_get_attachment_image_src($attach_id, 'full')[0];

                    $output = array(
                        'thumb' => wp_get_attachment_image_src($attach_id, 'medium')[0],
                        'attach_id' => $attach_id,
                    );

                    $msg = array('code'=>'0','msg' => $output);               
                }
            }

            }    

        }  
        echo json_encode($msg);
        exit();  
    }   

}
add_action('wp_ajax_nopriv_moment_cover_upload', 'moment_cover_upload');
add_action('wp_ajax_moment_cover_upload', 'moment_cover_upload');

function get_m_audio(){
    global $post;
    $pid = $post->ID;
    $data = get_post_meta($pid,'moment_audio',true);
    $html = '';

    if(is_array($data) && !empty($data)){
          
            $type = $data[0]['type'];
            if($type == 'local'){
                $audio_datas = $data[0];
            } else {
                $id = $data[0]['n_id'];

                //$cache_key = md5($id);
                $cache = wp_cache_get($id, 'moment_music', true);
                if ($cache !== false) {
                    $audio_datas = $cache;
                } else {
                    $audio_datas = json_decode(get_music_info($id,$type),true);
                    wp_cache_set($id, $audio_datas, 'moment_music');
                }
            }

            if(is_array($audio_datas)){
                $cover = $audio_datas['cover'];
                $title = $audio_datas['title'];
                $author = $audio_datas['author'];
                $url = $audio_datas['url'];

                $html = '<div class="pix_player">
                    <div class="player_thum">
                        <img class="lazy" data-src="'.$cover.'">
                    </div>
                    <div class="player_meta">
                        <div class="title"><span class="name">'.$title.'</span><span class="author">'.$author.'</span></div>
                        <div class="player_tool">
                            <div class="player_time"><div class="current_time"></div><span></span><div class="total_time"></div></div>
                        </div>
                    </div>
                    <a class="play_btn" data ="'.$url.'"><i class="ri-play-line"></i></a>
                    <div class="play_bg"><img class="lazy" src="'.THEME_URL .'/img/lazyload.png" data-src="'.$cover.'"></div>
                </div>';
            } else {
                $html = '音乐参数错误';
            }


        return '<div class="audio_list">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }
}


//视频模块 ------------------------------------------------------------------
function video_type_box(){
    $output = '';
    $output = '<div class="add_video_box">
                    <div class="edit_video_box">
                        <div class="video_choose" uk-switcher="animation: uk-animation-fade">
                            <li><a href="#" class="video_c_btn local" vi_type="local">本地</a></li>
                            <li><a href="#" class="video_c_btn bili" vi_type="bili">B站</a></li>
                        </div>
                        <div class="tips"># 请插入封面(可不设置),视频外链 <span></span></div>
                        <div class="edit_content video_type uk-switcher">
                            <div class="local_video">
                                <div class="video_left m_media_left">
                                    <i class="ri-add-line"></i>
                                    <input type="file" name="moment_img_up" id="moment_img_up" accept="image/jpg,image/jpeg,image/png,image/gif,image/webp" multiple="multiple" title="上传封面">
                                </div>
                                <div class="video_right">
                                    <div class="video_meta">
                                        <input type="text" placeholder="视频外链" name="moment_video_url" id="moment_video_url" class="required" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="bili_video">
                                <input type="text" placeholder="B站视频bvid" name="moment_video_bili" id="moment_video_bili" class="required" required="required">
                            </div>
                        </div>
                    </div>
                </div>';
    echo $output;  
    exit();         
}
add_action('wp_ajax_nopriv_video_type_box', 'video_type_box');
add_action('wp_ajax_video_type_box', 'video_type_box');

function get_m_video(){
    global $post;
    $pid = $post->ID;
    $data = get_post_meta($pid,'moment_video',true);
    $html = '';

    if(is_array($data) && !empty($data)){
          
            $type = $data[0]['type'];
            if($type == 'bili'){
                $bvid = $data[0]['bvid'];
                $html = '<div class="pix_bili_player"><iframe src="//player.bilibili.com/player.html?bvid='.$bvid.'&page=1" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true" > </iframe></div>';
            } else {
                $url = $data[0]['url'];
                $att_id = isset($data[0]['att_id']) ? $data[0]['att_id'] : '';
                $cover = "";
                if($att_id){
                    $cover = wp_get_attachment_image_src($att_id, 'full')[0];
                    $cover = 'background-image:url('.$cover.')';
                }
                $html = '<div class="pix_local_player">
                            <div class="video_play_btn" style="'.$cover.'"><a><i class="ri-play-mini-line"></i></a></div>
                            <video src="'.$url.'" id="pix_video_player" objectfit="cover" x5-video-player-type="h5" onplay="stopOtherMedia(this)"></video>
                        </div>';
            }

        return '<div class="video_list">
                    <div class="list_inner">'.$html.'</div>
                </div>';
    }
}