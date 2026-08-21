<?php
//用户中心概览页面
$user_id = get_current_user_id();

?>

<div class="dash-center user-dash-box pix-dashboard-overview">
    <div class="vip-lv-block pix-dashboard-overview-hero">
        <div class="pix-dashboard-overview-hero-cell">
            <div class="user-lv-block pix-dashboard-overview-level">
                    <?php echo user_lv_block($user_id); ?>
            </div>
        </div>

        <div class="pix-dashboard-overview-hero-cell pix-dashboard-overview-hero-checkin">
            <div class="user-checkin-block pix-dashboard-overview-checkin">
            <?php echo user_sign_block($user_id); ?>
            </div>
            <div id="checkin-modal-here"></div>
        </div>

    </div>

    <div class="user-stats-block pix-dashboard-overview-wallet">
        <div class="stats-title pix-dashboard-overview-section-title">
            <i class="ri-wallet-line"></i><span>我的资产</span>
        </div>
        <div class="user-wallet-block pix-dashboard-overview-wallet-grid">
            <div class="pix-dashboard-overview-wallet-cell">
                <?php echo user_credit_block($user_id); ?>
            </div>
        </div>
    </div>

    <?php echo user_stats_block($user_id); ?>

</div>
