<?php 
//通用函数


//获取头像
function get_user_avatar(){
    $de_img = THEME_URL.'/img/avatar.png';
    $img = '<img src="'.$de_img.'">';
    $email = get_option('admin_email');
    $type = get_op('avatar_type');
    if($type == 'email'){
        $img = get_avatar( $email, '100' );
    } else if($type == 'custom'){
        $img = '<img src="'.get_op('default_avatar').'">';
    }

    return $img; 
}

//获取昵称
function get_nickname(){
    $nice_name = get_op('nice_name');
    if($nice_name){
        $name = $nice_name;
    } else {
        $user = get_user_by('id', 1);
        $name = $user->display_name;
    }

    return $name;
}

//获取个人简介
function get_admin_des(){
    $des = 'Born for design';
    $des_op = get_op('admin_des');
    if($des_op){
        $des = $des_op;
    } else {
        $des = get_bloginfo ( 'description' );
    }

    return $des;
}


//日期格式
//timeago(get_gmt_from_date(get_the_time('Y-m-d G:i:s')));
//timeago(get_gmt_from_date(get_comment_date('Y-m-d G:i:s')));
function timeago($time) {
    date_default_timezone_set ('ETC/GMT');	
    $time = strtotime($time);
    $difference = time() - $time; 
    switch ($difference) { 
    	case $difference <= '1' :
            $msg = '刚刚';
            break; 
        case $difference > '1' && $difference <= '60' :
            $msg = floor($difference) . '秒前';
            break; 
        case $difference > '60' && $difference <= '3600' :
            $msg = floor($difference / 60) . '分钟前';
            break;
         case $difference > '3600' && $difference <= '86400' :
            $msg = floor($difference / 3600) . '小时前';
            break; 
        case $difference > '86400' && $difference <= '604800' :
            $msg = floor($difference / 86400) . '天前';
            break; 
        case $difference > '604800' && $difference <= '2592000' :
            $msg = floor($difference / 604800) . '周前';
            break;    
        case $difference > '2592000':
            $msg = ''.date('Y年n月j日',$time).'';
            break;
    } 
    return $msg;
}

//加载自定义字体
if( get_op( 'custom_fonts' ) ) add_action( 'wp_head', 'custom_fonts' );
function custom_fonts() {
	$fonts = get_op( 'custom_fonts' );
	echo "<!-- 自定义css -->\n<style type=\"text/css\">\n";
	echo '@font-face{
			font-style:normal;
			font-family:"HarmonyOS_M";
			src:url("'.$fonts.'") format("truetype");
            font-display:swap;
		  }';
	echo "\n</style>\n";

	
}

//禁止自动裁剪

add_filter('big_image_size_threshold', '__return_false');  
 
//禁用其他尺寸
 
function shapeSpace_disable_medium_large_images($sizes) {
 
unset($sizes['medium_large']); // disable 768px size images
 
unset($sizes['1536x1536']);    // disable 2x medium-large size 
unset($sizes['2048x2048']);    // disable 2x large size return $sizes;
 
return $sizes;
 
}
add_filter('intermediate_image_sizes_advanced', 'shapeSpace_disable_medium_large_images');

//获取自己IP归属地
function getip(){
    static $ip = ''; 
    $ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
      $ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {  
      $ip = $_SERVER['HTTP_CLIENT_IP']; 
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) { 
      foreach ($matches[0] AS $xip) {
        if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
          $ip = $xip;
          break; 
        }
      }
    } 
    return $ip; 
  }

function get_myip(){
    $response = wp_remote_get( 'https://restapi.amap.com/v3/ip?ip='.getip().'&key='.get_op('gaode_key').'' );
    if ( is_array( $response ) && !is_wp_error($response) && $response['response']['code'] == '200' ) {
        $header = $response['headers']; // array of http header lines
        $body = $response['body']; // use the content
    }
    $data = json_decode(($body), true);
    $arr = array(
        'city' => $data['city'] ? $data['city'] : 'X市',
        'province' => $data['province'] ? $data['province'] : 'X省',
        'status' => $data['status'],
        'info' => $data['info']
    );
    echo json_encode($arr);
    exit();
}
add_action('wp_ajax_nopriv_get_myip', 'get_myip');
add_action('wp_ajax_get_myip', 'get_myip');

//获取我的位置cookie
function get_loca_cookie(){
    if(isset($_COOKIE['mylocal'])){
        return $_COOKIE['mylocal'];
    } else {
        return '';
    }
}

