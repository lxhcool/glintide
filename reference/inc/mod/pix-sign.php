<?php
function check_daily_checked($user_id){
    global $wpdb;
    $today = current_time('Y-m-d');
    $table = $wpdb->prefix . 'ppo_task_log';

    // 检查今天是否已经签到
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND task_key = 'checkin' AND DATE(reward_time) = %s",
        $user_id, $today
    ));

    return $exists;
}

function ppo_checkin_reward_day($streak) {
    $streak = max(1, (int)$streak);
    return (($streak - 1) % 7) + 1;
}

function ppo_get_checkin_total_days($user_id) {
    $meta_total = max(0, (int)get_user_meta($user_id, 'ppo_checkin_total_days', true));

    if (!$user_id) {
        return $meta_total;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';
    $log_total = (int)$wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT DATE(reward_time)) FROM $table WHERE user_id = %d AND task_key = 'checkin'",
        $user_id
    ));

    return max($meta_total, $log_total);
}

function ppo_get_checkin_streak_from_logs($user_id) {
    if (!$user_id) {
        return 0;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';
    $dates = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT DATE(reward_time) AS checkin_date FROM $table WHERE user_id = %d AND task_key = 'checkin' ORDER BY checkin_date DESC LIMIT 370",
        $user_id
    ));

    if (empty($dates)) {
        return 0;
    }

    $date_map = array_fill_keys($dates, true);
    $today = current_time('Y-m-d');
    $cursor = isset($date_map[$today]) ? $today : date('Y-m-d', strtotime('-1 day', strtotime($today)));

    if (!isset($date_map[$cursor])) {
        return 0;
    }

    $streak = 0;
    while (isset($date_map[$cursor])) {
        $streak++;
        $cursor = date('Y-m-d', strtotime('-1 day', strtotime($cursor)));
    }

    return $streak;
}

function ppo_get_effective_checkin_streak($user_id) {
    $streak = max(0, (int)get_user_meta($user_id, 'ppo_checkin_streak_days', true));
    $log_streak = ppo_get_checkin_streak_from_logs($user_id);
    if ($streak <= 0) {
        return $log_streak;
    }

    $last_date = get_user_meta($user_id, 'ppo_checkin_last_date', true);
    $today = current_time('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day', strtotime($today)));

    return ($last_date === $today || $last_date === $yesterday) ? max($streak, $log_streak) : $log_streak;
}

function user_sign_block($user_id){
    //$checked = check_daily_checked($user_id);
    $streak = ppo_get_checkin_total_days($user_id);
    $btn = '<a class="user-sign-modal-btn user-sign-btn-style"
        hx-get="/wp-admin/admin-ajax.php?action=ppo_checkin_modal"
        hx-target="#checkin-modal-here">每日签到<img id="indicator" class="htmx-indicator" src="'.THEME_URL.'/img/oval.svg"></a>';

    $html = '<div class="checkin-top">
                <div class="title">签到领福利</div>
                <a class="checkin-detail">
                    <img class="lazy" data-src="'.THEME_URL.'/img/checkin.png">
                    </a></div>
            <div class="chcekin-bottom">
                 <div class="total-sign">已累计签到'.$streak.'天</div>
                <div class="daily-check-btn-box">'.$btn.'</div>
            </div>
                <div class="circle"></div>';

    return $html;
}

