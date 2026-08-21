<?php
require THEME_DIR . '/inc/user/action-user.php';

 //将用户页面的 author 改为 user
function change_author_permalinks() {
    global $wp_rewrite;
    $wp_rewrite->author_base = 'user';
}
add_action('init','change_author_permalinks');

// 主题激活时刷新重写规则，使 author_base 修改生效
add_action('after_switch_theme', function() {
    $wp_rewrite = $GLOBALS['wp_rewrite'];
    $wp_rewrite->author_base = 'user';
    $wp_rewrite->flush_rules();
});

// 切换主题时也刷新，避免用户手动切换主题后路由失效
add_action('switch_theme', function() {
    $wp_rewrite = $GLOBALS['wp_rewrite'];
    $wp_rewrite->flush_rules();
});

 //根据用户ID 进入用户页面
add_filter('author_link', 'ppo_author_url_with_id', 1000, 2);
function ppo_author_url_with_id($link, $author_id) {
    $link_base = trailingslashit(get_option('home'));
    $link = "user/".$author_id;
    return $link_base . $link;
}

// 用户中心页面地址 
 function ppo_get_user_url( $type='', $user_id=0 ){
    $user_id = intval($user_id);
    if( $user_id==0 ){
        $user_id = get_current_user_id();
    }
    $url = home_url('/dashboard').'/'.$type;
    return $url;
}

// 获取用户头像
function ppo_get_user_avatar($id , $size='40' , $type=''){
    
}

function pix_normalize_local_asset_url($url){
    $url = trim((string) $url);
    if($url === ''){
        return '';
    }

    $parts = wp_parse_url($url);
    if(empty($parts['host'])){
        return esc_url_raw($url);
    }

    $path = isset($parts['path']) ? $parts['path'] : '';
    $themes_marker = '/wp-content/themes/';
    $themes_pos = strpos($path, $themes_marker);
    if($themes_pos !== false){
        $after_theme = substr($path, $themes_pos + strlen($themes_marker));
        $slash_pos = strpos($after_theme, '/');
        if($slash_pos !== false){
            $relative_theme_path = substr($after_theme, $slash_pos + 1);
            $new_url = trailingslashit(THEME_URL) . ltrim($relative_theme_path, '/');
            if(!empty($parts['query'])){
                $new_url .= '?' . $parts['query'];
            }
            return esc_url_raw($new_url);
        }
    }

    $current = wp_parse_url(home_url());
    if(!empty($current['host']) && strtolower($parts['host']) === strtolower($current['host'])){
        return esc_url_raw($url);
    }

    if(strpos($path, '/wp-content/') === false){
        return esc_url_raw($url);
    }

    $new_url = home_url($path);
    if(!empty($parts['query'])){
        $new_url .= '?' . $parts['query'];
    }

    return esc_url_raw($new_url);
}

// 用户管理中心地址
function user_dashboard_url($type=''){
    $url = home_url('/dashboard').'/'.$type;
    return $url;
}

// 头像下拉用户菜单目录
function pix_user_menu_catalog($user_id = 0){
    $user_id = intval($user_id) ?: get_current_user_id();
    $author_url = get_author_posts_url($user_id);

    $items = array(
        'center' => array(
            'title' => '个人中心',
            'icon'  => 'ri-home-4-line',
            'url'   => user_dashboard_url('center'),
        ),
        'submit' => array(
            'title' => '投稿管理',
            'icon'  => 'ri-edit-2-line',
            'url'   => user_dashboard_url('trend'),
        ),
        'order' => array(
            'title' => '我的订单',
            'icon'  => 'ri-shopping-bag-3-line',
            'url'   => user_dashboard_url('order'),
        ),
        'task' => array(
            'title' => '任务中心',
            'icon'  => 'ri-task-line',
            'url'   => user_dashboard_url('task'),
        ),
        'account' => array(
            'title' => '账号设置',
            'icon'  => 'ri-user-settings-line',
            'url'   => user_dashboard_url('edit'),
        ),
        'collect' => array(
            'title' => '我的收藏',
            'icon'  => 'ri-star-smile-line',
            'url'   => add_query_arg('tab', 'collect', $author_url),
        ),
        'message' => array(
            'title' => '消息中心',
            'icon'  => 'ri-chat-smile-2-line',
            'url'   => home_url('/msg'),
        ),
        'comment' => array(
            'title' => '我的评论',
            'icon'  => 'ri-chat-3-line',
            'url'   => add_query_arg('tab', 'comments', $author_url),
        ),
    );

    return apply_filters('pix_user_menu_catalog', $items, $user_id);
}

