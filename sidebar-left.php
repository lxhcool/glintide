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

<aside id="secondary-left" class="widget-area pix-home-sidebar pix-home-sidebar-left">
	<div class="widget_inner pix-home-widget-stack">
		<?php dynamic_sidebar( 'sidebar-left' ); ?>
	</div>
</aside>