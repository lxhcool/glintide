<?php
//头部加载代码

add_action('wp_head', 'pix_custom_head');
function pix_custom_head()
{
    pix_custom_style();
    
}

add_action('init', 'pix_use_custom_favicon', 20);
function pix_use_custom_favicon()
{
    if (!pix_get_custom_favicon()) {
        return;
    }

    remove_action('wp_head', 'wp_site_icon', 99);

    add_action('wp_head', 'pix_custom_favicon', 99);
}

// 后台/登录页在输出阶段覆盖 WordPress 原生站点图标，避免受初始化顺序影响。
function pix_prepare_custom_favicon_head($hook)
{
    if (!pix_get_custom_favicon()) {
        return;
    }

    // admin_head/login_head 的 wp_site_icon 使用默认优先级 10 注册。
    remove_action($hook, 'wp_site_icon', 10);
    add_action($hook, 'pix_custom_favicon', 99);
}

add_action('admin_head', function () {
    pix_prepare_custom_favicon_head('admin_head');
}, 0);

add_action('login_head', function () {
    pix_prepare_custom_favicon_head('login_head');
}, 0);

function pix_get_custom_favicon()
{
    $favicon = get_cu('favicon');
    if (is_array($favicon)) {
        $favicon = $favicon['url'] ?? '';
    }

    return $favicon ? $favicon : '';
}

function pix_custom_favicon()
{
    $favicon = pix_get_custom_favicon();
    if (empty($favicon)) {
        return;
    }

    echo '<link rel="icon" href="' . esc_url($favicon) . '" sizes="32x32">' . "\n";
    echo '<link rel="icon" href="' . esc_url($favicon) . '" sizes="192x192">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url($favicon) . '">' . "\n";
    echo '<meta name="msapplication-TileImage" content="' . esc_url($favicon) . '">' . "\n";
}

function pix_admin_login_logo_url()
{
    $logo = get_cu('admin_login_logo');
    if (is_array($logo)) {
        $logo = $logo['url'] ?? '';
    }

    if (empty($logo)) {
        $logo = pix_global_logo_url('dark');
    }

    return $logo ? $logo : get_theme_file_uri('/img/logo.png');
}

function pix_admin_login_page_style()
{
    $logo = pix_admin_login_logo_url();
    ?>
    <style id="pix-admin-login-style">
      body.login {
        background: #f4f6fb;
        min-height: 100vh;
        min-height: 100dvh;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        overflow-x: hidden;
      }
      body.login #login {
        width: min(100% - 40px, 380px);
        position: relative;
        flex: 0 0 auto;
        margin: 72px 0 0;
        padding: 30px;
        box-sizing: border-box;
        border: 1px solid #e2e7f1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 14px 32px rgba(31, 42, 68, .08);
      }
      body.login #login h1 {
        position: absolute;
        top: -100px;
        left: 0;
        width: 100%;
        margin: 0;
      }
      body.login #login h1 a {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        background-image: url('<?php echo esc_url($logo); ?>');
        background-position: center;
        background-size: contain;
      }
      body.login #loginform,
      body.login #lostpasswordform,
      body.login #registerform {
        margin-top: 0;
        padding: 0;
        border: 0;
        box-shadow: none;
      }
      body.login #loginform .input,
      body.login #lostpasswordform .input,
      body.login #registerform .input {
        min-height: 42px;
        margin-top: 6px;
        border: 1px solid #d8deea;
        border-radius: 8px;
        box-shadow: none;
      }
      body.login #loginform .input:focus,
      body.login #lostpasswordform .input:focus,
      body.login #registerform .input:focus {
        border-color: #3157ff;
        box-shadow: 0 0 0 3px rgba(49, 87, 255, .12);
      }
      body.login #wp-submit {
        min-height: 40px;
        padding: 0 18px;
        border: 0;
        border-radius: 8px;
        background: #3157ff;
        box-shadow: none;
        text-shadow: none;
      }
      body.login #wp-submit:hover,
      body.login #wp-submit:focus {
        background: #2447d8;
        box-shadow: none;
      }
      body.login #nav a[href*="lostpassword"],
      body.login #nav a[href*="retrievepassword"] {
        display: none;
      }
      body.login #backtoblog {
        margin: 20px 0 0;
        text-align: center;
      }
      body.login #backtoblog a {
        color: #687386;
      }
      body.login .language-switcher {
        flex: 0 0 auto;
        margin: 18px 0 0;
      }
      @media (max-width: 480px) {
        body.login #login {
          margin-top: 64px;
          padding: 24px;
        }
      }
    </style>
    <?php
}
add_action('login_head', 'pix_admin_login_page_style', 20);

