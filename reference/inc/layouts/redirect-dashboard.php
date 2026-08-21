<?php
// 用户中心路由
    // 检查用户是否登录
    if (!is_user_logged_in()) {
        wp_redirect(home_url());
        exit;
    }
    
    get_header();
    global $wp_query;
    $user_id = get_current_user_id();
    $user_info = get_userdata($user_id);
    $type = get_query_var('ppo_admin_page') ? get_query_var('ppo_admin_page') : 'center';

    $de_avatar = THEME_URL.'/img/avap.png';
    $user_avatar = get_u_avatar($user_id,'url');
    $can_edit_avatar = true;
    if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id)){
        $can_edit_avatar = function_exists('pix_normal_user_upload_allowed') ? pix_normal_user_upload_allowed('avatar', $user_id) : true;
    }

    // 获取VIP信息
    $vip_data = ppo_get_user_vip_data($user_id);
    $is_vip = !empty($vip_data);
    $vip_icon = $is_vip && isset($vip_data['icon']) ? $vip_data['icon'] : '';
    $vip_name = $is_vip && isset($vip_data['title']) ? $vip_data['title'] : '';

    // 获取LV信息
    $lv_data = ppo_get_user_level_info($user_id);
    $lv_icon = isset($lv_data['icon']) ? $lv_data['icon'] : '';
    $lv_name = isset($lv_data['name']) ? $lv_data['name'] : 'Lv.1';

    // 个人描述
    $des = !empty($user_info->description) ? $user_info->description : '这个家伙很懒，什么也没有留下';

?>

<div class="pix-content home-box home-classic dashboard-content pix-modern pix-modern-dashboard pix-dashboard-account-page dashboard-type-<?php echo esc_attr($type); ?>">
    <button type="button" class="pix-mobile-back" aria-label="返回上一页">
        <i class="ri-arrow-left-s-line"></i>
    </button>
    <!-- 用户封面 -->
    <div class="user-banner">
        <div class="user-banner-info">
            <div class="left">
                <div class="user-avatar-show">
                    <a class="<?php echo $can_edit_avatar ? 'edit-avatar' : 'trend-avatar'; ?>">
                        <?php if($can_edit_avatar): ?>
                            <i class="ri-image-edit-line ca_icon"></i>
                        <?php endif; ?>
                        <img src="<?php echo esc_url($user_avatar); ?>">
                    </a>

                    <?php if($can_edit_avatar): ?>
                    <div id="user-avatar-modal" class="pix-modal pix-hs-modal pix-avatar-modal hidden" role="dialog" tabindex="-1" aria-labelledby="user-avatar-modal-title">
                        <div class="pix-modal-dialog hs-overlay-animation-target">
                        <div class="pix-modal-panel user-avatar-modal">
                            <div id="user-avatar-modal-title" class="avatar-modal-title">头像上传</div> 
                            <div class="tips">可拖拽至圆形区域上传，支持jpg,png,gif格式，最大2M，尺寸不小于256x256</div>                         
                            <div class="avatar-upload-box">
                                <div class="pix-user-avatar-uploader" data-default-avatar="<?php echo esc_url($de_avatar); ?>"></div>
                            </div>
                            <div class="avatar-upload-tips">
                                    <div class="tips">
                                        头像上传后，将替换您的头像，且无法找回
                                    </div>
                                </div>
                            <div class="avatar-select-box">
                                <div class="title">或选择其他头像：</div>
                                <div class="bind-avatar-box"><?php echo get_bind_avatar($user_id); ?></div>
                            </div>
                            <div class="avatar-btn"><a class="change-avatar">确定</a></div>
                            <button class="pix-modal-close" type="button" data-pix-modal-close="#user-avatar-modal" aria-label="关闭"><i class="ri-close-line"></i></button>
                        </div>
                        </div>
        
                    </div>
                    <?php endif; ?>
                    
                </div>
                <div class="user-info">
                    <h4 class="user-nickname">
                        <span><?php echo $user_info->display_name?></span>
                        <?php if($is_vip && $vip_icon): ?>
                            <span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="<?php echo esc_attr($vip_name); ?>" aria-label="<?php echo esc_attr($vip_name); ?>" tabindex="0"><img src="<?php echo esc_url($vip_icon); ?>" class="banner-vip-icon" alt="<?php echo esc_attr($vip_name); ?>"></span>
                        <?php endif; ?>
                        <?php if($lv_icon): ?>
                            <span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="<?php echo esc_attr($lv_name); ?>" aria-label="<?php echo esc_attr($lv_name); ?>" tabindex="0"><img src="<?php echo esc_url($lv_icon); ?>" class="banner-lv-icon" alt="<?php echo esc_attr($lv_name); ?>"></span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" class="my-user-center" aria-label="用户首页"><i class="ri-home-smile-line"></i><span>主页</span></a>
                    </h4>
                    <p class="user-desc"><?php echo esc_html($des); ?></p>
                </div>
            </div>
        </div>

        <?php echo user_avatar_upload_btn(); ?>
        <?php
        $user_cover = get_user_meta($user_id, 'user_cover_image', true);
        $user_cover = !empty($user_cover) ? $user_cover : THEME_URL.'/img/banner1.png';
        ?>
        <img class="user-cover-preview lazy" data-src="<?php echo esc_url($user_cover); ?>">
    </div>

    <!-- 用户中心 -->
    <div class="user-center pix-dashboard-shell">



        <div class="user-left pix-dashboard-sidebar">
            <div class="user-left-nav">
                <?php echo user_center_nav($type,$user_id); ?>
                <?php do_action('pix_user_left_after_nav', $user_id); ?>
            </div>
        </div>

        <div class="user-right pix-dashboard-main">
            <div class="user-index">
                <?php get_template_part( 'inc/user/admin/admin', $type ); ?>
            </div>
        </div>

       
    </div>
</div>
<?php get_footer();
