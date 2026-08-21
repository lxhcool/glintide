<?php
if (!class_exists('Classic_mod')) {
class Classic_mod {

// 顶部导航条
public static function classic_header(){
    $html = '';
    $nav_base = get_cu('classic_nav_base_tab', array());
    $nav_align = isset($nav_base['classic_nav_align']) ? $nav_base['classic_nav_align'] : 'left';
    $nav_align = in_array($nav_align, array('left', 'center'), true) ? $nav_align : 'left';

    $html = '<div class="classic-header classic-nav-' . esc_attr($nav_align) . '">
                <div class="classic-header-bg" data-pix-sticky="top" data-pix-sticky-start="0">
                    <div class="header-bg-inner">
                        <div class="logo-area">'.self::logo().'</div>
                        <div class="center-area">'.self::nav().'</div>
                        <div class="user-area">'.self::top_tools().'</div>
                    </div>
                </div>
            </div>';
    return $html;        
}

// 导航区域
public static function nav(){
    $html = '';
    $menu_id = get_cu('classic_nav_id');
    $data = get_cu('classic_nav');
    if(isset($menu_id)){
        $primary_nav =  wp_nav_menu( array(
            'menu' => $menu_id,
            'menu_id'        => 'classic_menu',
            'echo'           => false,
        ) );
    
        $html = $primary_nav;               
    } else {
        $html = '请前往后台设置顶部主导航';
    }

    if(is_array($data)){
        $menu_type = $data['classic_effects_type'];
        $menu_e = $data['effects'];
    } else {
        $menu_type = 'line';
        $menu_e = 'normal';
    }
    return '<div class="classic-nav '.$menu_type.' '.$menu_e.'">'.$html.'</div>';
}

// logo
public static function logo(){
    $html = '';
    $data = get_cu('classic_logo_tab');
    if(is_array($data)){
        $type = $data['classic_logo_type'];
        $img = $data['classic_logo'];
        $title = $data['classic_title'];
        $des = $data['classic_des'];
    }

    $title = isset($title) && $title ? $title : pix_global_logo_text();
    $des = isset($des) ? '<span class="des">'.$des.'</span>' : '';
    $type = isset($type) ? $type : 'text';
    if($type == 'text'){
        $html = '<div class="classic-logo-box text-type">
                    <a href="'.home_url().'">
                        <h4>'.$title.'</h4>
                        '.$des.'
                    </a>    
                </div>';
    } else {
        $img = !empty($img) ? $img : pix_global_logo_url('dark');
        $html = '<div class="classic-logo-box img-type">
                    <a href="'.home_url().'"><img src="'.$img.'"></a>
                </div>';
    }

    return '<div class="classic-logo">'.$html.'</div>';
}

// 顶部封面
public static function banner_info(){

    $html = '';
    $content_type = get_cu('cls_banner_content', 'ava');

    if ($content_type === 'text') {
        $content = get_cu('opt-wp-editor-2', '');
        return $content ? '<div class="cls-banner-custom-content">'.wp_kses_post($content).'</div>' : '';
    }

    $is_login = is_user_logged_in();
    if ($is_login) {
        $user_id = get_current_user_id();
    } else {
        $admin_user = get_user_by('email', get_option('admin_email'));
        $user_id = $admin_user ? (int) $admin_user->ID : 1;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return '';
    }

    $ava = get_u_avatar($user_id, 'url');
    $name = $user->display_name ? $user->display_name : get_bloginfo('name');
    $des = !empty($user->description) ? $user->description : 'TA很懒，什么也没写';
    $badges = ($is_login && function_exists('ppo_user_badges_html')) ? ppo_user_badges_html($user_id, 'banner') : '';

    $des_html = '<div class="des max-w-72 truncate text-sm leading-snug text-white/75">'.esc_html($des).'</div>';

    $html = '<div class="cls-banner-info pix-home-banner-profile absolute right-10 bottom-5 z-10 flex items-center gap-4">
                <div class="info flex min-w-max flex-col items-end">
                    <div class="profile-line">
                        <div class="name max-w-64 truncate text-base font-semibold leading-tight text-white">'.esc_html($name).'</div>
                        '.$badges.'
                    </div>
                    '.$des_html.'
                </div>
                <div class="ava flex h-16 w-16 shrink-0 overflow-hidden rounded-[10px]"><img class="lazy h-full w-full object-cover" data-src="'.esc_url($ava).'"></div>
            </div>';
            
    return $html;        
}

// 顶部封面图片
public static function banner_image(){
    $type = get_cu('cls_banner_type', 'upload');
    $images = array();

    if ($type === 'link') {
        $links = get_cu('cls_banner_link', '');
        $links = preg_split('/\r\n|\r|\n/', (string) $links);
        foreach ($links as $link) {
            $link = trim($link);
            if ($link) {
                $images[] = $link;
            }
        }
    } else {
        $upload = get_cu('cls_banner_upload', '');
        if (is_array($upload)) {
            $images = $upload;
        } elseif (is_string($upload) && $upload !== '') {
            $images = array_filter(array_map('trim', explode(',', $upload)));
        }
    }

    $images = array_values(array_filter($images));
    if (empty($images)) {
        return THEME_DEFAULT_URL;
    }

    $image = $images[array_rand($images)];
    if (is_numeric($image)) {
        $image_url = wp_get_attachment_image_url((int) $image, 'full');
        return $image_url ? $image_url : THEME_DEFAULT_URL;
    }

    return $image;
}

// 顶部工具
public static function top_tools(){
    $html = '';
    $notice = '';
    $user_id = get_current_user_id();
    if(is_user_logged_in()){
       $notice = self::top_msg_drop($user_id); 
    }
    $html = '<div class="tools-box">
                <button type="button" class="cls-search item pix-search-trigger" aria-label="搜索"><i class="ri-search-line"></i></button>
                '.$notice.'
                '.self::top_publish_drop($user_id).'
                <div class="cls-user-pannel item">'.self::user_pannel().'</div>
            </div>';

    return $html;
}

public static function top_msg_drop($user_id){
    $html = '';
    $nav = '';
    $moment_count = get_unread_comment_msg_count($user_id);
    $like_count = get_unread_like_msg_count($user_id);
    $system_count = get_unread_system_msg_count($user_id);
    $chat_count = ppo_get_unread_message_count($user_id);

    $total_count = $moment_count + $like_count + $system_count + $chat_count;
    $unread_bage = $total_count > 0? '<span class="msg-unread-dot"></span>' : '';
    $arr = array(
        array('title' => '我的消息', 'icon' => 'ri-chat-smile-2-line', 'tab' => 'whisper','count'=> $chat_count > 0 ? '<span class="msg-badge">'.$chat_count.'</span>' : ''),
        array('title' => '回复我的', 'icon' => 'ri-message-2-line', 'tab' => 'reply','count'=> $moment_count > 0 ? '<span class="msg-badge">'.$moment_count.'</span>' : ''),
        array('title' => '点赞收藏', 'icon' => 'ri-thumb-up-line', 'tab' => 'like','count' => $like_count > 0 ? '<span class="msg-badge">'.$like_count.'</span>' : ''),
        array('title' => '系统通知', 'icon' => 'ri-settings-4-line', 'tab' => 'system','count' => $system_count > 0 ? '<span class="msg-badge">'.$system_count.'</span>' : ''),
    );

    foreach($arr as $key => $item){
        $nav .= '<li><a href="'.home_url('/msg/'.$item['tab'].'').'"  class="msg-drop-item">';
        $nav .= '<span class="nav-title">'. $item['title'] .'</span>'.$item['count'].'</a></li>'; 
    }

    $html = '<div class="cls-notify item">
                <a href="'.home_url('/msg').'"><i class="ri-notification-2-line"></i>'.$unread_bage.'</a>
                <div class="msg-drop-pannel pix-dropdown-panel" data-pix-dropdown="hover">
                    <ul>
                        '.$nav.'
                    </ul>
                 </div>
            </div>'; 

    return $html;
}

public static function top_publish_drop($user_id){
    $html = '';
    $cr_moment  = '';
    $moment_label = function_exists('ppo_moment_label') ? ppo_moment_label('moment') : '片刻';
    $moments_label = function_exists('ppo_moment_label') ? ppo_moment_label('moments') : '圈子';
    $moment_url = get_post_type_archive_link('moment');

    if (!$moment_url) {
        $moment_slug = function_exists('ppo_moment_slug') ? ppo_moment_slug('moment_slug', 'moment') : 'moment';
        $moment_url = home_url('/' . trim($moment_slug, '/') . '/');
    }

    //$user_id = get_current_user_id();
    $user_power = get_user_power($user_id);

    $can_create_moment_circle = function_exists('pix_can_create_moment_circle')
        ? pix_can_create_moment_circle($user_id)
        : (user_can($user_id, 'manage_options') || in_array('cr_moment', (array)$user_power, true));

    if($can_create_moment_circle){
        $cr_moment = '<li><a  class="publish-mos"><i class="ri-focus-2-line"></i> 创建'.esc_html($moments_label).'</a></li>';
    }
    $html = '<div class="cls-publish item">
                <i class="ri-add-circle-line pix-dropdown-toggle" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false"></i>
                <div class="publish-drop-pannel pix-dropdown-panel" data-pix-dropdown="click">
                    <ul>
                        <li><a href="'.esc_url($moment_url).'"><i class="ri-focus-line"></i> 发布'.esc_html($moment_label).'</a></li>
                        '.$cr_moment.'
                    </ul>
                 </div>
            </div>
             ';
    return $html;
}

public static function user_pannel(){
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $login = is_user_logged_in();

    if($login){
        $avatar = get_u_avatar($user_id, 'url');
        $nickname = $current_user->display_name;
        $user_info = get_userdata($user_id);
        $des = !empty($user_info->description) ? $user_info->description : '这个人很懒，什么也没有留下';
        $credit = get_user_credit($user_id);
        $credit_data = array('name' => get_op('credit_name', '积分'));

        $lv_data = ppo_get_user_level_info($user_id);
        $lv_icon = isset($lv_data['icon']) ? $lv_data['icon'] : '';
        $lv_name = isset($lv_data['name']) ? $lv_data['name'] : 'Lv.1';

        $vip_data = ppo_get_user_vip_data($user_id);
        $is_vip = !empty($vip_data);

        $author_url = get_author_posts_url($user_id);

        if($is_vip){
            $end_time = get_user_meta($user_id, 'user_vip_data', true);
            if(!empty($end_time) && isset($end_time['end_time'])) {
                if ($end_time['end_time'] >= strtotime('2100-01-01')) {
                    $vip_end = '永久有效';
                } else {
                    $vip_end = date('Y-m-d', $end_time['end_time']) . ' 到期';
                }
            } else {
                $vip_end = '永久有效';
            }
            $vip_name = isset($vip_data['title']) ? $vip_data['title'] : '普通用户';
            $vip_icon = isset($vip_data['icon']) ? $vip_data['icon'] : '';
        }

        $html = '<div class="user-has-login">
                    <div class="login-avatar pix-dropdown-toggle" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false"><img class="lazy" data-src="'.$avatar.'"></div>
                    <div class="user-drop-pannel pix-dropdown-panel" data-pix-dropdown="click">
                        <a href="'.$author_url.'" class="user-pannel-header">
                            <img src="'.$avatar.'" class="user-pannel-avatar">
                            <div class="user-pannel-info">
                                <div class="user-pannel-name">'.esc_html($nickname);
        if($lv_icon){
            $html .= '<span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="'.esc_attr($lv_name).'" aria-label="'.esc_attr($lv_name).'" tabindex="0"><img src="'.$lv_icon.'" class="user-pannel-lv-icon" alt="'.esc_attr($lv_name).'"></span>';
        }
        $html .= '</div>
                                <div class="user-pannel-meta">
                                    <span class="user-pannel-uid">UID:'.$user_id.'</span>
                                    <span class="user-pannel-desc">'.esc_html($des).'</span>
                                </div>
                            </div>
                        </a>';

        $user_menu_items = pix_get_user_menu_items($user_id);
        $user_menu_html = '';
        foreach($user_menu_items as $menu_item){
            $user_menu_html .= '<a href="'.esc_url($menu_item['url']).'" class="user-pannel-menu-item">
                                <i class="'.esc_attr($menu_item['icon']).'"></i>
                                <span>'.esc_html($menu_item['title']).'</span>
                                <i class="ri-arrow-right-s-line"></i>
                            </a>';
        }

        $html .= '<div class="user-pannel-stats">
                            <a href="'.user_dashboard_url('task').'" class="user-pannel-stat">
                                <span class="user-pannel-stat-label">'.esc_html($credit_data['name']).'</span>
                                <span class="user-pannel-stat-value user-pannel-stat-credit">'.$credit.'</span>
                                <img src="'.THEME_URL.'/img/icon/credit.png" class="user-pannel-stat-credit">
                            </a>
                        </div>

                        <div class="user-pannel-menu">'.$user_menu_html.'</div>

                        <div class="user-pannel-logout">
                            '.(current_user_can('manage_options') ? '<a href="'.admin_url().'" class="admin-link"><i class="ri-settings-3-line"></i><span>后台管理</span></a>' : '').'
                            <a href="'.wp_logout_url(home_url()).'">
                                <i class="ri-logout-circle-r-line"></i>
                                <span>退出登录</span>
                            </a>
                        </div>
                    </div>
                </div>';
    } else {
        $html = global_login_modal();
    }

    return $html;
}    


    }
}