// 读取头像下拉菜单配置，并统一限制最多 8 项
function pix_get_user_menu_items($user_id = 0){
    $catalog = pix_user_menu_catalog($user_id);
    $default_keys = array('center', 'submit', 'order', 'task', 'account');
    $customizer_options = get_option('ppo_customizer', array());
    $config = isset($customizer_options['user_menu_items']) && is_array($customizer_options['user_menu_items'])
        ? $customizer_options['user_menu_items']
        : get_op('user_menu_items', array());
    $enabled = isset($config['enabled']) && is_array($config['enabled']) ? $config['enabled'] : array();
    $keys = array();

    foreach($enabled as $key => $label){
        $candidate = is_string($key) && isset($catalog[$key]) ? $key : sanitize_key($label);
        if(isset($catalog[$candidate]) && !in_array($candidate, $keys, true)){
            $keys[] = $candidate;
        }
    }

    if(empty($keys)){
        $keys = $default_keys;
    }

    // 订单/钱包/会员功能已移除，过滤对应菜单项
    $removed_keys = array('order', 'wallet', 'vip');
    $keys = array_values(array_filter($keys, function ($key) use ($removed_keys) {
        return !in_array($key, $removed_keys, true);
    }));

    $items = array();
    foreach(array_slice($keys, 0, 8) as $key){
        if(isset($catalog[$key])){
            $items[$key] = $catalog[$key];
        }
    }

    return $items;
}

// 检查是否有用户中心查看权限
/* function check_user_show(){
    global $wp_query;
    $curauth = $wp_query->get_queried_object();
    $current_user = wp_get_current_user();

    $oneself = $current_user->ID==$curauth->ID || current_user_can('edit_users') ? 1 : 0;
    return $oneself;
} */

// 用户中心菜单
function user_center_nav($type,$user_id){   
    $arr = user_nav_arr();
    $main_html = '';
    $extras_html = '';
    $extra_tabs = array('license', 'my-licenses', 'redeem-auth');
    foreach($arr as $data){
        $active = $type === $data['tab'] ? 'active' : '';
        $item = '<li><a href="'.ppo_get_user_url( $data['tab'], $user_id).'" class="user_nav_tab tab_'.$data['tab'].' '.$active.'"><i class="'.$data['icon'].'"></i><span>'.$data['title'].'</span></a></li>';
        if (in_array($data['tab'], $extra_tabs)) {
            $extras_html .= $item;
        } else {
            $main_html .= $item;
        }
    }

    $output = '<ul class="user-nav-main">'.$main_html.'</ul>';
    if (!empty($extras_html)) {
        $output .= '<ul class="user-nav-extras">'.$extras_html.'</ul>';
    }
    return $output;
}

function get_user_content_counts($user_id) {
    $counts = array(
        'posts'    => count_user_posts($user_id, 'post', true),
        'moment'   => count_user_posts($user_id, 'moment', true),
        'comments' => get_comments(array(
            'user_id' => $user_id,
            'count'   => true,
            'status'  => 'approve',
        )),
        'collect'  => 0,
        'moments'  => 0,
    );

    $collect_list = get_user_meta($user_id, 'post_collect', true);
    if ($collect_list && is_array($collect_list)) {
        $counts['collect'] = count($collect_list);
    }

    $joined = get_user_meta($user_id, 'user_mo_joined', true);
    if ($joined) {
        $joined_arr = array_filter(explode(',', $joined));
        $counts['moments'] = count($joined_arr);
    }

    return $counts;
}

function user_index_nav($type,$user_id){
    $html = '';
    $counts = get_user_content_counts($user_id);

    $args = array();

    $args['posts'] = array(
        'title'     => '文章',
        'tab'     => 'posts',
        'icon'     => '<i class="ri-article-line"></i>',
        'count'     => $counts['posts'],
        'skeleton' => 'post-list'
    );

    $args['moment'] = array(
        'title'     => '片刻',
        'tab'     => 'moment',
        'icon'     => '<i class="ri-donut-chart-line"></i>',
        'count'     => $counts['moment'],
        'skeleton' => 'moment-list'
    );

    $args['moments'] = array(
        'title'     => '圈子',
        'tab'     => 'moments',
        'icon'     => '<i class="ri-bubble-chart-line"></i>',
        'count'     => $counts['moments'],
        'skeleton' => 'moments-list'
    );

    $args['comments'] = array(
        'title'     => '评论',
        'tab'     => 'comments',
        'icon'     => '<i class="ri-chat-3-line"></i>',
        'count'     => $counts['comments'],
        'skeleton' => 'user-comment'
    );

    $args['collect'] = array(
        'title'     => '收藏',
        'tab'     => 'collect',
        'icon'     => '<i class="ri-star-smile-line"></i>',
        'count'     => $counts['collect'],
        'skeleton' => 'post-list'
    );

    $rest_nonce = wp_create_nonce('wp_rest');

    foreach($args as $key => $value){
        $active = $type === $key? 'active' : '';
        $rest_url = '/wp-json/ppo/v1/user-'.$key.'?page=1&user_id='.$user_id.'&_wpnonce='.$rest_nonce;
        $html.= '<a href="'.esc_url($rest_url).'"
        hx-get="'.esc_url($rest_url).'"
        hx-target="#user-content"
        hx-swap="innerHTML"
        hx-push-url="'.get_author_posts_url($user_id).'?tab='.$key.'"
        data-skeleton="'.(isset($value['skeleton'])? $value['skeleton'] : '').'"
        hx-history="false"
        class="user-tab pix-user-home-tab user-'.$key.'-tab '.$active.'">
        <span class="icon pix-user-home-tab-icon">'.$value['icon'].'</span>
        <span class="title pix-user-home-tab-label">'.$value['title'].'<small class="pix-user-home-tab-count">'.$value['count'].'</small></span>
    </a>';
    }
 
   return '<div class="ppo-navtab pix-user-home-tabs big-rounded">'.$html.'</div>';
}

