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
$moment_title = get_the_title();
$has_moment_title = trim(wp_strip_all_tags((string) $moment_title)) !== '';
?>

<div id="post-<?php the_ID(); ?>" <?php post_class('post-item moment_item pix-moment-card pix-moment-card-enter'); ?>>
    <div class="moment-single-inner pix-moment-card-inner">

        <div class="moment-avatar pix-moment-card-avatar">
                <a href="<?php echo $url; ?>" target="_blank">
                    <img class="mo-ava lazy" data-src="<?php echo $avatar; ?>" alt="">
                </a>
        </div><!-- .entry-header -->

        <div class="moment-content-right pix-moment-card-main">
            <div class="mo-meta pix-moment-card-header">
                <div lcass="left">
                    <div class="nickname"><a href="<?php echo $url; ?>" target="_blank"><?php the_author(); ?></a><?php echo $badges; ?></div>
                    <div class="lv-info"></div>
                </div>
                <div class="right">
                    <?php echo get_moment_first_cat($post->ID); ?>
                    
                </div>
            </div>

            <div class="mosub-meta pix-moment-card-submeta">
                <time class="timeago pix-moment-card-time" itemprop="datePublished" datetime="<?php echo get_the_date('c');?>"><?php echo ''.get_gmt_from_date(get_the_time('Y-m-d G:i:s')); ?></time>
            </div>

            <div class="post-content mos-content pix-moment-card-content<?php echo $has_moment_title ? ' has-title' : ' no-title'; ?>">
                <?php if($has_moment_title){ ?>
                <div class="momoent_title pix-moment-card-title"><a href="<?php echo get_permalink();?>"><?php echo esc_html($moment_title); ?></a></div>
                <?php } ?>
                <div class="ppo-rich-text pix-moment-card-text">
                <div class="ppo-rich-text-content" data-max-length="100">
                    <p><?php echo ppo_moment_filter(); ?></p>
                </div>
            </div>
                <?php echo get_moment_type_content(); ?>
                <?php echo get_moment_tag_link($post->ID); ?>
            </div>

            
        </div><!-- .entry-content -->

    
    </div>


    <?php if($type != 'pending'){ ?>

        <div class="moment-footer pix-moment-card-footer">
                <div class="left pix-moment-card-actions">
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
                    <a class="moment-comment-btn pix-moment-card-comment-btn" pid="<?php echo get_the_ID(); ?>"><i class="ri-message-3-line"></i><span class="comment-count pix-moment-card-comment-count"><?php echo get_comments_number(); ?></span></a>
                </div>
            </div><!-- .entry-footer -->

    <div class="moment_comments_wrap t_com_<?php echo $post->ID; ?>" style="display:none">
				<?php
					global $withcomments;
					$withcomments = true;
					comments_template('inc/layouts/comment-tmp.php'); 				
				?>
			</div>

    <?php } else { ?>

    <div class="moment-footer pending-footer pix-moment-card-footer pix-moment-card-pending-footer" pid="<?php echo $post->ID; ?>">
        <div class="left pix-moment-card-actions">
            <a href="<?php echo home_url('/moment-edit?pid='.$post->ID.''); ?>" target="_blank" class="mo-edit-page pix-moment-manage-action pix-moment-manage-action-primary"><i class="ri-edit-line"></i>重新编辑</a>
        </div>

        <div class="btn-group right pix-moment-card-status">
            <a class="mo-pending-allow pix-moment-manage-action pix-moment-manage-action-primary" action="allow">批准</a>
            <a class="mo-pending-remove pix-moment-manage-action pix-moment-manage-action-danger" action="remove">删除</a>
        </div>
    </div>

    <?php } ?>
    

</div><!-- #post-<?php the_ID(); ?> -->
