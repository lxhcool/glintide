<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package pix
 */

get_header();
$nav_h = cls_nav_height();
$sidebar = pix_sidebar();
$show_post_toc = function_exists('pix_post_toc_has_headings')
    && pix_post_toc_has_headings(get_post_field('post_content', get_queried_object_id()));
$post_right_has_widgets = is_active_sidebar('post-right');
if ($show_post_toc) {
    $sidebar['right'] = true;
}
$layout_classes = array('pix-single-layout');
if ($sidebar['left']) {
    $layout_classes[] = 'pix-single-layout--has-left';
}
if ($sidebar['right']) {
    $layout_classes[] = 'pix-single-layout--has-right';
}
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-single">
    <div class="<?php echo esc_attr(implode(' ', $layout_classes)); ?>">

        <?php if ($sidebar['left']): ?>
            <aside class="left left-widget pix-single-sidebar pix-single-sidebar-left" aria-label="文章左侧栏">
                <div class="widget_inner post_left_inner pix-single-widget-stack">
                    <?php dynamic_sidebar( 'post-left' ); ?>
                </div>
            </aside>
        <?php endif; ?>

        <div class="center-content pix-single-main<?php
            if (!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
        ?>">
            <div id="primary" class="site-main">

                <div class="single-header pix-single-header">
                    <div class="single-banner pix-single-banner">
                        <img class="post-thum pix-single-banner-img" src="<?php echo esc_url(get_ppo_thum( get_the_ID(), 'large','random')); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                    </div>
                </div>

                <div class="single-wrap pix-single-wrap">
                    <?php
                    while ( have_posts() ) :
                        the_post();

                        get_template_part( 'tpl/content', 'single' );

                        if ( comments_open() || get_comments_number() ) :
                            comments_template();
                        endif;

                    endwhile;
                    ?>
                </div>

            </div><!-- #main -->
        </div>

        <?php if ($sidebar['right']): ?>
            <aside class="right right-widget pix-single-sidebar pix-single-sidebar-right<?php echo $post_right_has_widgets ? ' pix-single-sidebar-right--has-widgets' : ''; ?>" aria-label="文章右侧栏">
                <div class="widget_inner post_right_inner pix-single-widget-stack">
                    <?php
                    if ($show_post_toc && function_exists('pix_post_toc_render')) {
                        echo pix_post_toc_render();
                    }

                    if ($post_right_has_widgets) {
                        dynamic_sidebar( 'post-right' );
                    }
                    ?>
                </div>
            </aside>
        <?php endif; ?>

    </div>
</div>
<?php get_footer(); ?>
