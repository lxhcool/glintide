<?php
// 用户关注

// 1. 关注用户
function ppo_follow_user($follower_id, $following_id) {
    global $wpdb;
    if ($follower_id == $following_id) return false;

    $table = $wpdb->prefix . 'ppo_follow';

    $result = $wpdb->query($wpdb->prepare(
        "INSERT IGNORE INTO $table (follower_id, following_id) VALUES (%d, %d)",
        $follower_id, $following_id
    ));

    if ($result > 0) {
        wp_cache_delete('ppo_following_count_' . $follower_id, 'ppo_follow');
        wp_cache_delete('ppo_follower_count_' . $following_id, 'ppo_follow');

        do_action('ppo_follow_user', $follower_id, $following_id);
    }

    return $result > 0;
}

// 2. 取消关注
function ppo_unfollow_user($follower_id, $following_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    $result = $wpdb->delete($table, [
        'follower_id'  => $follower_id,
        'following_id' => $following_id,
    ], ['%d', '%d']);

    if ($result !== false) {
        wp_cache_delete('ppo_following_count_' . $follower_id, 'ppo_follow');
        wp_cache_delete('ppo_follower_count_' . $following_id, 'ppo_follow');
    }

    return $result !== false;
}

// 3. 是否已关注
function ppo_is_following($follower_id, $following_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE follower_id = %d AND following_id = %d",
        $follower_id, $following_id
    ));

    return $exists > 0;
}

// 4. 是否互相关注
function ppo_is_mutual_follow($user_id_1, $user_id_2) {
    return ppo_is_following($user_id_1, $user_id_2) && ppo_is_following($user_id_2, $user_id_1);
}

function ppo_send_follow_notification($follower_id, $following_id) {
    if (!function_exists('ppo_send_private_message')) {
        return false;
    }

    $follower_id = absint($follower_id);
    $following_id = absint($following_id);
    if (!$follower_id || !$following_id || $follower_id === $following_id) {
        return false;
    }

    $follower = get_userdata($follower_id);
    if (!$follower) {
        return false;
    }

    $name = $follower->display_name ?: $follower->user_login;
    $profile_url = get_author_posts_url($follower_id);
    $avatar = get_u_avatar($follower_id, 'img');
    $is_mutual = ppo_is_mutual_follow($follower_id, $following_id);
    $title = $is_mutual ? '你们已互相关注' : '你有新的关注者';
    $desc = $is_mutual
        ? '关注了你，现在你们可以自由私信啦。'
        : '关注了你。回关后，你们就可以自由私信。';

    $message = '<div class="bot-msg-card follow-msg-card">'
        . '<h4>' . esc_html($title) . '</h4>'
        . '<div class="follow-msg-user">'
        . '<a class="follow-msg-avatar" href="' . esc_url($profile_url) . '">' . $avatar . '</a>'
        . '<div class="follow-msg-info">'
        . '<a class="follow-msg-name" href="' . esc_url($profile_url) . '">' . esc_html($name) . '</a>'
        . '<p>' . esc_html($desc) . '</p>'
        . '</div>'
        . '</div>'
        . '<div class="bot-msg-bottom">'
        . '<a class="btn btn-primary" href="' . esc_url($profile_url) . '">查看主页</a>'
        . '</div>'
        . '</div>';

    return ppo_send_private_message('sys_bot', $following_id, $message);
}
add_action('ppo_follow_user', 'ppo_send_follow_notification', 20, 2);

// 5. 获取我关注的用户（我 follow 的人）
function ppo_get_following_user_ids($user_id, $limit = 100, $offset = 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    return $wpdb->get_col($wpdb->prepare(
        "SELECT following_id FROM $table WHERE follower_id = %d ORDER BY follow_time DESC LIMIT %d OFFSET %d",
        $user_id, $limit, $offset
    ));
}

// 6. 获取关注我的用户（follow 我的人）
function ppo_get_follower_user_ids($user_id, $limit = 100, $offset = 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    return $wpdb->get_col($wpdb->prepare(
        "SELECT follower_id FROM $table WHERE following_id = %d ORDER BY follow_time DESC LIMIT %d OFFSET %d",
        $user_id, $limit, $offset
    ));
}

