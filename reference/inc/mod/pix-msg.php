<?php
// 消息中心导航
function msg_center_nav($type){
    $html = '';
    $user_id = get_current_user_id();
    $moment_count = get_unread_comment_msg_count($user_id);
    $like_count = get_unread_like_msg_count($user_id);
    $system_count = get_unread_system_msg_count($user_id);
    $chat_count = ppo_get_unread_message_count($user_id);
    $arr = array(
        array('title' => '我的消息', 'icon' => 'ri-chat-smile-2-line', 'tab' => 'whisper','count'=> $chat_count > 0 ? '<span class="msg-badge">'.$chat_count.'</span>' : ''),
        array('title' => '回复我的', 'icon' => 'ri-message-2-line', 'tab' => 'reply','count'=> $moment_count > 0 ? '<span class="msg-badge">'.$moment_count.'</span>' : ''),
        array('title' => '点赞收藏', 'icon' => 'ri-thumb-up-line', 'tab' => 'like','count' => $like_count > 0 ? '<span class="msg-badge">'.$like_count.'</span>' : ''),
        array('title' => '系统通知', 'icon' => 'ri-settings-4-line', 'tab' => 'system','count' => $system_count > 0 ? '<span class="msg-badge">'.$system_count.'</span>' : ''),
    );

    foreach($arr as $item){
        $active = $type === $item['tab'] ? 'active' : '';
        //$html .= '<li><a href="'.home_url('/msg/'.$item['tab'].'').'" up-target=".user-right" up-preload class="nav-item '.$active.'">';
        $html .= '<li><a href="'.home_url('/msg/'.$item['tab'].'').'"  class="nav-item pix-dashboard-message-tab '.$item['tab'].'-nav '.$active.'">';
        $html .= '<i class="'. $item['icon'] .'"></i>';
        $html .= '<span class="nav-title">'. $item['title'] .'</span>'.$item['count'].'</a></li>'; 
    }

    return '<ul class="pix-dashboard-message-tabs">'.$html.'</ul>';
}

// 评论我的  
// 获取回复我的评论列表
/**
 * 获取回复指定用户的所有评论
 * 
 * @param int|null $user_id 用户ID（默认当前登录用户）
 * @param array $args 自定义查询参数（可选）
 * @return WP_Comment[] 返回评论对象数组
 */

// 分页方式
function get_user_comment_notifications($user_id = null, $paged = 1, $per_page = 10) {
    global $wpdb;

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $offset = ($paged - 1) * $per_page;

    // === 获取我发的评论ID
    $my_comment_ids = get_comments([
        'user_id' => $user_id,
        'status' => 'approve',
        'type' => 'comment',
        'fields' => 'ids',
        'number' => 500,
    ]);

    // === 获取我发的文章ID
    $my_post_ids = get_posts([
        'author' => $user_id,
        'post_status' => 'publish',
        'numberposts' => 500,
        'fields' => 'ids',
    ]);

    if (empty($my_comment_ids) && empty($my_post_ids)) {
        return [];
    }

    // === 获取已读ID
    $read_ids = get_user_meta($user_id, 'unread_msg_comment_ids', true);
    $read_ids = is_array($read_ids) ? $read_ids : [];

    // === SQL 查询
    $where_clauses = [];
    if (!empty($my_comment_ids)) {
        $where_clauses[] = "(comment_parent IN (" . implode(',', array_map('intval', $my_comment_ids)) . ") AND user_id != {$user_id})";
    }
    if (!empty($my_post_ids)) {
        $where_clauses[] = "(comment_post_ID IN (" . implode(',', array_map('intval', $my_post_ids)) . ") AND user_id != {$user_id})";
    }

    $where_sql = implode(' OR ', $where_clauses);

    $sql = "
        SELECT * FROM $wpdb->comments
        WHERE comment_approved = 1 AND ($where_sql)
        ORDER BY comment_date DESC
        LIMIT %d OFFSET %d
    ";

    $results = $wpdb->get_results($wpdb->prepare($sql, $per_page, $offset));

    $notifications = [];
    foreach ($results as $comment) {
        $type = in_array($comment->comment_parent, $my_comment_ids) ? 'reply_to_my_comment' : 'comment_on_my_post';
        $comment_user = $comment->user_id ? $comment->user_id : $comment->comment_author_email;

        $notifications[] = [
            'type' => $type,
            'comment_ID' => $comment->comment_ID,
            'post_ID' => $comment->comment_post_ID,
            'content' => $comment->comment_content,
            'author' => $comment->comment_author,
            'comment_user' => $comment_user,
            'is_email' => !$comment->user_id,
            'parent_comment_id' => $comment->comment_parent,
            'date' => $comment->comment_date,
            'link' => get_comment_link($comment),
            'is_read' => in_array($comment->comment_ID, $read_ids),
        ];
    }

    return $notifications;
}



// 回复我的列表
function get_msg_reply_list($user_id ,$paged, $per_page){
    $comments = get_user_comment_notifications($user_id = null ,$paged, $per_page);
    $html = '';
    foreach($comments as $comment){
        $comment_id = $comment['comment_ID']; // 获取评论ID
        $comment_author = $comment['author']; // 获取评论作者
        $comment_content = $comment['content']; // 获取评论内容 
        $comment_user = $comment['comment_user']; // 获取评论用户ID
        $comment_date = $comment['date']; // 获取评论日期
        $comment_link = $comment['link']; // 获取评论链接
        $pid = $comment['post_ID']; // 获取文章ID
        $parent_comment_id = $comment['parent_comment_id']; // 获取父级评论ID
        $read_icon = $comment['is_read'] ? '<span class="unread-bage"></span>' : '';

        if ( is_numeric( $comment_user ) ) {
            // 是 user_id（数字） → 登录用户
            $avatar = get_u_avatar( (int)$comment_user, 'url' );
            $url = get_author_posts_url( $comment_user );
            $user = get_user_by('id', $comment_user);
            $name = $user->display_name;
        } else {
            // 是邮箱地址 → 游客用户
            $avatar = get_avatar_url( $comment_user, [ 'size' => 60 ] );
            $url = $comment_link;
            $name = $comment_author;
        }

        if($comment['type'] == 'comment_on_my_post'){
            $text = '评论了您的文章<a class="p-link" href="'. $comment_link.'">《'.get_the_title($comment['post_ID']).'》</a>';
        } else {
            $text = '回复了您的评论';
        }

        if($parent_comment_id > 0) {
            $c = get_comment($parent_comment_id);
            $thum = '<p class="parent-expert">'.wp_trim_words( $c->comment_content, 11 ).'</p>';
        } else {
            $thum = '<img class="lazy" data-src="'.get_ppo_thum( $pid, 'large','random').'">';
        }
           

        $reply_url = home_url('/msg/reply/'. $pid .'?replytocom='.$comment_id.'#respond');

        $reply_text = '<a  class="msg-reply-link" data-commentid="'.$comment_id.'" data-postid="'.$pid.'" data-belowelement="reply-'.$comment_id.'" data-replyto="回复给'.$name.'"><i class="ri-chat-3-line"></i>回复</a>';

        $html .= '<div class="reply-item pix-dashboard-message-item pix-dashboard-message-reply-item reply-item-'.$comment_id.' comments-area" data="reply">
                    <div class="reply-'.$comment_id.' reply-inner pix-dashboard-message-inner">
                        <div class="reply-avatar pix-dashboard-message-avatar">
                            <div class="reply-user">
                                <a href="'.$url.'" target="_blank">
                                    <img src="'.get_u_avatar($comment_user,'url').'" alt="头像">
                                    '.$read_icon.'
                                </a>
                            </div>
                        </div>
                            <div class="comment-right pix-dashboard-message-content">
                            <div class="reply-info pix-dashboard-message-title"><a class="u-name" href="'.$url.'" target="_blank"><span>'.$name.'</span></a>'.$text.'</div>
                            <div class="comment-content pix-dashboard-message-excerpt">'.$comment_content .'</div>
                            <div class="footer pix-dashboard-message-meta">
                                <span class="date">'.date('Y-m-d H:i', strtotime($comment_date)).'</span>
                                <span class="reply-btn">'.$reply_text.'</span>
                                <span class="msg-like-btn">'.comment_like_btn($comment_id).'</span>
                            </div>
                            </div>
                        <div class="thum pix-dashboard-message-thumb"><a href="'.get_permalink( $pid ).'" target="_blank">'.$thum.'</a></div>
                    </div>
                </div>';
    }

    return $html; 

    
}