// 用户中心菜单数组
function user_nav_arr(){
    $args = array();


        $args['index'] = array(
            'title'     => '概览',
            'tab'     => 'center',
            'icon'     => 'ri-account-box-line',
        );


        $args['trend'] = array(
            'title'     => '动态',
            'tab'     => 'trend',
            'icon'     => 'ri-apps-fill',
        );

        $args['comment'] = array(
            'title'     => '任务',
            'tab'     => 'task',
            'icon'     => 'ri-calendar-check-line',
        );


        $args['edit'] = array(
            'title'     => '设置',
            'tab'     => 'edit',
            'icon'     => 'ri-settings-4-line',
        );

        $args = apply_filters('pix_dashboard_menu_items', $args);

    return $args;

}

 /* 获取用户注册时间 */
function user_registered_date(){
    $userinfo=get_userdata(get_current_user_id());
    $authorID= $userinfo->id;
    $user = get_userdata( $authorID );
    $registered = $user->user_registered;
    echo '注册于 : ' . date( 'Y年m月d日', strtotime( $registered ) );
}

function user_show_registered_date($user_id){
    $user = get_userdata($user_id);
    $registered = $user->user_registered;
    return '' . date( 'Y年m月d日', strtotime( $registered ) );
}

// 记录登录时间
function ppo_set_user_last_login($user_login,$user) {
    update_user_meta( $user->ID, 'ppo_last_login', time() );
}
add_action('wp_login','ppo_set_user_last_login', 20, 2);

// 调用最后登录时间
function get_last_login($user_id) {
    $last_login = get_user_meta($user_id, 'ppo_last_login', true);
    $date_format = 'Y年m月d日';
    $output = $last_login ? date( $date_format, $last_login ) : '-';
    echo $output;
}

// 增加user meta 
 function ppo_add_contact_fields($contactmethods){
    $contactmethods['user_gender'] = '性别';
    $contactmethods['user_qq'] = 'QQ';
    $contactmethods['user_phone'] = '手机号码';
    $contactmethods['user_location'] = '居住地址';
    return $contactmethods;
}
add_filter('user_contactmethods', 'ppo_add_contact_fields');

//检测邮箱是否绑定
function check_email_bind($user_id){
    $user_info = get_userdata($user_id);

    $email = $user_info->user_email;
    return !empty($email) ? true : false;
}

 //手机加密中间四位
 function encryptTel($tel) {
    $new_tel = substr_replace($tel, '****', 3, 4);
    return $new_tel;
}

// 获取用户头像
function get_u_avatar($user_id,$type = 'img'){
    $custom_avatar = get_user_meta($user_id,'custom_avatar',true);
    $de = get_op('user_avatar', THEME_URL.'/img/ava.png');
    if(!empty($custom_avatar)){
        $avatar_url = $custom_avatar;
    } else if($de) {
        $avatar_url = $de;
    } else {
        $avatar_url = THEME_URL.'/img/ava.png';
    }

    $avatar_url = pix_normalize_local_asset_url($avatar_url);

    if($type == 'url'){
        return $avatar_url;
    }
    return '<img data-src="'.esc_url($avatar_url).'" class="user-avatar lazy"/>';
}

// 获取已绑定的社交头像
function get_bind_avatar($user_id){
    $html = '';
    $juhe_data = get_op('open_juhe_data');
    $juheArr = is_array($juhe_data) && isset($juhe_data['juhe_type']) ? $juhe_data['juhe_type'] : array();

    if(is_array($juheArr)){
        foreach($juheArr as $type){
            $open_id = get_user_meta($user_id,'open_'.$type.'_openid',true);
            $user_info = get_user_meta($user_id,'open_'.$type.'_userinfo',true);
            if($open_id && is_array($user_info) && isset($user_info['avatar'])){
                $avatar = $user_info['avatar'];
                $html .= '<a class="bind-avatar" type="'.$type.'"><img src="'.$avatar.'"></a>';
                //return $type;
            }

        }
    }

    $upload_avatar = '';
    $upload = get_user_meta($user_id,'upload_avatar',true);
    if($upload){
        $upload = pix_normalize_local_asset_url($upload);
        $upload_avatar = '<a class="bind-avatar" type="upload"><img src="'.esc_url($upload).'"></a>';
    }

    $de_avatar = '';
    $de = get_op('user_avatar',THEME_URL.'/img/ava.png');
    if($de){
        $de = pix_normalize_local_asset_url($de);
        $de_avatar = '<a class="bind-avatar" type="default"><img src="'.esc_url($de).'"></a>';
    }
    return $upload_avatar.$html.$de_avatar;
}

