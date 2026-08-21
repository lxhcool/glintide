<?php
function ppo_xp_name() {
    $name = get_op('xp_slug', '经验值');
    $name = is_string($name) ? trim($name) : '';
    return $name !== '' ? $name : '经验值';
}

function ppo_xp_icon() {
    $icon = get_op('xp_icon');
    return !empty($icon) ? $icon : THEME_URL.'/img/xp.png';
}

function ppo_get_level_index_by_xp($user_xp, $levels = null) {
    if ($levels === null) {
        $levels = get_op('user_level_item');
    }

    if (!is_array($levels) || empty($levels)) {
        return 0;
    }

    $levels = array_values($levels);
    $user_xp = intval($user_xp);

    for ($i = count($levels) - 1; $i >= 0; $i--) {
        $level_xp = intval($levels[$i]['lv_xp'] ?? 0);
        if ($user_xp >= $level_xp) {
            return $i;
        }
    }

    return 0;
}

/**
 * 获取用户等级信息（名称 + 图标）
 *
 * @param int $user_id 用户 ID
 * @return array|null 返回 ['name' => 等级名称, 'icon' => 图标URL]，未设置则返回 null
 */
function ppo_get_user_level_info($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return null;
    }

    // 获取 Codestar 中配置的等级数组（字段ID为 user_levels）
    $levels = get_op('user_level_item');
    if (!is_array($levels) || empty($levels)) {
        return null;
    }

    $levels = array_values($levels);
    $user_xp = intval(get_user_meta($user_id, 'ppo_user_xp', true));
    $level_index = ppo_get_level_index_by_xp($user_xp, $levels);
    $stored_level_index = intval(get_user_meta($user_id, 'ppo_user_level', true));

    $level_index = max(0, min($level_index, count($levels) - 1));
    if ($stored_level_index !== $level_index) {
        update_user_meta($user_id, 'ppo_user_level', $level_index);
    }

    $level = $levels[$level_index];

    return [
        'name' => isset($level['lv_name']) ? $level['lv_name'] : '未知等级',
        'icon' => isset($level['lv_icon']) ? $level['lv_icon'] : '',
        'xp' => isset($level['lv_xp']) ? $level['lv_xp'] : '',
        'lv' => $level_index + 1
    ];
}

function ppo_get_user_level_by_index($index) {
    $levels = get_op('user_level_item');
    if (!is_array($levels)) {
        return null;
    }

    $levels = array_values($levels);
    if (!isset($levels[$index])) {
        return null;
    }

    $level = $levels[$index];

    return [
        'name' => isset($level['lv_name'])? $level['lv_name'] : '未知等级',
        'icon' => isset($level['lv_icon'])? $level['lv_icon'] : '',
        'xp' => isset($level['lv_xp'])? $level['lv_xp'] : '',
        'lv' => $index + 1
    ];
}

function ppo_get_user_level_data($user_id) {
    $user_id = absint($user_id);
    if (!$user_id) {
        return null;
    }

    $levels = get_op('user_level_item');
    if (!is_array($levels) || empty($levels)) {
        return null;
    }

    $level_info = ppo_get_user_level_info($user_id);
    if (!is_array($level_info) || empty($level_info['lv'])) {
        return null;
    }

    $levels = array_values($levels);
    $index = max(0, intval($level_info['lv']) - 1);
    if (!isset($levels[$index]) || !is_array($levels[$index])) {
        return null;
    }

    $level = $levels[$index];
    $level['lv'] = $index + 1;
    $level['lv_index'] = $index;
    $level['lv_name'] = isset($level['lv_name']) ? $level['lv_name'] : ($level_info['name'] ?? '');
    $level['lv_icon'] = isset($level['lv_icon']) ? $level['lv_icon'] : ($level_info['icon'] ?? '');
    $level['lv_xp'] = isset($level['lv_xp']) ? $level['lv_xp'] : ($level_info['xp'] ?? 0);
    $level['down_num'] = isset($level['down_num']) ? $level['down_num'] : 0;
    $level['limits'] = isset($level['limits']) && is_array($level['limits']) ? $level['limits'] : array();

    return $level;
}

