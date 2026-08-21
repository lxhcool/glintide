<?php
/**
 * Template part for displaying moments
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package pix
 */
$author_id = get_the_author_meta( 'ID' ); 
$url = get_author_posts_url($author_id);
$avatar = get_u_avatar($author_id,$type = 'url');
$type = !empty($args) ? $args['type'] : false;
$badges = function_exists('ppo_user_badges_html') ? ppo_user_badges_html($author_id, 'moment') : '';
?>

<div id="post-<?php the_ID(); ?>" <?php post_class('post-item moment_item pix-moment-card pix-moment-single-card pix-moment-card-enter single-detail'); ?>>
    <div class="moment-single-inner pix-moment-card-inner pix-moment-single-inner">

        <div class="moment-avatar pix-moment-card-avatar pix-moment-single-avatar">
                <a href="<?php echo $url; ?>" target="_blank">
                    <img class="mo-ava lazy" data-src="<?php echo $avatar; ?>" alt="">
                </a>
        </div><!-- .entry-header -->

        <div class="moment-content-right pix-moment-card-main pix-moment-single-main">
            <div class="mo-meta pix-moment-card-header pix-moment-single-header">
                <div lcass="left">
                    <div class="nickname"><a href="<?php echo $url; ?>" target="_blank"><?php the_author(); ?></a><?php echo $badges; ?></div>
                    <div class="lv-info"></div>
                </div>
                <div class="right">
                    <?php echo get_moment_first_cat($post->ID); ?>
                    
                </div>
            </div>

            <div class="mosub-meta pix-moment-card-submeta pix-moment-single-submeta">
                <time class="timeago pix-moment-card-time pix-moment-single-time" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
            </div>

            <div class="post-content mos-content pix-moment-card-content pix-moment-single-content">
                <div class="momoent_title single pix-moment-card-title pix-moment-single-title"><?php the_title(); ?></div>
                <div class="ppo-rich-text pix-moment-card-text pix-moment-single-text">
                <div class="ppo-rich-text-content">
                    <p><?php echo ppo_moment_filter(); ?></p>
                </div>
            </div>
                <?php echo get_moment_type_content(); ?>
                <?php echo get_moment_tag_link($post->ID); ?>
            </div>

            
        </div><!-- .entry-content -->

    </div>

        <div class="moment-footer pix-moment-card-footer pix-moment-single-footer">
                <div class="left pix-moment-card-actions pix-moment-single-actions">
                    <div class="pix-moment-card-quick-actions">
                        <button type="button" class="pix-moment-card-quick-toggle" aria-label="快捷操作" aria-expanded="false"><i class="ri-more-line"></i></button>
                        <div class="pix-moment-card-quick-panel" role="menu">
                            <div class="post-like item pix-moment-card-action pix-moment-card-action-like" role="menuitem"><?php echo post_like_btn($post->ID); ?></div>
                            <div class="post-collect item pix-moment-card-action pix-moment-card-action-collect" role="menuitem"><?php echo post_collect_btn($post->ID); ?></div>
                            <div class="moment-share item pix-moment-card-action pix-moment-card-action-share" role="menuitem"><a class="poster-btn moment-share-btn pix-moment-card-share-btn" href="#poster-modal" data-pid="<?php echo esc_attr($post->ID); ?>" data-post-type="moment" aria-label="分享片刻"><i class="ri-share-forward-box-line"></i></a></div>
                        </div>
                    </div>
                    <?php echo mo_edit_btn($post->ID); ?>
                </div>
                <div class="right pix-moment-card-status">
                    <?php if(is_sticky()){ ?>
                        <span class="sticky_m_icon"><i class="ri-upload-line"></i>置顶</span>
                    <?php } ?>
                    <?php if(is_moment_hot($post->ID)){ ?>
                        <span class="hot_m_icon"><i class="ri-vip-diamond-line"></i>精华</span>
                    <?php } ?>
                </div>
            </div><!-- .entry-footer -->

    <div class="moment_comments_wrap pix-moment-single-comments t_com_<?php echo $post->ID; ?>" style="display:none">
				<?php
					global $withcomments;
					$withcomments = true;
					comments_template('inc/layouts/comment-tmp.php'); 				
				?>
			</div>

</div><!-- #post-<?php the_ID(); ?> -->
