<?php
/* 安全验证 */
//use Yurun\Util\HttpRequest;

if (!class_exists('Pixcap\\Pixcap')) {
    require_once get_template_directory() . '/inc/vendor/pixcap/pixcap.php';
}

function pixcap_client_ip() {
    return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? ''));
}

function pixcap_rate_limit($bucket, $identity = '', $limit = 30, $window = 300) {
    $ip = pixcap_client_ip();
    $key = 'pixcap_rl_' . md5($bucket . '|' . $ip . '|' . strtolower((string) $identity));
    $count = get_transient($key);

    if ($count === false) {
        set_transient($key, 1, $window);
        return true;
    }

    if (intval($count) >= intval($limit)) {
        return false;
    }

    set_transient($key, intval($count) + 1, $window);
    return true;
}

function pixcap_instance() {
    static $instance = null;
    if ($instance instanceof \Pixcap\Pixcap) {
        return $instance;
    }

    $hmac_key = hash('sha256', wp_salt('auth') . '|pixcap|challenge|' . wp_salt('nonce'));
    $fast_key = hash('sha256', wp_salt('secure_auth') . '|pixcap|solution|' . wp_salt('logged_in'));
    $instance = new \Pixcap\Pixcap($hmac_key, $fast_key, 15.0);
    return $instance;
}

function pixcap_challenge_cost() {
    $cost = intval(get_op('pixcap_cost', 50000));
    return max(10000, min(200000, $cost));
}

function pixcap_payload_key($payload) {
    if (!is_array($payload)) {
        return '';
    }

    $challenge = isset($payload['challenge']) ? (string) $payload['challenge'] : '';
    $signature = isset($payload['signature']) ? (string) $payload['signature'] : '';
    if ($challenge === '' || $signature === '') {
        return '';
    }

    return 'pixcap_used_' . md5($challenge . '|' . $signature);
}

function pixcap_mark_payload_used($payload) {
    $key = pixcap_payload_key($payload);
    if ($key === '') {
        return;
    }

    $expires = isset($payload['expires']) ? intval($payload['expires']) : 0;
    $ttl = max(60, min(600, $expires - time() + 60));
    set_transient($key, 1, $ttl);
}

function pixcap_challenge() {
    if (!pixcap_rate_limit('challenge', '', 60, 300)) {
        wp_send_json(array('success' => false, 'message' => '请求过于频繁，请稍后再试'), 429);
    }

    nocache_headers();
    wp_send_json(pixcap_instance()->createChallenge(array(
        'algorithm' => 'PBKDF2/SHA-256',
        'cost'      => pixcap_challenge_cost(),
        'keyLength' => 32,
        'expires'   => time() + 300,
    )));
}
add_action('wp_ajax_nopriv_pixcap_challenge', 'pixcap_challenge');
add_action('wp_ajax_pixcap_challenge', 'pixcap_challenge');

function pixcap_request_payload() {
    $payload = array();
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $payload = isset($json['payload']) && is_array($json['payload']) ? $json['payload'] : $json;
        }
    }

    if (empty($payload) && isset($_POST['payload'])) {
        $posted = wp_unslash($_POST['payload']);
        $payload = is_array($posted) ? $posted : json_decode((string) $posted, true);
    }

    if (empty($payload) && isset($_POST['logincaptcha'])) {
        $posted = wp_unslash($_POST['logincaptcha']);
        $payload = is_array($posted) ? $posted : json_decode((string) $posted, true);
    }

    return is_array($payload) ? $payload : array();
}

function pixcap_check($payload = null) {
    if ($payload === null) {
        $payload = pixcap_request_payload();
    }

    if (is_string($payload)) {
        $decoded = json_decode(wp_unslash($payload), true);
        $payload = is_array($decoded) ? $decoded : array();
    }

    if (is_array($payload) && isset($payload['pixcap']) && is_array($payload['pixcap'])) {
        $payload = $payload['pixcap'];
    }

    if (!is_array($payload) || empty($payload)) {
        return array('code' => 1, 'msg' => '请先完成人机验证');
    }

    if (!pixcap_rate_limit('verify', $payload['challenge'] ?? '', 40, 300)) {
        return array('code' => 1, 'msg' => '验证请求过于频繁，请稍后再试');
    }

    $used_key = pixcap_payload_key($payload);
    if ($used_key !== '' && get_transient($used_key)) {
        return array('code' => 1, 'msg' => '验证已使用，请重新验证');
    }

    $result = pixcap_instance()->verifySolution(array('payload' => $payload));
    if (!empty($result['verified'])) {
        pixcap_mark_payload_used($payload);
        return array('code' => 0, 'msg' => '验证成功');
    }

    if (!empty($result['expired'])) {
        return array('code' => 1, 'msg' => '验证已过期，请重试');
    }

    if (!empty($result['invalidSignature'])) {
        return array('code' => 1, 'msg' => '验证签名无效，请重试');
    }

    return array('code' => 1, 'msg' => '人机验证失败，请重试');
}

