<?php
/**
 * Template part for displaying single
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

$like_count = get_post_meta(get_the_ID(), 'likes_count', true);
$like_count = !empty($like_count) ? $like_count : '0';
?>
<div id="post-<?php the_ID(); ?>" <?php post_class('post-single pix-single-post'); ?>>

	<div class="single-title pix-single-title">
		<?php the_title( '<h2 class="entry-title pix-single-entry-title">', '</h2>' ); ?>
		<div class="single-top-meta pix-single-top-meta">
			<div class="left pix-single-meta-list">
				<time class="timeago item pix-single-meta-item pix-single-time" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
				<div class="post-views item pix-single-meta-item"><i class="ri-eye-line"></i><span class="number"><?php echo get_post_views (get_the_ID()); ?></span></div>
				<div class="post-likes item pix-single-meta-item"><i class="ri-heart-3-line"></i><span class="number"><?php echo $like_count ?></span></div>
			</div>
			<div class="right pix-single-cat-wrap">
				<div class="psf-cat pix-single-cat"><i class="ri-hashtag"></i><?php echo get_post_first_cat(); ?></div>
			</div>
		</div>
	</div>

	<div class="single-inner pix-single-inner">

			<div class="single-content pix-single-content">
				
				<?php the_content(); ?>

			</div><!-- .entry-content -->
	
			<div class="single-line pix-single-line"><span></span></div>

            <div class="single-footer pix-single-footer"> 
                <div class="single_footer_box pix-single-footer-box"> 
                    <?php echo post_footer_tool(); ?>
                </div>
            </div>

			<div class="prev-next pix-single-prev-next">
				<?php echo prev_next_post(); ?>
			</div>

		</div>


</div><!-- #post-<?php the_ID(); ?> -->
