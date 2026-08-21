<?php
/**
 * Pix 主题布局系统
 *
 * 提供统一的布局控制函数
 */

// 获取布局模式（预留 Pro 模式扩展）
function pix_layout_mode() {
    return 'classic';
}

// 判断是否为管理中心
function pix_is_dashboard() {
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    return strpos($uri, '/dashboard') === 0;
}

// 获取侧栏配置
function pix_sidebar() {
    // 用户中心、管理中心由各自模板控制布局
    if (is_author() || pix_is_dashboard()) {
        return ['left' => false, 'right' => false];
    }

    // 页面由 metabox 控制
    if (is_page()) {
        $meta = get_post_meta(get_the_ID(), '_ppo_page_options', true);
        if (!empty($meta['page_layout']) && $meta['page_layout'] === 'full') {
            return ['left' => false, 'right' => false];
        }
        return [
            'left'  => get_cu('cls_left_wid', false),
            'right' => get_cu('cls_right_wid', false),
        ];
    }

    // 首页、文章、片刻等使用全局侧栏设置
    return [
        'left'  => get_cu('cls_left_wid', false),
        'right' => get_cu('cls_right_wid', false),
    ];
}

// 获取全局侧栏配置（不受页面 metabox 影响）
function pix_global_sidebar() {
    return [
        'left'  => get_cu('cls_left_wid', false),
        'right' => get_cu('cls_right_wid', false),
    ];
}

// 获取侧栏宽度
function pix_sidebar_width() {
    return (int) get_cu('sidebar_width', 320);
}

// 获取内容区宽度
function pix_get_content_width() {
    if (is_author()) {
        return (int) get_cu('author_width', 1280);
    }

    if (pix_is_dashboard()) {
        return (int) get_cu('dashboard_width', 1280);
    }

    return (int) get_cu('classic_center_width', 640);
}

// 计算总体宽度
function pix_total_width() {
    $sidebar = pix_sidebar();
    $width = pix_get_content_width();

    if ($sidebar['left'])  $width += pix_sidebar_width();
    if ($sidebar['right']) $width += pix_sidebar_width();

    return $width;
}

// 计算全局总体宽度（不受页面 metabox 影响）
function pix_global_total_width() {
    $sidebar = pix_global_sidebar();
    $width = (int) get_cu('classic_center_width', 640);

    if ($sidebar['left'])  $width += pix_sidebar_width();
    if ($sidebar['right']) $width += pix_sidebar_width();

    return $width;
}

// 判断是否为全宽布局
function pix_is_fullwidth() {
    if (is_page()) {
        $meta = get_post_meta(get_the_ID(), '_ppo_page_options', true);
        if (!empty($meta['page_layout']) && $meta['page_layout'] === 'full') {
            return true;
        }
    }
    return false;
}

// 判断是否显示指定侧栏
function pix_show_sidebar($position = 'left') {
    $sidebar = pix_sidebar();
    return $sidebar[$position] ?? false;
}
