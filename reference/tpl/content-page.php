<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */
$like_count = get_post_meta(get_the_ID(), 'likes_count', true);
$like_count = !empty($like_count) ? $like_count : '0';
?>
<div id="post-<?php the_ID(); ?> post-single" <?php post_class('post-single'); ?>>

	<div class="single-title">
		<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
		<div class="single-top-meta">
			<div class="left">
				<time class="timeago item" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
				<div class="post-views item"><i class="ri-eye-line"></i><span class="number"><?php echo get_post_views (get_the_ID()); ?></span></div>
				<div class="post-likes item"><i class="ri-heart-3-line"></i><span class="number"><?php echo $like_count ?></span></div>
			</div>
			<div class="right">
			</div>
		</div>
	</div>

		<div class="single-inner"> 

			<div class="single-content">
				
				<?php the_content(); ?>

			</div><!-- .entry-content -->
	
			<div class="single-line"><span></span></div>

            <div class="single-footer"> 
                <div class="single_footer_box"> 
                    <?php echo post_footer_tool(); ?>
                </div>
            </div>

			<div class="prev-next">
				<?php echo prev_next_post(); ?>
			</div>

		</div>


</div><!-- #post-<?php the_ID(); ?> -->
