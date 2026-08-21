<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post-item pix-home-post-card pix-home-post-enter'); ?>>
	<div class="entry-header pix-home-post-header">
		<div class="post-feature pix-home-post-feature">
			<a class="pix-home-post-thumb-link" href="<?php the_permalink(); ?>">
				<img class="post-thum pix-home-post-thumb" src="<?php echo esc_url(get_ppo_thum( get_the_ID(), 'large','random')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
			</a>
		</div>
	</div><!-- .entry-header -->

	<div class="entry-content pix-home-post-content">
		<div class="pix-home-post-copy">
			<div class="post-title pix-home-post-title">
				<?php the_title( '<h2 class="entry-title pix-home-post-title-heading"><a class="pix-home-post-title-link" href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
			</div>
			<div class="time-meta pix-home-post-time">
				<span class="nickname">@<?php the_author(); ?></span><span class="pix-home-post-time-separator">-</span><time class="timeago" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
			</div>
			<div class="post-content pix-home-post-excerpt">
				<p><?php echo mb_strimwidth(strip_shortcodes(strip_tags(apply_filters('the_content', $post->post_content))), 0, 120,"...");?></p>
			</div>
		</div>
		
	</div><!-- .entry-content -->

	<div class="entry-footer pix-home-post-footer">
		<div class="post-f-meta pix-home-post-meta">
			<div class="left post-social-data"><?php echo show_post_meta(); ?></div>
			<div class="right post-cats"><div class="pf-cat"><i class="ri-hashtag"></i><?php echo get_post_first_cat(); ?></div></div>
		</div>
	</div><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
