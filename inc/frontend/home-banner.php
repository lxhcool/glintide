<?php
/**
 * Glintide 首页顶部 Banner
 *
 * 使用首页设置中的封面图和封面内容，不改变后台字段结构。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function glintide_home_banner_images() {
	$type   = glintide_get_option( 'cls_banner_type', 'upload' );
	$images = array();

	if ( 'link' === $type ) {
		$links = glintide_get_option( 'cls_banner_link', '' );
		$links = preg_split( '/\r\n|\r|\n/', (string) $links );
		foreach ( $links as $link ) {
			$link = trim( $link );
			if ( $link ) {
				$images[] = $link;
			}
		}
	} else {
		$upload = glintide_get_option( 'cls_banner_upload', array() );
		if ( is_array( $upload ) ) {
			$images = $upload;
		} elseif ( is_string( $upload ) && $upload ) {
			$images = array_filter( array_map( 'trim', explode( ',', $upload ) ) );
		}
	}

	$urls = array();
	foreach ( $images as $image ) {
		if ( is_array( $image ) ) {
			$image = $image['url'] ?? ( $image['id'] ?? '' );
		}

		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( (int) $image, 'full' );
		}

		$image = is_string( $image ) ? esc_url_raw( $image ) : '';
		if ( $image ) {
			$urls[] = $image;
		}
	}

	return $urls;
}

function glintide_home_banner_image_url() {
	$images = glintide_home_banner_images();
	if ( ! empty( $images ) ) {
		return $images[ array_rand( $images ) ];
	}

	return GLINTIDE_URL . '/assets/images/banner.jpg';
}

function glintide_home_banner_user() {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$admin_user = get_user_by( 'email', get_option( 'admin_email' ) );
		$user_id    = $admin_user ? (int) $admin_user->ID : 0;
	}

	return $user_id ? get_userdata( $user_id ) : false;
}

function glintide_home_banner_info() {
	$content_type = glintide_get_option( 'cls_banner_content', 'ava' );

	if ( 'text' === $content_type ) {
		$content = glintide_get_option( 'opt-wp-editor-2', '' );
		return $content ? '<div class="cls-banner-custom-content">' . wp_kses_post( $content ) . '</div>' : '';
	}

	$user = glintide_home_banner_user();
	if ( ! $user ) {
		return '';
	}

	$avatar = function_exists( 'glintide_get_avatar_url' ) ? glintide_get_avatar_url( $user->ID ) : glintide_get_default_avatar_url();
	$name   = $user->display_name ? $user->display_name : get_bloginfo( 'name' );
	$desc   = $user->description ? $user->description : 'TA很懒，什么也没写';
	$default_avatar = function_exists( 'glintide_get_default_avatar_url' ) ? glintide_get_default_avatar_url() : GLINTIDE_URL . '/assets/images/default-avatar.png';

	$html  = '<div class="cls-banner-info">';
	$html .= '<div class="info">';
	$html .= '<div class="name">' . esc_html( $name ) . '</div>';
	$html .= '<div class="des">' . esc_html( $desc ) . '</div>';
	$html .= '</div>';
	$html .= '<div class="ava">';
	$html .= '<img src="' . esc_url( $avatar ) . '" alt="' . esc_attr( $name ) . '" data-glintide-avatar data-glintide-avatar-fallback="' . esc_url( $default_avatar ) . '">';
	$html .= '</div>';
	$html .= '</div>';

	return $html;
}

function glintide_render_home_banner() {
	$background = 'background-image: url("' . esc_url( glintide_home_banner_image_url() ) . '");';
	$html       = '<div class="cls-banner" style="' . esc_attr( $background ) . '">';
	$html      .= glintide_home_banner_info();
	$html      .= '</div>';

	return $html;
}