//  // 可设置的VIP价格信息
//  function vip_price_info(){
//     $arr = PPO_Vip::get_vip_lv();
//     $html = '';
//     if(is_array($arr) && !empty($arr)){
//         foreach($arr as $i => $v){
//             $html .= '<li><code>'.$i.'</code> - '.$v.'</li>';
//         }

//         echo '<div class="vip-price">
//                 <h4>已设置的VIP等级：</h4>
//                 '.$html.'
//                 <div>
//                 <h4 class="tips">例如原价为220，可设置格式如下(一行一个)：</h4>
//                 <li>vip0|200</li>
//                 <li>vip1|180</li>
//                 <li>vip2|free</li>
//                 <li>没有优惠价格的等级可以不填写，free为免费</li>
//                 </div>
//              </div>';
//     }
// }

// 获取所有等级 会员等级 和 常规等级
function all_lv_merge(){
    $data = get_all_lv();

    return $data;
}

// 用户VIP等级数组，包括普通用户
function get_all_lv(){
    $data = array();
    $normal = array("" => "普通用户");

    $level_data = array();
    $levels = get_op('user_level_item');
    if(is_array($levels) && !empty($levels)){
        foreach(array_values($levels) as $index => $level){
            $key = 'lv'.($index + 1);
            $level_data[$key] = !empty($level['lv_name']) ? $level['lv_name'] : $key;
        }
    }

    $da = array_merge($normal, $data, $level_data);
    return $da;
}

// 获取用户余额
function get_user_balance($uid){
    $balance = get_user_meta($uid,'ppo_balance',true);
    return !empty($balance) ? $balance : 0;
}

// 获取用户积分
function get_user_credit($uid){
    $credit = get_user_meta($uid,'ppo_credit',true);
    return !empty($credit) ? (int)$credit : 0;
}

// VIP等级下载额度数组
function get_user_down_num_arr($uid){
    $uid = absint($uid);
    $vip = get_user_meta( $uid, 'ppo_vip', true );
    $vip_item = get_op('vip_item');
    $free = max(0, intval(get_op('ordinary_free_num','20')));
    $level_down_num = function_exists('ppo_get_user_level_down_num') ? ppo_get_user_level_down_num($uid) : 0;
    $time = function_exists('current_time') ? current_time('Y-m-d') : date('Y-m-d');

    if($vip && preg_match('/\d+/', $vip, $matches)){
        $i = intval($matches[0]);
        $data = is_array($vip_item) && isset($vip_item[$i]) && is_array($vip_item[$i]) ? $vip_item[$i] : array();
        $down_num = max(intval($data['down_num'] ?? 0), $level_down_num);
        $arr = array(
            'down_num' => $down_num,
            'total_down_num' => $down_num,
            'time'     => $time
        );

    } else { // 普通用户
        $down_num = max($free, $level_down_num);
        $arr = array(
            'down_num' => $down_num,
            'total_down_num' => $down_num,
            'time'     => $time
        );
    }

    return $arr;

}

// 获取用户vip数据
function ppo_get_user_vip_data($user_id){
    return '';
}

// 用户等级和会员徽章
function ppo_user_badges_html($user_id, $context = ''){
    $user_id = absint($user_id);
    if (!$user_id) {
        return '';
    }

    $html = '';
    $vip_data = function_exists('ppo_get_user_vip_data') ? ppo_get_user_vip_data($user_id) : '';
    if (is_array($vip_data) && !empty($vip_data['icon'])) {
        $vip_title = !empty($vip_data['title']) ? $vip_data['title'] : 'VIP';
        $html .= '<span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="'.esc_attr($vip_title).'" aria-label="'.esc_attr($vip_title).'" tabindex="0"><img src="'.esc_url($vip_data['icon']).'" class="ppo-user-badge ppo-user-vip-badge" alt="'.esc_attr($vip_title).'"></span>';
    }

    $lv_data = function_exists('ppo_get_user_level_info') ? ppo_get_user_level_info($user_id) : null;
    if (is_array($lv_data) && !empty($lv_data['icon'])) {
        $lv_name = !empty($lv_data['name']) ? $lv_data['name'] : 'Lv.'.intval($lv_data['lv'] ?? 1);
        $html .= '<span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="'.esc_attr($lv_name).'" aria-label="'.esc_attr($lv_name).'" tabindex="0"><img src="'.esc_url($lv_data['icon']).'" class="ppo-user-badge ppo-user-level-badge" alt="'.esc_attr($lv_name).'"></span>';
    }

    if (!$html) {
        return '';
    }

    $context_class = $context ? ' ppo-user-badges-'.$context : '';
    return '<span class="ppo-user-badges'.$context_class.'">'.$html.'</span>';
}

