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

<aside id="secondary-right" class="widget-area pix-home-sidebar pix-home-sidebar-right">
	<div class="widget_inner pix-home-widget-stack">
		<?php dynamic_sidebar( 'sidebar-right' ); ?>
	</div>
</aside>