function load_comment_msg(){
    $paged = $_POST['com_msg_paged'];
    $per_page = 9;

    $comments = get_msg_reply_list($user_id = null ,$paged, $per_page);

    $arr = array(
        'status' => 1,
        'html' => $comments,
        'paged' => $paged,
    );

    wp_send_json($arr);
}
add_action('wp_ajax_load_comment_msg', 'load_comment_msg');

// 评论添加消息计数
add_action('wp_insert_comment', 'notify_comment_to_user', 10, 2);
function notify_comment_to_user($comment_ID, $comment_object) {
    $comment = get_comment($comment_ID);
    if (!$comment || $comment->comment_approved != 1) return;

    $post = get_post($comment->comment_post_ID);
    if (!$post) return;

    $mentioned_user_ids = []; // 要通知的用户ID

    // 是回复别人的评论
    if ($comment->comment_parent) {
        $parent = get_comment($comment->comment_parent);
        if ($parent && (int)$parent->user_id !== (int)$comment->user_id) {
            $mentioned_user_ids[] = (int)$parent->user_id;
        }
    }

    // 是评论别人的文章
    if ((int)$post->post_author !== (int)$comment->user_id) {
        $mentioned_user_ids[] = (int)$post->post_author;
    }

    $mentioned_user_ids = array_unique(array_filter($mentioned_user_ids));

    foreach ($mentioned_user_ids as $uid) {
        $meta_key = 'unread_msg_comment_ids';
        $existing = get_user_meta($uid, $meta_key, true);
        $existing = is_array($existing) ? $existing : [];

        if (!in_array($comment_ID, $existing)) {
            $existing[] = $comment_ID;
            update_user_meta($uid, $meta_key, $existing);
        }
    }
}

// 获取未读消息
function get_unread_comment_msg_count($user_id = null) {
    if ( is_null($user_id) ) {
        $user_id = get_current_user_id();
    }

    if ( !$user_id ) {
        return 0;
    }

    $unread_ids = get_user_meta( $user_id, 'unread_msg_comment_ids', true );
    if ( !is_array($unread_ids) ) {
        return 0;
    }

    return count($unread_ids);
}

// 清除评论消息计数
function mark_all_comment_msg_unread($user_id) {
    delete_user_meta($user_id, 'unread_msg_comment_ids');
}

add_action('wp_ajax_upadte_comment_msg_read', 'upadte_comment_msg_read');
//add_action('wp_ajax_nopriv_upadte_comment_msg_read', 'upadte_comment_msg_read');
function upadte_comment_msg_read(){
    if (!is_user_logged_in()) {
        wp_send_json_error(['status' => 0, 'msg' => '请先登录']);
    }
    check_ajax_referer('ppo_msg_action', 'nonce');

    $user_id = get_current_user_id();
    $res = delete_user_meta($user_id, 'unread_msg_comment_ids');
    wp_send_json(['status' => 1]);
}

// 添加消息
function ppo_msg_add($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $defaults = [
        'receive_user' => 0,
        'send_id' => 0,
        'type' => '',
        'title' => '',
        'content' => '',
        'related_id' => null,
        'extra' => null,
        'other' => null,
        'status' => 'unread',
        'create_time' => current_time('mysql'),
    ];

    $data = wp_parse_args($data, $defaults);

    $inserted = $wpdb->insert(
        $table_name,
        [
            'receive_user' => intval($data['receive_user']),
            'send_id' => intval($data['send_id']),
            'type' => sanitize_text_field($data['type']),
            'title' => sanitize_text_field($data['title']),
            'content' => wp_kses_post($data['content']),
            'related_id' => is_null($data['related_id']) ? null : intval($data['related_id']),
            'extra' => is_null($data['extra']) ? null : wp_json_encode($data['extra']),
            'other' => $data['other'],
            'status' => sanitize_text_field($data['status']),
            'create_time' => $data['create_time'],
        ],
        [
            '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
        ]
    );

    return $inserted ? $wpdb->insert_id : false;
}

// 删除点赞评论的消息（取消评论点赞时调用）
function ppo_msg_delete_like_comment($send_id, $receive_user, $comment_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $deleted = $wpdb->delete(
        $table_name,
        [
            'send_id' => intval($send_id),
            'receive_user' => intval($receive_user),
            'type' => 'comment_like',   // 点赞评论类型
            'related_id' => intval($comment_id),
        ],
        [
            '%d', '%d', '%s', '%d'
        ]
    );

    return $deleted !== false;
}

// 删除收藏文章的消息
function ppo_msg_delete_like_post($send_id, $receive_user, $post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $deleted = $wpdb->delete(
        $table_name,
        [
            'send_id' => intval($send_id),
            'receive_user' => intval($receive_user),
            'type' => 'post_like',   // 点赞文章类型
            'related_id' => intval($post_id),
        ],
        [
            '%d', '%d', '%s', '%d'
        ]
    );

    return $deleted !== false;
}

// 删除收藏文章的消息
function ppo_msg_delete_collect_post($send_id, $receive_user, $post_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $deleted = $wpdb->delete(
        $table_name,
        [
            'send_id' => intval($send_id),
            'receive_user' => intval($receive_user),
            'type' => 'post_collect',   // 点赞文章类型
            'related_id' => intval($post_id),
        ],
        [
            '%d', '%d', '%s', '%d'
        ]
    );

    return $deleted !== false;
}

/**
 * 获取点赞与收藏类消息（comment_like, post_like, post_collect），支持分页
 *
 * @param int $user_id 当前用户ID
 * @param int $paged 当前页码，默认1
 * @param int $per_page 每页条数，默认10
 * @return array 返回数组包括：messages（消息列表），total（总数），pages（总页数）
 */
