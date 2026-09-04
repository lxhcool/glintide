<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */

$loca = get_post_meta(get_the_ID(), 'mylocal',true);
$post_ID = get_the_ID();
$cat = wp_get_post_terms($post_ID,'moments');
$cat = $cat [0]->name; //输出分类 id

?>


<div id="post-<?php the_ID(); ?>" <?php post_class('loop_content p_item moment_item'); ?>>
	<div class="search_moment_inner search_item">
        <div class="left">
            <div class="icon_mark"><i class="ri-message-3-line"></i></div>
        </div>
        <div class="right">
            <div class="top">
                <div class="cat"># <?php echo $cat ?></div>
                <time itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.timeago( get_gmt_from_date(get_the_time('Y-m-d G:i:s')) ); ?></time>
            </div>
            <div class="bottom">
                <a href="<?php echo get_permalink();?>"><?php echo mb_strimwidth(strip_shortcodes(strip_tags(apply_filters('the_content', $post->post_content))), 0, 80,"...");?></a>
            </div>
        </div>
        
    </div>
</div><!-- #post-<?php the_ID(); ?> -->
