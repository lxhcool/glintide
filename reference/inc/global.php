<?php
/**
 * Pix主题 - 全局函数
 * 
 * 定义全局函数和加载所有功能模块
 * 
 * @package pix
 * @author lxhcool
 * @version 1.0.4
 */

//定义设置
if (!function_exists('get_op')) {
    function get_op($option = '', $default = null)
    {
        $options = get_option('ppo_options'); // Attention: Set your unique id of the framework
        return (isset($options[$option])) ? $options[$option] : $default;
    }
}

if (!function_exists('ppo_moment_label')) {
    function ppo_moment_label($key = 'moment'){
        $labels = array(
            'moment' => array('option' => 'moment_name', 'default' => '片刻'),
            'moments' => array('option' => 'moments_name', 'default' => '圈子'),
            'owner' => array('option' => 'moments_owner', 'default' => '圈主'),
            'user' => array('option' => 'moments_user', 'default' => '圈友'),
        );

        if (!isset($labels[$key])) {
            return '';
        }

        return get_op($labels[$key]['option'], $labels[$key]['default']);
    }
}

if (!function_exists('ppo_moment_slug')) {
    function ppo_moment_slug($option = 'moment_slug', $default = 'moment'){
        $slug = strtolower((string) get_op($option, $default));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ? $slug : $default;
    }
}

//加载所有模块
$pix_mods = array(
    'pix-rest',
    'pix-widget',
    'pix-shortcode',
    'pix-encode',
    'pix-credit',
    'pix-moment',
    'pix-object',
    'pix-uploader',
    'pix-upload-service',
    'pix-nav',
    'pix-comment',
    'pix-head',
    'pix-router',
    'pix-user',
    'pix-icon',
    'pix-classic',
    'pix-post',
    'pix-captcha',
    'pix-login',
    'pix-send',
    'pix-oauth',
    'pix-hide',
    'pix-msg',
    'pix-follow',
    'pix-level',
    'pix-task',
    'pix-sign',
    'nav-builder',
    'pix-layout',
    'pix-xcompat',
);

//加载所有功能模块
foreach( $pix_mods as $pix_mod) {
    $file = get_theme_file_path('inc/mod/' . $pix_mod . '.php');
    if (!file_exists($file)) {
        wp_die('主题核心文件缺失，请重新安装主题');
    }
    require $file;
}  

//主题设置文件
require_once get_theme_file_path('/inc/options/init.php'); 


//定义customize设置
if ( ! function_exists( 'get_cu' ) ) {
    function get_cu( $option = '', $default = null ) {
      // 优先读取 Glintide 设置面板（ppo_options），未设置时回退到定制器（ppo_customizer）
      $panel_options = get_option( 'ppo_options' );
      if ( is_array( $panel_options ) && array_key_exists( $option, $panel_options ) ) {
        return $panel_options[$option];
      }
      $options = get_option( 'ppo_customizer' ); // Attention: Set your unique id of the framework
      if ( strpos( $option, 'mobile_bottom_nav_' ) === 0
        && isset( $options['mobile_bottom_nav_tabs'] )
        && is_array( $options['mobile_bottom_nav_tabs'] )
        && array_key_exists( $option, $options['mobile_bottom_nav_tabs'] ) ) {
        return $options['mobile_bottom_nav_tabs'][$option];
      }
      return ( isset( $options[$option] ) ) ? $options[$option] : $default;
    }
  }

//图标
function pix_icon($name = ''){
    $icon = '<i class="'.$name.'"></i>';
    return $icon;
}

//站点logo
function pix_global_logo_text(){
    $text_logo = get_cu('logo_text');
    $text_logo = $text_logo ? $text_logo : get_op('text_logo');
    return $text_logo ? $text_logo : get_bloginfo( 'name' );
}