function ppo_msg_get_likes_and_favorites($user_id, $paged = 1, $per_page = 10) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $offset = ($paged - 1) * $per_page;

    // 限定的消息类型
    $types = ['comment_like', 'post_like', 'post_collect'];
    $placeholders = implode(',', array_fill(0, count($types), '%s'));

    // 查询总数
    $sql_total = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name
         WHERE receive_user = %d AND type IN ($placeholders)",
        array_merge([$user_id], $types)
    );
    $total = (int) $wpdb->get_var($sql_total);
    $pages = ceil($total / $per_page);

    // 查询分页数据
    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name
         WHERE receive_user = %d AND type IN ($placeholders)
         ORDER BY create_time DESC
         LIMIT %d OFFSET %d",
        array_merge([$user_id], $types, [$per_page, $offset])
    );
    $messages = $wpdb->get_results($sql);

    return [
        'messages' => $messages,
        'total' => $total,
        'pages' => $pages,
        'current_page' => $paged,
    ];
}

function get_msg_like_list($paged, $per_page){
    $user_id = get_current_user_id();
    $html = '';
    $list = ppo_msg_get_likes_and_favorites($user_id, $paged, $per_page);
    foreach($list['messages'] as $item){
        $type = $item->type;
        $post_id = $item->related_id;
        $msg_title = $item->title;
        $send = $item->send_id;
        $name = get_user_by('id', $send )->display_name;
        $url = get_author_posts_url( $send );
        $date = $item->create_time;
        $read_icon = $item->status == 'unread' ? '<span class="unread-bage"></span>' : '';

        if($type == 'comment_like') {
            $c = get_comment($post_id);
            $post_id = $c->comment_post_ID;
        }

        $post = get_post($post_id);
        $is_moment = $post && $post->post_type === 'moment';

        if($is_moment){
            $post_title = $post->post_title;
            $post_content = wp_strip_all_tags($post->post_content);
            if(empty($post_title)){
                $display_title = mb_substr($post_content, 0, 30, 'utf-8');
                if(mb_strlen($post_content, 'utf-8') > 30) $display_title .= '...';
            } else {
                $display_title = $post_title;
            }
            $thum_html = '<div class="thum moment-thumb"><a href="'.get_permalink($post_id).'" target="_blank"><i class="ri-donut-chart-line"></i></a></div>';
        } else {
            $display_title = get_the_title($post_id);
            $thum_html = '<div class="thum"><a href="'.get_permalink($post_id).'" target="_blank"><img class="lazy" data-src="'.get_ppo_thum($post_id, 'large','random').'"></a></div>';
        }

        $html .= '<div class="msg-like-item pix-dashboard-message-item pix-dashboard-message-like-item">
                    <div class="like-inner pix-dashboard-message-inner">
                        <div class="like-avatar pix-dashboard-message-avatar">
                            <div class="like-user">
                                <a href="'.$url.'" target="_blank">
                                    <img src="'.get_u_avatar($send,'url').'" alt="头像">
                                    '.$read_icon.'
                                </a>
                            </div>
                        </div>
                            <a class="like-right pix-dashboard-message-content" href="'.get_permalink($post_id).'" target="_blank">
                            <div class="like-info pix-dashboard-message-title"><span>'.$name.'</span>'.$msg_title.'</div>
                            <div class="like-content pix-dashboard-message-excerpt">'.$display_title.'</div>
                            <div class="footer pix-dashboard-message-meta">
                                <span class="date">'.date('Y-m-d H:i', strtotime($date)).'</span>

                            </div>
                            </a>
                        '.$thum_html.'
                    </div>
                </div>';
    }

    return $html;
}

// 加载喜欢收藏
function load_like_msg(){
    $paged = $_POST['com_msg_paged'];
    $per_page = 9;

    $likes = get_msg_like_list($paged, $per_page);

    $arr = array(
        'status' => 1,
        'html' => $likes,
        'paged' => $paged,
    );

    wp_send_json($arr);
}
add_action('wp_ajax_load_like_msg', 'load_like_msg');

/**
 * 获取指定用户的点赞和收藏类未读消息数量（comment_like, post_like, post_collect）
 *
 * @param int $user_id 用户ID
 * @return int 未读消息数量
 */
function get_unread_like_msg_count($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $types = ['comment_like', 'post_like', 'post_collect'];
    $placeholders = implode(',', array_fill(0, count($types), '%s'));

    $sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name
         WHERE receive_user = %d AND status = %s AND type IN ($placeholders)",
        array_merge([$user_id, 'unread'], $types)
    );

    return (int) $wpdb->get_var($sql);
}

/**
 * 将点赞与收藏类消息（comment_like, post_like, post_collect）标记为已读
 *
 * @param int $user_id 用户ID
 * @return int 成功更新的消息条数
 */
function mark_like_collect_as_read($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';

    $types = ['comment_like', 'post_like', 'post_collect'];
    $placeholders = implode(',', array_fill(0, count($types), '%s'));

    $sql = $wpdb->prepare(
        "UPDATE $table_name
         SET status = 'read'
         WHERE receive_user = %d AND status = %s AND type IN ($placeholders)",
        array_merge([$user_id, 'unread'], $types)
    );

    $updated = $wpdb->query($sql);
    return $updated;
}

add_action('wp_ajax_upadte_like_msg_read', 'upadte_like_msg_read');
//add_action('wp_ajax_nopriv_upadte_like_msg_read', 'upadte_like_msg_read');
function upadte_like_msg_read(){
    if (!is_user_logged_in()) {
        wp_send_json_error(['status' => 0, 'msg' => '请先登录']);
    }
    check_ajax_referer('ppo_msg_action', 'nonce');

    $user_id = get_current_user_id();
    $count = mark_like_collect_as_read($user_id);
    wp_send_json(['status' => 1, 'count' => $count]);
}

// 系统消息
/**
 * 获取所有类型的消息列表（系统消息、会员消息、活动消息的集合）
 *
 * @param int $page 页码，默认为1
 * @param int $per_page 每页数量，默认为10
 * @return array 包含消息数据和分页信息的数组
 */