function ppo_get_user_level_down_num($user_id) {
    $level = ppo_get_user_level_data($user_id);
    if (!is_array($level)) {
        return 0;
    }

    return max(0, intval($level['down_num'] ?? 0));
}

function ppo_get_user_level_powers($user_id) {
    $level = ppo_get_user_level_data($user_id);
    if (!is_array($level) || empty($level['limits']) || !is_array($level['limits'])) {
        return array();
    }

    $allowed_powers = array('comment', 'moment', 'cr_moment', 'msg', 'up_image', 'up_video', 'up_file');
    $powers = array_map('strval', $level['limits']);
    return array_values(array_intersect($powers, $allowed_powers));
}

function ppo_user_level_has_power($user_id, $power) {
    $power = is_string($power) ? trim($power) : '';
    if ($power === '') {
        return false;
    }

    return in_array($power, ppo_get_user_level_powers($user_id), true);
}

function user_lv_block($user_id,$type = 'detail') {
    $lv_data = ppo_get_user_level_info($user_id);
    if (!$lv_data) {
        return '';
    }

    $xp_name = ppo_xp_name();
    $user_xp = intval(get_user_meta($user_id, 'ppo_user_xp', true));
    $next_lv = ppo_get_user_level_by_index($lv_data['lv']);
    $current_level = intval($lv_data['lv']);
    $current_level_xp = intval($lv_data['xp']);
    $is_max_level = empty($next_lv);

    if ($is_max_level) {
        $need_text = '已达到最高等级';
        $next_now = $user_xp.'/'.$current_level_xp;
        $xp_percentage = 100;
        $next_level_label = '已满级';
    } else {
        $next_level_xp = intval($next_lv['xp']);
        $need_xp = max(0, $next_level_xp - $user_xp);
        $need_text = '升级还需'.$need_xp.$xp_name;
        $next_now = $user_xp.'/'.$next_level_xp;
        $xp_range = max(1, $next_level_xp - $current_level_xp);
        $xp_percentage = min(100, max(0, round((($user_xp - $current_level_xp) / $xp_range) * 100)));
        $next_level_label = 'LV'.($current_level + 1);
    }

    if ($type == 'detail') {
        $detail_text = '<span class="lv-detail">等级详情<i class="ri-arrow-right-s-line"></i></span>';
    } else {
        $detail_text = '<span class="xp-detail lv-detail">'.$xp_name.'明细<i class="ri-arrow-right-s-line"></i></span>';
    }

    $html = '<span class="lv-bage pix-dashboard-task-level-badge">我的等级</span>
            <span class="lv-info pix-dashboard-task-level-info">
                <span class="lv-icon pix-dashboard-task-level-icon">
                    <img class="lazy" data-src="'.$lv_data['icon'].'">
                    <span class="lv-slug pix-dashboard-task-level-name">'.$lv_data['name'].'</span>
                </span>
                '.$detail_text.'
            </span>
            <span class="xp-line pix-dashboard-task-level-progress">
                <span class="xp-tips pix-dashboard-task-level-progress-meta"><span class="xp-need">'.$need_text.'</span><span class="next-now">'.$next_now.'</span></span>
                <span class="base-line pix-dashboard-task-progress-bar"><span class="current-line pix-dashboard-task-progress-value" style="width:'.$xp_percentage.'%"></span></span>
                <span class="next-lv pix-dashboard-task-level-progress-labels"><span class="currentlv">LV'.$current_level.'</span><span class="next-lv">'.$next_level_label.'</span></span>
            </span>';

        if ($type == 'detail') {
            return '<a href="'.user_dashboard_url('task').'">'.$html.'</a>';
        } else {
            return '<a href="/wp-json/ppo/v1/user-exp?page=1&_wpnonce='.wp_create_nonce('wp_rest').'" 
                hx-get="/wp-json/ppo/v1/user-exp?page=1&_wpnonce='.wp_create_nonce('wp_rest').'"
                hx-target=".dash-task-info"
                hx-swap="innerHTML"
                hx-push-url="'.home_url().'/dashboard/task?tab=detail"
                data-skeleton="normal-list"
                hx-history="false">'.$html.'</a>';
        }    
            
}

