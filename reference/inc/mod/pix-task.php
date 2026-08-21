<?php
/**
 * 获取每日任务配置数据
 *
 * @param string $task_key 任务标识键，如 comment_task、login_task 等
 * @param string|null $field 可选字段（xp/point/limits），留空则返回整个配置数组
 * @return mixed
 */
function ppo_get_user_task_config($task_key, $field = null) {
    $tasks = get_op('daily_tasks');
    if (!is_array($tasks) || empty($tasks[$task_key])) {
        return null;
    }

    $task_config = $tasks[$task_key];

    if ($field) {
        return isset($task_config[$field]) ? $task_config[$field] : null;
    }

    return $task_config;
}

// 获取新手用户奖励配置数据
function ppo_get_new_user_rewards_config($task_key, $field = null) {
    $tasks = get_op('new_user_rewards');
    if (!is_array($tasks) || empty($tasks[$task_key])) {
        return null;
    }

    $task_config = $tasks[$task_key];

    if ($field) {
        return isset($task_config[$field]) ? $task_config[$field] : null;
    }

    return $task_config;
}

/**
 * 发放用户任务奖励
 *
 * @param int    $user_id
 * @param string $task_key Codestar 设置的任务 key，如 comment、post_article 等
 * @return array ['status' => bool, 'message' => string]
 */
function ppo_give_task_reward($user_id, $task_key,$note,$related_id = null) {
    if (!$user_id || !$task_key) {
        return ['status' => false, 'message' => '参数不完整'];
    }

    // 获取 Codestar 中的任务配置
    $xp     = intval(ppo_get_user_task_config($task_key, 'xp') ?? 0);
    $point  = intval(ppo_get_user_task_config($task_key, 'point') ?? 0);
    $limits = ppo_get_user_task_config($task_key, 'limits');
    $limits = isset($limits) ? intval($limits) : null;

    if ($xp === 0 && $point === 0) {
        return ['status' => false, 'message' => '任务配置为空'];
    }

    // 检查是否超过每日限制
    if ($limits !== null) {
        global $wpdb;
        $table = $wpdb->prefix . 'ppo_task_log';

        $today_bounds = pix_site_day_bounds();

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND task_key = %s AND reward_time BETWEEN %s AND %s",
            $user_id, $task_key, $today_bounds['start'], $today_bounds['end']
        ));

        if ($count >= $limits) {
            return ['status' => false, 'message' => '已达今日奖励上限'];
        }
    }

    // 添加经验值
    if ($xp > 0) {
        $current_xp = intval(get_user_meta($user_id, 'ppo_user_xp', true));
        update_user_meta($user_id, 'ppo_user_xp', $current_xp + $xp);
    }

    if ($point > 0) {
        Credit::credit_change($user_id, $point, array(
            'change_type' => 'task_reward',
            'order_id' => 'TASK-' . sanitize_key($task_key) . '-' . intval($related_id),
            'note' => $note,
        ));
    }

    // 写入日志
    if (!function_exists('ppo_insert_task_log')) {
        return ['status' => false, 'message' => '日志函数未定义'];
    }
    ppo_insert_task_log($user_id, $task_key, $xp, $point, $note,$related_id);
    ppo_clear_today_task_count_cache($user_id, $task_key);

    return ['status' => true, 'message' => '奖励成功'];
}

/**
 * 发放新手任务奖励
 *
 * @param int    $user_id
 * @param string $task_key 新手任务的 key，例如：register、upload_avatar
 * @param string $note     奖励说明
 * @param mixed  $related_id 可选，关联 ID（如上传的头像ID、用户资料ID）
 * @return array ['status' => bool, 'message' => string]
 */
