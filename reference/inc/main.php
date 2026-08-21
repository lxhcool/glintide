<?php
/**
 * Pix主题 - JS/CSS 加载队列
 * 
 * 管理所有 JavaScript 和 CSS 文件的加载顺序
 * 
 * @package pix
 * @author lxhcool
 * @version 1.0.4
 */
function pix_enqueue_vite_asset($entry = 'src/js/app.js') {
    $dist_dir = THEME_DIR . '/inc/assets/dist';
    $dist_url = THEME_URL . '/inc/assets/dist';
    $manifest_path = $dist_dir . '/manifest.json';

    if (!file_exists($manifest_path)) {
        return;
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);
    if (empty($manifest[$entry]['file'])) {
        return;
    }

    $asset = $manifest[$entry];

    if (!empty($asset['css']) && is_array($asset['css'])) {
        foreach ($asset['css'] as $index => $css_file) {
            $css_path = $dist_dir . '/' . $css_file;
            wp_enqueue_style(
                'pix-vite-' . $index,
                $dist_url . '/' . $css_file,
                array(),
                file_exists($css_path) ? filemtime($css_path) : PIX_VERSION
            );
        }
    }

    $js_path = $dist_dir . '/' . $asset['file'];
    wp_enqueue_script(
        'pix-vite-app',
        $dist_url . '/' . $asset['file'],
        array(),
        file_exists($js_path) ? filemtime($js_path) : PIX_VERSION,
        true
    );
}