add_filter('login_headerurl', function () {
    return home_url('/');
});

add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

function pix_disable_wp_lostpassword()
{
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    if (in_array($action, array('lostpassword', 'retrievepassword', 'rp', 'resetpass'), true)) {
        wp_safe_redirect(home_url('/'));
        exit;
    }
}
add_action('login_init', 'pix_disable_wp_lostpassword');

//自定义样式
function pix_custom_style(){
    $style = '';
    $hb_top = get_cu('hb_top_tab');
    $hb_center = get_cu('hb_center_tab');
    $hb_bottom = get_cu('hb_bottom_tab');

    //头部模式：经典模式恢复原外边距；悬浮模式由 header-spacer 高度控制顶部间距（桌面端，移动端保持 50px+safe-area）
    if (get_cu('header_mode', 'floating') === 'classic') {
        $top_margin_classic = get_cu('site_top_margin_classic', 60);
        if ($top_margin_classic > 0) {
            $style .= 'body.classic .site{margin-top:'.intval($top_margin_classic).'px;}';
        }
    } else {
        $nav_base = get_cu('classic_nav_base_tab', array());
        $top_margin = isset($nav_base['site_top_margin']) ? intval($nav_base['site_top_margin']) : 65;
        if ($top_margin > 0) {
            $style .= '@media (min-width: 768px){.header-spacer{height:'.$top_margin.'px;}}';
        }

        //内容区顶部圆角（左上、右上）——仅悬浮模式生效，经典模式强制取消；排除页脚
        $content_radius = get_cu('content_radius', 12);
        if ($content_radius > 0) {
            $style .= '.pix-content:not(.site-footer){border-radius:'.intval($content_radius).'px '.intval($content_radius).'px 0 0;overflow:clip;}';
        }
    }

    //导航构建器
    if(is_array($hb_top)){
        if($hb_top['shadow'] == true){
            $style .= '.top-nav-row.main-nav-item{box-shadow: 0px 11px 18px rgb(204 208 255 / 14%);}';
        }
        if(!empty($hb_top['img'])){
            $style .= '.top-nav-row.main-nav-item{background-image:url('.$hb_top['img'].');}';
        }
    }

    if(is_array($hb_center)){
        if($hb_center['shadow'] == true){
            $style .= '.center-nav-row.main-nav-item{box-shadow: 0px 11px 18px rgb(204 208 255 / 14%);}';
        }
        if(!empty($hb_center['img'])){
            $style .= '.center-nav-row.main-nav-item{background-image:url('.$hb_center['img'].');}';
        }
    }

    if(is_array($hb_bottom)){
        if($hb_bottom['shadow'] == true){
            $style .= '.bottom-nav-row.main-nav-item{box-shadow: 0px 11px 18px rgb(204 208 255 / 14%);}';
        }
        if(!empty($hb_bottom['img'])){
            $style .= '.bottom-nav-row.main-nav-item{background-image:url('.$hb_bottom['img'].');}';
        }
    }


    //动态计算总体宽度（经典模式）
    if (pix_layout_mode() === 'classic') {
        // 用户中心、管理中心使用独立宽度
        if (is_author() || pix_is_dashboard()) {
            $width = pix_get_content_width();
            if ($width > 0) {
                $style .= '.pix-content{max-width:'.$width.'px;}';
                $style .= '.classic-header{max-width:'.$width.'px;}';
            }
        } elseif (pix_is_fullwidth()) {
            // 全宽页面 = 全局侧栏 + 内容区 总宽度
            $total = pix_global_total_width();
            if ($total > 0) {
                $style .= '.pix-content{max-width:'.$total.'px;}';
                $style .= '.classic-header{max-width:'.$total.'px;}';
            }
        } else {
            // 其他页面计算总体宽度
            $total = pix_total_width();
            if ($total > 0) {
                $style .= '.pix-content{max-width:'.$total.'px;}';
                $style .= '.classic-header{max-width:'.$total.'px;}';
            }
        }
    }

    if ($style) {
        $style = compress_css($style);
        echo '<style type="text/css">' . $style . '</style>';
    }

}

 
/** 
* 压缩html : 清除换行符,清除制表符,去掉注释标记 
* @param $string 
* @return压缩后的$string 
* */ 
function compress_html($string){ 
    $string=str_replace("\r\n",'',$string);//清除换行符 
    $string=str_replace("\n",'',$string);//清除换行符 
    $string=str_replace("\t",'',$string);//清除制表符 
    $pattern=array( 
    "/> *([^ ]*) *</",//去掉注释标记 
    "/[\s]+/", 
    "/<!--[^!]*-->/", 
    "/\" /", 
    "/ \"/", 
    "'/\*[^*]*\*/'" 
    ); 
    $replace=array ( 
    ">\\1<", 
    " ", 
    "", 
    "\"", 
    "\"", 
    "" 
    ); 
    return preg_replace($pattern, $replace, $string); 
    } 

