<div class="msg-right-content pix-dashboard-message-panel pix-dashboard-message-system-panel">
    <div class="title pix-dashboard-message-heading">系统消息</div>
    <div class="system-msg-box msg-box-append pix-dashboard-message-list pix-dashboard-message-system-list pix-dashboard-list" action="load_system_msg">
        <?php echo all_system_msg_list(1); ?>
    </div>

    <div class="msg-load-more pix-dashboard-message-load-more"></div>

    <div id="system-msg-modal" class="pix-modal pix-hs-modal pix-dashboard-modal pix-dashboard-system-message-modal pix-dashboard-system-message-hs-modal hidden" role="dialog" tabindex="-1" aria-labelledby="system-msg-modal-title">
    <div class="pix-modal-dialog hs-overlay-animation-target">
    <div class="pix-modal-panel system-msg-modal modal-rounded pix-dashboard-modal-dialog">

        <button class="pix-modal-close" type="button" data-pix-modal-close="#system-msg-modal" aria-label="关闭"><i class="ri-close-line"></i></button>

        <div class="pix-modal-header system-msg-modal-header">
            <h2 id="system-msg-modal-title" class="pix-modal-title system-msg-modal-title">加载中..</h2>
            <div class="system-modal-meta"></div>
        </div>

        <div class="system-msg-modal-content pix-dashboard-modal-body">

        </div>

        <div class="pix-modal-footer system-msg-modal-footer">
            <button class="pix-modal-button pix-modal-button-primary" type="button" data-pix-modal-close="#system-msg-modal">知道了</button>
        </div>

        </div>
        </div>
    </div>
</div>