function get_all_system_messages($user_id = 0,$paged = 1, $per_page = 10) {
    global $wpdb;

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user_id_str = (string) $user_id;
    $user_vip = get_user_meta($user_id, 'ppo_vip', true); // 如 vip1，非 VIP 为 ''

    $offset = ($paged - 1) * $per_page;
    $table = $wpdb->prefix . 'ppo_msg';

    // 构造 WHERE 条件
    $where_parts = [];

    // 所有用户都能看
    $where_parts[] = "info_meta = 'all_user'";

    // 所有 VIP 能看（VIP 等级不为空）
    if (!empty($user_vip)) {
        $where_parts[] = "info_meta = 'all_vip'";
        $where_parts[] = $wpdb->prepare("info_meta = %s", $user_vip);
    }

    // 指定用户（JSON 字符串包含 user_id，如 ["1","15"]）
    //$json_user_id = '"' . $user_id_str . '"'; // 正确格式：带双引号的字符串
    $where_parts[] = $wpdb->prepare("info_meta = 'chose_user' AND JSON_CONTAINS(other, %s)", (int)$user_id);

    $where_sql = implode(' OR ', $where_parts);

    // 查询数据
    $messages = $wpdb->get_results("
        SELECT * FROM $table
        WHERE type = 'system_msg' AND ($where_sql)
        ORDER BY create_time DESC
        LIMIT $per_page OFFSET $offset
    ");

    // 查询总数
    $total = $wpdb->get_var("
        SELECT COUNT(*) FROM $table
        WHERE type = 'system_msg' AND ($where_sql)
    ");
    
    // 返回结果
    return array(
        'messages' => $messages,
        'current_page' => $paged,
        'per_page' => $per_page,
        'total' => (int) $total,
        'total_pages' => ceil($total / $per_page)
    );
}

// 展示消息列表样式
function all_system_msg_list($paged){
    $html = '';
    $all_messages = get_all_system_messages('',$paged, 9);

    if (!empty($all_messages['messages'])) {

        foreach ($all_messages['messages'] as $message) {
            $user_id = get_current_user_id();
            $type = $message->type;
            $title = $message->title;
            $ID = $message->ID;
            $timestamp = strtotime($message->create_time);
            //$content = $message->content;
            $extra = maybe_unserialize($message->extra);
            $extra = is_array($extra) ? $extra : array();
            $fallback_logo = THEME_URL . '/img/bell.png';
            $logo = !empty($extra['icon']) ? $extra['icon'] : $fallback_logo;
            $is_read = is_system_msg_readed($ID, $user_id);
            $read_icon = !$is_read ? '<span class="unread-bage"></span>' : '';
            $html.= '<div class="system-msg-item pix-dashboard-message-item pix-dashboard-message-system-item" data-id="'.$ID.'">
                        <div class="system-msg-inner pix-dashboard-message-inner">
                            <div class="icon pix-dashboard-message-avatar"><img class="lazy" data-src="'.esc_url($logo).'" onerror="this.onerror=null;this.src=\''.esc_url($fallback_logo).'\';">'.$read_icon.'</div>
                            <div class="right-info pix-dashboard-message-content">
                                <div class="name pix-dashboard-message-title">'.$title.'</div>
                                <div class="meta pix-dashboard-message-meta"><time><i class="ri-timer-2-line"></i>'.date('Y年m月d日', $timestamp).'</time><span><i class="ri-notification-3-line"></i>'.esc_html($extra['type'] ?? '系统消息').'</span></div>
                            </div>
                        </div>
                    </div>';
           
        }

        
    } 
    
    return $html;

}

/**
 * 根据ID获取系统消息详情
 *
 * @param int $message_id 消息ID
 * @return object|false 消息对象或false（如果未找到）
 */
function get_notification_by_id($message_id) {
    // 验证消息ID是否为正整数
    $message_id = absint($message_id);
    if (empty($message_id)) {
        return false;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ppo_msg';
    
    // 查询消息
    $query = "SELECT * FROM $table_name WHERE ID = %d LIMIT 1";
    $message = $wpdb->get_row(
        $wpdb->prepare($query, $message_id)
    );
    
    return $message;
}

// ajax系统消息弹窗和已读更新
function system_msg_detail(){
    $user_id = get_current_user_id();
    $id = isset($_POST['id']) ? $_POST['id'] : false;
    if(!$id) return;
    $msg = get_notification_by_id($id);
    if(!$msg) {
        wp_send_json([
            'status' => 0,
            'title' => '系统消息',
            'content' => '',
            'meta' => ''
        ]);
    }
    $title = $msg->title;
    $content = $msg->content;
    $extra = maybe_unserialize($msg->extra);
    $extra = is_array($extra) ? $extra : array();
    $time = strtotime($msg->create_time);
    $time = get_gmt_from_date(date('Y-m-d G:i:s', $time));
    $type = !empty($extra['type']) ? $extra['type'] : '系统消息';
    
    // 更新用户已读到数据库
    $read_res = mark_system_msg_as_read($id, $user_id);

    //记录用户已读系统消息到meta
    mark_system_msg_as_read_meta($user_id, $id);

    $meta = '<time class="timeago" datetime="'.$time.'">'.$time.'</time>
             <span><i class="ri-notification-3-line"></i>'.$type.'</span>';

    wp_send_json([
        'status' => 1,
        'title' => $title,
        'content' => $content,
        'meta' => $meta 
    ]);
}
add_action('wp_ajax_system_msg_detail', 'system_msg_detail');

// 判断系统消息用户是否已读 
function is_system_msg_readed($msg_id, $user_id) {
    global $wpdb;

    $table = $wpdb->prefix . 'ppo_msg';
    $msg = $wpdb->get_row(
        $wpdb->prepare("SELECT extra FROM $table WHERE ID = %d", $msg_id)
    );

    if (!$msg) return false;

    $extra = maybe_unserialize($msg->extra);
    if (!is_array($extra)) return false;

    $readed = isset($extra['user_readed']) ? json_decode($extra['user_readed'], true) : [];

    return in_array($user_id, $readed);
}

// 更新用户系统消息为已读状态 写入每条系统消息
function mark_system_msg_as_read($msg_id, $user_id) {
    global $wpdb;

    $table = $wpdb->prefix . 'ppo_msg';
    $msg = $wpdb->get_row(
        $wpdb->prepare("SELECT ID, extra FROM $table WHERE ID = %d", $msg_id)
    );

    if (!$msg) return false;

    // 反序列化 extra
    $extra = maybe_unserialize($msg->extra);
    if (!is_array($extra)) $extra = [];

    // 获取 user_readed 数组
    $readed = isset($extra['user_readed']) ? json_decode($extra['user_readed'], true) : [];
    
    // 防止重复加入
    if (!in_array($user_id, $readed)) {
        $readed[] = $user_id;
        $extra['user_readed'] = json_encode($readed); // 以 JSON 格式存储
        $wpdb->update($table, [
            'extra' => maybe_serialize($extra)
        ], ['ID' => $msg_id]);
    }

    return true;
}

// 更新已读消息到用户meta
function mark_system_msg_as_read_meta($user_id, $msg_id) {
    $read_ids = get_user_meta($user_id, 'ppo_read_system_msg_ids', true);
    if (!is_array($read_ids)) {
        $read_ids = [];
    }

    if (!in_array($msg_id, $read_ids)) {
        $read_ids[] = $msg_id;
        $read_ids = array_unique($read_ids);
        update_user_meta($user_id, 'ppo_read_system_msg_ids', $read_ids);
    }
}

// 记录用户已读系统消息最大id  无用
function update_user_last_read_msg_msg($user_id, $msg_id) {
    $last_read = (int) get_user_meta($user_id, 'ppo_system_msg_last_read_id', true);
    if ($msg_id > $last_read) {
        update_user_meta($user_id, 'ppo_system_msg_last_read_id', $msg_id);
    }
}

// 获取未读系统消息数量
function get_unread_system_msg_count($user_id = 0) {
    global $wpdb;

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $table = $wpdb->prefix . 'ppo_msg';
    $user_vip = get_user_meta($user_id, 'ppo_vip', true);
    //$user_id_str = '"' . (string) $user_id . '"';

    $where_parts = [];
    $where_parts[] = "info_meta = 'all_user'";
    if (!empty($user_vip)) {
        $where_parts[] = "info_meta = 'all_vip'";
        $where_parts[] = $wpdb->prepare("info_meta = %s", $user_vip);
    }
    $where_parts[] = $wpdb->prepare("info_meta = 'chose_user' AND JSON_CONTAINS(other, %s)", (int)$user_id);
    $where_sql = implode(' OR ', $where_parts);

    // 获取所有可见消息ID
    $sql = "
        SELECT ID FROM $table
        WHERE type = 'system_msg'
        AND ($where_sql)
    ";
    //$query = $wpdb->prepare($sql);
    $all_ids = $wpdb->get_col($sql);

    // 获取已读ID
    $read_ids = get_user_meta($user_id, 'ppo_read_system_msg_ids', true);
    if (!is_array($read_ids)) {
        $read_ids = [];
    }

    // 差集 = 未读
    $unread_ids = array_diff($all_ids, $read_ids);

    return count($unread_ids);
}

// ajax加载系统消息
function load_system_msg(){
    $paged = $_POST['com_msg_paged'];
    $per_page = 9;

    $comments = all_system_msg_list($paged);

    $arr = array(
        'status' => 1,
        'html' => $comments,
        'paged' => $paged,
    );

    wp_send_json($arr);
}
add_action('wp_ajax_load_system_msg', 'load_system_msg');

// 私信功能 ------------------------------------------------------------------------------------------ //
// 创建会话ksy
function ppo_get_conversation_key($user1, $user2) {
    $ids = [$user1, $user2];
    sort($ids, SORT_STRING); // 字符串排序，确保对称一致
    return $ids[0] . '_' . $ids[1];
}

// 发送私信
function ppo_send_private_message($sender_id, $receiver_id, $message) {
    global $wpdb;

    if (empty($sender_id) || empty($receiver_id) || empty($message)) {
        return false;
    }

    if(is_numeric($sender_id) && function_exists('pix_check_forbidden_words') && pix_check_forbidden_words($message)){
        return false;
    }

    // 可选校验：允许的 bot ID
    if (!is_numeric($sender_id)) {
        $allowed_bots = ['sys_bot', 'moment_bot','pay_bot'];
        if (!in_array($sender_id, $allowed_bots)) {
            return false;
        }
    }

    $table = $wpdb->prefix . 'ppo_private_messages';

    // 检查表是否存在，不存在则创建
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        if (function_exists('ppo_create_private_messages_table')) {
            ppo_create_private_messages_table();
        }
        // 如果表仍然不存在，说明创建失败
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return false;
        }
    }

    $conversation_key = ppo_get_conversation_key($sender_id, $receiver_id);

    return $wpdb->insert($table, [
        'sender_id'         => $sender_id,
        'receiver_id'       => $receiver_id,
        'message'           => wp_kses_post($message),
        'send_time'         => current_time('mysql'),
        'is_read'           => 0,
        'conversation_key'  => $conversation_key,
    ]);
}

function ppo_private_msg_rate_key($scope, $sender_id, $receiver_id = '') {
    $ip = function_exists('get_real_ip') ? get_real_ip() : ($_SERVER['REMOTE_ADDR'] ?? '');
    return 'ppo_pm_rate_' . md5(sanitize_key($scope) . '|' . intval($sender_id) . '|' . sanitize_text_field((string) $receiver_id) . '|' . sanitize_text_field((string) $ip));
}

function ppo_private_msg_rate_check($sender_id, $receiver_id, $message) {
    $minute_limit = max(0, intval(get_op('private_msg_rate_per_minute', 10)));
    $window_limit = max(0, intval(get_op('private_msg_rate_per_5min', 30)));
    $duplicate_window = max(0, intval(get_op('private_msg_duplicate_window', 20)));

    $checks = array();
    if ($minute_limit > 0) {
        $checks[] = array('private_minute', 60, $minute_limit, '发送太频繁了，请稍后再试');
    }
    if ($window_limit > 0) {
        $checks[] = array('private_5min', 300, $window_limit, '私信发送次数过多，请稍后再试');
    }

    foreach ($checks as $check) {
        list($scope, $ttl, $limit, $message_text) = $check;
        $key = ppo_private_msg_rate_key($scope, $sender_id);
        $count = intval(get_transient($key));
        if ($count >= $limit) {
            return new WP_Error('private_msg_rate_limited', $message_text);
        }
    }

    if ($duplicate_window > 0) {
        $normalized = trim(wp_strip_all_tags((string) $message));
        $duplicate_key = ppo_private_msg_rate_key('private_duplicate_' . md5($normalized), $sender_id, $receiver_id);
        if ($normalized !== '' && get_transient($duplicate_key)) {
            return new WP_Error('private_msg_duplicate', '请不要重复发送相同内容');
        }
    }

    return true;
}

function ppo_private_msg_rate_hit($sender_id, $receiver_id, $message) {
    $minute_limit = max(0, intval(get_op('private_msg_rate_per_minute', 10)));
    $window_limit = max(0, intval(get_op('private_msg_rate_per_5min', 30)));
    $duplicate_window = max(0, intval(get_op('private_msg_duplicate_window', 20)));

    if ($minute_limit > 0) {
        $key = ppo_private_msg_rate_key('private_minute', $sender_id);
        set_transient($key, intval(get_transient($key)) + 1, 60);
    }

    if ($window_limit > 0) {
        $key = ppo_private_msg_rate_key('private_5min', $sender_id);
        set_transient($key, intval(get_transient($key)) + 1, 300);
    }

    if ($duplicate_window > 0) {
        $normalized = trim(wp_strip_all_tags((string) $message));
        if ($normalized !== '') {
            $duplicate_key = ppo_private_msg_rate_key('private_duplicate_' . md5($normalized), $sender_id, $receiver_id);
            set_transient($duplicate_key, 1, $duplicate_window);
        }
    }
}

function ppo_private_msg_count_between($sender_id, $receiver_id) {
    global $wpdb;

    $table = $wpdb->prefix . 'ppo_private_messages';
    return intval($wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE sender_id = %d AND receiver_id = %d AND deleted_by_sender = 0",
        $sender_id,
        $receiver_id
    )));
}