// 签到回调
add_action('wp_ajax_ppo_checkin_modal', 'ppo_checkin_modal_handler');
function ppo_checkin_modal_handler() {
    $user_id = get_current_user_id();
    $streak = ppo_get_effective_checkin_streak($user_id);
    $checkin_list = '';

    $daily_checkin = get_op('daily_checkin');
    $daily_checkin = is_array($daily_checkin) ? $daily_checkin : [];
    $today_checked = check_daily_checked($user_id);

    if ($today_checked) {
        $today_index = ppo_checkin_reward_day($streak);
        $checked_index = $today_index;
    } elseif ($streak > 0) {
        $today_index = ppo_checkin_reward_day($streak + 1);
        $checked_index = ppo_checkin_reward_day($streak);
    } else {
        $streak = 0;
        $today_index = 1;
        $checked_index = 0;
    }

    $point_data = array('name' => get_op('credit_name', '积分'), 'icon' => THEME_URL . '/img/icon/credit.png');

    $xp_name = function_exists('ppo_xp_name') ? ppo_xp_name() : get_op('xp_slug', '经验值');
    $xp_icon = function_exists('ppo_xp_icon') ? ppo_xp_icon() : (!empty(get_op('xp_icon')) ? get_op('xp_icon') : THEME_URL.'/img/xp.png');

    for ($i = 1; $i <= 7; $i++) {
        $xp = isset($daily_checkin["daily_checkin_{$i}_xp"])? (int)$daily_checkin["daily_checkin_{$i}_xp"] : 0;
        $point = isset($daily_checkin["daily_checkin_{$i}_point"])? (int)$daily_checkin["daily_checkin_{$i}_point"] : 0;
        $is_today = ($i === $today_index);
        $is_checked = ($checked_index > 0 && $i <= $checked_index);

        $fudai = $i == 7 ? '<div class="fudai"><img class="lazy" data-src="' . THEME_URL . '/img/fudai.png"></div>' : '';
        
        $label = $is_today ? '今天' : "第 {$i} 天";
        $cehecked_icon = $is_checked ? '<img class="lazy" data-src="' . THEME_URL . '/img/checked.svg">已签' : $label;

        $checkin_tip = $xp_name . '+' . intval($xp) . ' ' . $point_data['name'] . '+' . intval($point);
        $checkin_list .= '<div class="checkin-list' . ($is_checked ? ' checked' : '') . '' . ($is_today ? ' today' : '') . ' pix-tooltip" data-pix-tooltip="'.esc_attr($checkin_tip).'" aria-label="'.esc_attr($checkin_tip).'" tabindex="0">
                            <div class="checkin-card">
                            '.$fudai.'
                            <div class="checkin-card-inner">
                                <div class="give-xp"><img class="lazy" data-src="'.esc_url($xp_icon).'" alt="'.esc_attr($xp_name).'"><span>+'.$xp.'</span></div>
                                <div class="give-point">'.$point_data['icon'].'<span>+'.$point.'</span></div>
                            </div>
                            </div>
                            <div class="checkin-daynum">'.$cehecked_icon.'</div>
                         </div>';
    }

    $today_checked_btn = !$today_checked ? '<a class="user-sign-btn"><img class="lazy" data-src="' . THEME_URL . '/img/checkin-btn.png"></a>' : '<a class="user-has-sign-btn">今日已签到</a>';

    $current_xp = (int)get_user_meta($user_id, 'ppo_user_xp', true);
    $current_point = (int)get_user_meta($user_id, 'ppo_credit', true);
    $html = '<div id="checkin-modal" class="pix-modal pix-hs-modal pix-dashboard-modal pix-dashboard-checkin-modal hidden" role="dialog" tabindex="-1" aria-label="签到">
                <div class="pix-modal-dialog">
                <div class="pix-modal-panel pix-dashboard-modal-dialog checkin-modal modal-rounded">
                <div class="circle1"></div><div class="circle2"></div>
                    <button class="pix-modal-close checkin-modal-cancel" type="button" data-pix-modal-close="#checkin-modal" aria-label="关闭"><i class="ri-close-line"></i></button>
                    <div class="checkin-banner">
                        <div class="left">
                            <img class="lazy" data-src="'.THEME_URL.'/img/checkin-text.png">
                            <div class="user-xp-info">
                                <span class="user-xp pix-tooltip" data-pix-tooltip="'.esc_attr($xp_name).'" aria-label="'.esc_attr($xp_name).'" tabindex="0"><img class="lazy" data-src="'.esc_url($xp_icon).'" alt="'.esc_attr($xp_name).'"><b>'.$current_xp.'</b></span>
                                <span class="user-point pix-tooltip" data-pix-tooltip="'.esc_attr($point_data['name']).'" aria-label="'.esc_attr($point_data['name']).'" tabindex="0">'.$point_data['icon'].'<b>'.$current_point.'</b></span>
                            </div>
                        </div>
                        <div class="right"><img class="lazy" data-src="'.THEME_URL.'/img/calendar.png"></div>
                    </div>
                    <div class="checkin-calendar">
                    <div class="checkin-streak">已连续签到<b>'.$streak.'</b>天</div>
                    <div class="calendar-inner">'.$checkin_list.'</div></div>
                    <div class="checkin-btn-box">'.$today_checked_btn.'</div>
                </div>
                </div>
            </div>';

            echo $html;
            exit;
}


