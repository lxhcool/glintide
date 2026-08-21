<?php
// 管理中心动态页面 - 显示用户的文章、片刻、圈子、评论、收藏
$user_id = get_current_user_id();
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'posts';

function admin_dynamic_nav($type, $uid) {
    $html = '';
    $counts = get_user_content_counts($uid);

    $args = array();

    $args['posts'] = array(
        'title'     => '文章',
        'tab'     => 'posts',
        'icon'     => '<i class="ri-article-line"></i>',
        'count'     => $counts['posts'],
        'skeleton' => 'post-list',
    );

    $args['moment'] = array(
        'title'     => '片刻',
        'tab'     => 'moment',
        'icon'     => '<i class="ri-donut-chart-line"></i>',
        'count'     => $counts['moment'],
        'skeleton' => 'moment-list',
    );

    $args['moments'] = array(
        'title'     => '圈子',
        'tab'     => 'moments',
        'icon'     => '<i class="ri-bubble-chart-line"></i>',
        'count'     => $counts['moments'],
        'skeleton' => 'moments-list',
    );

    $args['comments'] = array(
        'title'     => '评论',
        'tab'     => 'comments',
        'icon'     => '<i class="ri-chat-3-line"></i>',
        'count'     => $counts['comments'],
        'skeleton' => 'user-comment',
    );

    $args['collect'] = array(
        'title'     => '收藏',
        'tab'     => 'collect',
        'icon'     => '<i class="ri-star-smile-line"></i>',
        'count'     => $counts['collect'],
        'skeleton' => 'post-list',
    );

    $rest_nonce = wp_create_nonce('wp_rest');

    foreach($args as $key => $value){
        $active = $type === $key ? 'active' : '';
        $tab_url = '/wp-json/ppo/v1/user-'.$key.'?page=1&user_id='.$uid.'&_wpnonce='.$rest_nonce.'&target='.urlencode('#admin-dynamic-content').'&push_url_base='.urlencode(home_url('/dashboard'));
        $push_url = add_query_arg('tab', $key, home_url('/dashboard/trend'));
        $html .= '<a href="'.esc_url($tab_url).'"
            hx-get="'.esc_url($tab_url).'"
            hx-target="#admin-dynamic-content"
            hx-swap="innerHTML"
            hx-push-url="'.esc_url($push_url).'"
            hx-history="false"
            data-skeleton="'.$value['skeleton'].'"
            class="user-tab pix-dashboard-secondary-tab pix-dashboard-dynamic-tab user-'.$key.'-tab '.$active.'">
            <span class="icon pix-dashboard-dynamic-tab-icon">'.$value['icon'].'</span>
            <span class="title pix-dashboard-dynamic-tab-label">'.$value['title'].'<small class="pix-dashboard-dynamic-tab-count">'.$value['count'].'</small></span>
        </a>';
    }

    return '<div class="ppo-navtab pix-dashboard-secondary-tabs pix-dashboard-dynamic-tabs pix-tab-center big-rounded">'.$html.'</div>';
}

function admin_dynamic_skeleton($type) {
    $templates = array(
        'posts' => '<div class="pix-dashboard-dynamic-skeleton-item"><div class="skeleton-container"><div class="skeleton skeleton-image"></div><div class="skeleton skeleton-text-line"></div><div class="skeleton skeleton-text-line short"></div></div></div>',
        'moment' => '<div class="pix-dashboard-dynamic-skeleton-item pix-dashboard-dynamic-skeleton-item-wide"><div class="skeleton-container"><div class="skeleton-row"><div class="skeleton skeleton-avatar-square"></div><div class="skeleton-text-group"><div class="skeleton skeleton-text-line"></div><div class="skeleton skeleton-text-line short"></div></div></div><div class="skeleton skeleton-image"></div><div class="skeleton skeleton-text-line"></div><div class="skeleton skeleton-text-line short"></div></div></div>',
        'moments' => '<div class="pix-dashboard-dynamic-skeleton-item"><div class="skeleton-container"><div class="skeleton skeleton-image"></div></div></div>',
        'comments' => '<div class="pix-dashboard-dynamic-skeleton-item pix-dashboard-dynamic-skeleton-item-wide"><div class="skeleton-row"><div class="skeleton-text-group"><div class="skeleton skeleton-text-line"></div><div class="skeleton skeleton-text-line short"></div></div><div class="skeleton skeleton-avatar-square"></div></div></div>',
        'collect' => '<div class="pix-dashboard-dynamic-skeleton-item"><div class="skeleton-container"><div class="skeleton skeleton-image"></div><div class="skeleton skeleton-text-line"></div><div class="skeleton skeleton-text-line short"></div></div></div>',
    );
    $grid_classes = array(
        'posts' => 'pix-user-home-posts-grid',
        'moment' => 'pix-user-home-moment-list',
        'moments' => 'pix-user-home-moments-list',
        'comments' => 'pix-user-home-comments-list',
        'collect' => 'pix-user-home-collect-grid',
    );

    $item = isset($templates[$type]) ? $templates[$type] : $templates['posts'];
    $grid_class = isset($grid_classes[$type]) ? $grid_classes[$type] : $grid_classes['posts'];
    return '<div class="skeleton-grid admin-dynamic-skeleton pix-dashboard-dynamic-skeleton pix-dashboard-route-skeleton pix-user-home-skeleton-grid '.$grid_class.'">'.$item.$item.$item.'</div>';
}

$api_url = '/wp-json/ppo/v1/user-'.$tab.'?page=1&user_id='.$user_id.'&_wpnonce='.wp_create_nonce('wp_rest').'&target='.urlencode('#admin-dynamic-content').'&push_url_base='.urlencode(home_url('/dashboard'));
?>
<div class="user-index">
    <div class="admin-dynamic pix-dashboard-dynamic">
        <?php echo admin_dynamic_nav($tab, $user_id); ?>
        <div id="admin-dynamic-content" class="admin-dynamic-content dash-dynamic user-dash-box pix-dashboard-dynamic-content"
            hx-get="<?php echo esc_url($api_url); ?>"
            hx-trigger="load"
            hx-swap="innerHTML">
            <?php echo admin_dynamic_skeleton($tab); ?>
        </div>
    </div>
</div>