function ppo_give_new_user_reward($user_id, $task_key, $note = '', $related_id = null) {
    if (!$user_id || !$task_key) {
        return ['status' => false, 'message' => '参数不完整'];
    }

    // 获取新手任务配置
    $xp    = intval(ppo_get_new_user_rewards_config($task_key, 'xp') ?? 0);
    $point = intval(ppo_get_new_user_rewards_config($task_key, 'point') ?? 0);

    if ($xp === 0 && $point === 0) {
        return ['status' => false, 'message' => '新手任务配置为空'];
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';

    // 检查是否已经领取过该新手任务（使用缓存）
    $cache_key = 'ppo_new_task_completed_' . $user_id . '_' . $task_key;
    $exists = wp_cache_get($cache_key);
    
    if ($exists === false) {
        // 缓存不存在，查询数据库
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND task_key = %s",
            $user_id, $task_key
        ));
        
        // 设置缓存，有效期30天
        wp_cache_set($cache_key, $exists, 'ppo', 2592000);
    }
    
    if ($exists > 0) {
        return ['status' => false, 'message' => '该新手任务已完成'];
    }

    // 添加经验值
    if ($xp > 0) {
        $current_xp = intval(get_user_meta($user_id, 'ppo_user_xp', true));
        update_user_meta($user_id, 'ppo_user_xp', $current_xp + $xp);
    }

    // 添加积分
    if ($point > 0) {
        Credit::credit_change($user_id, $point, array(
            'change_type' => 'new_user_reward',
            'order_id' => 'NEWTASK-' . sanitize_key($task_key) . '-' . intval($related_id),
            'note' => $note,
        ));
    }

    // 写入日志（注意区分新手任务和每日任务的 task_key）
    if (!function_exists('ppo_insert_task_log')) {
        return ['status' => false, 'message' => '日志函数未定义'];
    }

    ppo_insert_task_log($user_id, $task_key, $xp, $point, $note, $related_id);
    
    // 任务完成后清除缓存，确保下次查询时能获取最新状态
    $cache_key = 'ppo_new_task_completed_' . $user_id . '_' . $task_key;
    wp_cache_delete($cache_key, 'ppo');

    return ['status' => true, 'message' => '新手任务奖励成功'];
}


