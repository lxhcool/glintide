<?php
/**
 * Template part for displaying results in search pages
 *
 * @package pix
 */

$post_id = get_the_ID();
$post_type_obj = get_post_type_object(get_post_type());
$post_type_label = $post_type_obj && !empty($post_type_obj->labels->singular_name) ? $post_type_obj->labels->singular_name : '内容';
$thumb = function_exists('get_ppo_thum') ? get_ppo_thum($post_id, 'large', 'random') : get_the_post_thumbnail_url($post_id, 'large');
$thumb = $thumb ? $thumb : THEME_URL . '/img/thumbnail.png';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('pix-search-result-card'); ?>>
    <a class="pix-search-result-thumb" href="<?php the_permalink(); ?>">
        <img class="lazy" data-src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
        <span><?php echo esc_html($post_type_label); ?></span>
    </a>

    <div class="pix-search-result-content">
        <?php the_title('<h2><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>'); ?>
        <div class="pix-search-result-meta">
            <span><i class="ri-time-line"></i><?php echo esc_html(get_the_date('Y-m-d')); ?></span>
            <span><i class="ri-user-3-line"></i><?php the_author(); ?></span>
        </div>
        <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 34, '...')); ?></p>
        <a class="pix-search-result-link" href="<?php the_permalink(); ?>">查看详情 <i class="ri-arrow-right-line"></i></a>
    </div>
</article>
