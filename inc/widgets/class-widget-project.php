<?php
/**
 * Configurable project showcase widget.
 *
 * @package glintide
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Glintide_Widget_Project extends Glintide_Widget {
	public static $id = 'glintide_project_widget';
	public static $title = 'Glintide 项目展示';
	public static $description = '展示一个项目的截图、说明、状态及演示和源码链接，可重复添加。';
	public static $classname = 'glintide-project-widget-wrap';

	public static function fields() {
		return array(
			array( 'id' => 'project_name', 'type' => 'text', 'title' => '项目名称' ),
			array(
				'id' => 'project_image', 'type' => 'upload', 'title' => '项目截图',
				'library' => 'image', 'preview' => true,
				'button_title' => '选择截图', 'remove_title' => '移除截图',
				'desc' => '可选，建议使用横向截图，前台完整展示，不裁切。',
			),
			array( 'id' => 'project_description', 'type' => 'textarea', 'title' => '简短描述', 'desc' => '建议填写一两句话。' ),
			array(
				'id' => 'project_status', 'type' => 'button_set', 'title' => '项目状态',
				'options' => array( '' => '不显示', 'development' => '开发中', 'released' => '已发布', 'paused' => '已暂停' ),
				'default' => '',
			),
			array( 'id' => 'project_demo', 'type' => 'text', 'title' => '演示地址', 'desc' => '可选，仅支持 http / https 链接。' ),
			array( 'id' => 'project_source', 'type' => 'text', 'title' => '源码地址', 'desc' => '可选，可以填写 GitHub、GitLab 等仓库地址。' ),
		);
	}

	public static function render( $instance ) {
		$instance = is_array( $instance ) ? $instance : array();
		$values = array();
		foreach ( array( 'project_name', 'project_image', 'project_description', 'project_status', 'project_demo', 'project_source' ) as $key ) {
			$values[ $key ] = isset( $instance[ $key ] ) && is_string( $instance[ $key ] ) ? trim( $instance[ $key ] ) : '';
		}
		$name = sanitize_text_field( $values['project_name'] );
		if ( '' === $name ) {
			return glintide_widget_notice( '请填写项目名称' );
		}
		$image = esc_url_raw( $values['project_image'], array( 'http', 'https' ) );
		$description = sanitize_textarea_field( $values['project_description'] );
		$statuses = array( 'development' => '开发中', 'released' => '已发布', 'paused' => '已暂停' );
		$status = isset( $statuses[ $values['project_status'] ] ) ? $values['project_status'] : '';
		$media = $image ? '<div class="glintide-project__media"><img src="' . esc_url( $image ) . '" alt="' . esc_attr( $name . '项目截图' ) . '" loading="lazy" decoding="async"></div>' : '';
		$html = '<article class="glintide-project" aria-label="' . esc_attr( $name ) . '">' . $media;
		$html .= '<header class="glintide-project__body"><div class="glintide-project__heading"><h3 class="glintide-project__name">' . esc_html( $name ) . '</h3>';
		if ( $status ) {
			$html .= '<span class="glintide-project__status glintide-project__status--' . esc_attr( $status ) . '">' . esc_html( $statuses[ $status ] ) . '</span>';
		}
		$html .= '</div>';
		$links = '';
		foreach ( array( 'project_demo' => array( 'ri-external-link-line', '演示' ), 'project_source' => array( 'ri-code-s-slash-line', '源码' ) ) as $key => $link ) {
			$url = esc_url_raw( $values[ $key ], array( 'http', 'https' ) );
			if ( $url ) {
				$links .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" data-glintide-no-pjax title="' . esc_attr( $link[1] . '（新窗口打开）' ) . '" aria-label="' . esc_attr( $name . '：' . $link[1] . '（新窗口打开）' ) . '"><i class="' . esc_attr( $link[0] ) . '" aria-hidden="true"></i><span>' . esc_html( $link[1] ) . '</span></a>';
			}
		}
		if ( $links ) {
			$html .= '<div class="glintide-project__links">' . $links . '</div>';
		}
		$html .= '</header>';
		if ( $description ) {
			$html .= '<p class="glintide-project__description">' . esc_html( $description ) . '</p>';
		}
		return $html . '</article>';
	}
}

function glintide_project_widget( $args, $instance ) {
	echo $args['before_widget'];
	echo Glintide_Widget_Project::render( $instance );
	echo $args['after_widget'];
}