// 获取用户权限 array('up_image','up_video','up_file','msg','comment','post','moment','cr_moment'); 
function ppo_user_group_allowed($groups, $vip, $level = ''){
    if($groups === true || $groups === '1' || $groups === 1){
        return true;
    }

    if(!is_array($groups)){
        return false;
    }

    $group_ids = array_map('strval', $groups);
    if(in_array((string)$vip, $group_ids, true)){
        return true;
    }

    if($level !== '' && in_array((string)$level, $group_ids, true)){
        return true;
    }

    return false;
}

function pix_user_is_plain_user($user_id = 0){
    $user_id = absint($user_id);
    if(!$user_id){
        $user_id = get_current_user_id();
    }

    if(!$user_id || user_can($user_id, 'manage_options')){
        return false;
    }

    $vip = get_user_meta($user_id, 'ppo_vip', true);
    return empty($vip);
}

function pix_normal_user_can($option_key, $default = true, $user_id = 0){
    $user_id = absint($user_id);
    if(!$user_id){
        $user_id = get_current_user_id();
    }

    if(!$user_id){
        return false;
    }

    if(user_can($user_id, 'manage_options')){
        return true;
    }

    if(!pix_user_is_plain_user($user_id)){
        return true;
    }

    if((bool) get_op($option_key, $default)){
        return true;
    }

    $level_power_map = array(
        'normal_user_allow_comment'       => 'comment',
        'normal_user_allow_moment'        => 'moment',
        'normal_user_allow_create_circle' => 'cr_moment',
    );

    if(isset($level_power_map[$option_key]) && function_exists('ppo_user_level_has_power')){
        return ppo_user_level_has_power($user_id, $level_power_map[$option_key]);
    }

    return false;
}

function pix_normal_user_upload_allowed($scene = 'image', $user_id = 0){
    $user_id = absint($user_id);
    if(!$user_id){
        $user_id = get_current_user_id();
    }

    if(!$user_id || user_can($user_id, 'manage_options')){
        return true;
    }

    if(!pix_user_is_plain_user($user_id)){
        return true;
    }

    $scene_map = array(
        'avatar'       => array('normal_user_allow_upload_avatar', 'up_image', true, true),
        'cover'        => array('normal_user_allow_upload_cover', 'up_image', true, true),
        'comment_image'=> array('normal_user_allow_upload_comment_image', 'up_image', true, true),
        'moment_image' => array('normal_user_allow_upload_moment_image', 'up_image', true, true),
        'post_image'   => array('normal_user_allow_upload_post_image', 'up_image', false, true),
        'video'        => array('normal_user_allow_upload_video', 'up_video', false, false),
        'file'         => array('normal_user_allow_upload_file', 'up_file', false, false),
    );

    if(!isset($scene_map[$scene])){
        return true;
    }

    list($option_key, $level_power, $default, $needs_image_master) = $scene_map[$scene];

    if(function_exists('ppo_user_level_has_power') && ppo_user_level_has_power($user_id, $level_power)){
        return true;
    }

    if($needs_image_master && !pix_normal_user_can('normal_user_allow_upload_image', true, $user_id)){
        return false;
    }

    return pix_normal_user_can($option_key, $default, $user_id);
}