function pix_scripts() {
    global $wp_query, $post;
	wp_enqueue_style( 'pix-style', get_stylesheet_uri(), array(), PIX_VERSION );
    wp_enqueue_style( 'remixicon.css', THEME_URL . '/inc/assets/fonts/remixicon.css', array(), PIX_VERSION );
    wp_enqueue_style( 'swiper.css', THEME_URL . '/inc/assets/css/swiper-bundle.min.css', array(), PIX_VERSION );
    wp_enqueue_style( 'placeholder.min.css', THEME_URL . '/inc/assets/css/placeholder.min.css', array(), PIX_VERSION );
    wp_enqueue_style( 'fancybox.css', THEME_URL . '/inc/assets/css/fancybox.css', array(), PIX_VERSION );
    wp_enqueue_style( 'plugin.css', THEME_URL . '/inc/assets/css/plugin.css', array(), PIX_VERSION );
    wp_enqueue_style( 'highlight.min.css', THEME_URL . '/inc/assets/css/highlight.min.css', array(), PIX_VERSION );
    wp_enqueue_style( 'user.css', THEME_URL . '/inc/assets/css/user.css', array(), PIX_VERSION );
    wp_enqueue_style( 'pix-uploader.css', THEME_URL . '/inc/assets/css/pix-uploader.css', array(), PIX_VERSION );
    $pix_main_css_path = get_template_directory() . '/inc/assets/css/main.css';
    wp_enqueue_style( 'main.css', THEME_URL . '/inc/assets/css/main.css', array(), file_exists($pix_main_css_path) ? filemtime($pix_main_css_path) : PIX_VERSION );
    pix_enqueue_vite_asset();

    // ==================== JS 按需加载 ====================

    wp_enqueue_script( 'cookies.js', THEME_URL . '/inc/assets/js/cookies.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'codeinput.js', THEME_URL . '/inc/assets/js/codeinput.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'readmore.js', THEME_URL . '/inc/assets/js/readmore.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'lazyload.min.js', THEME_URL . '/inc/assets/js/lazyload.min.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'clipboard.min.js', THEME_URL . '/inc/assets/js/clipboard.min.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'toastify', THEME_URL . '/inc/assets/js/toastify.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'cocomsg', THEME_URL . '/inc/assets/js/coco-msg.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'timeago.js', THEME_URL . '/inc/assets/js/timeago.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'fancybox.js', THEME_URL . '/inc/assets/js/fancybox.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'highlight.min.js', THEME_URL . '/inc/assets/js/highlight.min.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'jquery-confirm.min.js', THEME_URL . '/inc/assets/js/jquery-confirm.min.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'htmx.min.js', THEME_URL . '/inc/assets/js/htmx.min.js', array(), PIX_VERSION, true );
    wp_enqueue_script( 'swiper.js', THEME_URL . '/inc/assets/js/swiper-bundle.min.js', array('jquery'), PIX_VERSION, true );
    wp_enqueue_script( 'pix-uploader.js', THEME_URL . '/inc/assets/js/pix-uploader.js', array('jquery'), PIX_VERSION, true );
    $pix_user_js_path = get_template_directory() . '/inc/assets/js/user.js';
    wp_enqueue_script( 'user.js', THEME_URL . '/inc/assets/js/user.js', array('jquery', 'pix-uploader.js'), file_exists($pix_user_js_path) ? filemtime($pix_user_js_path) : PIX_VERSION, true );
    $pix_moment_js_path = get_template_directory() . '/inc/assets/js/moment.js';
    wp_enqueue_script( 'moment.js', THEME_URL . '/inc/assets/js/moment.js', array('jquery', 'pix-uploader.js'), file_exists($pix_moment_js_path) ? filemtime($pix_moment_js_path) : PIX_VERSION, true );
    

    // 验证码 - 根据设置按需加载
    $captcha_type = get_op('captcha_type', 'normal');
    $content_protect_type = get_op('content_protect_type', 'pixcap');
    if ($captcha_type == 'geetest') {
        wp_enqueue_script( 'geetest', 'https://static.geetest.com/v4/gt4.js', array(), PIX_VERSION, true );
    } elseif ($captcha_type == 'ppoc') {
        wp_enqueue_script( 'geetest', 'https://static.geetest.com/v4/gt4.js', array(), PIX_VERSION, true );
        wp_enqueue_script( 'ppo-captcha', THEME_URL . '/inc/assets/js/ppo-captcha.js', array('jquery', 'geetest'), PIX_VERSION, true );
        wp_enqueue_style( 'ppo-captcha', THEME_URL . '/inc/assets/css/ppo-captcha.css', array(), PIX_VERSION );
    }

    if ($captcha_type == 'pixcap' || $content_protect_type == 'pixcap') {
        $pixcap_js_path = get_template_directory() . '/inc/vendor/pixcap/public/js/pixcap.js';
        $pixcap_css_path = get_template_directory() . '/inc/vendor/pixcap/public/css/pixcap.css';
        wp_enqueue_script( 'pixcap', THEME_URL . '/inc/vendor/pixcap/public/js/pixcap.js', array(), file_exists($pixcap_js_path) ? filemtime($pixcap_js_path) : PIX_VERSION, true );
        wp_enqueue_style( 'pixcap', THEME_URL . '/inc/vendor/pixcap/public/css/pixcap.css', array(), file_exists($pixcap_css_path) ? filemtime($pixcap_css_path) : PIX_VERSION );
        if ($captcha_type == 'pixcap' || $content_protect_type == 'pixcap') {
            $pixcap_content_css_path = get_template_directory() . '/inc/assets/css/pixcap-content.css';
            wp_enqueue_style( 'pixcap-content', THEME_URL . '/inc/assets/css/pixcap-content.css', array('pixcap'), file_exists($pixcap_content_css_path) ? filemtime($pixcap_content_css_path) : PIX_VERSION );
        }
        $pixcap_common = array(
            'challengeUrl' => admin_url('admin-ajax.php?action=pixcap_challenge'),
            'verifyUrl'    => admin_url('admin-ajax.php?action=pixcap_verify'),
            'logoUrl'      => THEME_URL . '/inc/vendor/pixcap/pixcap.svg',
            'theme'        => get_op('pixcap_theme', 'business'),
            'cost'         => (int) get_op('pixcap_cost', 50000),
        );
        if ($captcha_type == 'pixcap') {
            wp_add_inline_script( 'pixcap', 'window.PixcapConfig=' . wp_json_encode(array_merge($pixcap_common, array(
                'mode' => get_op('pixcap_mode', 'bubble'),
            ))) . ';', 'before' );
        }
        if ($content_protect_type == 'pixcap') {
            wp_add_inline_script( 'pixcap', 'window.PixcapContentConfig=' . wp_json_encode(array_merge($pixcap_common, array(
                'type' => 'pixcap',
            ))) . ';', 'before' );
        }
    } elseif ($captcha_type == 'code') {
        // 随机验证码 — 无需额外加载资源
    }

    // 登录 - 模态窗口，所有页面需要
    $pix_login_js_path = get_template_directory() . '/inc/assets/js/login.js';
    wp_enqueue_script( 'login', THEME_URL . '/inc/assets/js/login.js', array('jquery'), file_exists($pix_login_js_path) ? filemtime($pix_login_js_path) : PIX_VERSION, true );


    // 分享海报需要 html2canvas
    if (is_singular() || is_post_type_archive('moment') || is_tax(array('moments', 'moment_tag'))) {
        wp_enqueue_script( 'html2canvas.js', THEME_URL . '/inc/assets/js/html2canvas.min.js', array('jquery'), PIX_VERSION, true );
    }

    // 用户中心页面（/dashboard, /user/, /msg/ 等路由）
    /* $is_user_page = false;
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($request_uri, PHP_URL_PATH);
    if (preg_match('#/dashboard#', $path) || preg_match('#/user/#', $path) || preg_match('#/msg/#', $path)) {
        $is_user_page = true;
    }
    if ($is_user_page) {
        
    } */

    // 文章页 - 视频播放器
    if (is_singular()) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'video') || 
            has_shortcode($post->post_content, 'player') || 
            has_shortcode($post->post_content, 'ppo_video')) {
            wp_enqueue_script( 'artplayer.js', THEME_URL . '/inc/assets/js/artplayer.js', array('jquery'), PIX_VERSION, true );
            wp_enqueue_script( 'ppo-video', THEME_URL . '/inc/assets/js/ppo-video.js', array('jquery'), PIX_VERSION, true );
        }
    }

    // 鼠标光效 - 如果启用
    if (get_op('enable_cursor', false)) {
        wp_enqueue_script( 'gsap.js', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-M/gsap/3.9.1/gsap.min.js', array(), PIX_VERSION, true );
        wp_enqueue_script( 'cursor.js', THEME_URL . '/inc/assets/js/cursor.js', array('jquery', 'gsap.js'), PIX_VERSION, true );
    }

    // 按需加载 - 需要时由其他模块调用
    // html2canvas.js, readmore.js, codeinput.js 等按需加载

    // Sticky Sidebar
    wp_enqueue_script( 'sticky-sidebar', THEME_URL . '/inc/assets/js/sticky-sidebar.js', array('jquery'), PIX_VERSION, true );

    // 主应用入口
    $pix_app_js_path = get_template_directory() . '/inc/assets/js/app.js';
    $pix_app_js_version = file_exists($pix_app_js_path) ? filemtime($pix_app_js_path) : PIX_VERSION;

    wp_enqueue_script( 'app', THEME_URL . '/inc/assets/js/app.js', array(
        'jquery',
        'lazyload.min.js',
        'clipboard.min.js',
        'toastify',
        'cocomsg',
        'timeago.js',
        'fancybox.js',
        'highlight.min.js',
        'htmx.min.js',
        'swiper.js',
        'jquery-confirm.min.js',
        'pix-uploader.js'
    ), $pix_app_js_version, true );
	
    
   /*  if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		//wp_enqueue_script( 'comment-reply' );
	 } */
    
    wp_localize_script( 'app', 'Theme' , array(
	'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'posts' => json_encode( $wp_query->query_vars ),
    'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
	'max_page' => $wp_query->max_num_pages,
    'redirecturl' => ppo_get_curl(),
    'ppo_url' => THEME_URL,
    'uid' => (int)get_current_user_id(),
    'de_ava' => THEME_URL.'/img/ava.png',
    'home_url' => home_url(),
    'nav_height' => cls_nav_height(),
    'cash_name' => get_op('cash_name', '余额'),
    'credit_name' => get_op('credit_name', '积分'),
    'xp_name' => function_exists('ppo_xp_name') ? ppo_xp_name() : get_op('xp_slug', '经验值'),
    'xp_icon' => function_exists('ppo_xp_icon') ? ppo_xp_icon() : (!empty(get_op('xp_icon')) ? get_op('xp_icon') : THEME_URL.'/img/xp.png'),

    //comments
    'order' => get_option('comment_order'),
    'formpostion' => 'top',
    //'scroll_comment' => get_op('comment_nav','pagenav'),
    'pid' => (is_single() || is_page()) ? $post->ID : '',
    'tid' => get_queried_object()->term_id ?? false,
    'moment_nav' => get_op('moment_nav','btn'),
    'post_nav' => get_op('post_nav','btn'),
    'captcha_type' => $captcha_type,
    'pixcap_mode' => get_op('pixcap_mode', 'bubble'),
    'pixcap_theme' => get_op('pixcap_theme', 'business'),
    'content_protect_type' => $content_protect_type,
	'rest_nonce' => wp_create_nonce('wp_rest'),
	'moment_nonce' => wp_create_nonce('moment_ajax'),
	'post_nonce' => wp_create_nonce('post_ajax'),
	'upload_nonce' => wp_create_nonce('pix_upload_action'),
    'upload_settings' => array(
        'image_compress_enable' => (bool) get_op('image_compress_enable', true),
        'image_convert_webp' => (bool) get_op('image_convert_webp', false),
        'image_compress_quality' => max(1, min(100, (int) get_op('image_compress_quality', 86))),
        'image_compress_width' => get_op('image_compress_width', '1920'),
    ),
    'comment_upload' => array(
        'enabled' => (bool) get_op('comment_image_enable', true),
        'limit' => max(1, min(12, (int) get_op('comment_image_limit', 4))),
        'max_size' => max(1, min(20, (int) get_op('comment_image_max_size', 2))),
    ),
	'card_nonce' => wp_create_nonce('ppo_redeem_card'),
	'user_nonce' => wp_create_nonce('ppo_user_action'),
	'oauth_nonce' => wp_create_nonce('ppo_oauth_action'),
	'msg_nonce' => wp_create_nonce('ppo_msg_action'),
	));
}
add_action( 'wp_enqueue_scripts', 'pix_scripts' );

