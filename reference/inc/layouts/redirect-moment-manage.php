<?php
get_header();
$nav_h = cls_nav_height();
$term_id = isset($_GET['term_id']) ? $_GET['term_id'] : false;
$user_id = get_current_user_id();
$base_url = '';
$total_pages = '';
$current = 1;

if($term_id){
    $mo_owner = get_term_meta($term_id, 'mo_owner', true);
    $img = get_term_meta( $term_id, 'mo_cat_img' , true);
    $img = $img ? $img : THEME_URL.'/img/modef.png';
    $term_data = get_term_by('id',$term_id,'moments');
    $name = $term_data->name;
    $des = $term_data->description ? $term_data->description : '暂无介绍';
    $joined_dot = mo_wait_join_notice($term_id); 
    $pending_dot = mo_posts_notice($term_id);
    $join_type = get_term_meta($term_id, 'mo_free_join_type', true);
}

?>

<div class="pix-content moment-edit-warp home-classic pix-modern pix-modern-moment pix-moment-manage-page">
    <div class="pix-moment-shell">

    <?php if(is_active_sidebar( 'moment-left' )){ ?>	
            <div class="left left-widget pix-moment-sidebar pix-moment-sidebar-left">
                <div class="widget_inner moment_left_inner pix-moment-widget-stack">
                    <div class="pix-moment-sticky" style="--pix-moment-sticky-offset: <?php echo esc_attr($nav_h); ?>px;">
                        <?php dynamic_sidebar( 'moment-left' ); ?>
                    </div>    
                </div>	
            </div>			
        <?php } ?>

    <div class="center-content pix-moment-center">
        <div class="moment-edit-manage pix-moment-manage">
           <?php if(moment_auth('',$user_id,$term_id) && $term_id){ ?>
                <div class="manage-page-title pix-moment-manage-head">
                    <div class="cat-info pix-moment-manage-circle">
                        <div class="cat-thum pix-moment-manage-circle-icon"><img src="<?php echo $img; ?>" alt="" /></div>
                        <div class="cat-name pix-moment-manage-circle-info"><span><?php echo $name; ?></span><small><?php echo $des ?></small></div>
                    </div>
                    <div class="cat-nav pix-moment-manage-tabs" term_id="<?php echo $term_id; ?>">
                        <a class="review-mo review-page-btn pix-moment-manage-tab active" type="review_mo"><?php echo esc_html(ppo_moment_label('moment')); ?>待审<?php echo $pending_dot ?></a>
                        <?php if($join_type == 'verify') { ?>
                            <a class="review-join review-page-btn pix-moment-manage-tab" type="review_join">申请待审<?php echo $joined_dot ?></a>
                        <?php } ?>    
                        <a class="mo-user-manage review-page-btn pix-moment-manage-tab" type="review_mo_user"><?php echo esc_html(ppo_moment_label('user')); ?>管理</a>
                    </div>
                </div>
                <div class="moment-manage-inner pix-moment-manage-inner">
                    <?php echo get_pending_moment($term_id,$base_url,$total_pages,$current); ?>
                </div>
            <?php } else { ?>
                <div class="edit-page-title nodata pix-moment-manage-empty"><img src="<?php echo THEME_URL.'/img/limits.png'?>"><span>你没有权限访问此页面</span></div>
            <?php } ?>
        </div>
    </div>

        <?php if(is_active_sidebar( 'moment-right' )){ ?>		
                <div class="right right-widget pix-moment-sidebar pix-moment-sidebar-right">
                    <div class="widget_inner moment_right_inner pix-moment-widget-stack">
                        <div class="pix-moment-sticky" style="--pix-moment-sticky-offset: <?php echo esc_attr($nav_h); ?>px;">
                            <?php dynamic_sidebar( 'moment-right' ); ?>
                        </div>    
                    </div>	
                </div>			
            <?php } ?>
    
    </div>
</div>

<?php
get_footer();


