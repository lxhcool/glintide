<?php
/**
 * Glintide 小工具管理器(插拔式)
 *
 * 自动扫描 inc/widgets/ 目录下的 class-widget-*.php,
 * 加载并注册所有继承 Glintide_Widget 的小工具。
 * 新增小工具 = 新建一个类文件,删除 = 删除文件。
 *
 * @package glintide
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widgets {

	/**
	 * 已注册的小工具列表
	 *
	 * @var array
	 */
	protected static $widgets = array();

	/**
	 * 初始化:加载基类 + 扫描目录 + 注册
	 */
	public static function init() {
		require_once GLINTIDE_DIR . '/inc/widgets/class-glintide-widget.php';

		// 扫描小工具目录
		$files = glob( GLINTIDE_DIR . '/inc/widgets/class-widget-*.php' );
		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			require_once $file;
		}

		// 注册所有子类
		foreach ( get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, 'Glintide_Widget' ) ) {
				$class::register();
				self::$widgets[ $class::$id ] = $class;
			}
		}
	}

	/**
	 * 获取全部小工具
	 *
	 * @return array
	 */
	public static function get_all() {
		return self::$widgets;
	}

	/**
	 * 获取单个小工具类名
	 *
	 * @param string $id 小工具 ID
	 * @return string|false
	 */
	public static function get( $id ) {
		return self::$widgets[ $id ] ?? false;
	}
}