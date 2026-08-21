<?php
/**
 * The template for displaying all single moment
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package pix
 */

get_header();
$nav_h = cls_nav_height();
$sidebar = pix_sidebar();
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-moment pix-moment-single-page">
	<div class="pix-moment-shell">

	<?php if($sidebar['left']){ ?>
        <div class="left left-widget pix-moment-sidebar pix-moment-sidebar-left">
            <div class="widget_inner moment_left_inner">
                <?php dynamic_sidebar( 'moment-left' ); ?>  
            </div>	
        </div>			
    <?php } ?>

	<div class="center-content pix-moment-center<?php
        if(!$sidebar['left'] && !$sidebar['right']) echo ' pix-moment-center-expand';
        elseif(!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
    ?>">
		<div id="primary" class="site-main pix-moment-main">

		<div class="cls-banner cls-banner-moment">
                <?php echo Classic_mod::banner_info(); ?>
                <img src="<?php echo esc_url(Classic_mod::banner_image()); ?>" loading="lazy" decoding="async" alt="">
            </div>

		<div class="single-wrap pix-moment-single-wrap">
			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'tpl/single','moment');


				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>
		</div>

		</div><!-- #main -->
	</div>	

	<?php if($sidebar['right']){ ?>
        <div class="right right-widget pix-moment-sidebar pix-moment-sidebar-right">
            <div class="widget_inner moment_right_inner">
                <?php dynamic_sidebar( 'moment-right' ); ?>
            </div>	
        </div>			
    <?php } ?>	

	</div>
</div>
<?php
get_footer();
