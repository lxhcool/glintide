<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

?>

<div class="pix-user-home-post-wrap">
<article id="post-<?php the_ID(); ?>" <?php post_class('post-item post-grid pix-user-home-post-card pix-user-home-post-enter'); ?>>
	<div class="entry-header pix-user-home-post-header">
		<div class="post-feature pix-user-home-post-feature">
			<a class="pix-user-home-post-thumb-link" href="<?php the_permalink(); ?>">
				<img class="post-thum pix-user-home-post-thumb lazy" data-src="<?php echo get_ppo_thum( get_the_ID(), 'large','random'); ?>" alt="">
			</a>
		</div>
	</div><!-- .entry-header -->

	<div class="entry-content pix-user-home-post-content">
		<div class="post-title pix-user-home-post-title">
			<?php the_title( '<h2 class="entry-title pix-user-home-post-title-heading"><a class="pix-user-home-post-title-link" href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
		</div>
		<div class="time-meta pix-user-home-post-time">
			<span class="nickname">@<?php the_author(); ?></span> - <time class="timeago" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
		</div>
		<div class="post-content">
		</div>
		
	</div><!-- .entry-content -->

	<div class="entry-footer pix-user-home-post-footer">
		<div class="post-f-meta pix-user-home-post-meta">
			<div class="left post-social-data pix-user-home-post-social"><?php echo show_post_meta('pix-user-home-post-data', 'pix-user-home-post-data-item'); ?></div>
			<div class="right post-cats pix-user-home-post-cats"><div class="pf-cat pix-user-home-post-cat"><i class="ri-hashtag"></i><?php echo get_post_first_cat(); ?></div></div>
		</div>
	</div><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
</div>