// 插入任务日志
function ppo_insert_task_log($user_id, $task_key, $xp = 0, $point = 0, $note = '',$related_id = null) {
    if (!$user_id || !$task_key) {
        return false;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';

    $data = [
        'user_id'     => intval($user_id),
        'task_key'    => sanitize_text_field($task_key),
        'xp'          => intval($xp),
        'point'       => intval($point),
        'reward_time' => current_time('mysql'),
        'ip_address'  => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
        'user_agent'  => sanitize_textarea_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'related_id' => intval($related_id),
        'note'        => sanitize_textarea_field($note),
    ];

    $format = ['%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'];

    $result = $wpdb->insert($table, $data, $format);

    return $result !== false;
}


// 增加积分和经验动作
add_action('ppo_user_add_xp', 'ppo_handle_user_add_xp', 10, 3);
add_action('ppo_user_add_point', 'ppo_handle_user_add_point', 10, 3);

function ppo_handle_user_add_xp($user_id, $xp, $source = '') {
    if (!$user_id || $xp <= 0) return;

    // 读取旧值
    $current_xp = (int)get_user_meta($user_id, 'ppo_user_xp', true);
    $new_xp = $current_xp + $xp;

    // 更新 meta
    update_user_meta($user_id, 'ppo_user_xp', $new_xp);

}

function ppo_handle_user_add_point($user_id, $point, $source = '') {
    if (!$user_id || $point <= 0) return;

    Credit::credit_change($user_id, $point, array(
        'change_type' => $source ? sanitize_key($source) : 'point_reward',
        'order_id' => $source ? strtoupper(sanitize_key($source)) . '-' . current_time('timestamp') : '',
        'note' => $source ? $source : get_op('credit_name', '积分').'奖励',
    ));
}

// 任务触发

// 钩子：用户成功评论后执行
add_action('comment_post', 'ppo_on_comment_post_award', 20, 2);
function ppo_on_comment_post_award($comment_id, $approved) {
    if ($approved != 1) return; // 只处理已通过审核的评论

    $comment = get_comment($comment_id);
    $commenter_id = $comment->user_id;
    $pid = $comment->comment_post_ID;
    $post_author_id = get_post_field('post_author', $comment->comment_post_ID);

    // 评论他人文章
    if ($commenter_id && $commenter_id != $post_author_id) {
        ppo_give_task_reward($commenter_id, 'comment','评论文章/片刻',$pid);
    }

    // 自己的文章被他人评论
    if ($post_author_id && $commenter_id != $post_author_id) {
        ppo_give_task_reward($post_author_id, 'content_commented','文章/片刻被评论',$pid);
    }

   
}

// 发布文章
add_action('transition_post_status', 'ppo_on_post_type_publish_award', 10, 3);
function ppo_on_post_type_publish_award($new_status, $old_status, $post) {
    error_log("transition: $old_status → $new_status | type: {$post->post_type}");
    if ($new_status !== 'publish' || $old_status === 'publish') return; // 只处理“首次发布”
    if (!$post || !in_array($post->post_type, ['post', 'moment'])) return;

    $author_id = intval($post->post_author);
    if (!$author_id) return;

    $note_map = [
        'post'   => '发布文章',
        'moment' => '发布片刻',
    ];

    $note = $note_map[$post->post_type] ?? null;
    $task_key = $post->post_type;
    if (!$task_key || !$note) return;

    ppo_give_task_reward($author_id, $task_key, $note, $post->ID);
}

// 文章/片刻点赞
add_action('ppo_like_content', function($user_id, $note, $post_id) {
    $post_author_id = get_post_field('post_author', $post_id);
    if ($post_author_id && $post_author_id != $user_id) {
        ppo_give_task_reward($user_id, 'like_content', $note.'点赞', $post_id);
        ppo_give_task_reward($post_author_id, 'content_liked', $note.'被点赞', $post_id);
    }
}, 10, 3); 

// 评论点赞
add_action('ppo_like_comment', function($user_id, $note, $comment_id) {
     $comment = get_comment($comment_id);
    if ($comment && $comment->user_id && $comment->user_id != $user_id) {
        ppo_give_task_reward($user_id, 'like_comment', $note, $comment_id);
        ppo_give_task_reward($comment->user_id, 'comment_liked', '评论被点赞', $comment_id);
    }
}, 10, 3); 

// 收藏
add_action('ppo_collect_content', function($user_id, $note, $post_id) {
    $post_author_id = get_post_field('post_author', $post_id);
    if ($post_author_id && $post_author_id != $user_id) {
        ppo_give_task_reward($post_author_id, 'content_favored', $note, $post_id);
        ppo_give_task_reward($user_id, 'collect_content', $note, $post_id);
    }
}, 10, 3); 

// 关注
add_action('ppo_follow_user', function($follower_id, $following_id) {
    ppo_give_task_reward($follower_id, 'follow_user', '关注他人',$following_id);
    ppo_give_task_reward($following_id, 'be_followed', '被关注',$follower_id);
}, 10, 2); 

// 发送私信
add_action('ppo_send_msg', function($sender_id, $note, $receive_id) {
        ppo_give_task_reward($sender_id, 'send_msg', $note, $receive_id);
}, 10, 3); 


// 每日任务banner
function daily_checkin_html($user_id) {
    $count = ppo_get_today_task_count($user_id, 'checkin');
    $checkin_btn = '<a class="user-sign-btn pix-dashboard-task-checkin-action">立即签到</a>';
    if ($count >0) {
        $checkin_btn = '<div class="checkin-completed pix-dashboard-task-checkin-action is-completed">今日已签到</div>';
    }
    $html = '<div class="pix-dashboard-task-grid-item"><div class="daily_checkin_banner pix-dashboard-task-checkin-card">
                <div class="left pix-dashboard-task-card-main">
                    <img class="pix-dashboard-task-card-icon" src="'.esc_url(THEME_URL.'/img/icon/checkin.png').'" alt="">
                    <span>每日签到<a class="user-sign-modal-btn task-checkin-btn pix-dashboard-task-card-link" hx-get="/wp-admin/admin-ajax.php?action=ppo_checkin_modal"
                        hx-target="#checkin-modal-here">签到详情<i class="ri-arrow-right-s-line"></i></a></span>
                </div>
                <div class="right pix-dashboard-task-card-action">
                    '.$checkin_btn.'
                </div>
            </div></div>';

    return $html;
}

// 每日任务完成次数
function ppo_get_today_task_count($user_id, $task_key) {
    // 生成缓存键，包含用户ID、任务键和日期
    $today_bounds = pix_site_day_bounds();
    $today = $today_bounds['date'];
    $cache_key = 'ppo_task_count_' . $user_id . '_' . $task_key . '_' . $today;
    
    // 尝试从缓存获取
    $count = wp_cache_get($cache_key, 'ppo');
    if ($count !== false) {
        return $count;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_task_log';

    $count = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table_name
        WHERE user_id = %d
        AND task_key = %s
        AND reward_time BETWEEN %s AND %s
    ", $user_id, $task_key, $today_bounds['start'], $today_bounds['end']));

    $count = intval($count);
    
    // 设置缓存，有效期到站点时区的当天结束
    wp_cache_set($cache_key, $count, 'ppo', $today_bounds['expires_in']);
    
    return $count;
}

function ppo_clear_today_task_count_cache($user_id, $task_key) {
    $today_bounds = pix_site_day_bounds();
    wp_cache_delete('ppo_task_count_' . $user_id . '_' . $task_key . '_' . $today_bounds['date'], 'ppo');
}

// 检查新手任务是否已完成
function ppo_is_new_task_completed($user_id, $task_key) {
    // 生成缓存键，包含用户ID和任务键
    $cache_key = 'ppo_new_task_completed_' . $user_id . '_' . $task_key;
    
    // 尝试从缓存获取
    $exists = wp_cache_get($cache_key, 'ppo');
    if ($exists !== false) {
        return $exists > 0;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';
    
    // 查询数据库
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND task_key = %s",
        $user_id, $task_key
    ));
    
    // 设置缓存，有效期30天
    wp_cache_set($cache_key, $exists, 'ppo', 2592000);
    
    return $exists > 0;
}

// 每日任务列表展示
function daily_tsak_list($user_id) {
    $tasks = get_op('daily_tasks');
    
    if (!is_array($tasks) || empty($tasks)) {
        return '<div class="daily-task-empty pix-dashboard-empty-state pix-dashboard-task-empty">任务未配置</div>';
    }
    
    $html = '';
    $xp_title = function_exists('ppo_xp_name') ? ppo_xp_name() : get_op('xp_slug', '经验值');
    $xp_icon = function_exists('ppo_xp_icon') ? ppo_xp_icon() : (!empty(get_op('xp_icon')) ? get_op('xp_icon') : THEME_URL.'/img/xp.png');
    $point_data = array('name' => get_op('credit_name', '积分'), 'icon' => THEME_URL . '/img/icon/credit.png');

    $task_map = [
        'comment' => ['title'=>'评论他人', 'des'=>'评论他人文章或片刻，评论自己的文章/片刻不计入每日任务奖励'],
        'post' => ['title'=>'发布文章', 'des'=>'发布优质文章，并且通过审核'],
        'moment' => ['title'=>'发布片刻', 'des'=>'发布片刻内容，并且通过审核'],
        'like_content' => ['title'=>'点赞文章/片刻', 'des'=>'点赞他人文章或片刻，点赞自己不计入每日任务奖励'],
        'collect_content' => ['title'=>'收藏文章/片刻', 'des'=>'收藏他人文章或片刻，收藏自己不计入每日任务奖励'],
        'like_comment' => ['title'=>'点赞评论', 'des'=>'点赞他人评论，点赞自己不计入每日任务奖励'],
        'follow_user' => ['title'=>'关注他人', 'des'=>'关注其他用户'],
        'send_msg' => ['title'=>'发送私信', 'des'=>'给其他用户发送私信'],
        'buy_resource' => ['title'=>'购买资源', 'des'=>'购买站内任意资源'],
        'recharge' => ['title'=>'充值', 'des'=>'充值'.get_op('cash_name', '余额')],
        'be_followed' => ['title'=>'被他人关注', 'des'=>'被其他用户关注'],
        'content_liked' => ['title'=>'文章/片刻被点赞', 'des'=>'其他用户点赞你的文章或片刻'],
        'comment_liked' => ['title'=>'评论被点赞', 'des'=>'其他用户点赞你的评论'],
        'moment_featured' => ['title'=>'片刻被加精', 'des'=>'片刻被圈主加精'],
        'content_favored' => ['title'=>'文章/片刻被收藏', 'des'=>'文章或片刻被其他用户收藏'],
        'content_commented' => ['title'=>'文章/片刻被评论', 'des'=>'其他用户评论你的文章或片刻'],
    ];
    foreach ($tasks as $key => $task) {
       // $task_data = ppo_get_user_task_config($key);
        $completed_count = ppo_get_today_task_count($user_id, $key);
        $total = isset($task['limits']) ? max(0, (int)$task['limits']) : 0;
        $percent = $total > 0 ? min(100, ($completed_count / $total) * 100) : 0;

        $task_title = $task_map[$key]['title'] ?? $key;
        $task_xp = isset($task['xp']) ? (int)$task['xp'] : 0;
        $task_point = isset($task['point']) ? (int)$task['point'] : 0;

        $html .= '<div class="pix-dashboard-task-grid-item"><div class="daily-task-list pix-dashboard-task-card">
            <div class="left pix-dashboard-task-card-main">
                <div class="title pix-dashboard-task-card-title">'.esc_html($task_title).'</div>
                <div class="add pix-dashboard-task-card-reward">
                    <span title="'.esc_attr($xp_title).'"><img class="lazy" data-src="'.esc_url($xp_icon).'" alt="'.esc_attr($xp_title).'"> +'.esc_html($task_xp).'</span>
                    <span>'.$point_data['icon'].' +'.esc_html($task_point).'</span>
                </div>
            </div>
            <div class="right pix-dashboard-task-card-progress">
                <div class="task-count"><span class="tasked-count">'.esc_html($completed_count) .'</span>/'.esc_html($total).'</div>
                <div class="completed-bar pix-dashboard-task-progress-bar"><div class="percent pix-dashboard-task-progress-value" style="width:'.esc_attr($percent).'%"></div></div>
            </div>
                </div></div>';

    }

    $checkin_item = daily_checkin_html($user_id);

    return '<div class="daily-task-item pix-dashboard-task-grid">'.$checkin_item.$html.'</div>';
}

// 新手任务
function new_user_tsak_list($user_id) {
    $tasks = get_op('new_user_rewards');
    
    if (!is_array($tasks) || empty($tasks)) {
        return '<div class="daily-task-empty pix-dashboard-empty-state pix-dashboard-task-empty">任务未配置</div>';
    }
    
    $html = '';
    $xp_title = function_exists('ppo_xp_name') ? ppo_xp_name() : get_op('xp_slug', '经验值');
    $xp_icon = function_exists('ppo_xp_icon') ? ppo_xp_icon() : (!empty(get_op('xp_icon')) ? get_op('xp_icon') : THEME_URL.'/img/xp.png');
    $point_data = array('name' => get_op('credit_name', '积分'), 'icon' => THEME_URL . '/img/icon/credit.png');

    $task_map = [
        'register' => ['title'=>'首次注册', 'des'=>'评论他人文章或片刻，评论自己的文章/片刻不计入每日任务奖励'],
        'email' => ['title'=>'完善邮箱', 'des'=>'发布优质文章，并且通过审核'],
        'phone' => ['title'=>'完善手机号码', 'des'=>'发布片刻内容，并且通过审核'],
        'bind_qq' => ['title'=>'绑定QQ', 'des'=>'点赞他人文章或片刻，点赞自己不计入每日任务奖励'],
        'avatar' => ['title'=>'上传头像', 'des'=>'收藏他人文章或片刻，收藏自己不计入每日任务奖励'],
        'cover' => ['title'=>'上传封面图', 'des'=>'点赞他人评论，点赞自己不计入每日任务奖励'],
    ];
    foreach ($tasks as $key => $task) {
        // 检查新手任务是否已完成
        $is_completed = ppo_is_new_task_completed($user_id, $key);
        
        // 完成状态的图片
        $completed_html = '';
        if ($is_completed) {
            $completed_html = '<div class="task-completed pix-dashboard-task-completed"><img src="' . THEME_URL . '/img/icon/complete.png" alt="已完成" /></div>';
        }

        $task_title = $task_map[$key]['title'] ?? $key;
        $task_xp = isset($task['xp']) ? (int)$task['xp'] : 0;
        $task_point = isset($task['point']) ? (int)$task['point'] : 0;

        $html .= '<div class="pix-dashboard-task-grid-item"><div class="daily-task-list pix-dashboard-task-card pix-dashboard-task-newbie-card' . ($is_completed ? ' completed' : '') . '">
            <div class="left pix-dashboard-task-card-main">
                <div class="title pix-dashboard-task-card-title">'.esc_html($task_title).'</div>
                <div class="add pix-dashboard-task-card-reward">
                    <span title="'.esc_attr($xp_title).'"><img class="lazy" data-src="'.esc_url($xp_icon).'" alt="'.esc_attr($xp_title).'"> +'.esc_html($task_xp).'</span>
                    <span>'.$point_data['icon'].' +'.esc_html($task_point).'</span>
                </div>
            </div>
            <div class="right pix-dashboard-task-card-status">
                '.$completed_html.'
            </div>
                </div></div>';

    }

    return '<div class="daily-task-item pix-dashboard-task-grid pix-dashboard-task-newbie-grid">'.$html.'</div>';
}

// 新手任务奖励

// 首次注册奖励
add_action('user_register', function($user_id) {
    $result = ppo_give_new_user_reward($user_id, 'register', '首次注册');
    // 可选记录日志或发送通知
}, 10, 1);

add_action('wp_login', 'ppo_check_and_give_register_reward_on_login', 10, 2);
function ppo_check_and_give_register_reward_on_login($user_login, $user) {
    $user_id = $user->ID;

    // 检查是否已经发放过首次注册奖励
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';
    $task_key = 'register';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND task_key = %s",
        $user_id, $task_key
    ));

    if ($exists > 0) {
        return; // 已发放过，不再处理
    }

    // 发放注册奖励
    $result = ppo_give_new_user_reward($user_id, $task_key, '首次注册');

    // 可选：记录日志或提示
    if (!$result['status']) {
        error_log("用户 {$user_id} 注册奖励发放失败：" . $result['message']);
    }
}