/*获取当前网址 默认固定链接无法使用*/
function cst_get_curl(){
	global $wp;
	return home_url(add_query_arg(array(),$wp->request));
}

//footer版权 备案 1.0.6版本去除
/*function footer_box(){
    $beian_text = get_op('beian_text');
    $beian_link = get_op('beian_link');
    $diy = get_op('footer_text_diy');
    $copyright = '';
    if(get_op('copyright_sw') == true){
        $copyright = '<div class="copyright">THEME BY · <a href="https://pixit.cn" target="_blank">PIXIT</a></div>'; 
    }
    $html = ' <footer id="colophon" class="site-footer">
                <div class="footer_top">
                    <a href="'.$beian_link.'" class="beian" target="_blank">'.$beian_text.'</a>
                    <span>|</span>
                    '.$copyright.'
                </div>
                <div class="footer_text">'.$diy.'</div>
            </footer>';
    return $html;
}
*/


//底部菜单 1.0.4更新为音乐
function get_footer_nav(){
    $html = '<div class="footer_nav_box">
                <div class="inner footer_player uk-grid-collapse" uk-grid>
                    <div class="left uk-width-1-3@m uk-visible@m">
                        <div class="left_inner">
                          
                        </div>
                    </div>
                        <div class="right uk-width-2-3@m">
                        <div class="right_inner">
                            <div class="bgm_box">'.pixplay_box().'</div>
                        </div>
                        
                        </div>
                   
                </div>  
                </div>';
    if(get_op('bgm_open')){
        return $html;
    }         
}

//底部菜单自定义
function footer_nav_link(){
    $output = '';
    $link_group = '';
    $lists =  get_op('footer_nav');
    if(isset($lists) && is_array($lists)){
    foreach($lists as $list){
        $title = $list['fn_title'];
        $icon = $list['fn_icon'];
        $img = $list['fn_img'];
        $link_type = $list['fn_link'];
        $link_text = $list['fn_link_text'];
        $new = $list['open_new'];
        if($icon){
            $f_icon = $icon;
        } else {
            $f_icon = '<img src="'.$img.'">';
        }

        if($new == true){
            $target = 'target="_blank"';
        } else { 
            $target = '';
        }
        switch($link_type){
            case 'custom':
                $f_link = '<a href="'.$link_text.'" '.$target.'><i class="'.$f_icon.'"></i><span>'.$title.'</span></a>';
                break;
            case 'links':
                $f_link = '<a href="#link_modal" uk-toggle><i class="'.$f_icon.'"></i><span>'.$title.'</span></a>';
                break;   
        }

       
        
            global $wpdb;
            $link_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->links WHERE link_visible = 'Y'");
            if($link_count == '0'){
                $count_text = '您还没有朋友，赶快去寻找吧！';
            } else{
                $count_text = '共计有'.$link_count.'个邻居';
            }
            $link_group = '<div id="link_modal" uk-modal>
                            <div class="uk-modal-dialog" uk-overflow-auto><button class="uk-modal-close-default" type="button" uk-close></button>
                            <div class="links_modal_inner"><div class="links_count"><div class="text">'.$count_text.'</div></div>'.link_item().'</div>
                            </div>
                            </div>';
        

        $output .= '<li>'.$f_link.'</li>';
    }}
    return $output.'<div class="modal_box">'.$link_group.'</div>'; 
}

function msg_modal_box(){
    $msg_group = '';
    $check_read = '';
    $read_num = '';
    $check_read = get_unread_number();
    $read_num = $check_read ? '<div class="unread_tip"># 您有'.$check_read.'条未读消息 #</div>' : '';
            $read = get_msg_list('read');
            $unread = get_msg_list('unread');
            if($read || $unread){
                $msg_list = '<div class="unread_box">'.@$read_num.''.$unread.'</div>
                                <div class="read_box">'.$read.'</div>';
            } else {
                $msg_list = '<p class="no_posts"><small># 暂无消息 #</small><img class="s_nodata" src="'.THEME_URL.'/img/nodata.png"></p>';
            }
            
            $msg_group = '<div id="msg_modal" uk-modal>                          
                            <div class="uk-modal-dialog" uk-overflow-auto>
                            <div class="msg_title"><i class="ri-mail-unread-line"></i>消息盒子</div>
                            <button class="uk-modal-close-default" type="button" uk-close></button>
                            <div class="msg_modal_inner">
                                '.$msg_list.'
                                <p class="msg_limit">只显示最新10条未读和已读信息</p>
                            </div>
                            </div>
                            </div>';

            return $msg_group;            
}