// 优先替换 jQuery（priority 0），确保先于插件执行，同时保留 jquery handle 避免 noConflict 问题
add_action('wp_enqueue_scripts', function() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', THEME_URL . '/inc/assets/js/jquery.min.js', array(), PIX_VERSION, true);
    }
}, 0);

require THEME_DIR . '/inc/base.php';  //基础函数
require THEME_DIR . '/inc/global.php';  //全局函数
require THEME_DIR . '/inc/opt.php';  //加载优化函数
require THEME_DIR . '/inc/vendor/autoload.php'; //支付，curl,社交登录

function pix_mobile_bottom_nav_enabled() {
    return (bool) get_cu('mobile_bottom_nav_enable', true);
}

function pix_mobile_bottom_nav_show_title() {
    return (bool) get_cu('mobile_bottom_nav_show_title', false);
}

function pix_mobile_bottom_nav_current_path() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $path = parse_url($request_uri, PHP_URL_PATH);
    return untrailingslashit($path ? $path : '/');
}

function pix_mobile_bottom_nav_is_active($url) {
    if (!$url || strpos($url, '#') === 0) {
        return false;
    }

    $path = parse_url($url, PHP_URL_PATH);
    $path = untrailingslashit($path ? $path : '/');
    $current_path = pix_mobile_bottom_nav_current_path();

    if ($path === '') {
        $path = '/';
    }

    return $path === '/'
        ? $current_path === '/'
        : ($current_path === $path || strpos($current_path . '/', $path . '/') === 0);
}