function pix_global_logo_url($type = 'dark'){
    $logo = '';
    if($type == 'light'){
        $logo = get_cu('site_logo_w');
    } else {
        $logo = get_cu('site_logo');
    }

    if (is_array($logo)) {
        $logo = $logo['url'] ?? '';
    }

    if (!$logo && $type == 'light') {
        $logo = get_cu('site_logo');
        if (is_array($logo)) {
            $logo = $logo['url'] ?? '';
        }
    }

    return $logo ? $logo : '';
}

function site_logo($type = ''){
    $logo = pix_global_logo_url($type ? $type : 'dark');
    $text_logo = pix_global_logo_text();

    if($logo){
        return '<img src="'.esc_url($logo).'" alt="'.esc_attr($text_logo).'">';
    } else {
        return '<h3>'.esc_html($text_logo).'</h3>';
    }
}

//body自定义样式
function body_class_set($classes){
    $class_arr = array();
    $class_arr[] = theme_mod();

    return array_merge( $classes, $class_arr );

}  
add_filter( 'body_class', 'body_class_set' ); 

//主题风格类型
function theme_mod(){
    $type = get_cu('web_mod' , 'classic');
    return $type;
}

//计算总体宽度
function get_total_width(){
    $center_width = get_cu('classic_center_width', 640) ? get_cu('classic_center_width', 640) : 640;
    $sidebar_width = get_cu('sidebar_width', 320) ? get_cu('sidebar_width', 320) : 320;
    $left = is_active_sidebar( 'blog-left' ) && get_cu('cls_left_wid',false);
    $right = is_active_sidebar( 'blog-right' ) && get_cu('cls_right_wid',false);
    
    $total_width = $center_width;
    if($left) $total_width += $sidebar_width;
    if($right) $total_width += $sidebar_width;
    
    return $total_width;
}

//获取文章特色图
function get_ppo_thum($pid,$size,$radom){
    $post = get_post($pid);
    if (!$post) {
        return default_feature($radom);
    }

    $wp_thum = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), $size );

	$first_img = '';
	ob_start();
	ob_end_clean();
	$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
	if(isset($matches[1][0])) {
	    $first_img = $matches[1][0];
	}

    if ($wp_thum) {
		$image_url = ( $wp_thum[0] != '' ) ? '' . $wp_thum[0] . '' : '""';
    } else {
        if($first_img){
            $image_url = $first_img;
        } else {
            $image_url = default_feature($radom);
        }
	}

    return $image_url;
}

//随机默认特色图
function default_feature($radom){
    $type = get_cu('def_thum_type','local');
    if($type == 'local'){
        $tbg = get_cu('def_thum', ''); // for eg. 15,50,70,125
        $fea_lists = is_array($tbg) ? $tbg : explode( ',', (string) $tbg );
        $fea_lists = array_filter(array_map('trim', $fea_lists));
    } else {
        $tbg = get_cu('def_thum_link', ''); // for eg. 15,50,70,125
        $fea_lists = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $tbg)));
    }

    $output = THEME_URL.'/img/banner.jpg';
    if ( ! empty( $fea_lists ) ) {
        $rand = array_rand($fea_lists);
        $radom_link = $radom == 'random' ? $fea_lists[$rand] : $fea_lists[0];
        $ga =  $type == 'local' ? wp_get_attachment_url( $radom_link, 'full' ) : $radom_link;
        $output = $ga ? $ga : THEME_URL.'/img/banner.jpg';
    } 
    return $output;
}

//获取自己本机IP
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

