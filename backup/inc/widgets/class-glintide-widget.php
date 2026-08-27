<?php
/**
 * Glintide 小工具抽象基类(策略模式)
 *
 * 每个小工具继承此类,实现 fields() 与 render(),
 * 由 Glintide_Widgets 管理器自动扫描注册。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

abstract class Glintide_Widget {

	/**
	 * 小工具 ID(CSF 注册 ID,同时是前端渲染函数名)
	 *
	 * @var string
	 */
	public static $id = '';

	/**
	 * 小工具名称
	 *
	 * @var string
	 */
	public static $title = '';

	/**
	 * 小工具描述
	 *
	 * @var string
	 */
	public static $description = '';

	/**
	 * 小工具 CSS 类名
	 *
	 * @var string
	 */
	public static $classname = '';

	/**
	 * 后台配置字段(CSF fields 数组)
	 *
	 * @return array
	 */
	public static function fields() {
		return array();
	}

	/**
	 * 前端渲染
	 *
	 * @param array $instance 小工具实例配置
	 * @return string HTML
	 */
	public static function render( $instance ) {
		return '';
	}

	/**
	 * 注册到 Codestar Framework
	 */
	public static function register() {
		if ( ! class_exists( 'CSF' ) ) {
			return;
		}

		CSF::createWidget(
			static::$id,
			array(
				'title'       => static::$title,
				'classname'   => static::$classname,
				'description' => static::$description,
				'fields'      => static::fields(),
			)
		);
	}
}

/* ================================================================
 * 公共辅助函数
 * ================================================================ */

/**
 * 小工具标题
 */
function glintide_widget_title( $title ) {
	$title = is_scalar( $title ) ? trim( (string) $title ) : '';

	return $title !== '' ? '<div class="wid_title">' . esc_html( $title ) . '</div>' : '';
}

/**
 * 小工具提示
 */
function glintide_widget_notice( $text ) {
	return '<div class="glintide-widget-notice wid-item"><i class="ri-information-line"></i><span>' . esc_html( $text ) . '</span></div>';
}

/**
 * 数字缩写(1.2k / 3.4M)
 */
function glintide_k_m_i( $num ) {
	if ( $num >= 1000000 ) {
		return round( $num / 1000000, 1 ) . 'M';
	} elseif ( $num >= 1000 ) {
		return round( $num / 1000, 1 ) . 'k';
	}
	return $num;
}

/**
 * 获取文章缩略图(特色图 → 首图 → 默认图)
 */
function glintide_get_thumb( $post_id ) {
	$thumb = get_the_post_thumbnail_url( $post_id, 'medium' );
	if ( $thumb ) {
		return $thumb;
	}

	$post = get_post( $post_id );
	if ( $post && preg_match( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches ) ) {
		return $matches[1];
	}

	return '';
}

/**
 * 用户头像(优先自定义头像,回退 Gravatar)
 */
function glintide_get_avatar_url( $user_id ) {
	$custom = get_user_meta( $user_id, 'custom_avatar', true );
	if ( $custom ) {
		return $custom;
	}

	$avatar = get_avatar_url( $user_id, array( 'size' => 96 ) );
	return $avatar ? $avatar : '';
}

/**
 * 站点 Logo(图片或文字)
 */
function glintide_site_logo_html() {
	$logo = glintide_get_option( 'site_logo', '' );
	if ( is_array( $logo ) ) {
		$logo = $logo['url'] ?? '';
	}

	if ( $logo ) {
		return '<img src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
	}

	$text_logo = glintide_get_option( 'logo_text', '' );
	$text_logo = $text_logo ? $text_logo : get_bloginfo( 'name' );

	return '<h3>' . esc_html( $text_logo ) . '</h3>';
}