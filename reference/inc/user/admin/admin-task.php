<?php
$user_id = get_current_user_id();
$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$xp_name = function_exists('ppo_xp_name') ? ppo_xp_name() : get_op('xp_slug', '经验值');
?>

<div class="dash-task user-dash-box pix-dashboard-task">
    <div class="dash-lv-info pix-dashboard-task-level">

    <div class="vip-task-block pix-dashboard-task-level-grid">
        <div class="pix-dashboard-task-level-main">
            <div class="user-lv-block pix-dashboard-task-level-card">
                <?php echo user_lv_block($user_id,'task'); ?>
            </div>
        </div>
        <div class="pix-dashboard-task-level-side">
            <div class="user-lv-detail pix-dashboard-task-level-detail">
                <?php echo user_lv_detail(); ?>
            </div>
        </div>
    </div>

    </div>

     <div class="dash-task-info pix-dashboard-task-content">
    <?php  if($tab == 'detail'){
            echo ppo_display_user_xp_detail($page,10);
        } else { ?>
        <div class="task-page-title pix-dashboard-task-title">
            <div class="left"><i class="ri-calendar-check-line"></i><span>每日任务</span></div>
            <a href="?tab=detail" class="right"><?php echo esc_html($xp_name); ?>明细<i class="ri-arrow-right-s-line"></i></a>
        </div>
        <?php echo daily_tsak_list($user_id); ?>
        <div id="checkin-modal-here"></div>
        <div class="task-page-title pix-dashboard-task-title">
            <div class="left"><i class="ri-user-smile-line"></i><span>新手任务</span></div>
        </div>
        <?php echo new_user_tsak_list($user_id); ?>
    <?php } ?>
    </div>
    
</div>
