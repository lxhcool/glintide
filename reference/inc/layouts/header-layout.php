<?php
/**
 * 全站抽屉与搜索遮罩
 *
 */
?>

<?php

if (!function_exists('pix_header_search_overlay')) {
function pix_header_search_overlay(){
    $query = get_search_query();
    $hot_terms = get_terms(array(
        'taxonomy' => 'category',
        'orderby' => 'count',
        'order' => 'DESC',
        'number' => 6,
        'hide_empty' => true,
    ));

    $hot_html = '';
    if (!is_wp_error($hot_terms) && !empty($hot_terms)) {
        foreach ($hot_terms as $term) {
            $hot_html .= '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
        }
    }

    return '<div class="pix-search-overlay" aria-hidden="true">
        <div class="pix-search-backdrop"></div>
        <div class="pix-search-panel" role="dialog" aria-modal="true" aria-label="站内搜索">
            <button type="button" class="pix-search-close" aria-label="关闭搜索"><i class="ri-close-line"></i></button>
            <div class="pix-search-kicker">Search PixPro</div>
            <h2>找文章、资源和灵感</h2>
            <form class="pix-search-form" role="search" method="get" action="' . esc_url(home_url('/')) . '">
                <i class="ri-search-2-line"></i>
                <input class="pix-search-input" type="search" name="s" value="' . esc_attr($query) . '" placeholder="输入关键词后回车搜索" autocomplete="off">
                <button type="submit">搜索</button>
            </form>
            ' . ($hot_html ? '<div class="pix-search-hot"><span>热门分类</span><div>' . $hot_html . '</div></div>' : '') . '
        </div>
    </div>';
}
}

if (!function_exists('pix_mobile_header_menu_item')) {
function pix_mobile_header_menu_item($url, $icon, $title, $extra_class = ''){
    return '<a class="pix-mobile-drawer-menu-item ' . esc_attr($extra_class) . '" href="' . esc_url($url) . '">
        <i class="' . esc_attr($icon) . '"></i>
        <span>' . esc_html($title) . '</span>
        <i class="ri-arrow-right-s-line"></i>
    </a>';
}
}