function get_user_power($user_id){
    $vip_power = array();
    if($user_id > 0){
        if(user_can($user_id, 'manage_options')){
            return array('up_image', 'up_video', 'up_file');
        }

        $vip = '';
        $index = '';
        $level_key = '';
        $lv_data = function_exists('ppo_get_user_level_info') ? ppo_get_user_level_info($user_id) : null;
        if(is_array($lv_data) && !empty($lv_data['lv'])){
            $level_key = 'lv'.intval($lv_data['lv']);
        }
        // 获取vip权限
        
        
        if($index !== ''){
            $vip_items = get_op('vip_item') ?? array();
            if(is_array($vip_items) && isset($vip_items[$index]) && is_array($vip_items[$index]) && isset($vip_items[$index]['limits'])){
                $vip_power = $vip_items[$index]['limits'];
            }
        }

        if(function_exists('ppo_get_user_level_powers')){
            $level_power = ppo_get_user_level_powers($user_id);
            if(is_array($level_power) && !empty($level_power)){
                $vip_power = array_merge((array)$vip_power, $level_power);
            }
        }
        
        
        // 媒体权限
        $image_power = get_op('allow_image_group') ?? array();
        if(ppo_user_group_allowed($image_power, $vip, $level_key)){
            $vip_power[] = 'up_image';
        }
    
        $video_power = get_op('allow_video_group') ?? array();
        if(ppo_user_group_allowed($video_power, $vip, $level_key)){
            $vip_power[] = 'up_video';
        }
    
        $file_power = get_op('allow_file_group') ?? array();
        if(ppo_user_group_allowed($file_power, $vip, $level_key)){
            $vip_power[] = 'up_file';
        }

        if(pix_user_is_plain_user($user_id)){
            if(pix_normal_user_can('normal_user_allow_create_circle', false, $user_id)){
                $vip_power[] = 'cr_moment';
            }
            if(pix_normal_user_upload_allowed('moment_image', $user_id)){
                $vip_power[] = 'up_image';
            }
            if(pix_normal_user_upload_allowed('video', $user_id)){
                $vip_power[] = 'up_video';
            }
            if(pix_normal_user_upload_allowed('file', $user_id)){
                $vip_power[] = 'up_file';
            }
        }
    }

    return array_values(array_unique($vip_power));
}

// ajax获取用户权限信息
function get_user_power_ajax(){
    $user_id = $_POST['uid'];
    if($user_id > 0){
        $power = get_user_power($user_id);
       wp_send_json(array('code' => 1, 'data' => $power));
    }
}
add_action('wp_ajax_get_user_power_ajax', 'get_user_power_ajax');
//add_action('wp_ajax_nopriv_get_user_power_ajax', 'get_user_power_ajax');

// ajax获取用户加入的圈子
function get_user_join_circle(){
    $user_id = $_POST['uid'];
    if($user_id > 0){
        $user_join = get_user_meta($user_id, 'user_mo_joined', true);
        $user_join = $user_join ? explode(',', $user_join) : array();
        wp_send_json(array('code' => 1, 'data' => $user_join));
    }
}
add_action('wp_ajax_get_user_join_circle', 'get_user_join_circle');
//add_action('wp_ajax_nopriv_get_user_join_circle', 'get_user_join_circle');

// 强制文章作者名称使用 display_name（默认已支持，无需额外代码）
add_filter('the_author', function($name) {
    $user = get_user_by('login', $name);
    return $user ? $user->display_name : $name;
});

// 强制评论作者名称使用 display_name（仅登录用户）
add_filter('get_comment_author', function($name, $comment_id, $comment) {
    if ($comment->user_id > 0) {
        $user = get_userdata($comment->user_id);
        return $user ? $user->display_name : $name;
    }
    return $name;
}, 10, 3);

/**
 * 删除默认列并添加自定义列
 */
add_filter('manage_users_columns', 'modify_user_columns',10,1);
function modify_user_columns($columns) {
    // 删除指定列（替换 'your_column_slug' 为实际列标识）
    unset($columns['name']); 

    // 添加“昵称”列
    $columns['p_nickname'] = '昵称';
    return $columns;
}

/**
 * 填充“昵称”列数据
 */
add_filter('manage_users_custom_column', 'fill_custom_nickname_column', 10, 3);
function fill_custom_nickname_column($value, $column_name, $user_id) {
    if ($column_name === 'p_nickname') {
        $user = get_userdata($user_id);
        return $user->display_name;
    }
    return $value;
}

/**
 * 使“昵称”列支持排序
 */
add_filter('manage_users_sortable_columns', 'add_sortable_nickname_column');
function add_sortable_nickname_column($columns) {
    $columns['p_nickname'] = 'display_name';
    return $columns;
}

/**
 * 按 display_name 排序
 */
add_action('pre_get_users', 'sort_users_by_display_name');
function sort_users_by_display_name($query) {
    if (is_admin() && $query->get('orderby') === 'display_name') {
        $query->set('orderby', 'display_name');
    }
}

// 用户封面上传按钮
function user_avatar_upload_btn(){
    $user_id = get_current_user_id();
    if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_cover = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('cover', $user_id) : true;
        if(!$allow_cover){
            return '';
        }
    }

    $html = '<div class="user-cover-upload-btn">
                <a class="cover-upload"><i class="ri-image-add-line"></i>上传封面</a>
                <div class="pix-user-cover-uploader"></div>
            </div>';

    return $html;        
}