function user_lv_detail() {
    $levels = get_op('user_level_item');

    if (!is_array($levels)) {
        return '等级参数未配置';
    }

    // 获取用户当前等级
    // 获取用户当前等级
    $user_id = get_current_user_id();
    $current_level_data = ppo_get_user_level_info($user_id);
    $current_level_index = $current_level_data['lv'] ?? 1; // 默认为 1
    $current_level_index = $current_level_index - 1; // 减 1 来匹配索引

    $html = '';
    $max_height = 55; // 最大高度（像素）
    $min_height = 0;  // 最小高度（像素）
    $level_count = count($levels);

    $today_xp = ppo_get_user_today_xp($user_id);

    foreach ($levels as $index => $level) {
        $lv_name = $level['lv_name'] ?? '未知等级';
        $lv_xp = intval($level['lv_xp'] ?? 0);
        $lv_icon = !empty($level['lv_icon']) ? esc_url($level['lv_icon']) : '';
        $lv_label = 'LV' . ($index + 1);
        
        // 等比递增计算高度
        if ($level_count > 1) {
            // 计算当前等级的比例（0到1之间）
            $ratio = $index / ($level_count - 1);
            // 使用指数增长确保前面的等级也有合理高度
            $height = $min_height + ($max_height - $min_height) * pow($ratio, 0.6);
        } else {
            // 只有一个等级时使用中间高度
            $height = ($min_height + $max_height) / 2;
        }
        
        $height = round($height);
        
        // 判断是否是用户当前等级
        $current_class = ($index == $current_level_index) ? ' current' : '';

        $icon_html = '<span class="pix-dashboard-task-level-step-fallback"'.($lv_icon ? ' hidden' : '').'>'.esc_html($lv_label).'</span>';
        if ($lv_icon) {
            $icon_html = '<img src="'.$lv_icon.'" alt="'.esc_attr($lv_name).'" onerror="this.hidden=true;this.nextElementSibling.hidden=false;">'.$icon_html;
        }

        $html .= '<div class="lv-detail-item pix-dashboard-task-level-step swiper-slide">
                    <span class="lv-xp pix-dashboard-task-level-step-xp'.$current_class.'">'.$lv_xp.'</span>
                    <span class="lv-height pix-dashboard-task-level-step-bar'.$current_class.'" style="height:'.$height.'px"></span>
                    <div class="lv-icon pix-dashboard-task-level-step-icon pix-tooltip" data-pix-tooltip="'.esc_attr($lv_name).'" aria-label="'.esc_attr($lv_name).'" tabindex="0">
                        '.$icon_html.'
                    </div>
                </div>';

    }

    $list = '<div id="user-lv-detail-list" class="swiper pix-dashboard-task-level-swiper"><div class="swiper-wrapper">'.$html.'</div></div>';
    $title_line = '<div class="user-lv-detail-title pix-dashboard-task-level-detail-title"><span><i class="ri-line-chart-line"></i>等级成长体系</span><a class="pix-dashboard-task-level-today"><span>今日：</span>+'.$today_xp.'</a></div>';
    return $title_line.$list;
}

/**
 * 根据用户经验值更新用户等级
 * 
 * @param int $user_id 用户ID
 * @param int|null $user_xp 用户经验值（可选，如果不提供则从数据库获取）
 * @return array 返回更新结果 ['success' => bool, 'old_level' => int, 'new_level' => int, 'level_up' => bool]
 */
