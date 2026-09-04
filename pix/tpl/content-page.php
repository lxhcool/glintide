<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<div class="left">
			<i class="ri-focus-2-line"></i>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</div>

		<div class="right">
			<time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
			<?php echo get_like_btn(); ?>
		</div>
	</header><!-- .entry-header -->

	<div class="entry-content single-content">
		<?php
		the_content();
		?>
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
