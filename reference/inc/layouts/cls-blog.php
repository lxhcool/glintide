<?php
$nav_type = get_op('post_nav','btn');

?>

<div class="cls-blog-cat-filter pix-home-filter">
    <?php echo post_cat_filter(); ?>
</div>


<div id="blog-item" class="p-item box-p pix-home-post-list" >

    <?php

        /* if ( have_posts() ) :

            while ( have_posts() ) :
            
                the_post();
                
                get_template_part( 'tpl/content' );
                
            endwhile;
            
        else :

            get_template_part( 'tpl/content', 'none' );

        endif; */

        $cat = get_cu('cls_show_cats_de','0');

        $args = array(
            'post_type' => 'post', 
            'post_status' => 'publish',
            //'cat' => $cat,
            //'paged' => 5
            );

        if($cat != '0'){
            $args['cat'] = $cat;
        }    
                 
        $my_query = new WP_Query($args);
        
        if( $my_query->have_posts() ) {

            while ($my_query->have_posts()) : $my_query->the_post();

            get_template_part( 'tpl/content');

            endwhile; 
            wp_reset_postdata(); 
        }
        
        //print_r($my_query);
        $max = $my_query->max_num_pages;
        $cat = get_cu('cls_show_cats_de','0');
    ?>

</div>

<?php if($nav_type == 'btn' || $nav_type == 'scroll') { ?>
<div class="pagenav-box pix-home-pagenav flex h-10 flex-row items-stretch justify-between">
    <div class="number-pager pix-home-page-counter h-[inherit] flex-1 rounded-lg bg-pix-primary-subtle px-5">
        <div class="pager-info pix-home-page-info flex h-full items-center justify-between">
            <div class="paged-number pix-home-page-number text-[13px] text-pix-primary-muted">当前：<span class="current">1</span> / <span class="total"><?php echo esc_html($max); ?></span></div>
            <div class="go-top pix-home-page-top flex"><a class="pix-tooltip flex items-center p-1.5 leading-none text-pix-primary-muted" href="#page" data-pix-tooltip="回到顶部" aria-label="回到顶部"><i class="ri-arrow-up-line"></i></a></div>
        </div>
    </div>
    <?php if ( $my_query->max_num_pages > 1 ){ ?>
        <div class="load-more-box pix-home-loadmore-box ml-2.5 h-[inherit] w-[100px]">
            <button type="button" class="loadmore-btn pix-home-loadmore-btn flex h-[inherit] w-full cursor-pointer items-center justify-center rounded-lg bg-pix-primary text-[13px] text-white" data-paged="1" data-action="cls_load_posts" data-append="#blog-item" data-max="<?php echo esc_attr($max); ?>" data-cat="<?php echo esc_attr($cat); ?>">加载更多</button>
        </div>   
    <?php } ?>
     
</div>

<?php } else { ?>

    <div class="pagination-box pix-home-pagination-box" data-action="cls_load_posts" data-cat="<?php echo $cat ?>" data-max="<?php echo $max ?>" data-append="#blog-item" > 
    <?php 
        $paged = max(1, get_query_var('paged'));
        echo paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '?paged=%#%',
            'current' => $paged,
            'total' => $max,
            'prev_text' => '上一页',
            'next_text' => '下一页',
            'type' => 'list'
        ));
    
        ?>
    </div>

<?php } ?>
