<?php
// 经典编辑器短代码按钮
add_action('init', 'ppo_mce_button');
function ppo_mce_button() {
    add_filter('mce_external_plugins', 'ppo_shortcode_plugin');
    add_filter('mce_buttons', 'ppo_register_button');
    //add_filter('mce_buttons_2', 'ppo_register_button_2');
}

function ppo_register_button($buttons) {
    $buttons[] = 'ppo_hide';
    return $buttons;
}

function ppo_shortcode_plugin($plugin_array)
{
    $plugin_array['ppo_hide']   = THEME_URL . '/inc/assets/js/ppo-shortcode.js?ver=' . PIX_VERSION . '';
    return $plugin_array;
}

// 定义 ppo-shortcode.js 依赖的全局变量（原主题缺失，导致编辑器插件初始化失败）
add_action('admin_footer', function () {
    echo '<script>window.ppo_admin_global = window.ppo_admin_global || {theme_url: ' . wp_json_encode(THEME_URL) . '};</script>';
});