function msg_btn(){
    $check_read = '';
    $check_read = get_unread_number();
    $num_html = ($check_read >0) ? '<small class="f_unread_num">'.get_unread_number().'</small>' : '';
    return '<div class="top_tool"><a class="com_msg_btn" href="#msg_modal"  check="'.$check_read.'" uk-toggle><i class="ri-notification-2-line"></i>'.$num_html.'</a></div>';
}

//友链
function link_item(){
    $arr = get_op('linkscat_show');
    $linkcats = get_terms( array(
        'taxonomy'     => 'link_category',
        'include'      => $arr,
        'count'        => true,
        'hide_empty'   => 1,
        'orderby'      => 'include',
    ) );
    $output = '';
	foreach($linkcats as $linkcat){
		$id = $linkcat->term_id;
		$output .= '<div class="link_group_content"><div class="link_cat_name"><i class="ri-bookmark-line"></i>'.$linkcat->name.'</div><div id="link_'.$id.'" class="link_group">';
			$bookmarks = get_bookmarks( 'orderby=date&category='.$id);
			if ( !empty($bookmarks) ) {
				foreach ( $bookmarks as $bookmark ) {
					$img_type = $bookmark->link_notes ? $bookmark->link_notes : '';
					$preg = "/^http(s)?:\\/\\/.+/";
                    $avatar = '<img src="'.THEME_URL.'/img/avatar.png">';
                    if(!empty($img_type)){
                        if ( preg_match($preg,@$img_type) ) {
                            $avatar = '<img alt="avatar" src="'. $bookmark->link_notes .'" srcset="'. $bookmark->link_notes .'" class="avatar avatar-80" height="80" width="80">';
                        }
                        else {
                            $avatar = get_avatar( $bookmark->link_notes, 80 );
                        }
                    }
					
					$output .= '<div class="item" title="'. $bookmark->link_description .'">';
					$output .= '<div class="link-avatar"><a href="'. $bookmark->link_url .'" target="_blank">'. $avatar .'</a></div>';
					$output .= '<div class="info">';
					$output .= '<h3 class="name"><a href="'. $bookmark->link_url .'" target="_blank">'. $bookmark->link_name .'</a></h3>';
					$output .= '<div class="meta button"><a href="'. $bookmark->link_url .'" target="_blank"><i class="iconfont icon-zhuanfa_3"></i></a></div>';
					$output .= '<div class="description">'. $bookmark->link_description .'</div></div>';
					$output .= '</div>';
				}
			}
			$output .= '</div></div>';
	}

    return $output;
   
}

//server评论消息推送
function pix_server_comment_notify($comment_id) {
    $text = get_bloginfo('name'). '上有新的评论';
    $comment = get_comment($comment_id);
    $desp = $comment->comment_author.' 在片刻ID['.$comment->comment_post_ID.']中的留言为：'.$comment->comment_content;
    $key = get_op('send_key');
    $response = wp_remote_post( 'https://sctapi.ftqq.com/'.$key.'.send?title='.$text.'&desp='.$desp.'');
    
    }
if(get_op('server_push') == true) {    
add_action('wp_insert_comment', 'pix_server_comment_notify', 19, 2);
}

//顶部封面随机图
function top_banner(){
    $type = null !== get_op('topbg_banner_type') ? get_op('topbg_banner_type') : 'local';
    if($type == 'local'){
        $tbg = get_op('topbg_banner'); // for eg. 15,50,70,125
        $bg_lists = explode( ',', $tbg );
    } else {
        $tbg = get_op('topbg_banner_link'); // for eg. 15,50,70,125
        $bg_lists = explode( "\r\n", $tbg );
    }
    
    $output = '';
if ( ! empty( $bg_lists ) ) {
    $rand = array_rand($bg_lists);
    $ga =  $type == 'local' ? wp_get_attachment_url( $bg_lists[$rand], 'full' ) : $bg_lists[$rand];
    $output = $ga ? $ga : THEME_URL.'/img/banner.jpg';
} 
return $output;
}

