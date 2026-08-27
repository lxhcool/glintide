<?php
/**
 * 左栏侧边栏
 *
 * @package glintide
 */

if ( ! is_active_sidebar( 'sidebar-left' ) ) {
	return;
}
?>

<aside id="secondary-left" class="widget-area glintide-home-sidebar glintide-home-sidebar-left">
	<div class="widget_inner glintide-home-widget-stack">
		<?php dynamic_sidebar( 'sidebar-left' ); ?>
	</div>
</aside>