// 获取用户关注的人数（关注数）
function ppo_get_following_count($user_id) {
    $cache_key = 'ppo_following_count_' . $user_id;
    $cached = wp_cache_get($cache_key, 'ppo_follow');

    if ($cached !== false) {
        return $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE follower_id = %d",
        $user_id
    ));

    wp_cache_set($cache_key, $count, 'ppo_follow', 3600); // 缓存 1 小时
    return $count;
}

// 获取用户被关注的人数（粉丝数）
function ppo_get_follower_count($user_id) {
    $cache_key = 'ppo_follower_count_' . $user_id;
    $cached = wp_cache_get($cache_key, 'ppo_follow');

    if ($cached !== false) {
        return $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_follow';

    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE following_id = %d",
        $user_id
    ));

    wp_cache_set($cache_key, $count, 'ppo_follow', 3600); // 缓存 1 小时
    return $count;
}


// ajax关注
add_action('wp_ajax_ppo_follow_user_ajax', 'ppo_follow_user_ajax');
function ppo_follow_user_ajax(){
    $follower_id = get_current_user_id(); 
    $following_id = $_POST['following_id'];
    if(!$follower_id || !$following_id) {
        wp_send_json_error('参数错误');
    }

    if(ppo_is_following($follower_id, $following_id)){
        wp_send_json_error('已关注');
    }

    if(ppo_follow_user($follower_id, $following_id)){
        wp_send_json_success('关注成功');
    }

    wp_send_json_error('关注失败，请稍后重试');
    
}

// ajax取消关注
add_action('wp_ajax_ppo_unfollow_user_ajax', 'ppo_unfollow_user_ajax');
function ppo_unfollow_user_ajax(){
    $follower_id = get_current_user_id();
    $following_id = $_POST['following_id'];
    if(!$follower_id || !$following_id) {
        wp_send_json_error('参数错误');
    }

    if(ppo_unfollow_user($follower_id, $following_id)){
        wp_send_json_success('已取消关注');
    }

    wp_send_json_error('取消关注失败，请稍后重试');
    
}

// 粉丝列表
function follower_list($user_id, $page,$limit) {
    $html = '';
    $empty_img = THEME_URL. '/img/empty.png'; 
    $offset = ($page - 1) * $limit;
    $items = ppo_get_follower_user_ids($user_id, $limit,$offset);
    $follower_count = ppo_get_follower_count($user_id);
    $tab = follow_sub_nav($user_id);
    foreach ($items as $item) {
        $user_info = get_user_by('id',$item);
        $name = $user_info->display_name;
        $des = $user_info->description ? $user_info->description : 'TA还没有简介啊~~';
        $is_followed = ppo_is_mutual_follow($user_id, $item);
        $follow_link = $is_followed ? '<a class="follow-user-btn follow-list-btn pix-user-home-follow-btn pix-tooltip pix-tooltip-bottom" action="unfollow" data-uid="'.absint($item).'" data-pix-tooltip="取消关注">互相关注</a>' : '<a class="follow-user-btn follow-list-btn pix-user-home-follow-btn pix-tooltip pix-tooltip-bottom" action="follow" data-uid="'.absint($item).'" data-pix-tooltip="点击关注">回关</a>';
        $html.= '<div class="pix-user-home-follow-item">
                    <div class="follower-item pix-user-home-follow-card">
                        <a href="'. esc_url(get_author_posts_url($item)) .'" class="follower-avatar pix-user-home-follow-avatar">'. get_u_avatar($item, 'img') .'</a>
                        <div class="follower-info pix-user-home-follow-info">
                            <div class="name pix-user-home-follow-name"><a href="'. esc_url(get_author_posts_url($item)) .'">'.esc_html($name).'</a></div>
                            <div class="des pix-user-home-follow-desc">'.esc_html($des).'</div>
                            '.$follow_link.'
                        </div>
                    </div>
                </div>';
    }

    if(empty($items)) {
        $html = '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img class="lazy" data-src="'.$empty_img.'"></div>';
    } else {
        $html = '<div class="user-follower-list pix-user-home-follow-list">'.$html.'</div>';
    }

    $nav = ppo_htmx_pager([
        'base_url'    => '/wp-json/ppo/v1/user-follow',
        'user_id'     => $user_id,
        'total_pages' => ceil($follower_count / $limit),
        'current'     => $page,
        'target'      => '#user-content',
        'push_url'    => true,
        'push_url_base' => get_author_posts_url($user_id),
        'query_args'  => ['tab' => 'follow','type' => 'follower'],
        'skeleton' => 'follow-list',
        'class'       => 'pix-user-home-pagination',
    ]);

    $html = $tab.$html.'<div class="pix-user-home-pagination-wrap">'.$nav.'</div>';
    return $html;
}