function ppo_private_msg_permission($sender_id, $receiver_id) {
    $sender_id = absint($sender_id);
    $receiver_id = absint($receiver_id);

    if (!$sender_id) {
        return array('allowed' => false, 'code' => 'login_required', 'msg' => '请先登录后再发送私信');
    }

    if (!$receiver_id || !get_user_by('id', $receiver_id)) {
        return array('allowed' => false, 'code' => 'invalid_receiver', 'msg' => '接收用户不存在');
    }

    if ($sender_id === $receiver_id) {
        return array('allowed' => false, 'code' => 'self', 'msg' => '不能给自己发送私信');
    }

    if (current_user_can('manage_options')) {
        return array('allowed' => true, 'code' => 'admin', 'msg' => '管理员可发送私信');
    }

    $is_plain_user = function_exists('pix_user_is_plain_user') ? pix_user_is_plain_user($sender_id) : false;
    $level_has_msg = function_exists('ppo_user_level_has_power') ? ppo_user_level_has_power($sender_id, 'msg') : false;
    $private_rule = $is_plain_user ? get_op('normal_user_private_msg_rule', 'follow_once') : 'follow_once';

    if ($is_plain_user && $level_has_msg && $private_rule === 'off') {
        $private_rule = 'follow_once';
    }

    if ($is_plain_user && $private_rule === 'off') {
        return array('allowed' => false, 'code' => 'normal_off', 'msg' => '普通用户私信已关闭');
    }

    if ($is_plain_user && $private_rule === 'open') {
        return array('allowed' => true, 'code' => 'normal_open', 'msg' => '普通用户可直接发送私信');
    }

    if (function_exists('ppo_is_mutual_follow') && ppo_is_mutual_follow($sender_id, $receiver_id)) {
        return array('allowed' => true, 'code' => 'mutual', 'msg' => '互相关注可发送私信');
    }

    if ($is_plain_user && $private_rule === 'mutual') {
        return array('allowed' => false, 'code' => 'mutual_required', 'msg' => '请先互相关注后再发送私信');
    }

    if (function_exists('ppo_is_following') && ppo_is_following($sender_id, $receiver_id)) {
        if ($is_plain_user && $private_rule === 'follow') {
            return array('allowed' => true, 'code' => 'follow', 'msg' => '关注后可持续发送私信');
        }

        if (ppo_private_msg_count_between($sender_id, $receiver_id) < 1) {
            return array('allowed' => true, 'code' => 'opener', 'msg' => '可发送一条开场私信');
        }

        return array('allowed' => false, 'code' => 'opener_used', 'msg' => '对方回关后，才能继续发送私信');
    }

    if ($is_plain_user && $private_rule === 'follow') {
        return array('allowed' => false, 'code' => 'follow_required', 'msg' => '请先关注对方后再发送私信');
    }

    return array('allowed' => false, 'code' => 'follow_required', 'msg' => '请先关注对方，关注后可发送一条开场私信');
}

