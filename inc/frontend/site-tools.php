<?php
/**
 * Glintide 右栏工具区
 */

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

function glintide_render_right_tools() {
    $default_avatar = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';
    $footer_icp = glintide_get_option( 'footer_icp', array() );
    $footer_icp = is_array( $footer_icp ) ? $footer_icp : array();
    $icp_url = ! empty( $footer_icp['url'] ) ? esc_url( $footer_icp['url'] ) : '';
    $icp_text = ! empty( $footer_icp['text'] ) ? sanitize_text_field( $footer_icp['text'] ) : '';
    $icp_target = ! empty( $footer_icp['target'] ) ? sanitize_key( $footer_icp['target'] ) : '_blank';
    $git_url = esc_url( glintide_get_option( 'footer_git_url', '' ) );
    $user_link = wp_login_url( home_url( '/' ) );
    $user_label = '登录';
    $user_name = '用户头像';
    $avatar = $default_avatar;

    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();
        $user = get_userdata( $user_id );
        $avatar_url = function_exists( 'glintide_get_avatar_url' ) ? glintide_get_avatar_url( $user_id ) : '';
        $user_name = $user ? $user->display_name : $user_name;
        $user_link = get_author_posts_url( $user_id );
        $user_label = '用户主页';

        if ( $avatar_url ) {
            $avatar = $avatar_url;
        }
    }

    $user_html = '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $user_name ) . '" data-glintide-avatar data-glintide-avatar-fallback="' . esc_url( $default_avatar ) . '">';

    $html = '<div class="glintide-right-rail">';
    $html .= '<div class="glintide-right-tools" role="toolbar" aria-label="页面工具">';
    $html .= '<button class="glintide-right-tool" type="button" aria-label="搜索" title="搜索">';
    $html .= '<i class="ri-search-line" aria-hidden="true"></i>';
    $html .= '</button>';
    $html .= '<a class="glintide-right-tool glintide-right-user" href="' . esc_url( $user_link ) . '" aria-label="' . esc_attr( $user_label ) . '" title="' . esc_attr( $user_label ) . '">';
    $html .= $user_html;
    $html .= '</a>';
    $html .= '</div>';

    if ( is_active_sidebar( 'sidebar-right' ) ) {
        $html .= '<div class="glintide-right-widget-stack">';
        ob_start();
        dynamic_sidebar( 'sidebar-right' );
        $html .= ob_get_clean();
        $html .= '</div>';
    }

    if ( ( $icp_url && $icp_text ) || $git_url ) {
        $html .= '<div class="glintide-right-rail-footer">';
        if ( $git_url ) {
            $html .= '<a class="glintide-right-rail-git" href="' . $git_url . '" target="_blank" rel="noopener noreferrer" aria-label="Git 仓库" title="Git 仓库"><i class="ri-github-line" aria-hidden="true"></i></a>';
        }
        if ( $icp_url && $icp_text ) {
            $html .= '<a class="glintide-right-rail-icp" href="' . $icp_url . '" target="' . esc_attr( $icp_target ) . '" rel="noopener noreferrer">' . esc_html( $icp_text ) . '</a>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}