// 关注列表
function following_list($user_id, $page,$limit) {
    $html = '';
    $empty_img = THEME_URL. '/img/empty.png'; 
    $offset = ($page - 1) * $limit;
    $items = ppo_get_following_user_ids($user_id, $limit,$offset);
    $following_count = ppo_get_following_count($user_id);
    $tab = follow_sub_nav($user_id);
    foreach ($items as $item) {
        $user_info = get_user_by('id',$item);
        $name = $user_info->display_name;
        $des = $user_info->description ? $user_info->description : 'TA还没有简介啊~~';
        $is_followed = ppo_is_mutual_follow($user_id, $item);
        $follow_link = $is_followed ? '<a class="follow-user-btn follow-list-btn pix-user-home-follow-btn pix-tooltip pix-tooltip-bottom" action="unfollow" data-uid="'.absint($item).'" data-pix-tooltip="取消关注">互相关注</a>' : '<a class="follow-user-btn follow-list-btn pix-user-home-follow-btn pix-tooltip pix-tooltip-bottom" action="unfollow" data-uid="'.absint($item).'" data-pix-tooltip="取消关注">已关注</a>';
        $html.= '<div class="pix-user-home-follow-item">
                    <div class="follower-item pix-user-home-follow-card">
                        <a href="'. esc_url(get_author_posts_url($item)) .'" class="follower-avatar pix-user-home-follow-avatar">'. get_u_avatar($item, 'img') .'</a>
                        <div class="follower-info pix-user-home-follow-info">
                            <div class="name pix-user-home-follow-name"><a href="'. esc_url(get_author_posts_url($item)) .'">'.esc_html($name).'</a></div>
                            <div class="des pix-user-home-follow-desc">'.esc_html($des).'</div>
                            '.$follow_link.'
                        </div>
                    </div>
                </div>';
    }

    if(empty($items)) {
        $html = '<div class="nodata pix-user-home-empty pix-user-home-empty-state"><img class="lazy" data-src="'.$empty_img.'"></div>';
    } else {
        $html = '<div class="user-following-list pix-user-home-follow-list">'.$html.'</div>';
    }

    $nav = ppo_htmx_pager([
        'base_url'    => '/wp-json/ppo/v1/user-follow',
        'user_id'     => $user_id,
        'total_pages' => ceil($following_count / $limit),
        'current'     => $page,
        'target'      => '#user-content',
        'push_url'    => true,
        'push_url_base' => get_author_posts_url($user_id),
        'query_args'  => ['tab' => 'follow','type' => 'following'],
        'skeleton' => 'follow-list',
        'class'       => 'pix-user-home-pagination',
    ]);

    $html = $tab.$html.'<div class="pix-user-home-pagination-wrap">'.$nav.'</div>';

    return $html;
}

// 关注粉丝回调
function ppo_get_user_follow($request){
    $user_id  = intval($request->get_param('user_id'));
    $page     = max(1, intval($request->get_param('page')));
    $type = $request->get_param('type');
    $per_page = 12;
    if($type == 'follower') {
        $html = follower_list($user_id, $page,$per_page);
    } else {
        $html = following_list($user_id, $page,$per_page);
    }
    
    echo $html;
    exit;                     
}                  
                       