function ppo_private_msg_compose_notice($message) {
    return '<div class="private-msg-compose-notice pix-dashboard-message-compose-notice">' . esc_html($message) . '</div>';
}

// 获取会话内容
function ppo_get_conversation_messages($user_id, $other_user_id, $limit = 20, $before_id = 0) {
    global $wpdb;

    $table = $wpdb->prefix . 'ppo_private_messages';
    $conversation_key = ppo_get_conversation_key($user_id, $other_user_id);

    $where = "
        conversation_key = %s
        AND (
            (sender_id = %s AND deleted_by_sender = 0)
            OR (receiver_id = %s AND deleted_by_receiver = 0)
        )
    ";

    $params = [$conversation_key, $user_id, $user_id];

    if ($before_id > 0) {
        $where .= " AND id < %d";
        $params[] = $before_id;
    }

    $inner_sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT %d";
    $params[] = $limit;

    $full_sql = "
        SELECT * FROM (
            $inner_sql
        ) AS recent_msgs
        ORDER BY id ASC
    ";

    array_unshift($params, $full_sql); // 将 SQL 模板放到参数最前面

    $prepared_sql = call_user_func_array([$wpdb, 'prepare'], $params);

    return $wpdb->get_results($prepared_sql);
}


// 获取用户的会话列表（最新消息）
function ppo_get_user_conversations($user_id, $limit = 20, $offset = 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_private_messages';

    // 获取每个会话最新消息（见上条回答）
    $subquery = "
        SELECT MAX(id) as max_id
        FROM $table
        WHERE (sender_id = %d OR receiver_id = %d)
          AND (
              (sender_id = %d AND deleted_by_sender = 0)
           OR (receiver_id = %d AND deleted_by_receiver = 0)
          )
        GROUP BY conversation_key
    ";

    $sub_sql = $wpdb->prepare($subquery, $user_id, $user_id, $user_id, $user_id);
    $main_sql = "
        SELECT m.*
        FROM $table m
        INNER JOIN (
            $sub_sql
        ) AS t ON m.id = t.max_id
        ORDER BY m.send_time DESC
        LIMIT %d OFFSET %d
    ";
    $main_sql = $wpdb->prepare($main_sql, $limit, $offset);
    $conversations = $wpdb->get_results($main_sql);

    // 扫描每个会话，查未读数（当前用户是接收方 & 未读）
    foreach ($conversations as &$conv) {
        $conv->unread_count = (int) $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE conversation_key = %s
              AND receiver_id = %d
              AND is_read = 0
              AND deleted_by_receiver = 0
        ", $conv->conversation_key, $user_id));
    }

    return $conversations;
}

// 获取未读消息数
function ppo_get_unread_message_count($user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'ppo_private_messages';

    return (int) $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM $table
        WHERE receiver_id = %d AND is_read = 0 AND deleted_by_receiver = 0
    ", $user_id));
}

// 标记已读
function ppo_mark_conversation_as_read($user_id, $other_user_id) {
    global $wpdb;

    $table = $wpdb->prefix . 'ppo_private_messages';
    $conversation_key = ppo_get_conversation_key($user_id, $other_user_id);

    return $wpdb->update(
        $table,
        ['is_read' => 1],
        [
            'conversation_key' => $conversation_key,
            'receiver_id' => $user_id,
            'is_read' => 0
        ]
    );
}

// 私信和关注按钮
function ppo_user_msg_follow_btn($curauth){
    $user_id = get_current_user_id();
    $is_following = ppo_is_following($user_id, $curauth);
    $is_mutual_follow = ppo_is_mutual_follow($user_id, $curauth);
    $is_followed = $is_following || $is_mutual_follow;
    $permission = ppo_private_msg_permission($user_id, $curauth);

    if($is_following){
        $follow_btn = '<a class="follow-user-btn unfollow user-banner-btn" action="unfollow" data-uid="'.$curauth.'">已关注</a>';
        if($is_mutual_follow){ 
            $follow_btn = '<a class="follow-user-btn unfollow user-banner-btn" action="unfollow" data-uid="'.$curauth.'">互相关注</a>';
        } 
    } else {
        $follow_btn = '<a class="follow-user-btn user-banner-btn" action="follow" data-uid="'.$curauth.'">关注</a>';
    }

    $msg_btn_class = !empty($permission['allowed']) ? 'send-msg-btn user-banner-btn' : 'send-msg-btn user-banner-btn is-disabled';
    $msg_btn_text = '私信';
    if (!empty($permission['allowed']) && !empty($permission['code']) && $permission['code'] === 'opener') {
        $msg_btn_text = '私信一次';
    } elseif (empty($permission['allowed']) && !empty($permission['code']) && $permission['code'] === 'follow_required') {
        $msg_btn_text = '关注后私信';
    }

    $html = '<div class="right">
               '.$follow_btn.'
               <a class="'.$msg_btn_class.'" href="javascript:void(0);" data-uid="'.$curauth.'" data-message="'.esc_attr($permission['msg']).'">'.$msg_btn_text.'</a>
            </div>';

    $modal_footer = !empty($permission['allowed'])
        ? '<div class= "send-msg-box pix-dashboard-message-compose">
                        <textarea class="send-msg-textarea private-msg-textarea pix-dashboard-form-control" rows="3" placeholder="'.esc_attr(!empty($permission['code']) && $permission['code'] === 'opener' ? '可以发送一条开场私信' : '输入消息内容').'"></textarea>
                    </div>

                    <div class="bottom-tool">
                        <div class="left"></div>
                        <div class="right">
                            <button class="pix-dashboard-form-cancel" type="button" data-pix-modal-close="#user-chat-modal">取消</button>
                            <button class="push-private-msg-btn pix-dashboard-form-submit" type="button" data-uid="'.$curauth.'">发送</button>
                        </div>
                    </div>'
        : ppo_private_msg_compose_notice($permission['msg']);

    $modal = '<div id="user-chat-modal" class="pix-modal pix-hs-modal pix-user-chat-modal hidden" role="dialog" tabindex="-1" aria-label="私信">
                <div class="pix-modal-dialog">
                <div class="pix-modal-panel user-chat-modal">
                    <button class="pix-modal-close" type="button" data-pix-modal-close="#user-chat-modal" aria-label="关闭"><i class="ri-close-line"></i></button>
                    <div class="pix-modal-header user-chat-modal-header">
                        <div class="avatar"></div>
                        <div class="user_name">拉取中..</div>
                    </div>
                    <div class="pix-modal-body user-chat-modal-body chat-scroll-body">
                        <div class="private-msg-list-content"></div>
                    </div>
                    <div class="pix-modal-footer user-chat-modal-footer">
                    '.$modal_footer.'
                        
                    </div>
                </div>
                </div>
            </div>';        

    
    return $html.$modal;
}

