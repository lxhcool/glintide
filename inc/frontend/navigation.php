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

/** Pair the left mini widgets while preserving every other widget's slot. */
function glintide_left_sidebar_widget_order( $sidebars ) {
    if ( empty( $sidebars['sidebar-left'] ) || ! is_array( $sidebars['sidebar-left'] ) ) {
        return $sidebars;
    }
    $priority = array(
        'glintide_weather_widget'       => 0,
        'glintide_moon_widget'          => 1,
        'glintide_uptime_widget'        => 2,
        'glintide_year_progress_widget' => 3,
        'glintide_sticker_widget'       => 4,
    );
    $slots = array();
    $widgets = array();
    foreach ( $sidebars['sidebar-left'] as $slot => $id ) {
        $base = preg_replace( '/-\d+$/', '', $id );
        if ( isset( $priority[ $base ] ) ) {
            $slots[] = $slot;
            $widgets[] = array( 'id' => $id, 'rank' => $priority[ $base ], 'position' => count( $widgets ) );
        }
    }
    usort( $widgets, function ( $a, $b ) {
        return $a['rank'] === $b['rank'] ? $a['position'] - $b['position'] : $a['rank'] - $b['rank'];
    } );
    foreach ( $slots as $index => $slot ) {
        $sidebars['sidebar-left'][ $slot ] = $widgets[ $index ]['id'];
    }
    return $sidebars;
}

/** Menu-adjacent reading links; never expose unpublished or protected content. */
function glintide_render_featured_posts() {
    $selected = glintide_get_option( 'left_featured_posts', array() );
    $selected = is_array( $selected ) ? array_values( array_unique( array_filter( array_map( 'absint', $selected ) ) ) ) : array();
    $args = array(
        'post_type' => 'post', 'post_status' => 'publish', 'has_password' => false,
        'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true,
    );
    if ( $selected ) {
        $args['post__in'] = $selected;
        $args['orderby'] = 'post__in';
    } else {
        $args['meta_query'] = array( 'relation' => 'OR',
            array( 'key' => '_glintide_card_type', 'value' => 'text' ),
            array( 'key' => '_glintide_card_type', 'compare' => 'NOT EXISTS' ),
        );
    }
    $posts = get_posts( $args );
    if ( ! $posts ) {
        return '';
    }
    $heading = $selected ? '精选文章' : '最近文章';
    $html = '<section class="glintide-left-featured" aria-label="' . esc_attr( $heading ) . '"><h2>' . esc_html( $heading ) . '</h2><ul>';
    foreach ( $posts as $post ) {
        $title = get_the_title( $post );
        $html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '" data-glintide-modal="' . esc_attr( $post->ID ) . '" data-glintide-no-pjax><span>' . esc_html( $title ? $title : '未命名文章' ) . '</span><time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( 'Y.m.d', $post ) ) . '</time></a></li>';
    }
    return $html . '</ul></section>';
}

function glintide_menu_detail_attributes( $atts, $item, $args ) {
    if ( 'top' === ( $args->theme_location ?? '' ) && 'post_type' === $item->type && 'post' === $item->object ) {
        $atts['data-glintide-modal'] = absint( $item->object_id );
        $atts['data-glintide-no-pjax'] = '';
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'glintide_menu_detail_attributes', 10, 3 );

function glintide_render_left_navigation() {
    $site_name = get_bloginfo( 'name' );
    $footer_text = glintide_get_option( 'footer_text', '' );
    $footer_text = $footer_text ? do_shortcode( wp_kses_post( $footer_text ) ) : '&copy; ' . date( 'Y' ) . ' Glintide';
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
        $html .= '<nav class="glintide-left-rail-menu" aria-label="主导航"><h2 class="glintide-left-rail-menu-title">浏览</h2>' . $menu . '</nav>';
    }

    $html .= glintide_render_featured_posts();

    if ( is_active_sidebar( 'sidebar-left' ) ) {
        $html .= '<div class="glintide-left-widget-stack">';
        ob_start();
        add_filter( 'sidebars_widgets', 'glintide_left_sidebar_widget_order' );
        dynamic_sidebar( 'sidebar-left' );
        remove_filter( 'sidebars_widgets', 'glintide_left_sidebar_widget_order' );
        $html .= ob_get_clean();
        $html .= '</div>';
    }

    $html .= '<div class="glintide-left-rail-footer">';
    $html .= '<div class="glintide-left-rail-footer-copy">' . wp_kses_post( $footer_text ) . '</div>';
    $html .= '<button class="glintide-left-rail-theme" type="button" data-glintide-theme-toggle aria-pressed="false" aria-label="切换深色模式" title="切换深色模式"><i class="ri-moon-line" data-glintide-theme-icon aria-hidden="true"></i></button>';
    $html .= '</div>';

    $html .= '</div>';

    return $html;
}
