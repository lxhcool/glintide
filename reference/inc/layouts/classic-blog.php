<?php
/**
 * PIX经典首页 - 博客
 *
 */

$nav_h = cls_nav_height();
$sidebar = pix_sidebar();
$layout_classes = array('pix-home-layout');
if ($sidebar['left']) {
    $layout_classes[] = 'pix-home-layout--has-left';
}
if ($sidebar['right']) {
    $layout_classes[] = 'pix-home-layout--has-right';
}

?>

<div class="<?php echo esc_attr(implode(' ', $layout_classes)); ?>">

    <?php if ($sidebar['left']): ?>
        <aside class="left left-widget pix-home-sidebar pix-home-sidebar-left" aria-label="博客左侧栏">
            
            <div class="widget_inner blog_left_inner pix-home-widget-stack">
                <?php dynamic_sidebar( 'blog-left' ); ?>        
            </div>
        </aside>
    <?php endif; ?>

    <div class="center-content pix-home-main<?php
        if (!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
    ?>">
        <div id="primary" class="site-main">

            <div class="cls-banner">
                <?php echo Classic_mod::banner_info(); ?>
                <img src="<?php echo esc_url(Classic_mod::banner_image()); ?>" loading="lazy" decoding="async" alt="">
            </div>

            <div class="home-blog-content cls-content pix-home-blog-content">
                <?php get_template_part('inc/layouts/cls', 'blog'); ?>
            </div>

        </div><!-- #main -->
    </div>

    <?php if ($sidebar['right']): ?>
        <aside class="right right-widget pix-home-sidebar pix-home-sidebar-right" aria-label="博客右侧栏">
            <div class="widget_inner blog_right_inner pix-home-widget-stack">
                <?php dynamic_sidebar( 'blog-right' ); ?>
            </div>
        </aside>
    <?php endif; ?>

</div>