function follow_sub_nav($user_id){       
    $type = isset($_GET['type'])? $_GET['type'] : 'follower';           
    $active_follower = $type === 'follower' ? 'active' : '';
    $active_following = $type === 'following' ? 'active' : '';
    $html = '<div class="follow-nav pix-user-home-follow-nav">
                <a class="follower pix-user-home-follow-tab '.$active_follower.'"
                    href="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=follower" 
                    hx-get="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=follower"
                    hx-target="#user-content"
                    hx-swap="innerHTML"
                    hx-push-url="'.get_author_posts_url($user_id).'?tab=follow&type=follower"
                    data-skeleton="follow-list"
                    hx-history="false">粉丝</a>
                <a class="follower pix-user-home-follow-tab '. $active_following.'"
                    href="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=following"
                    hx-get="/wp-json/ppo/v1/user-follow?page=1&user_id='.$user_id.'&type=following"
                    hx-target="#user-content"
                    hx-swap="innerHTML"
                    hx-push-url="'.get_author_posts_url($user_id).'?tab=follow&type=following"
                    data-skeleton="follow-list"
                    hx-history="false">关注</a>
            </div>';

            return $html;
}

// ajax加载关注粉丝列表
function load_follow_list_ajax() {
    $action = isset($_POST['follow_action']) ? $_POST['follow_action'] : false;
    $follower_id = get_current_user_id();
    $paged = isset($_POST['page']) ? $_POST['page'] : 1;
    $limit = 12;
    $offset = ($paged - 1) * $limit;
    if(!$action || !$follower_id) {
        return;
    }

   if($action == 'follower') {
        $follower_count = ppo_get_follower_count($follower_id);
        $total_pages = ceil($follower_count / $limit);
        $html = follower_list($follower_id, $paged, $limit);
        $nav_action = 'follower_ajax_pageload';
    } else {
        $following_count = ppo_get_following_count($follower_id);
        $total_pages = ceil($following_count / $limit);
        $html = following_list($follower_id, $paged, $limit);
        $nav_action = 'following_ajax_pageload';
    } 

    //$html = !empty($html) ? $html : '<div class="nodata"><img class="lazy" data-src="'.$empty_img.'"></div>';

    $nav = ppo_htmx_pagination($total_pages, 1, 2, $nav_action, '.follow-content .inner');

    echo $html.$nav;
    wp_die();
}
add_action('wp_ajax_load_follow_list_ajax', 'load_follow_list_ajax');
add_action('wp_ajax_nopriv_load_follow_list_ajax', 'load_follow_list_ajax');

// 粉丝分页ajax
function follower_ajax_pageload(){
    $user_id = get_current_user_id();
    $paged = isset($_GET['page']) ? $_GET['page'] : 1;
    $follower_count = ppo_get_follower_count($user_id);
    $limit = 12;
    $offset = ($paged - 1) * $limit;
    $total_pages = ceil($follower_count / $limit);

    $html = follower_list($user_id, $paged, $limit);
    $nav = ppo_htmx_pagination($total_pages, $paged, 2, 'follower_ajax_pageload', '.follow-content .inner');
    echo $html.$nav;
    wp_die();
}
add_action('wp_ajax_follower_ajax_pageload', 'follower_ajax_pageload');
add_action('wp_ajax_nopriv_follower_ajax_pageload', 'follower_ajax_pageload');

// 关注分页ajax
function following_ajax_pageload(){
    $user_id = get_current_user_id();
    $paged = isset($_GET['page']) ? $_GET['page'] : 1;
    $following_count = ppo_get_following_count($user_id);
    $limit = 12;
    $offset = ($paged - 1) * $limit;
    $total_pages = ceil($following_count / $limit);

    $html = following_list($user_id, $paged, $limit);
    $nav = ppo_htmx_pagination($total_pages, $paged, 2, 'following_ajax_pageload', '.follow-content .inner');
    echo $html.$nav;
    wp_die();
}
add_action('wp_ajax_following_ajax_pageload', 'following_ajax_pageload');
add_action('wp_ajax_nopriv_following_ajax_pageload', 'following_ajax_pageload');