function pixcap_verify() {
    nocache_headers();
    $result = pixcap_check();
    wp_send_json(array(
        'verified'         => $result['code'] === 0,
        'success'          => $result['code'] === 0,
        'expired'          => $result['msg'] === '验证已过期，请重试',
        'invalidSignature' => $result['msg'] === '验证签名无效，请重试',
        'invalidSolution'  => $result['code'] !== 0,
        'message'          => $result['msg'],
    ));
}
add_action('wp_ajax_nopriv_pixcap_verify', 'pixcap_verify');
add_action('wp_ajax_pixcap_verify', 'pixcap_verify');

function pix_content_protect_type() {
    return sanitize_key(get_op('content_protect_type', 'pixcap'));
}

function pix_content_submission_limit($context) {
    switch ($context) {
        case 'moment':
            return array(4, 300);
        case 'comment':
        default:
            return array(8, 300);
    }
}

function pix_content_submission_guard($context, $payload = null, $trap = null) {
    $protect_type = pix_content_protect_type();
    if ($protect_type === 'off') {
        return array('code' => 0, 'msg' => '验证通过');
    }

    if ($trap === null) {
        $trap = isset($_POST['pix_guard']) ? wp_unslash($_POST['pix_guard']) : '';
    }
    $trap = trim((string) $trap);
    if ($trap !== '') {
        return array('code' => 1, 'msg' => '验证失败，请重试');
    }

    $limit_info = pix_content_submission_limit($context);
    $identity = $context . '|' . (function_exists('get_current_user_id') ? (int) get_current_user_id() : 0);
    if (!pixcap_rate_limit('content_' . $context, $identity, $limit_info[0], $limit_info[1])) {
        return array('code' => 1, 'msg' => '操作过于频繁，请稍后再试');
    }

    if ($protect_type === 'pixcap') {
        if (is_string($payload)) {
            $decoded = json_decode(wp_unslash($payload), true);
            $payload = is_array($decoded) ? $decoded : array();
        }
        $result = $payload !== null ? pixcap_check($payload) : pixcap_check();
        if (!empty($result['code'])) {
            return $result;
        }
    }

    return array('code' => 0, 'msg' => '验证通过');
}

// 极验证二次验证请求
function geetest_check($data){

    $captcha_id = get_op('geetest_id','');
    $captcha_key = get_op('geetest_key','');
    $api_server = "http://gcaptcha4.geetest.com";

    if(empty($captcha_id) || empty($captcha_key)) {
        return array('code' => 1 , 'msg' => '行为验证后台参数错误，请联系管理员');
    }

    if (isset($data['lot']) && is_array($data['lot'])) {
        $data = $data['lot'];
    }

    if(empty($data['lot_number']) || empty($data['captcha_output']) || empty($data['pass_token']) || empty($data['gen_time']) ) {
        return array('code' => 1 , 'msg' => '行为验证参数错误，请重试');
    }

    $lot_number     = $data['lot_number'];
    $captcha_output = $data['captcha_output'];
    $pass_token     = $data['pass_token'];
    $gen_time       = $data['gen_time'];

    $sign_token = hash_hmac('sha256', $lot_number, $captcha_key);

    $query = array(
        "lot_number"     => $lot_number,
        "captcha_output" => $captcha_output,
        "pass_token"     => $pass_token,
        "gen_time"       => $gen_time,
        "sign_token"     => $sign_token,
    );

    $url = sprintf($api_server . "/validate" . "?captcha_id=%s", $captcha_id);

    $http     = new Yurun\Util\HttpRequest;
    $response = $http->post($url, $query);
    $result   = $response->json(true);

    if (!isset($result['result'])) {
        return array('code' => 1, 'msg' => '行为验证服务出错');
    }

    if ($result['result'] === 'success') {
        return array('code' => 0);
    }

    return array('code' => 1, 'msg' => '行为验证失败' . ((!empty($result['reason']) ? '：' . $result['reason'] : '')) . ((!empty($result['msg']) ? '：' . $result['msg'] : ''))); 
}

// ppo滑动验证
use Kkokk\Poster\PosterManager;