// 用户注册完成后检查邮箱和手机号，并发送系统通知
add_action('user_register', 'ppo_check_user_contact_after_register', 99, 1);
function ppo_check_user_contact_after_register($user_id) {
    // 立即标记为"待发送"状态，防止 wp_login 钩子重复发送
    update_user_meta($user_id, 'ppo_contact_notification_sent', 'pending');

    // 使用延迟处理，确保用户数据已完全写入
    wp_schedule_single_event(time() + 2, 'ppo_check_user_contact_delayed', array($user_id));
}

function ppo_send_contact_completion_notice($user_id) {
    $user_id = absint($user_id);
    if (!$user_id) {
        return false;
    }

    $status = get_user_meta($user_id, 'ppo_contact_notification_sent', true);
    if ($status === '1') {
        return true;
    }

    global $wpdb;
    $user_email = $wpdb->get_var($wpdb->prepare(
        "SELECT user_email FROM $wpdb->users WHERE ID = %d",
        $user_id
    ));

    if (!empty($user_email) && $user_email !== 'noemail@example.com') {
        update_user_meta($user_id, 'ppo_contact_notification_sent', '1');
        return true;
    }

    $title = '完善个人信息';
    $content = '<div class="bot-msg-content">亲爱的用户，欢迎注册！为了更好地为您服务，请完善您的邮箱信息。</div><div class="bot-msg-bottom"><a href="' . user_dashboard_url('edit') . '" class="btn btn-primary">立即完善</a></div>';

    if (function_exists('ppo_send_private_message')) {
        $message = '<h3>' . $title . '</h3>' . $content;
        $res = ppo_send_private_message('sys_bot', $user_id, $message);
        if ($res) {
            update_user_meta($user_id, 'ppo_contact_notification_sent', '1');
            return true;
        }
    }

    return false;
}

