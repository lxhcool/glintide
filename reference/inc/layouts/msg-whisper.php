<?php 
    $user_id = get_current_user_id();
    //$user_list = ppo_get_user_conversations($user_id,20,);
?>

<div class="msg-right-content pix-dashboard-message-panel pix-dashboard-message-whisper-panel">
    <div class="title pix-dashboard-message-heading">我的消息</div>

    <div class="whisper-msg-box pix-dashboard-message-whisper-box">
        <div class="pix-dashboard-message-chat-grid">
            <div class="pix-dashboard-message-chat-sidebar">
                <div class="chat-user-list pix-dashboard-message-conversation-list">
                    <?php echo ppo_chat_user_list_html($user_id); ?>
                </div>
            </div>
            
            <div class="grid-height pix-dashboard-message-chat-main">
                <div class="pix-dashboard-message-mobile-chat-head">
                    <button class="pix-dashboard-message-mobile-back" type="button" aria-label="返回联系人列表">
                        <i class="ri-arrow-left-s-line"></i>
                    </button>
                    <div class="pix-dashboard-message-mobile-chat-title">我的消息</div>
                </div>
                <div class="chat-user-warp pix-dashboard-message-chat-wrap">
                    <div class="chat-scroll-body pix-dashboard-message-chat-scroll">
                        <div class="private-msg-list-content pix-dashboard-message-chat-list"></div>
                    </div>
                    <div class="chat-footer-texarea pix-dashboard-message-chat-footer is-empty">
                        <div class="chat-box-warp pix-dashboard-message-chat-compose">
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>
