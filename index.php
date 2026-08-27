<?php
/**
 * Glintide 最小首页模板
 *
 * @package glintide
 */

get_header();
?>

<main id="primary" class="glintide-content">
	<div class="glintide-site-layout">
		<aside class="glintide-site-column glintide-site-column--left" aria-label="左侧栏">
			<?php echo glintide_render_left_navigation(); ?>
		</aside>
		<section class="glintide-site-column glintide-site-column--center" aria-label="主要内容"></section>
		<aside class="glintide-site-column glintide-site-column--right" aria-label="右侧栏">
			<?php echo glintide_render_right_tools(); ?>
		</aside>
	</div>
</main>

<?php get_footer(); ?>