// 用户封面上传
add_action('wp_ajax_upload_user_cover', function () {
    if (!is_user_logged_in()) {
      wp_send_json_error('请先登录');
    }

    $user_id = get_current_user_id();
    if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $allow_cover = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('cover', $user_id) : true;
        if(!$allow_cover){
            wp_send_json_error('普通用户暂不能修改封面');
        }
    }
  
    if (empty($_FILES['cover_image'])) {
      wp_send_json_error('没有上传文件');
    }
  
    $file = $_FILES['cover_image'];
  
    // 检查类型
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
      wp_send_json_error('只支持 JPG/PNG/GIF/WEBP 格式');
    }

    // 检查大小限制（最大 300KB）
    $max_size = 300 * 1024; // 300KB
    if ($file['size'] > $max_size) {
    wp_send_json_error('图片大小不能超过 300KB');
    }
  
    // 构造新的文件名和路径
    $upload_dir = wp_upload_dir();
    $target_dir = trailingslashit($upload_dir['path']);
    $filename = "user-cover-{$user_id}.png";
    $file_path = $target_dir . $filename;
  
    // 先删除旧封面附件记录（不删除物理文件）
    $old_attachment_id = get_user_meta($user_id, 'user_cover_attachment_id', true);
    if ($old_attachment_id) {
      wp_delete_attachment($old_attachment_id, false);
    }
  
    // 转为 PNG 格式（以统一存储）
    switch ($file['type']) {
      case 'image/jpeg': $image = imagecreatefromjpeg($file['tmp_name']); break;
      case 'image/png':  $image = imagecreatefrompng($file['tmp_name']); break;
      case 'image/gif':  $image = imagecreatefromgif($file['tmp_name']); break;
      case 'image/webp': $image = imagecreatefromwebp($file['tmp_name']); break;
      default: wp_send_json_error('无法解析图片');
    }
  
    if (!$image) {
      wp_send_json_error('图片处理失败');
    }
  
    imagepng($image, $file_path); // 保存为 PNG
    imagedestroy($image);
  
    // 创建 WordPress 文件数组
    $filetype = wp_check_filetype($filename, null);
    $attachment = [
      'post_mime_type' => $filetype['type'],
      'post_title'     => "用户 {$user_id} 封面图",
      'post_content'   => '',
      'post_status'    => 'inherit',
      'post_author'    => $user_id,
    ];
  
    // 插入附件
    $attachment_id = wp_insert_attachment($attachment, $file_path);
    require_once ABSPATH . 'wp-admin/includes/image.php';
  
    $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attach_data);
  
    // 获取 URL 并保存到用户 meta
    $url = wp_get_attachment_url($attachment_id);
    update_user_meta($user_id, 'user_cover_image', esc_url_raw($url));
    update_user_meta($user_id, 'user_cover_attachment_id', $attachment_id);
  
    // 上传封面图动作
    do_action('ppo_user_uploaded_banner', $user_id, $attachment_id);

    wp_send_json_success(['url' => $url]);
  });
  
// 用户中心左侧模块
function user_center_left($user_id){
    $user_info = get_userdata($user_id);
    $des = !empty($user_info->description) ? $user_info->description : 'TA很懒，什么也没写';
    $web = !empty($user_info->user_url) ? $user_info->user_url : '暂无~';
    $qq = !empty($user_info->user_qq) ? $user_info->user_qq : '暂无~';
    $gender = isset($user_info->user_gender) ? $user_info->user_gender : '保密';
    $location_name = !empty($user_info->user_location) ? $user_info->user_location : '未知地带';
    $follower_count = ppo_get_follower_count($user_id);
    $following_count = ppo_get_following_count($user_id);

    if($gender == 0){
        $gender_name = '男';
    } else if($gender == 1){
        $gender_name = '女';
    } else {
        $gender_name = '保密';
    }
    $follow = '<div class="user-follow-block pix-user-home-follow-block">
                    <a href="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=follower" 
                            hx-get="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=follower"
                            hx-target="#user-content"
                            hx-swap="innerHTML"
                            hx-push-url="'.get_author_posts_url($user_id).'?tab=follow&type=follower"
                            data-skeleton="follow-list"
                            hx-history="false" class="follower-block pix-user-home-follow-link">
                        <div class="icon pix-user-home-follow-icon"><i class="ri-group-2-fill"></i></div>
                        <div class="info pix-user-home-follow-info">
                            <span class="pix-user-home-follow-label">粉丝</span>
                            <small class="pix-user-home-follow-count">'.$follower_count.'<i class="ri-arrow-right-s-line"></i></small>
                        </div>
                    </a>
                    <a href="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=following" 
                            hx-get="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=following"
                            hx-target="#user-content"
                            hx-swap="innerHTML"
                            hx-push-url="'.get_author_posts_url($user_id).'?tab=follow&type=following"
                            data-skeleton="follow-list"
                            hx-history="false" class="following-block pix-user-home-follow-link">
                        <div class="icon pix-user-home-follow-icon"><i class="ri-user-smile-fill"></i></div>
                        <div class="info pix-user-home-follow-info">
                            <span class="pix-user-home-follow-label">关注</span>
                            <small class="pix-user-home-follow-count">'.$following_count.'<i class="ri-arrow-right-s-line"></i></small>
                        </div>
                    </a>
            </div>';
    
    $location = '<div class="user-location-block pix-user-home-location-block">
                    <div class="info pix-user-home-location-info">
                        <i class="ri-map-pin-user-line"></i>
                        <span class="pix-user-home-location-text">'.esc_html($location_name).'</span>
                    </div>
                    <img class="lazy pix-user-home-location-map" data-src="'.THEME_URL.'/img/map.jpg">
                </div>';        

    $user_info = '<div class="user-info-block pix-user-home-info-block">
                    <li class="pix-user-home-info-item"><span class="pix-user-home-info-label">性别</span><small class="pix-user-home-info-value">'.$gender_name.'</small></li>
                    <li class="pix-user-home-info-item"><span class="pix-user-home-info-label">简介</span><small class="pix-user-home-info-value">'.$des.'</small></li>
                    <li class="pix-user-home-info-item"><span class="pix-user-home-info-label">站点</span><small class="pix-user-home-info-value">'.$web.'</small></li>
                    <li class="pix-user-home-info-item"><span class="pix-user-home-info-label">QQ</span><small class="pix-user-home-info-value">'.$qq.'</small></li>
                    <li class="pix-user-home-info-item"><span class="pix-user-home-info-label">注册时间</span><small class="pix-user-home-info-value">'.user_show_registered_date($user_id).'</small></li>
                </div>';            

    return $follow.$location.$user_info;
}