add_action('wp_ajax_ppo_checkin', 'ppo_checkin_handler');
function ppo_checkin_handler() {
    global $wpdb;
    $user_id = get_current_user_id();
    $today = current_time('Y-m-d');
    $table = $wpdb->prefix . 'ppo_task_log';

    // 检查今天是否已经签到
    $exists = check_daily_checked($user_id);

    if ($exists) {
        $msg = array('msg' => '今日已签到', 'already_checked' => true);
        wp_send_json($msg);
    }

    // 连续签到逻辑
    $previous_streak = ppo_get_effective_checkin_streak($user_id);
    $streak = $previous_streak > 0 ? ($previous_streak + 1) : 1;
    $streak = max(1, $streak);
    
    $daily_checkin = get_op('daily_checkin');
    $daily_checkin = is_array($daily_checkin) ? $daily_checkin : [];

    // 奖励所属“第几天”循环（1 ~ 7）
    $day = ppo_checkin_reward_day($streak);

    $xp = isset($daily_checkin["daily_checkin_{$day}_xp"]) ? (int)$daily_checkin["daily_checkin_{$day}_xp"] : 0;
    $point = isset($daily_checkin["daily_checkin_{$day}_point"]) ? (int)$daily_checkin["daily_checkin_{$day}_point"] : 0;

    // 插入签到记录到 ppo_task_log
    $wpdb->insert($table, [
        'user_id'    => $user_id,
        'task_key'   => 'checkin',
        'xp'         => $xp,
        'point'      => $point,
        'reward_time'=> current_time('mysql'),
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'note'       => '每日签到'
    ]);

    // 更新 user_meta
    update_user_meta($user_id, 'ppo_checkin_last_date', $today);
    update_user_meta($user_id, 'ppo_checkin_streak_days', $streak);
    $total = max((int)get_user_meta($user_id, 'ppo_checkin_total_days', true) + 1, ppo_get_checkin_total_days($user_id));
    update_user_meta($user_id, 'ppo_checkin_total_days', $total);

    wp_cache_delete('ppo_task_count_' . $user_id . '_checkin_' . $today, 'ppo');

    // 触发 hook（可供你的积分/经验系统同步）
    do_action('ppo_user_add_xp', $user_id, $xp, 'checkin');
    do_action('ppo_user_add_point', $user_id, $point, 'checkin');

    // 返回签到结果
    $msg = array('msg' => '签到成功', 'xp' => $xp, 'point' => $point, 'streak' => $streak, 'total' => $total, 'reward_day' => $day);
    wp_send_json($msg);

}

// 获取今日签到用户排行
function get_today_checkin_users($limit = 6) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_task_log';
    $today = current_time('Y-m-d');

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT user_id, reward_time FROM $table WHERE task_key = 'checkin' AND DATE(reward_time) = %s ORDER BY reward_time ASC LIMIT %d",
        $today, $limit
    ));

    return $results;
}

// 获取连续签到排行
function get_streak_rank_users($limit = 6) {
    $users = get_users(array(
        'meta_key' => 'ppo_checkin_streak_days',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'number' => max($limit * 5, 30),
    ));

    $result = array();
    foreach ($users as $user) {
        $streak = ppo_get_effective_checkin_streak($user->ID);
        if ($streak > 0) {
            $result[] = array(
                'user_id' => $user->ID,
                'streak' => $streak,
            );
        }
    }

    usort($result, function($a, $b) {
        return (int)$b['streak'] <=> (int)$a['streak'];
    });

    return array_slice($result, 0, $limit);
}

