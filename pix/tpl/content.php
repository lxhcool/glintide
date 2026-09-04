<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */
$lazy = THEME_URL .'/img/lazyload.png';
?>

<div id="post-<?php the_ID(); ?>" <?php post_class('loop_content p_item uk-animation-slide-bottom-small'); ?>>
	<div class="normal_item_inner">
		<div class="normal_content">
			<div class="entry-content">
				<?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
				<div class="entry_meta">
					<span class="nickname">@<?php the_author(); ?></span> - <time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
				</div>
				<p><?php echo mb_strimwidth(strip_shortcodes(strip_tags(apply_filters('the_content', $post->post_content))), 0, 120,"...");?></p>
				<div class="feature round12">
					<a href="<?php the_permalink(); ?>">
						<img class="round12 shadow lazy" src="<?php echo $lazy  ?>" data-src="<?php echo cst_get_thum( get_the_ID(), 'large'); ?>" alt="">
					</a>
				</div>
			</div><!-- .entry-content -->

			<div class="entry-footer">
				<div class="post_footer_meta">
					<div class="left">
						<span class="post_views"><i class="ri-eye-line"></i><?php get_post_views($post -> ID); ?></span>
						<span class="comnum"><i class="ri-chat-4-line"></i><small><?php echo get_comments_number(); ?></small></span>
						<?php echo get_like_btn(); ?>
					</div>

					<div class="right">
                            <?php if(is_sticky()){ echo '<span class="sticky_icon"><i class="ri-fire-line"></i> TOP</span>';} ?>
                            <div class="normal_cat"># <?php echo get_post_first_cat_link(); ?></div>
                        </div>
				</div>
				
			</div><!-- .entry-footer -->
		</div>
	</div>
</div><!-- #post-<?php the_ID(); ?> -->
