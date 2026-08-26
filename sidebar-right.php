<?php
/**
 * 右栏侧边栏
 *
 * @package glintide
 */

if ( ! is_active_sidebar( 'sidebar-right' ) ) {
	return;
}
?>

<aside id="secondary-right" class="widget-area glintide-home-sidebar glintide-home-sidebar-right">
	<div class="widget_inner glintide-home-widget-stack">
		<?php dynamic_sidebar( 'sidebar-right' ); ?>
	</div>
</aside>