function pix_mobile_bottom_nav_item($title, $icon, $url, $class = '') {
    $active = pix_mobile_bottom_nav_is_active($url) ? ' is-active' : '';
    $class = trim('pix-mobile-bottom-nav-item ' . $class . $active);

    return sprintf(
        '<a class="%1$s" href="%2$s"><i class="%3$s"></i><span>%4$s</span></a>',
        esc_attr($class),
        esc_url($url),
        esc_attr($icon),
        esc_html($title)
    );
}

function pix_mobile_bottom_nav_unread_badge($user_id) {
    if (!$user_id) {
        return '';
    }

    $count = 0;
    $count += function_exists('get_unread_comment_msg_count') ? (int) get_unread_comment_msg_count($user_id) : 0;
    $count += function_exists('get_unread_like_msg_count') ? (int) get_unread_like_msg_count($user_id) : 0;
    $count += function_exists('get_unread_system_msg_count') ? (int) get_unread_system_msg_count($user_id) : 0;
    $count += function_exists('ppo_get_unread_message_count') ? (int) ppo_get_unread_message_count($user_id) : 0;

    if ($count <= 0) {
        return '';
    }

    return '<span class="pix-mobile-bottom-nav-badge">' . esc_html($count > 99 ? '99+' : (string) $count) . '</span>';
}