//首页搜索
function top_search(){
    $type = isset($_COOKIE["s_type"]) ? $_COOKIE["s_type"] : 'post';
    $p_active = ($type == 'post') ? 'active' : '';
    $m_active = ($type == 'moment') ? 'active' : '';
    $html = '<div id="search_modal" class="uk-modal-full" uk-modal>
                <div class="uk-modal-dialog search_modal_inner uk-animation-fast">
                    <button class="uk-modal-close-full uk-close-large s_close" type="button" uk-close></button>

                    <div class="search_box">
                    <div class="s_set_box">
                            <div class="inner">
                                <a class="'.$p_active.'" data="post">文章</a>
                                <a class="'.$m_active.'" data="moment">片刻</a>
                            </div>
                        </div>
                    <form method="get" id="index_search" class="search-form index_s_form" action="'.esc_url(home_url('/')).'">
                        <input class="s_input uk-input" type="search" name="s" placeholder="Search Something">
                        <input type="hidden" name="type" value="'.$type.'">
                        <i class="ri-search-fill"></i>
                    </form>	
                    </div>
                </div>
            </div>';
    return $html;        
}

/*-----------------------------------------------------------------------------------*/
/* 获取文章所属分类  
/*-----------------------------------------------------------------------------------*/
function get_post_first_cat_link() {
	$category = get_the_category();
	$cat_link = $category[0]->cat_name;
	return $cat_link;
}

//站点logo
function site_logo(){
    $logo = get_op('site_logo');
    $hei = get_op('logo_height');
    $class = $hei ? 'style="height:'.$hei.'px"' : '';

    $html = '<div class="top_logo"><a href="'.home_url('/').'"><img src="'.$logo.'" '.$class.'></a></div>';
    return $html;
}

function m_site_logo(){
    $logo = get_op('m_site_logo');
    //$hei = get_op('logo_height');
    //$class = $hei ? 'style="height:'.$hei.'px"' : '';

    $html = '<div class="top_logo close_bar"><a href="'.home_url('/').'"><img src="'.$logo.'"></a></div>';
    return $html;
}


/*-----------------------------------------------------------------------------------*/
/* 全局七牛缓存
/*-----------------------------------------------------------------------------------*/
if(get_op('qiniu_cdn')) {
    if (!is_admin()) {
        add_action('wp_loaded', 'loper_ob_start');
        function loper_ob_start() {
            ob_start('loper_qiniu_cdn_replace');
        }
        function loper_qiniu_cdn_replace($html) {
            $local_host = get_op('qiniu_local_host'); //博客域名
            $qiniu_host = get_op('qiniu_host'); //七牛域名
            $cdn_exts = 'png|jpg|jpeg|gif|mp4|mp3|wav|webp'; //扩展名（使用|分隔）
            $cdn_dirs = 'wp-content|wp-includes'; //目录（使用|分隔）
            $cdn_dirs = str_replace('-', '\-', $cdn_dirs);
            if ($cdn_dirs) {
                $regex = '/' . str_replace('/', '\/', $local_host) . '\/((' . $cdn_dirs . ')\/[^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
                $html = preg_replace($regex, $qiniu_host . '/$1$4', $html);
            } else {
                $regex = '/' . str_replace('/', '\/', $local_host) . '\/([^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
                $html = preg_replace($regex, $qiniu_host . '/$1$3', $html);
            }
            return $html;
        }
    }
    }

//ajax七牛云替换链接
function ajax_qiniu_cdn_replace($html) {
    
    $local_host = get_op('qiniu_local_host'); //博客域名
    $qiniu_host = get_op('qiniu_host'); //七牛域名
    $cdn_exts = 'png|jpg|jpeg|gif|mp4|mp3|wav|webp'; //扩展名（使用|分隔）
    $cdn_dirs = 'wp-content|wp-includes'; //目录（使用|分隔）
    $cdn_dirs = str_replace('-', '\-', $cdn_dirs);
    if ($cdn_dirs) {
        $regex = '/' . str_replace('/', '\/', $local_host) . '\/((' . $cdn_dirs . ')\/[^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
        $html = preg_replace($regex, $qiniu_host . '/$1$4', $html);
    } else {
        $regex = '/' . str_replace('/', '\/', $local_host) . '\/([^\s\?\\\'\"\;\>\<]{1,}.(' . $cdn_exts . '))([\"\\\'\s\?]{1})/';
        $html = preg_replace($regex, $qiniu_host . '/$1$3', $html);
    }
    return $html;
}    

//侧栏收缩
function body_class_set($classes){
    $class_arr = array();

    $theme_layout = get_op('layout_set');

    if(isset($theme_layout)){
        $class_arr[] = $theme_layout;
    }
    
    $dark = isset($_COOKIE["dark"]) ? $_COOKIE["dark"] : 'normal';
    $theme = get_op('theme_set');
    if($theme == 'dark-theme'){
        $class_arr[] = 'dark';
    } else {
        if($dark){
            $class_arr[] = $dark;
        }
    }



    return array_merge( $classes, $class_arr );

}  
add_filter( 'body_class', 'body_class_set' );  