if (!function_exists('pix_mobile_header_user_drawer')) {
function pix_mobile_header_user_drawer(){
    $logged_in = is_user_logged_in();
    $user_id = get_current_user_id();
    $site_name = pix_global_logo_text();
    $auth_attrs = 'data-pix-auth-open="login" aria-haspopup="dialog" aria-controls="modal-login"';

    if ($logged_in) {
        $current_user = wp_get_current_user();
        $avatar = get_u_avatar($user_id, 'url');
        $nickname = $current_user->display_name;
        $user_info = get_userdata($user_id);
        $desc = !empty($user_info->description) ? $user_info->description : '这个人很懒，什么也没有留下';
        $author_url = get_author_posts_url($user_id);
        $credit = function_exists('get_user_credit') ? get_user_credit($user_id) : 0;
        $credit_data = array('name' => get_op('credit_name', '积分'));

        $profile_html = '<a class="pix-mobile-drawer-profile is-login" href="' . esc_url($author_url) . '">
            <img src="' . esc_url($avatar) . '" alt="' . esc_attr($nickname) . '">
            <div>
                <strong>' . esc_html($nickname) . '</strong>
                <span>' . esc_html($desc) . '</span>
            </div>
        </a>';

        $vip_icon_html = '';
        $assets_html = '<div class="pix-mobile-drawer-assets">
            <div class="pix-mobile-drawer-stat-grid">
                <a class="pix-mobile-drawer-stat" href="' . esc_url(user_dashboard_url('task')) . '">
                    <span>' . esc_html($credit_data['name'] ?? '积分') . '</span>
                    <strong>' . esc_html($credit) . '</strong>
                    <img src="' . esc_url(THEME_URL . '/img/icon/credit.png') . '" alt="">
                </a>
            </div>
        </div>';

        $menu_html = '';
        foreach (pix_get_user_menu_items($user_id) as $menu_item) {
            $menu_html .= pix_mobile_header_menu_item($menu_item['url'], $menu_item['icon'], $menu_item['title']);
        }
        $footer_html = '';
        if (current_user_can('manage_options')) {
            $footer_html .= pix_mobile_header_menu_item(admin_url(), 'ri-settings-3-line', '后台管理');
        }
        $footer_html .= pix_mobile_header_menu_item(wp_logout_url(home_url()), 'ri-logout-circle-r-line', '退出登录', 'is-danger');
    } else {
        $assets_html = '';
        $footer_html = '';
        $profile_html = '<div class="pix-mobile-drawer-profile">
            <div class="pix-mobile-drawer-guest-icon"><i class="ri-user-smile-line"></i></div>
            <div>
                <strong>欢迎来到 ' . esc_html($site_name) . '</strong>
                <span>登录后查看你的内容、消息和动态。</span>
            </div>
        </div>';

        $menu_html = '<a class="pix-mobile-drawer-login" href="#modal-login" ' . $auth_attrs . '>
            <i class="ri-login-circle-line"></i>
            <span>登录 / 注册</span>
        </a>';
    }

    return '<div class="pix-mobile-drawer" aria-hidden="true">
        <button type="button" class="pix-mobile-drawer-backdrop" aria-label="关闭菜单"></button>
        <aside class="pix-mobile-drawer-panel" role="dialog" aria-modal="true" aria-label="移动端菜单">
            <div class="pix-mobile-drawer-head">
                <div class="pix-mobile-drawer-logo">' . site_logo('dark') . '</div>
                <button type="button" class="pix-mobile-drawer-close" aria-label="关闭菜单"><i class="ri-close-line"></i></button>
            </div>
            <div class="pix-mobile-drawer-scroll">
                ' . $profile_html . '
                ' . $assets_html . '
                <nav class="pix-mobile-drawer-menu" aria-label="用户菜单">
                    ' . $menu_html . '
                </nav>
            </div>
            ' . ($footer_html ? '<div class="pix-mobile-drawer-footer">' . $footer_html . '</div>' : '') . '
        </aside>
    </div>';
}
}


if (!function_exists('pix_mobile_topbar')) {
function pix_mobile_topbar(){
    $request_path = isset($_SERVER['REQUEST_URI']) ? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') : '';
    $is_dashboard_page = get_query_var('ppo_page_type') === 'dashboard' || $request_path === 'dashboard' || strpos($request_path, 'dashboard/') === 0;
    $is_user_home_page = is_author() || strpos($request_path, 'user/') === 0;
    $is_back_mode = $is_dashboard_page || $is_user_home_page;

    return '<div class="pix-mobile-topbar ' . ($is_back_mode ? 'is-back-mode' : 'is-menu-mode') . '" role="navigation" aria-label="移动端顶部菜单">
        <div class="pix-mobile-topbar-left">
            <button type="button" class="pix-mobile-topbar-btn pix-mobile-menu-trigger" aria-label="打开菜单" aria-expanded="false"><i class="ri-menu-2-line"></i></button>
            <button type="button" class="pix-mobile-topbar-btn pix-mobile-topbar-back" aria-label="返回上一页" data-home-url="' . esc_url(home_url('/')) . '" data-dashboard-url="' . esc_url(user_dashboard_url('center')) . '"><i class="ri-arrow-left-s-line"></i></button>
        </div>
        <a class="pix-mobile-topbar-logo" href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr(get_bloginfo('name')) . '">
            ' . site_logo('dark') . '
        </a>
        <button type="button" class="pix-mobile-topbar-btn pix-search-trigger" aria-label="搜索">
            <i class="ri-search-line"></i>
        </button>
    </div>';
}
}

// 桌面头部：仅经典模式输出（悬浮模式无头部）
if (get_cu('header_mode', 'floating') === 'classic') {
    $web_mod = get_cu('web_mod','classic');
    if($web_mod == 'classic'){
        echo Classic_mod::classic_header();
    } else {
        echo Nav_builder::nav_layout();
    }
}

echo pix_mobile_topbar();
echo pix_mobile_header_user_drawer();
echo pix_header_search_overlay();