// 签到小工具
function pix_sign_widget_func($data) {
    $title = isset($data['title']) ? $data['title'] : '';
    $rank_limit = isset($data['rank_limit']) ? intval($data['rank_limit']) : 6;

    $user_id = get_current_user_id();
    $today_checked = check_daily_checked($user_id);

    $total_days = ppo_get_checkin_total_days($user_id);

    $streak_days = ppo_get_effective_checkin_streak($user_id);

    $html = function_exists('pix_widget_title') ? pix_widget_title($title) : (trim((string)$title) !== '' ? '<div class="wid_title">' . esc_html($title) . '</div>' : '');
    $html .= '<div class="pix-sign-widget wid-item">';

    if ($user_id) {
        $placeholder_img = THEME_URL . '/img/sign-placeholder.png';
        $html .= '<div class="pix-sign-header">';

        if ($today_checked) {
            $html .= '<div class="pix-sign-btn-box"><span class="pix-sign-btn-disabled">今日已签到</span></div>';
        } else {
            $html .= '<div class="pix-sign-btn-box"><a href="javascript:void(0);" class="pix-sign-btn user-sign-btn" data-action="signin"><i class="ri-gift-line"></i>每日签到领福利！</a></div>';
        }
        $html .= '<div class="pix-sign-placeholder"><img src="'.THEME_URL.'/img/icon/checkin.png" alt="签到"></div>';
        $html .= '</div>';
    }

    $html .= '<div class="pix-sign-tabs">';
    $html .= '<div class="pix-sign-tab active" data-tab="today">今日签到</div>';
    $html .= '<div class="pix-sign-tab" data-tab="streak">连续签到</div>';
    $html .= '</div>';

    $html .= '<div class="pix-sign-rank-list" data-tab="today">';
    $today_users = get_today_checkin_users($rank_limit);
    if (empty($today_users)) {
        $html .= '<div class="pix-sign-empty">暂无数据</div>';
    } else {
        foreach ($today_users as $index => $item) {
            $user = get_userdata($item->user_id);
            if (!$user) continue;
            $avatar = get_u_avatar($item->user_id, 'url');
            $nickname = $user->display_name;
            $time = date('H:i', strtotime($item->reward_time));
            $rank = $index + 1;
            $html .= '<div class="pix-sign-rank-item">';
            $html .= '<span class="pix-sign-rank-num rank-' . $rank . '">' . $rank . '</span>';
            $html .= '<img src="' . esc_url($avatar) . '" alt="' . esc_attr($nickname) . '" class="pix-sign-avatar">';
            $html .= '<span class="pix-sign-name">' . esc_html($nickname) . '</span>';
            $html .= '<span class="pix-sign-time">' . $time . '</span>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    $html .= '<div class="pix-sign-rank-list" data-tab="streak" style="display:none;">';
    $streak_users = get_streak_rank_users($rank_limit);
    if (empty($streak_users)) {
        $html .= '<div class="pix-sign-empty">暂无数据</div>';
    } else {
        foreach ($streak_users as $index => $item) {
            $user = get_userdata($item['user_id']);
            if (!$user) continue;
            $avatar = get_u_avatar($item['user_id'], 'url');
            $nickname = $user->display_name;
            $streak = $item['streak'];
            $rank = $index + 1;
            $html .= '<div class="pix-sign-rank-item">';
            $html .= '<span class="pix-sign-rank-num rank-' . $rank . '">' . $rank . '</span>';
            $html .= '<img src="' . esc_url($avatar) . '" alt="' . esc_attr($nickname) . '" class="pix-sign-avatar">';
            $html .= '<span class="pix-sign-name">' . esc_html($nickname) . '</span>';
            $html .= '<span class="pix-sign-streak">' . $streak . '天</span>';
            $html .= '</div>';
        }
    }
    $html .= '</div>';

    $html .= '<div class="pix-sign-footer">';
    $html .= '<a href="#" class="pix-sign-more">签到排行榜 <i class="ri-arrow-right-s-line"></i></a>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}