// 获取ip归属地
function get_ip_location($ip){
    $ip = trim((string) $ip);
    if(!filter_var($ip, FILTER_VALIDATE_IP) || !pix_is_public_ip($ip)){
        return false;
    }

    $type = get_op('ip_srvice','tpy');
    $ip_city = get_op('ip_city','province');
    $output = false;
    $api = '';

    switch ($type) {
        case 'tx':
            $key = get_op('txip_key');
            if(!$key){
                return false;
            }
            $api = 'https://apis.map.qq.com/ws/location/v1/ip?ip='.rawurlencode($ip).'&key='.rawurlencode($key);
            break;
        case 'gd':
            $key = get_op('gdip_key');
            if(!$key){
                return false;
            }
            $api = 'https://restapi.amap.com/v3/ip?ip='.rawurlencode($ip).'&key='.rawurlencode($key);
            break;
        case 'tpy':
        default:
            $type = 'tpy';
            $api = 'https://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.rawurlencode($ip);
            break;
    }

    $apis = array($api);
    if($type === 'tpy'){
        $apis[] = 'https://api.kieng.cn/ipgeography?ip='.rawurlencode($ip);
    }

    $data = false;
    foreach($apis as $api_url){
        $response = wp_remote_get($api_url, array(
            'timeout' => 5,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (WordPress; PixPro)'
            ),
        ));

        if(!is_array($response) || is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200){
            continue;
        }

        $body = trim((string) wp_remote_retrieve_body($response));
        $data = json_decode($body, true);
        if(!is_array($data) && function_exists('mb_convert_encoding')){
            $data = json_decode(mb_convert_encoding($body, 'UTF-8', 'GB18030,GBK,GB2312,UTF-8'), true);
        }
        if(is_array($data)){
            break;
        }
    }

    if(!is_array($data)){
        return false;
    }

    if($type == 'tx'){
        if(isset($data['status']) && (int) $data['status'] === 0 && !empty($data['result']['ad_info'])){
            $ad_info = $data['result']['ad_info'];
            $output = pix_pick_ip_location_value($ad_info, $ip_city);
        }
    } else if($type == 'gd'){
        if(isset($data['status']) && (string) $data['status'] === '1'){
            $output = pix_pick_ip_location_value($data, $ip_city);
        }
    } else {
        $output = pix_pick_ip_location_value($data, $ip_city);
    }

    return !empty($output) ? $output : false;
}

function pix_pick_ip_location_value($data, $ip_city = 'province'){
    if(!is_array($data)){
        return false;
    }

    $province = pix_clean_ip_location_part($data['province'] ?? $data['pro'] ?? '');
    $city = pix_clean_ip_location_part($data['city'] ?? '');
    $addr = pix_clean_ip_location_part($data['addr'] ?? $data['pos'] ?? '');

    if($ip_city === 'city'){
        return $city ?: $province ?: $addr;
    }

    return $province ?: $city ?: $addr;
}

function pix_clean_ip_location_part($value){
    if(is_array($value) || is_object($value)){
        return '';
    }

    $value = trim(wp_strip_all_tags((string) $value));
    if($value === '' || $value === '局域网' || $value === '本机地址'){
        return '';
    }

    return $value;
}

function pix_is_public_ip($ip){
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
}

// 获取真实IP地址
function get_real_ip() {
    $headers = array(
        'HTTP_CF_CONNECTING_IP',
        'HTTP_CDN_SRC_IP',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    );
    $valid_ip = '';

    foreach($headers as $header){
        if(empty($_SERVER[$header])){
            continue;
        }

        $ips = explode(',', (string) $_SERVER[$header]);
        foreach($ips as $ip){
            $ip = trim($ip);
            if(filter_var($ip, FILTER_VALIDATE_IP) && pix_is_public_ip($ip)){
                return $ip;
            }
            if(!$valid_ip && filter_var($ip, FILTER_VALIDATE_IP)){
                $valid_ip = $ip;
            }
        }
    }

    return $valid_ip;
}

// 菜单添加无刷新标签
/* add_filter('nav_menu_link_attributes', function($atts, $item, $args, $depth) {
    // 添加 Unpoly 属性，仅针对主菜单（按需判断 $args）
    $atts['up-follow'] = 'true';
    $atts['up-preload'] = 'hover';
    return $atts;
  }, 10, 4); */