function pix_mobile_bottom_nav_render() {
    if (is_admin() || !pix_mobile_bottom_nav_enabled()) {
        return;
    }

    $user_id = get_current_user_id();
    $moment_label = function_exists('ppo_moment_label') ? ppo_moment_label('moment') : '片刻';
    $moment_url = get_post_type_archive_link('moment');

    if (!$moment_url) {
        $moment_slug = function_exists('ppo_moment_slug') ? ppo_moment_slug('moment_slug', 'moment') : 'moment';
        $moment_url = home_url('/' . trim($moment_slug, '/') . '/');
    }

    $custom_items = array(
        array(
            'title' => get_cu('mobile_bottom_nav_item_1_title', '首页'),
            'icon'  => get_cu('mobile_bottom_nav_item_1_icon', 'ri-home-5-line'),
            'url'   => get_cu('mobile_bottom_nav_item_1_url', home_url('/')),
        ),
        array(
            'title' => get_cu('mobile_bottom_nav_item_2_title', $moment_label),
            'icon'  => get_cu('mobile_bottom_nav_item_2_icon', 'ri-bubble-chart-line'),
            'url'   => get_cu('mobile_bottom_nav_item_2_url', $moment_url),
        ),
    );

    $message_url = home_url('/msg');
    $profile_url = is_user_logged_in() && function_exists('user_dashboard_url') ? user_dashboard_url('center') : '#modal-login';
    $profile_attrs = is_user_logged_in() ? '' : ' data-pix-auth-open="login" aria-haspopup="dialog" aria-controls="modal-login"';
    ?>
    <div class="pix-mobile-publish-sheet" aria-hidden="true">
        <button type="button" class="pix-mobile-publish-backdrop" aria-label="关闭发布菜单"></button>
        <div class="pix-mobile-publish-panel" role="dialog" aria-modal="true" aria-label="发布菜单">
            <div class="pix-mobile-publish-grip" aria-hidden="true"></div>
            <a class="pix-mobile-publish-option" href="<?php echo esc_url($moment_url); ?>" data-pix-mobile-publish="moment">
                <i class="ri-edit-box-line"></i>
                <span>发布<?php echo esc_html($moment_label); ?></span>
                <i class="ri-arrow-right-s-line"></i>
            </a>
        </div>
    </div>

    <nav class="pix-mobile-bottom-nav<?php echo pix_mobile_bottom_nav_show_title() ? ' is-title-visible' : ''; ?>" aria-label="移动端底部菜单" data-moment-url="<?php echo esc_url($moment_url); ?>">
        <?php
        foreach ($custom_items as $item) {
            echo pix_mobile_bottom_nav_item($item['title'], $item['icon'], $item['url']);
        }
        ?>
        <button type="button" class="pix-mobile-bottom-nav-action" aria-label="打开发布菜单" aria-expanded="false">
            <i class="ri-add-line"></i>
        </button>
        <a class="pix-mobile-bottom-nav-item<?php echo pix_mobile_bottom_nav_is_active($message_url) ? ' is-active' : ''; ?>" href="<?php echo esc_url($message_url); ?>">
            <i class="ri-notification-3-line"></i>
            <span>消息</span>
            <?php echo pix_mobile_bottom_nav_unread_badge($user_id); ?>
        </a>
        <a class="pix-mobile-bottom-nav-item<?php echo pix_mobile_bottom_nav_is_active($profile_url) ? ' is-active' : ''; ?>" href="<?php echo esc_url($profile_url); ?>"<?php echo $profile_attrs; ?>>
            <i class="ri-user-3-line"></i>
            <span>我的</span>
        </a>
    </nav>
    <?php
}
add_action('wp_footer', 'pix_mobile_bottom_nav_render', 30);

function pix_mobile_bottom_nav_body_class($classes) {
    if (pix_mobile_bottom_nav_enabled()) {
        $classes[] = 'pix-mobile-bottom-nav-enabled';
    }

    return $classes;
}
add_filter('body_class', 'pix_mobile_bottom_nav_body_class');
