<?php
/**
 * Glintide 前台左栏导航
 *
 * Logo 使用主题设置中的 site_logo，未设置时由 logo_text 或站点名兜底。
 * 菜单使用 WordPress 后台配置的 top 菜单位置。
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

function glintide_render_left_navigation() {
    $site_name = get_bloginfo( 'name' );
    $menu = wp_nav_menu(
        array(
            'theme_location' => 'top',
            'container'      => false,
            'menu_class'     => 'glintide-left-rail-menu-list',
            'menu_id'        => '',
            'fallback_cb'    => false,
            'echo'           => false,
            'depth'          => 2,
        )
    );

    $html = '<div class="glintide-left-rail">';
    $html .= '<div class="glintide-left-rail-header">';
    $html .= '<a class="glintide-left-rail-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr( $site_name ) . '">';
    $html .= glintide_site_logo_html();
    $html .= '</a>';
    $html .= '</div>';

    if ( $menu ) {
        $html .= '<nav class="glintide-left-rail-menu" aria-label="主导航">' . $menu . '</nav>';
    }

    $html .= '<div class="glintide-left-rail-footer">';
    $html .= '<span class="glintide-left-rail-copyright">&copy; 2026 Glintide All rights reserved.</span>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}