function ppo_update_user_level_by_xp($user_id, $user_xp = null) {
    if (!$user_id) {
        return ['success' => false, 'message' => '用户ID无效'];
    }

    
    // 获取用户当前经验值
    if ($user_xp === null) {
        $user_xp = intval(get_user_meta($user_id, 'ppo_user_xp', true));
    }
    $user_xp = intval($user_xp);
    
    // 获取等级配置
    $levels = get_op('user_level_item');
    
    if (!is_array($levels) || empty($levels)) {
        return ['success' => false, 'message' => '等级配置未设置'];
    }

    // 获取用户当前等级
    $current_level = intval(get_user_meta($user_id, 'ppo_user_level', true));
    
    $new_level = ppo_get_level_index_by_xp($user_xp, $levels);
    

    // 比较新旧等级
    $level_up = $new_level > $current_level;
    if ($new_level > $current_level) {
        // 触发升级钩子
        do_action('ppo_user_level_up', $user_id, $current_level, $new_level);
    }

    if ($new_level !== $current_level) {
        update_user_meta($user_id, 'ppo_user_level', $new_level);
    }

    $result = [
        'success' => true,
        'old_level' => $current_level,
        'new_level' => $new_level,
        'level_up' => $level_up,
        'user_xp' => $user_xp
    ];
    
    
    return $result;
}

/**
 * 在用户经验值更新后自动更新等级
 */
add_action('updated_user_meta', 'ppo_auto_update_level_on_xp_change', 10, 4);
function ppo_auto_update_level_on_xp_change($meta_id, $user_id, $meta_key, $meta_value) {
    if ($meta_key === 'ppo_user_xp') {
        ppo_update_user_level_by_xp($user_id);
    }
}

/**
 * 在添加用户经验值后自动更新等级
 */
add_action('added_user_meta', 'ppo_auto_update_level_on_xp_add', 10, 4);
function ppo_auto_update_level_on_xp_add($meta_id, $user_id, $meta_key, $meta_value) {
    if ($meta_key === 'ppo_user_xp') {
        ppo_update_user_level_by_xp($user_id);
    }
}

/**
 *  用户经验明细
 */

/**
 * 获取用户经验明细
 *
 * @param int $user_id 用户ID
 * @param int $limit 限制条数，默认10条
 * @param int $offset 偏移量，默认0
 * @return array 用户经验明细数组
 */
function ppo_get_user_xp_detail($user_id, $limit = 10, $offset = 0) {
    global $wpdb;
    
    if (!$user_id) {
        return [];
    }
    
    // 任务日志表名
    $table = $wpdb->prefix . 'ppo_task_log';
    
    // 查询用户经验明细
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE user_id = %d 
        AND xp > 0 
        ORDER BY reward_time DESC 
        LIMIT %d OFFSET %d",
        $user_id, $limit, $offset
    ), ARRAY_A);
    
    if (empty($results)) {
        return [];
    }
    
    // 格式化结果
    $detail = [];
    foreach ($results as $row) {
        $detail[] = [
            'id' => $row['id'],
            'task_key' => $row['task_key'],
            'task_name' => !empty($row['note']) ? $row['note'] : $row['task_key'], // 使用note作为任务名称，为空时使用task_key
            'xp' => intval($row['xp']),
            'reward_time' => $row['reward_time'],
            'formatted_time' => date('Y-m-d H:i:s', strtotime($row['reward_time']))
        ];
    }
    
    return $detail;
}

/**
 * 显示用户经验明细
 *
 * @param int $user_id 用户ID
 * @param int $limit 限制条数，默认10条
 * @param int $offset 偏移量，默认0
 * @return string 经验明细HTML
 */
/**
 * 获取用户经验记录总数
 *
 * @param int $user_id 用户ID
 * @return int 经验记录总数
 */
function ppo_get_user_xp_count($user_id) {
    global $wpdb;
    
    if (!$user_id) {
        return 0;
    }
    
    // 任务日志表名
    $table = $wpdb->prefix . 'ppo_task_log';
    
    // 查询用户经验记录总数
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table 
        WHERE user_id = %d 
        AND xp > 0",
        $user_id
    ));
    
    return intval($count);
}

/**
 * 获取用户今日经验获取总数
 *
 * @param int $user_id 用户ID
 * @return int 今日经验获取总数
 */