add_theme_support(
    'custom-background',
    apply_filters(
        'pix_custom_background_args',
        array(
            'default-color' => 'd0dada',
            'default-image' => '',
        )
    )
);
add_theme_support( 'customize-selective-refresh-widgets' );

//主题配色
function theme_color(){
    $theme = get_op('theme_set') ? get_op('theme_set') : 'green-normal';
    if($theme == 'green-normal' || $theme == 'dark-theme'){
        return;
    } else {
        wp_enqueue_style( 'theme.color', THEME_URL . '/inc/assets/theme/'.$theme.'.css', array(), _S_VERSION );
    }
}

//后台字体 
if( ! function_exists( 'my_custom_icons' ) ) {

    function my_custom_icons( $icons ) {
    
        //
        // Use this for reset current icons
        // $icons = array();
    
        //
        // Adding new icons
        $icons[]  = array(
          'title' => 'pix_icon',
          'icons' => icon_array(),
        );
    
        //
        // Move custom icons to top of the list.
        $icons = array_reverse( $icons );
    
        return $icons;
    
      }
      add_filter( 'csf_field_icon_add_icons', 'my_custom_icons' );
    }

/*-----------------------------------------------------------------------------------*/
/* post views
/*-----------------------------------------------------------------------------------*/
function get_post_views ($post_id) {   
  
    $count_key = 'views';   
    $count = get_post_meta($post_id, $count_key, true);   
  
    if ($count == '') {   
        delete_post_meta($post_id, $count_key);   
        add_post_meta($post_id, $count_key, '0');   
        $count = '0';   
    }   
  
    echo number_format_i18n($count);   
  
}   
  
function set_post_views ($post_id) {   
    global $post; 
    $post_id = isset($post->ID) ? $post->ID : false;
    if($post_id){
        $count_key = 'views';   
        $count = get_post_meta($post_id, $count_key, true);   
      
        if (is_single() || is_page()) {   
      
            if ($count == '') {   
                delete_post_meta($post_id, $count_key);   
                add_post_meta($post_id, $count_key, '0');   
            } else {   
                update_post_meta($post_id, $count_key, $count + 1);   
            }   
      
        }   
    }
  
}   
add_action('get_header', 'set_post_views'); 
   

/**
 * 添加样式
 */
if( get_op( 'code_css' ) ) add_action( 'wp_head', 'addCss' );
function addCss() {
	$code_css = get_op( 'code_css' );
	echo "<!-- 自定义css -->\n<style type=\"text/css\">\n";
	echo $code_css;
	echo "\n</style>\n";
} 

/**
 * 添加js
 */
if( get_op( 'code_js' ) ) add_action( 'wp_footer', 'addjs', 50 );
function addjs() {
	$code_js = get_op( 'code_js' );
	echo "<!-- 自定义js -->\n<script type=\"text/javascript\">\n";
	echo $code_js;
	echo "\n</script>\n";
}

/**
 * 头部HTML
 */
if( get_op( 'head_html' ) ) add_action( 'wp_head', 'addhtmlmeta', 1);
function addhtmlmeta() {
	$code_html = get_op( 'head_html' );
	echo $code_html;
}

/**
 * 底部HTML
 */
if( get_op( 'footer_html' ) ) add_action( 'wp_footer', 'addhtmlfoot', 50);
function addhtmlfoot() {
	$code_html = get_op( 'footer_html' );
	echo $code_html;
} 

//站点备案
function pix_site_cr(){
    $html = '';
    $data = get_op('sfooter_info');
    if(is_array($data)){
        foreach($data as $list){
            $name = isset($list['sf_title']) ? $list['sf_title'] : '';
            $link = isset($list['sf_link']) ? $list['sf_link'] : '';
            $img = isset($list['sf_img']) ? '<img src="'.$list['sf_img'].'">' : '';
            $target = $list['sf_open_new'] ? 'target="_blank"' : '';
            $line = $list['sf_img'] == '' ? '' : 'oneline';

            $html .= '<li class="sf_item '.$line.'"><a href="'.$link.'" '.$target.'>'.$img.'<span>'.$name.'</span></a></li>';
        }

        echo '<div class="inner"><div class="items sf_wid_info">'.$html.'</div></div>';
    }

}