// 用户VIP区块（会员功能已移除）
function user_vip_block($user_id){
    return '';
}

// 用户数据统计区块
function user_stats_block($user_id){
    $counts = get_user_content_counts($user_id);
    $following = ppo_get_following_count($user_id);
    $followers = ppo_get_follower_count($user_id);
    $collect_moment = 0;

    $html = '<div class="user-stats-block pix-dashboard-overview-stats">';
    $html .= '<div class="stats-title pix-dashboard-overview-section-title"><i class="ri-bar-chart-line"></i><span>数据统计</span></div>';
    $html .= '<div class="stats-list pix-dashboard-overview-stats-list">';

    $stats_items = array(
        'posts'     => array('label' => '文章', 'count' => $counts['posts'], 'icon' => 'ri-article-line', 'tab' => 'posts'),
        'moment'    => array('label' => '片刻', 'count' => $counts['moment'], 'icon' => 'ri-donut-chart-line', 'tab' => 'moment'),
        'moments'   => array('label' => '圈子', 'count' => $counts['moments'], 'icon' => 'ri-bubble-chart-line', 'tab' => 'moments'),
        'comments'  => array('label' => '评论', 'count' => $counts['comments'], 'icon' => 'ri-chat-3-line', 'tab' => 'comments'),
        'following' => array('label' => '关注', 'count' => $following, 'icon' => 'ri-user-follow-line', 'tab' => 'follow','type'=>'following'),
        'followers' => array('label' => '粉丝', 'count' => $followers, 'icon' => 'ri-user-heart-line', 'tab' => 'follow','type'=>'follower'),
        'collect'   => array('label' => '文章收藏', 'count' => $counts['collect'], 'icon' => 'ri-star-line', 'tab' => 'collect'),
        'moment_collect' => array('label' => '片刻收藏', 'count' => $collect_moment, 'icon' => 'ri-heart-line', 'tab' => 'collect'),
    );

    foreach($stats_items as $key => $item){
        $type = isset($item['type']) ? '&type='.$item['type'] : '';
        $url = get_author_posts_url($user_id).'?tab='.$item['tab'].$type;
        $html .= '<div class="pix-dashboard-overview-stats-cell">';
        $html .= '<a href="'.esc_url($url).'" class="stats-item pix-dashboard-overview-stats-item">';
        $html .= '<div class="stats-icon pix-dashboard-overview-stats-icon"><i class="'.esc_attr($item['icon']).'"></i></div>';
        $html .= '<div class="stats-label pix-dashboard-overview-stats-label">'.esc_html($item['label']).'</div>';
        $html .= '<div class="stats-count pix-dashboard-overview-stats-count">'.esc_html($item['count']).'</div>';
        $html .= '</a></div>';
    }

    $html .= '</div></div>';
    return $html;
}

// 用户积分区块
function user_credit_block($user_id){
    $credit = get_user_credit($user_id);
    $credit_name = get_op('credit_name', '积分');
    $html = '<a href="'.esc_url(user_dashboard_url('task')).'" class="user-pannel-stat pix-dashboard-overview-wallet-card">
                <span class="user-pannel-stat-label">'.esc_html($credit_name).'</span>
                <span class="user-pannel-stat-value user-pannel-stat-credit">'.esc_html($credit).'</span>
                <img src="'.esc_url(THEME_URL.'/img/icon/credit.png').'" class="user-pannel-stat-credit" alt="">
            </a>';
    return $html;
}