function ppo_get_user_today_xp($user_id) {
    global $wpdb;
    
    if (!$user_id) {
        return 0;
    }
    
    // 任务日志表名
    $table = $wpdb->prefix . 'ppo_task_log';
    
    // 获取站点时区下今天的开始和结束时间
    $today_bounds = pix_site_day_bounds();
    
    // 查询用户今日经验获取总数
    $total_xp = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(xp) FROM $table 
        WHERE user_id = %d 
        AND xp > 0 
        AND reward_time BETWEEN %s AND %s",
        $user_id, $today_bounds['start'], $today_bounds['end']
    ));
    
    return intval($total_xp);
}

/**
 * 显示用户经验明细（REST API回调）
 *
 * @param WP_REST_Request $request REST请求对象
 * @return string 经验明细HTML
 */
function ppo_display_user_xp_detail($page = 1 , $per_page = 10) {
    $user_id = get_current_user_id();
    
    // 如果仍然没有用户ID，返回错误
    if (!$user_id) {
        echo '<div class="xp-detail-empty pix-dashboard-empty-state pix-dashboard-task-empty">无法获取用户信息，请刷新页面重试</div>';
        exit;
    }
    
    $offset = ($page - 1) * $per_page;
    $detail = ppo_get_user_xp_detail($user_id, $per_page, $offset);
    $total_count = ppo_get_user_xp_count($user_id);
    
    $xp_name = ppo_xp_name();
    $xp_icon = ppo_xp_icon();

    if (empty($detail)) {
        echo '<div class="xp-detail-empty pix-dashboard-empty-state pix-dashboard-task-empty">暂无'.$xp_name.'获取记录</div>';
        exit;
    }

    $nav = ppo_htmx_pager([
        'base_url'    => '/wp-json/ppo/v1/user-exp',
        'user_id'     => '',
        'total_pages' => ceil($total_count / $per_page),
        'current'     => $page,
        'target'      => '.dash-task-info',
        'push_url'    => true,
        'push_url_base' => user_dashboard_url('task'),
        'query_args'  => ['tab' => 'detail'],
        'skeleton' => 'normal-list',
        'wpnonce' => true,
    ]);
    
    $html = '<div class="xp-detail-list pix-dashboard-task-xp-detail">';
    $html .= '<div class="task-page-title pix-dashboard-task-title">
                <div class="left"><i class="ri-list-check"></i><span>'.$xp_name.'明细</span><span class="xp-detail-total"> 共' . $total_count . '条记录</span></div>
                <a href="'.user_dashboard_url('task').'" class="right">查看任务<i class="ri-arrow-right-s-line"></i></a></div>';
    $html .= '<div class="xp-detail-content pix-dashboard-list pix-dashboard-task-xp-list">';
    
    foreach ($detail as $item) {
        $html .= '<div class="xp-detail-item pix-dashboard-list-item pix-dashboard-task-xp-item">
                    <div class="xp-detail-info pix-dashboard-task-xp-info">
                        <span class="xp-detail-task pix-dashboard-list-title">' . esc_html($item['task_name']) . '</span>
                        <span class="xp-detail-time pix-dashboard-list-meta">' . esc_html($item['formatted_time']) . '</span>
                    </div>
                    <div class="xp-detail-reward pix-dashboard-list-action pix-dashboard-task-xp-reward"><img src="'.esc_url($xp_icon).'" alt="'.esc_attr($xp_name).'图标" class="xp-icon"> +'. esc_html($item['xp']) .'</div>
                  </div>';
    }
    
    $html .= '</div>';
    $html .= '<div class="xp-detail-footer pix-dashboard-pagination-wrap">
                '.$nav.'
              </div>';
    $html .= '</div>';
    
    return $html;
}

function ppo_display_user_xp_detail_rest($request) {
    $page = $request->get_param('page') ? intval($request->get_param('page')) : 1;
    $per_page = $request->get_param('per_page') ? intval($request->get_param('per_page')) : 10;
    $html = ppo_display_user_xp_detail($page, $per_page);
    echo $html;
    exit;
}