function ppoc_check(){
    $type = 'slider';
    $key = isset($_POST['key']) ? $_POST['key'] : '';
    $x = isset($_POST['x']) ? $_POST['x'] : '';
    $leeway = 8;

    if($key == '' || $x == ''){
        wp_send_json(array('code' => 404 , 'msg' => '非法操作，验证参数错误！'));
    } 
    
    $data = ppoc_verify($key,$x,$leeway);

    if($data){
        wp_send_json(array('code' => 200 , 'msg' => '验证成功！'));
    } else {
        wp_send_json(array('code' => 404 , 'msg' => '验证失败'));
    }
}
add_action( 'wp_ajax_nopriv_ppoc_check', 'ppoc_check' );
add_action( 'wp_ajax_ppoc_check', 'ppoc_check' );

//获取滑块验证图和参数
function get_ppoc_data(){
    try {
        $expiration = 3600;
        $rand = mt_rand(1,20);
        
        $local_path = get_template_directory() . '/img/captcha/' . $rand . '.jpg';
        if (!file_exists($local_path)) {
            wp_send_json(array('code' => 0, 'msg' => '验证码背景图片不存在：' . $local_path));
        }
        
        $params = [
            'src'           => $local_path,
            'im_width'      => 340,
            'im_height'     => 251,
            'im_type'       => 'png',
            'quality'       => 80,
            'type'          => mt_rand(3,6),
            'bg_width'      => 340,
            'bg_height'     => 191,
            'slider_width'  => 50,
            'slider_height' => 50,
            'slider_border' => 2,
            'slider_bg'     => 1,
        ];
        
        $type = 'slider';
        
        $captcha = PosterManager::Captcha()->setCache(new \Kkokk\Poster\Cache\SessionCacheAdapter());
        $result = $captcha->type($type)->config($params)->get();
          
        wp_send_json($result);
    } catch (Exception $e) {
        wp_send_json(array('code' => 0, 'msg' => '验证码生成失败：' . $e->getMessage()));
    }
}
add_action( 'wp_ajax_nopriv_get_ppoc_data', 'get_ppoc_data' );
add_action( 'wp_ajax_get_ppoc_data', 'get_ppoc_data' );

function ppoc_verify($key, $value, $leeway = 0){
    $adapter = new \Kkokk\Poster\Cache\SessionCacheAdapter();
    $x = $adapter->pull($key);

    if (empty($x)) return false;

    return $x >= ($value - $leeway) && $x <= ($value + $leeway);
}

//验证码
function captcha_code_data(){
    session_start();

    $params = [
        'src'         => '',
        'im_width'    => 220,
        'im_height'   => 60,
        'im_type'     => 'png',
        'quality'     => 80,
        'type'        => 'alpha_num',
        'font_family' => get_template_directory().'/inc/assets/grobold.ttf',
        'font_size'   => 32,
        'font_count'  => 4,
        'line_count'  => 0,
        'char_count'  => 0,
    ];

    $type = 'input';

    try {
        $captcha = PosterManager::Captcha()->setCache(new \Kkokk\Poster\Cache\SessionCacheAdapter());
        $data = $captcha->type($type)->config($params)->get();

        if(!empty($data) && isset($data['img'])){
            $_SESSION['captcha_code'] = $data['secret'] ?? $data['key'];
            wp_send_json(array('code' => 200 , 'res' => $data['img']));
        }

        wp_send_json(array('code' => 404 , 'msg' => '验证码生成失败: 数据为空'));
    } catch (Exception $e) {
        wp_send_json(array('code' => 404 , 'msg' => '验证码生成异常: '.$e->getMessage()));
    }
}

add_action( 'wp_ajax_nopriv_captcha_code_data', 'captcha_code_data' );
add_action( 'wp_ajax_captcha_code_data', 'captcha_code_data' );

// 验证码检查
function captcha_code_check(){
    if (!isset($_SESSION)) {
	    session_start();
	}
    $check_code = isset($_POST['captcha_code']) ? $_POST['captcha_code'] : '';
    $msg = '';

    if(!$check_code){
        $msg .= __('请输入验证码. ');
    } else {
        $cacheKey = isset($_SESSION['captcha_code']) ? $_SESSION['captcha_code'] : '';
        if($cacheKey){
            $adapter = new \Kkokk\Poster\Cache\SessionCacheAdapter();
            $correctCode = $adapter->pull($cacheKey);
            if($correctCode && strtolower($check_code) == strtolower($correctCode)){
                $_SESSION['captcha_verified'] = true;
                wp_send_json(array('code' => 200 , 'msg' => '验证成功'));
            }
        }
        $msg .= __('验证码错误. ');
    }

    wp_send_json(array('code' => 404 , 'msg' => $msg));
}
add_action( 'wp_ajax_nopriv_captcha_code_check', 'captcha_code_check' );
add_action( 'wp_ajax_captcha_code_check', 'captcha_code_check' );

//手机或邮箱验证码验证
function check_phone_email_code($mobile,$auth_code){
    $data = get_transient('auth_'.$mobile.'');
	
	return $data;
}
