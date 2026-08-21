<?php
/**
 * The template for displaying all pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

get_header();
$nav_h = cls_nav_height();
$sidebar = pix_sidebar();
?>

<div class="pix-content home-box home-classic">
    <div class="pix-page-layout">

        <?php if ($sidebar['left']): ?>
            <div class="left left-widget">
                <div class="widget_inner page_left_inner">
                    <?php dynamic_sidebar( 'page-left' ); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="center-content<?php
            if (pix_is_fullwidth()) echo ' full-width';
            elseif (!$sidebar['left'] && !$sidebar['right']) echo ' pix-width-expand';
            elseif (!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
        ?>">
            <div id="primary" class="site-main">

                <div class="single-header">
                    <div class="single-banner">
                        <img class="post-thum lazy" data-src="<?php echo get_ppo_thum( get_the_ID(), 'large','random'); ?>" alt="">
                    </div>
                </div>

                <div class="single-wrap">
                    <?php
                    while ( have_posts() ) :
                        the_post();

                        get_template_part( 'tpl/content', 'page' );

                        if ( comments_open() || get_comments_number() ) :
                            comments_template();
                        endif;

                    endwhile;
                    ?>
                </div>

            </div><!-- #main -->
        </div>

        <?php if ($sidebar['right']): ?>
            <div class="right left-widget">
                <div class="widget_inner page_right_inner">
                    <?php dynamic_sidebar( 'page-right' ); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php get_footer(); ?>
