<?php
// 用户中心路由
    global $wp_query;
    $curauth = $wp_query->get_queried_object();
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_info = get_userdata($curauth->ID);

    // Myself?
    $myself = $current_user->ID == $curauth->ID ? 1 : 0;
    $oneself = $current_user->ID==$curauth->ID || current_user_can('edit_users') ? 1 : 0;
    // Admin ?
    $admin = $current_user->ID==$curauth->ID&&current_user_can('edit_users') ? 1 : 0;

    //$def_type = $oneself ? 'center' : 'trend';
    //$type = get_query_var('ppo_user_page') ? get_query_var('ppo_user_page') : $def_type;

    $de_avatar = THEME_URL.'/img/avap.png';
    $user_avatar = get_u_avatar($curauth->ID,'url');
    $avatar_edit = $oneself ? 'edit-avatar' : 'trend-avatar';
    $avatar_icon = $oneself ? '<i class="ri-image-edit-line ca_icon"></i>' : '';

    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'posts';
    $page = isset($_GET['page'])? sanitize_text_field($_GET['page']) : 1;

    // 获取VIP信息
    $vip_data = ppo_get_user_vip_data($curauth->ID);
    $is_vip = !empty($vip_data);
    $vip_icon = $is_vip && isset($vip_data['icon']) ? $vip_data['icon'] : '';
    $vip_name = $is_vip && isset($vip_data['title']) ? $vip_data['title'] : '';

    // 获取LV信息
    $lv_data = ppo_get_user_level_info($curauth->ID);
    $lv_icon = isset($lv_data['icon']) ? $lv_data['icon'] : '';
    $lv_name = isset($lv_data['name']) ? $lv_data['name'] : 'Lv.1';

    // 个人描述
    $des = !empty($user_info->description) ? $user_info->description : '这个家伙很懒，什么也没有留下';
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-user-home">
    <!-- 用户封面 -->
    <div class="user-banner pix-user-home-banner">
        <div class="user-banner-info pix-user-home-banner-info">
            <div class="left pix-user-home-banner-copy">
                <div class="user-avatar-show pix-user-home-avatar-wrap">
                    <a class="trend-avatar pix-user-home-avatar">
                        <img class="lazy pix-user-home-avatar-img" data-src="<?php echo esc_url($user_avatar); ?>">
                    </a>
                    
                </div>
                <div class="user-info pix-user-home-info">
                    <h4 class="user-nickname pix-user-home-name-row">
                        <span class="pix-user-home-name"><?php echo esc_html($user_info->display_name); ?></span>
                        <?php if($is_vip && $vip_icon): ?>
                            <span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="<?php echo esc_attr($vip_name); ?>" aria-label="<?php echo esc_attr($vip_name); ?>" tabindex="0"><img src="<?php echo esc_url($vip_icon); ?>" class="banner-vip-icon" alt="<?php echo esc_attr($vip_name); ?>"></span>
                        <?php endif; ?>
                        <?php if($lv_icon): ?>
                            <span class="pix-tooltip pix-badge-tooltip" data-pix-tooltip="<?php echo esc_attr($lv_name); ?>" aria-label="<?php echo esc_attr($lv_name); ?>" tabindex="0"><img src="<?php echo esc_url($lv_icon); ?>" class="banner-lv-icon" alt="<?php echo esc_attr($lv_name); ?>"></span>
                        <?php endif; ?>
                    </h4>
                    <p class="user-desc pix-user-home-desc"><?php echo esc_html($des); ?></p>
                </div>
            </div>

            <?php if(!$myself): ?>
                <?php echo ppo_user_msg_follow_btn($curauth->ID); ?>
            <?php endif; ?>
        </div>

        <?php if($myself): ?>
            <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="my-user-manage pix-user-home-manage"><i class="ri-user-settings-line"></i> 管理中心</a>
        <?php endif; ?>
 
        <?php
        $user_cover = get_user_meta($curauth->ID, 'user_cover_image', true);
        $user_cover = !empty($user_cover) ? $user_cover : THEME_URL.'/img/banner1.png';
        ?>
        <img class="user-cover-preview pix-user-home-cover lazy" data-src="<?php echo esc_url($user_cover); ?>">
    </div>

    <!-- 用户中心 -->
    <div class="user-center pix-user-home-layout">

        <div class="user-left pix-user-home-sidebar">
            <div class="user-left-nav pix-user-home-sidebar-nav">
                <?php echo user_center_left($curauth->ID); ?>
            </div>
        </div>

        <div class="user-right pix-user-home-main">
            <div class="user-index pix-user-home-main-inner">
                <?php echo user_index_nav($tab,$curauth->ID); ?>
                <div class="user-index-content pix-user-home-content">
                 <?php get_template_part( 'inc/user/page/user', $tab , $page ); ?>
                </div>
            </div>
        </div>
       
    </div>
</div>
