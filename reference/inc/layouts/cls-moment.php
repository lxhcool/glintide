<?php
    $user_id = get_current_user_id();
    $catid = isset($args['catid']) ? absint($args['catid']) : 0;
    $tagid = isset($args['tagid']) ? absint($args['tagid']) : 0;
    $hide_moments = get_option('moments_hide');
    $hide_moments = $hide_moments ? $hide_moments : '';
    $merge_hide = get_mo_hide_unjoin($user_id);
    $show_nav = true;
    $nav_type = get_op('moment_nav','btn');
?>
<div class="cls-blog-cat-filter" data="<?php echo $catid ?>">
    <?php //echo post_cat_filter(); ?>
</div>


<div id="moment-item" class="p-item box-p pix-moment-list pix-moment-grid">

    <?php

        // 基础数组
        $sticky = get_option('sticky_posts');

        $mo_args = array(
            'post_type' => 'moment', 
            'post_status' => 'publish',
            'tax_query' => array(
                'relation' => 'AND',
            ),
            );
        
        // 如果是片刻首页 term_id为0    
       if($catid == 0 && $tagid == 0){
            echo moment_sticky_loop();
            $mo_args['post__not_in'] = $sticky;
            
            if(!empty($merge_hide)){
                array_push( $mo_args['tax_query'], array (
                    'taxonomy' => 'moments',
                    'field' => 'term_id',
                    'terms' => $merge_hide,
                    'operator' => 'NOT IN',
                ));
            } else {
                $mo_args['tax_query'] = null;
            }

        }

        // 为话题归档页面时
        if($tagid > 0){
            array_push( $mo_args['tax_query'], array (
                'taxonomy' => 'moment_tag',
                'field' => 'term_id',
                'terms' => $tagid,
            ));
        }

        // 为圈子分类页面时
        if($catid != 0 && $catid > 0){
            array_push( $mo_args['tax_query'], array (
                'taxonomy' => 'moments',
                'field' => 'term_id',
                'terms' => $catid,
            ));

            $view_state = pix_moment_apply_term_view_limit($mo_args, $catid, $user_id);
            if($view_state === 'preview'){
                echo '<div class="join-tips"><i class="ri-search-eye-line"></i>当前只可查看部分内容</div>';
                $show_nav = false;
            } elseif($view_state === 'blocked'){
                echo '<div class="join-tips"><i class="ri-lock-line"></i>加入圈子后可查看内容</div>';
                $show_nav = false;
            }

        }

    

        $my_query = new WP_Query($mo_args);

      
        
        if( $my_query->have_posts() ) {
            
            while ($my_query->have_posts()) : $my_query->the_post();

            get_template_part( 'tpl/content','moment');

            endwhile; 
            wp_reset_postdata(); 
        } else {
            echo '<div class="no-moment"><img src="'.THEME_URL.'/img/empty.png"></div>';
        }
      
        //print_r($my_query);
        $max = $my_query->max_num_pages;
        $cat = get_cu('cls_show_cats_de','0');
    ?>

</div>

<?php if($nav_type == 'btn' || $nav_type == 'scroll') { ?>
<div class="pagenav-box pix-moment-pagenav">
    <?php if ( $my_query->max_num_pages > 1 && $show_nav){ ?>
        <div class="number-pager pix-moment-page-counter">
            <div class="pager-info pix-moment-page-info">
                <div class="paged-number pix-moment-page-number">当前：<span class="current">1</span> / <span class="total"><?php echo $max ?></span></div>
                <div class="go-top pix-moment-page-top"><a class="pix-tooltip pix-moment-scroll-top" href="#page" data-pix-tooltip="回到顶部" aria-label="回到顶部"><i class="ri-arrow-up-line"></i></a></div>
            </div>
        </div>
    
        <div class="load-more-box pix-moment-loadmore-box">
            <div class="loadmore-btn pix-moment-loadmore-btn" data-paged="1" data-action="cls_load_moments" data-append="#moment-item" data-max="<?php echo $max ?>" data-cat="<?php echo $catid ?>" data-tag="<?php echo $tagid ?>">加载更多</div>
        </div>   
    <?php } ?>  
</div>
<?php } else { ?>

<div class="pagination-box pix-moment-pagination" data-action="cls_load_moments" data-cat="<?php echo $catid ?>" data-tag="<?php echo $tagid ?>" data-max="<?php echo $max ?>" data-append="#moment-item" >
<?php if($show_nav){
    $paged = max(1, get_query_var('paged'));
    echo paginate_links(array(
        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format' => '?paged=%#%',
        'current' => $paged,
        'total' => $max,
        'end_size' => 1,
        'mid_size' => 1,
        'prev_text' => '上一页',
        'next_text' => '下一页',
        'type' => 'list'
    ));
}
    ?>
</div>
<?php } ?>

<div id="comment_form_tmp"><?php get_template_part( 'inc/layouts/moment-comment'); ?></div>
