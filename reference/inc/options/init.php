<?php
class OPTION_Init {
    //全部设置文件

    public static $includes = array(
        'notice-page', 
        'options-btn',
        'options-level',
        'slot/hidden-fields',
        'slot/hbgroup-fields',
        'slot/hblist-fields',
        'theme-customize',
        'theme-taxonomy',
        'theme-option', //必须最后加载
    );    

    public static function init() {
        //加载所有设置参数
        $pix_options = self::$includes;
        foreach ( $pix_options as $pix_option) {
            $path = 'inc/options/' . $pix_option . '.php';
            require_once get_theme_file_path($path);
        }

    }

}

//挂载init
OPTION_Init::init();

// 后台设置面板样式（原加载代码在已移除的加密模块中，这里补回）
add_action('admin_enqueue_scripts', function () {
    $css_path = get_theme_file_path('inc/assets/css/codestar-custom.css');
    wp_enqueue_style(
        'pix-codestar-custom',
        THEME_URL . '/inc/assets/css/codestar-custom.css',
        array(),
        file_exists($css_path) ? filemtime($css_path) : PIX_VERSION
    );
});