function compress_css($css){
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

function pix_get_term_seo_options($term = null) {
    if (!$term) {
        $term = get_queried_object();
    }

    if (!$term instanceof WP_Term) {
        return array();
    }

    $meta_key = '';
    if ($term->taxonomy === 'category') {
        $meta_key = '_ppo_taxonomy_options';
    } elseif ($term->taxonomy === 'moments') {
        $meta_key = '_ppo_moments_options';
    }

    if (!$meta_key) {
        return array();
    }

    $options = get_term_meta($term->term_id, $meta_key, true);
    return is_array($options) ? $options : array();
}

// ==================== SEO：普通页面标题（文章/分类/首页等） ====================
function pix_seo_title_parts($title_parts) {
    $site_name = $title_parts['site'] ?? get_bloginfo('name');

    if (is_front_page() || is_home()) {
        $home_title = get_op('seo_home_title');
        if ($home_title) {
            $title_parts['title'] = $home_title;
            unset($title_parts['tagline']);
            unset($title_parts['site']);
        }
        return $title_parts;
    }

    if (is_singular('moment')) {
        $post = get_queried_object();
        $title_parts['title'] = $post->post_title;
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    if (is_category()) {
        $term = get_queried_object();
        $term_seo = pix_get_term_seo_options($term);
        $title_parts['title'] = !empty($term_seo['seo_title']) ? $term_seo['seo_title'] : $term->name;
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    if (is_tax('moments')) {
        $term = get_queried_object();
        $term_seo = pix_get_term_seo_options($term);
        $title_parts['title'] = !empty($term_seo['seo_title']) ? $term_seo['seo_title'] : $term->name;
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    if (is_author()) {
        $author = get_queried_object();
        $title_parts['title'] = $author->display_name;
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    if (is_search()) {
        $title_parts['title'] = '搜索：' . get_search_query();
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    if (is_404()) {
        $title_parts['title'] = '页面未找到';
        $title_parts['site'] = $site_name;
        return $title_parts;
    }

    return $title_parts;
}
add_filter('document_title_parts', 'pix_seo_title_parts');

// ==================== SEO：Dashboard 页面标题（覆盖WP默认输出） ====================
add_action('wp_head', function () {
    $type = get_query_var('ppo_admin_page');
    if (empty($type)) {
        return;
    }

    $site_name = get_bloginfo('name');
    $dashboard_titles = array(
        'center' => '概览',
        'trend'  => '动态',
        'vip'    => '会员',
        'wallet' => '钱包',
        'task'   => '任务',
        'order'  => '订单',
        'edit'   => '设置',
    );
    $page_title = isset($dashboard_titles[$type]) ? $dashboard_titles[$type] : '管理中心';

    $dashboard_descriptions = array(
        'center' => '个人中心概览',
        'trend'  => '我的动态',
        'vip'    => '会员中心',
        'wallet' => '我的钱包',
        'task'   => '每日任务',
        'order'  => '我的订单',
        'edit'   => '个人设置',
    );
    $desc_text = isset($dashboard_descriptions[$type]) ? $dashboard_descriptions[$type] : '管理中心';

    remove_action('wp_head', '_wp_render_title_tag', 1);
    echo '<title>' . esc_html($page_title . ' - ' . $site_name) . '</title>' . "\n";
    echo '<meta name="description" content="' . esc_attr($site_name . ' - ' . $desc_text) . '">' . "\n";
}, 0);

// ==================== SEO：消息页面标题 ====================
add_action('wp_head', function () {
    $page_type = get_query_var('ppo_page_type');

    if ($page_type === 'msg') {
        $site_name = get_bloginfo('name');
        $msg_type = get_query_var('msg_action') ?: 'whisper';

        $msg_titles = array(
            'whisper' => '私信',
            'like'    => '点赞通知',
            'reply'   => '评论回复',
            'system'  => '系统通知',
        );
        $page_title = isset($msg_titles[$msg_type]) ? $msg_titles[$msg_type] : '消息中心';

        $msg_descriptions = array(
            'whisper' => '查看私信消息',
            'like'    => '查看点赞通知',
            'reply'   => '查看评论回复',
            'system'  => '查看系统通知',
        );
        $desc_text = isset($msg_descriptions[$msg_type]) ? $msg_descriptions[$msg_type] : '消息中心';

        remove_action('wp_head', '_wp_render_title_tag', 1);
        echo '<title>' . esc_html($page_title . ' - ' . $site_name) . '</title>' . "\n";
        echo '<meta name="description" content="' . esc_attr($site_name . ' - ' . $desc_text) . '">' . "\n";
    }

    if ($page_type === 'vip') {
        $site_name = get_bloginfo('name');
        $vip_title = '会员订阅';

        remove_action('wp_head', '_wp_render_title_tag', 1);
        echo '<title>' . esc_html($vip_title . ' - ' . $site_name) . '</title>' . "\n";
        echo '<meta name="description" content="' . esc_attr($site_name . ' - 会员订阅页面') . '">' . "\n";
    }
}, 0);

// ==================== SEO：meta description / keywords（普通页面） ====================
function pix_seo_meta() {
    if (!get_op('seo_meta_description', true)) {
        return;
    }

    $description = '';
    $keywords = '';

    if (is_front_page() || is_home()) {
        $description = get_op('seo_home_description') ?: get_bloginfo('description');
        $keywords = get_op('seo_home_keywords');
    } elseif (is_singular()) {
        $post = get_queried_object();
        if (has_excerpt($post)) {
            $description = get_the_excerpt($post);
        } else {
            $description = wp_trim_words(strip_shortcodes($post->post_content), 80, '');
        }
        $tags = wp_get_post_tags($post->ID);
        if ($tags) {
            $keywords = implode(', ', wp_list_pluck($tags, 'name'));
        }
    } elseif (is_category()) {
        $term = get_queried_object();
        $term_seo = pix_get_term_seo_options($term);
        $description = term_description($term);
        $keywords = !empty($term_seo['seo_keywords']) ? $term_seo['seo_keywords'] : $term->name;
    } elseif (is_tag()) {
        $term = get_queried_object();
        $description = term_description($term);
        $keywords = $term->name;
    } elseif (is_tax('moments')) {
        $term = get_queried_object();
        $term_seo = pix_get_term_seo_options($term);
        $description = term_description($term);
        $keywords = !empty($term_seo['seo_keywords']) ? $term_seo['seo_keywords'] : $term->name;
    } elseif (is_author()) {
        $author = get_queried_object();
        $description = get_user_meta($author->ID, 'description', true);
    }

    if ($description) {
        $description = trim(preg_replace('/\s+/', ' ', strip_tags($description)));
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }

    if ($keywords) {
        echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
    }
}
add_action('wp_head', 'pix_seo_meta', 2);
    