// 获取聊天对象数据
function get_private_msg_data(){
    $receive_id = isset($_POST['receive_id']) ? $_POST['receive_id'] : false;
    $sender_id = get_current_user_id();

    if(!$receive_id || !$sender_id) return;

    $avatar = get_u_avatar($receive_id,'img');
    $receive_user = get_user_by('id',$receive_id);
    $name = $receive_user ? $receive_user->display_name : '';
    $description = $receive_user && !empty($receive_user->description) ? $receive_user->description : 'TA很懒，什么也没写';
    $badges = function_exists('ppo_user_badges_html') ? ppo_user_badges_html($receive_id, 'chat-modal') : '';
    $user_header = '<div class="user-chat-profile-main"><div class="user-chat-profile-line"><span class="user-chat-profile-name">'.esc_html($name).'</span>'.$badges.'</div><div class="user-chat-profile-desc">'.esc_html($description).'</div></div>';
    $messages = ppo_get_conversation_messages($sender_id, $receive_id,20);

    $first_id = !empty($messages) ? $messages[0]->id : 0;
    $last_id  = !empty($messages) ? end($messages)->id : 0;

    ppo_mark_conversation_as_read($sender_id, $receive_id);

    $chat_box = '';

    if(is_numeric($receive_id)){
        $permission = ppo_private_msg_permission($sender_id, $receive_id);
        $chat_box = !empty($permission['allowed']) ? msg_chat_box($permission) : ppo_private_msg_compose_notice($permission['msg']);
    }

    wp_send_json(array(
       'status' => 1,
        'avatar' => $avatar,
        'name' => $name,
        'user_header' => $user_header,
        'message' => private_msg_html($sender_id, $receive_id,$messages),
        'chat_box' => $chat_box,
        'before_id'   => $first_id,
    ));

}
add_action('wp_ajax_get_private_msg_data', 'get_private_msg_data');

// 发送窗口
function msg_chat_box($permission = array()){
    $placeholder = (!empty($permission['code']) && $permission['code'] === 'opener') ? '可以发送一条开场私信' : '输入消息内容';
    $html = '<div class="send-msg-box pix-dashboard-message-compose">
                <textarea class="send-msg-textarea private-msg-textarea pix-dashboard-form-control" rows="3" placeholder="'.esc_attr($placeholder).'"></textarea>
            </div>
            <div class="chat-footer-tool pix-dashboard-message-compose-actions">
                <button class="push-private-msg-btn pix-dashboard-form-submit" type="button">发送</button>
            </div>';
    return $html;
}

// 聊天内容html
function private_msg_html($sender_id, $receive_id,$messages){
    //$user_id = get_current_user_id();
    $html  = '';
    //$messages = ppo_get_conversation_messages($sender_id, $receive_id,20);
    if (empty($messages)) {
        $html =  '<div class="ppo-chat-empty pix-dashboard-empty-state pix-dashboard-message-empty">暂无对话内容</div>';
    }
    $prev_time = null;
    //$time_html = '';

    foreach ($messages as $msg) {
        //$sender_id = $msg->sender_id;
        //$receiver_id = $msg->receiver_id;
        $sender_info = ppo_get_user_info($msg->sender_id);
        $message = $msg->message;
        $time = strtotime($msg->send_time);

        $is_me = ($msg->sender_id == $sender_id);
        $is_me_class = $is_me ? 'me' : 'other';

        $avatar = $sender_info['avatar'];//$is_me ? get_u_avatar($sender_id,'img') : get_u_avatar($receiver_id,'img');

        // 显示时间判断
        if (!$prev_time || ($time - $prev_time) > 300) {
            $html .= '<div class="ppo-chat-timestamp"><time class="timeago" itemprop="datePublished" datetime="'.date('Y-m-d G:i:s', $time).'">' . date('Y-m-d G:i:s', $time) . '</time></div>';
            $prev_time = $time;
        }

        $bot = '';
        $is_bot = !is_numeric($msg->sender_id);
        if($is_bot){
            $bot = 'msg-bot';
        };

        $display_message = $is_bot ? $message : wpautop($message);

        $html .= '<div class="ppo-chat-item pix-dashboard-message-chat-item '.$is_me_class.' '.$bot.'">
                    <div class="chat-avatar pix-dashboard-message-avatar">'.$avatar.'</div>
                    <div class="pix-dashboard-message-chat-bubble '.$is_me_class.'_content">'.$display_message.'</div>
                </div>';

    }

    return $time_html.$html;
}

function ppo_get_user_info($user_id) {
    if (is_numeric($user_id)) {
        $user = get_userdata($user_id);
        if ($user) {
            return [
                'name'   => $user->display_name,
                'avatar' => get_u_avatar($user_id,'img'),
            ];
        }
    }

    // Bot 信息（可扩展）
    $bot_config = !empty(get_op('notice_bot')) ? get_op('notice_bot') : [];
    $sys_data = $bot_config['sys_bot'] ?? '';
    $moment_data = $bot_config['moment_bot'] ?? '';
    $pay_data = $bot_config['pay_bot'] ?? '';
    $avatar = THEME_URL.'/img/ava.png';
    $avatar = ppo_avatar_html($avatar);

    $bots = [
        'sys_bot' => [
            'name'   => $sys_data['name'] ?? '系统小助手',
            'avatar' => isset($sys_data['avatar']) ? ppo_avatar_html($sys_data['avatar']['url']) : $avatar,
        ],
        'moment_bot' => [
            'name'   => $moment_data['name'] ?? '片刻小助手',
            'avatar' => isset($moment_data['avatar']) ? ppo_avatar_html($moment_data['avatar']['url']) : $avatar,
        ],
        'pay_bot' => [
            'name'   => $pay_data['name'] ?? '支付小助手',
            'avatar' => isset($pay_data['avatar']) ? ppo_avatar_html($pay_data['avatar']['url']) : $avatar,
        ],
    ];

    return $bots[$user_id] ?? [
        'name'   => $user_id,
        'avatar' => $avatar,
    ];
}