// 延迟执行的函数
add_action('ppo_check_user_contact_delayed', 'ppo_check_user_contact_delayed');
function ppo_check_user_contact_delayed($user_id) {
    wp_cache_flush();
    global $wpdb;
    $user_email = $wpdb->get_var($wpdb->prepare(
        "SELECT user_email FROM $wpdb->users WHERE ID = %d",
        $user_id
    ));
    ppo_send_contact_completion_notice($user_id);
    do_action('ppo_user_registered_check_contact', $user_id, $user_email, '');
}

// 在用户登录时检查是否需要发送完善信息通知
add_action('wp_login', 'ppo_check_contact_notification_on_login', 10, 2);
function ppo_check_contact_notification_on_login($user_login, $user) {
    $user_id = $user->ID;

    $notification_sent = get_user_meta($user_id, 'ppo_contact_notification_sent', true);
    if ($notification_sent == '1') {
        return;
    }
    if ($notification_sent === 'pending') {
        delete_user_meta($user_id, 'ppo_contact_notification_sent');
    }

    ppo_send_contact_completion_notice($user_id);
}

//上传头像 ppo_user_uploaded_avatar
add_action('ppo_user_uploaded_avatar', function($user_id, $avatar) {
    ppo_give_new_user_reward($user_id, 'avatar', '上传头像');
}, 10, 2);

//上传封面 ppo_user_uploaded_cover
add_action('ppo_user_uploaded_cover', function($user_id, $cover_id) {
    ppo_give_new_user_reward($user_id, 'cover', '上传封面', $cover_id);
}, 10, 2);
