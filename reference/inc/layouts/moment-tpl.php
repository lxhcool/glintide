<?php
$nav_h = cls_nav_height();
$sidebar = pix_sidebar();
$moment_notice_show = get_cu('mos_home_notice_show', false);
$moment_hot_open = get_cu('mos_home_hot_show', false);
$moment_hot_list = get_cu('mos_home_hot', '');
$moment_notice_only = intval($args['catid']) === 0 && $moment_notice_show && (!$moment_hot_open || empty($moment_hot_list));
$moment_login_required = !is_user_logged_in();
$moment_filter_html = moment_cat_filter();
?>
<div class="pix-modern pix-modern-moment pix-moment-shell">

    <?php if ($sidebar['left']): ?>
        <div class="left left-widget pix-moment-sidebar pix-moment-sidebar-left">
            <div class="widget_inner moment_left_inner pix-moment-widget-stack">
                <?php dynamic_sidebar( 'moment-left' ); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="center-content pix-moment-center<?php
        if (!$sidebar['left'] && !$sidebar['right']) echo ' pix-moment-center-expand';
        elseif (!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
    ?>">
        <div id="primary" class="site-main pix-moment-main">

            <?php if (intval($args['catid']) === 0): ?>
                <div class="cls-banner cls-banner-moment">
                    <?php echo Classic_mod::banner_info(); ?>
                    <img src="<?php echo esc_url(Classic_mod::banner_image()); ?>" loading="lazy" decoding="async" alt="">
                </div>
            <?php endif; ?>

            <button type="button" class="pix-moment-mobile-compose-trigger" aria-label="发布片刻">
                <i class="ri-edit-box-line"></i>
                <span>发布</span>
            </button>

            <div class="moment-push-form pix-moment-composer-section<?php echo $moment_notice_only ? ' is-notice-only' : ''; ?><?php echo $moment_login_required ? ' is-login-required' : ''; ?>">
                <?php echo moment_banner_info(); ?>
                <?php
                if ($args['catid'] == 0) {
                    echo moment_rec_cat();
                    echo mos_notice_bar();
                }
                ?>
                <?php get_template_part('inc/layouts/moment', 'form'); ?>
            </div>

            <?php if (!empty($moment_filter_html)): ?>
                <div class="moment-cat-filter pix-moment-filter pix-moment-dropdown-scope">
                    <?php echo $moment_filter_html; ?>
                </div>
            <?php endif; ?>

            <div class="home-moment-content cls-content pix-moment-content pix-moment-grid-scope">
                <?php get_template_part('inc/layouts/cls', 'moment', $args); ?>
            </div>

        </div><!-- #main -->

        <div class="pix-moment-mobile-compose" aria-hidden="true">
            <div class="pix-moment-mobile-compose-panel" role="dialog" aria-modal="true" aria-label="发布片刻">
                <div class="pix-moment-mobile-compose-head">
                    <button type="button" class="pix-moment-mobile-compose-close" aria-label="关闭发布面板">
                        <i class="ri-close-line"></i>
                    </button>
                    <div class="pix-moment-mobile-compose-title">发布<?php echo esc_html(ppo_moment_label('moment')); ?></div>
                    <button type="button" class="pix-moment-mobile-compose-submit">发布</button>
                </div>
                <div class="pix-moment-mobile-compose-body"></div>
            </div>
        </div>
    </div>

    <?php if ($sidebar['right']): ?>
        <div class="right right-widget pix-moment-sidebar pix-moment-sidebar-right">
            <div class="widget_inner moment_right_inner pix-moment-widget-stack">
                <?php dynamic_sidebar( 'moment-right' ); ?>
            </div>
        </div>
    <?php endif; ?>

</div>