// 头像转换
function ppo_avatar_html($url){
    return !empty($url) ? '<img data-src="'.$url.'" class="user-avatar lazy"/>' : '';
}

// 发送私信
function send_private_msg(){
    $sender_id = get_current_user_id();
    $receive_id = isset($_POST['receive_id'])? absint($_POST['receive_id']) : 0;
    $msg = isset($_POST['msg'])? trim(wp_unslash($_POST['msg'])) : '';

    if (!check_ajax_referer('ppo_msg_action', 'nonce', false)) {
        wp_send_json(array('status' => 0, 'msg' => '请求已过期，请刷新页面后重试'));
    }

    if(!$sender_id ||!$receive_id ||!$msg) {
        wp_send_json(array('status' => 0, 'msg' => '参数错误'));
    }

    $permission = ppo_private_msg_permission($sender_id, $receive_id);
    if (empty($permission['allowed'])) {
        wp_send_json(array('status' => 0, 'msg' => $permission['msg']));
    }

    $rate_check = ppo_private_msg_rate_check($sender_id, $receive_id, $msg);
    if (is_wp_error($rate_check)) {
        wp_send_json(array(
            'status' => 0,
            'msg'    => $rate_check->get_error_message(),
        ));
    }

    if(function_exists('pix_check_forbidden_words')){
        $forbidden_word = pix_check_forbidden_words($msg);
        if($forbidden_word){
            wp_send_json(array(
                'status' => 0,
                'msg' => pix_forbidden_words_message($forbidden_word),
            ));
        }
    }

    $res = ppo_send_private_message($sender_id, $receive_id, $msg);

    if($res){
        ppo_private_msg_rate_hit($sender_id, $receive_id, $msg);
        do_action('ppo_send_msg', $sender_id, '发送私信',$receive_id);
        $avatar = get_u_avatar($sender_id,'img');
        $html = '<div class="ppo-chat-item pix-dashboard-message-chat-item me pix-animation-slide-bottom-small">
                    <div class="chat-avatar pix-dashboard-message-avatar">'.$avatar.'</div>
                    <div class="pix-dashboard-message-chat-bubble me_content">'.wpautop(esc_html($msg)).'</div>
                </div>';

        wp_send_json(array(
           'status' => 1,
           'msg' => $html
        ));
    }
    wp_send_json(array(
        'status' => 0,
        'msg' => '发送失败，请稍后重试',
    ));
}
add_action('wp_ajax_send_private_msg', 'send_private_msg');

// ajax聊天内容加载
add_action('wp_ajax_ppo_load_previous_messages', 'ppo_load_previous_messages');
//add_action('wp_ajax_nopriv_ppo_load_previous_messages', 'ppo_load_previous_messages');

function ppo_load_previous_messages() {
    global $wpdb;

    $sender_id  = get_current_user_id();  // 当前用户
    $receive_id = sanitize_text_field($_POST['receive_id']); // 可以是数字或 bot 字符串
    $before_id  = isset($_POST['before_id']) ? absint($_POST['before_id']) : 0;
    $limit      = 20; // 固定每页加载数量（也可开放成参数）

    if (!$sender_id || !$receive_id) {
        wp_send_json_error(['msg' => '参数错误']);
    }

    $table = $wpdb->prefix . 'ppo_private_messages';

    // 构建 WHERE 条件
    $where = "
        ((sender_id = %d AND receiver_id = %s) OR
         (sender_id = %s AND receiver_id = %d))
    ";
    $params = [$sender_id, $receive_id, $receive_id, $sender_id];

    if ($before_id > 0) {
        $where .= " AND id < %d";
        $params[] = $before_id;
    }

    $where .= " ORDER BY id DESC LIMIT %d";
    $params[] = $limit;

    // 构建 SQL
    $sql = $wpdb->prepare("
  SELECT * FROM $table
  WHERE ((sender_id = %s AND receiver_id = %s) OR (sender_id = %s AND receiver_id = %s))
  " . ($before_id ? "AND id < %d" : "") . "
  ORDER BY id DESC
  LIMIT %d
", $sender_id, $receive_id, $receive_id, $sender_id, $before_id ? $before_id : null, $limit);

    // 查询结果
    $messages = $wpdb->get_results($sql);

    // 倒序输出（从旧到新）
    $messages = array_reverse($messages);

    // ✅ 可选：格式化时间字段
    foreach ($messages as $msg) {

        $time = date('Y-m-d H:i:s', strtotime($msg->send_time));
        $show_time = false;

        if (!$prev_time || ($time - $prev_time) > 300) {
            $show_time = true;
            $prev_time = $time;
        }

        $sender_info = ppo_get_user_info($msg->sender_id); // 获取昵称/头像
        $data[] = [
            'id'          => $msg->id,
            'sender_id'   => $msg->sender_id,
            'receiver_id' => $msg->receiver_id,
            'message'     => esc_html($msg->message),
            'send_time'   => date('Y-m-d H:i:s', strtotime($msg->send_time)),
            'avatar'      => $sender_info['avatar'],
            'name'        => $sender_info['name'],
            'show_time' => $show_time,
        ];
    }

    wp_send_json_success($data);
}

// 聊天用户列表
function ppo_chat_user_list_html($user_id){
    $conversations = ppo_get_user_conversations($user_id, 20);
    $html = '';

    foreach ($conversations as $key => $chat) {
        $other_user_id = ($chat->sender_id == $user_id) ? $chat->receiver_id : $chat->sender_id;
        $other_info = ppo_get_user_info($other_user_id);
        $last_msg = wp_trim_words(strip_tags($chat->message), 15, '...');
        $last_msg_time = $chat->send_time; // 假设时间也是直接存储的
        $avatar = $other_info['avatar'];
        $name = $other_info['name'];
        $unread = $chat->unread_count > 0 ? '<span class="chat-unread-count">'.$chat->unread_count.'</span>' : '';

        $active = ($key == 0)? 'active' : '';

        $html.= '<div class="chat-user-list-item pix-dashboard-message-conversation-item '.$active.'" data-uid="'.$other_user_id.'">
                    <div class="chat-user-list-avatar pix-dashboard-message-avatar">
                        '.$avatar.'
                    </div>
                    <div class="chat-user-info pix-dashboard-message-content">
                    <div class="name pix-dashboard-message-title"><span>'.$name.'</span>
                        <div class="right pix-dashboard-message-meta">
                            <time class="timeago" itemprop="datePublished" datetime="'.$last_msg_time.'"></time>
                            '.$unread.'
                        </div>
                    </div>
                    <div class="content pix-dashboard-message-excerpt">'.$last_msg.'</div>
                    </div>
                    </div>';
    }

    return $html;
}

// 获取


