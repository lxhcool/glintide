<?php 
// 片刻发布模块
$user_id = get_current_user_id();
$term_id = get_queried_object()->term_id ?? false;
$power = get_user_power($user_id);
$func = get_mj_func($term_id);
$allow_card = is_allow_card($user_id,$term_id);
$max_word = mo_word_max($term_id);
$max = $max_word['max'];
$min = $max_word['min'];

$disable = $term_id ? '' : 'disabled';

$ga_num = mo_gallery_num($term_id);
$file_num = mo_file_num($term_id);
$moment_label = ppo_moment_label('moment');
$moments_label = ppo_moment_label('moments');
?>

<div class="mo-push-wrap pix-moment-composer">

    <div class="mo-form-main pix-moment-composer-card">

        <?php 
            if(!is_user_logged_in()){ ?>

        <div class="mo-login-required pix-moment-composer-login">
            <div class="mo-login-required-icon"><i class="ri-lock-unlock-line"></i></div>
            <div class="mo-login-required-main">
                <div class="mo-login-required-title">登录后解锁<?php echo esc_html($moment_label); ?>互动</div>
                <div class="mo-login-required-desc">需要登录后才可发布<?php echo esc_html($moment_label); ?>，并查看你加入或创建的<?php echo esc_html($moments_label); ?>。</div>
            </div>
            <a href="#modal-login" data-pix-auth-open="login" class="mo-login-required-btn">立即登录</a>
        </div>

        <?php } elseif(!check_mo_joined() && $term_id){
                echo join_mo_box($term_id);
            } else {
                $allow_normal_moment = function_exists('pix_normal_user_can') ? pix_normal_user_can('normal_user_allow_moment', true, $user_id) : true;
                if(function_exists('pix_user_is_plain_user') && pix_user_is_plain_user($user_id) && !$allow_normal_moment){
                    ?>
                    <div class="mo-login-required pix-moment-composer-login">
                        <div class="mo-login-required-icon"><i class="ri-shield-user-line"></i></div>
                        <div class="mo-login-required-main">
                            <div class="mo-login-required-title">普通用户暂不能发布<?php echo esc_html($moment_label); ?></div>
                            <div class="mo-login-required-desc">当前站点已关闭普通用户发布<?php echo esc_html($moment_label); ?>的权限，请联系管理员开启。</div>
                        </div>
                    </div>
                    <?php
                } else { ?>
        

        <?php echo mo_user_box($term_id); ?>
        <div class="mo-form-within pix-moment-composer-body">
            <div class="mo-title-form pix-moment-composer-title-field">
                <input class="pix-moment-composer-input" type="text" placeholder="标题（选填）" name="moment-title" id="moment-title">
            </div>
            <div class="mo-content-form pix-moment-composer-editor">
                <textarea id="moment_content" class="pix-editor-source" name="moemnt_content" placeholder="今日份分享 ! " maxlength="<?php echo $max ?>" style="overscroll-behavior: contain;"></textarea>
                <div id="pix-moment-editor" data-pix-editor data-input="#moment_content" data-placeholder="今日份分享 ! "></div>
            </div>
            <input type="text" id="pix_guard" name="pix_guard" value="" autocomplete="off" tabindex="-1" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;opacity:0;pointer-events:none;">

            <div class="mo-tool-nav pix-moment-composer-toolbar">
            <div class="left-tool pix-moment-composer-tools">
                <?php if(user_moments_power($user_id , $term_id,'audio')) { ?>
                    <a class="mo-music-btn mo-audio-btn mo-btn pix-moment-composer-tool pix-tooltip <?php echo $disable ?>" data="audio" data-pix-tooltip="音乐" aria-label="音乐"><i class="ri-disc-line"></i></a>
                <?php } ?>
                <?php if(user_moments_power($user_id , $term_id,'card')) { ?>
                    <a class="mo-card-btn mo-btn pix-moment-composer-tool pix-tooltip <?php echo $disable ?>" data="card" data-pix-tooltip="卡片" aria-label="卡片"><i class="ri-article-line"></i></a>
                <?php } ?>  
                <?php if(user_moments_power($user_id , $term_id,'gallery')) { ?>
                    <button type="button" class="pix-moment-attach-trigger pix-moment-attachment-trigger pix-moment-attachment-trigger-image pix-moment-composer-tool mo-gallery-btn pix-tooltip <?php echo $disable ?>" data-kind="image" data-pix-tooltip="图片" aria-label="图片"><i class="ri-image-add-line"></i></button>
                <?php } ?>
                <?php if(user_moments_power($user_id , $term_id,'video')) { ?>
                    <button type="button" class="pix-moment-attach-trigger pix-moment-attachment-trigger pix-moment-attachment-trigger-video pix-moment-composer-tool mo-video-btn pix-tooltip <?php echo $disable ?>" data-kind="video" data-pix-tooltip="视频" aria-label="视频"><i class="ri-video-add-line"></i></button>
                <?php } ?>
                <?php if(user_moments_power($user_id , $term_id,'file')) { ?>
                    <button type="button" class="pix-moment-attach-trigger pix-moment-attachment-trigger pix-moment-attachment-trigger-file pix-moment-composer-tool mo-file-btn pix-tooltip <?php echo $disable ?>" data-kind="file" data-pix-tooltip="文件" aria-label="文件"><i class="ri-file-add-line"></i></button>
                <?php } ?>
                <span class="fenge pix-moment-composer-divider">|</span>
                <div class="pix-moment-emoji-dropdown-wrap hs-dropdown [--placement:bottom] [--offset:8] [--auto-close:inside] [--strategy:static]">
                <a class="mo-smile-btn mo-btn pix-moment-tool-button pix-moment-smile-button pix-moment-emoji-button hs-dropdown-toggle" aria-haspopup="menu" aria-expanded="false"><i class="ri-emotion-line"></i></a>

                <div class="mo-smile-drop pix-moment-dropdown pix-moment-smile-dropdown pix-moment-emoji-dropdown hs-dropdown-menu hidden" role="menu">
                    <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition>
                    <div class="mo-smile-inner pix-moment-emoji-inner"></div>
                    </div>
                </div>
                </div>
            </div>

            <div class="right-tool pix-moment-composer-count">
                <div class="mo-num pix-moment-composer-num">0</div>
            </div>
        </div>
        
        </div>

        <div class="pix-moment2-panel pix-moment-composer-attach-panel pix-moment-attachment-panel is-collapsed">
            <div class="pix-moment2-head pix-moment-composer-panel-head pix-moment-attachment-head">
                <div class="pix-moment2-icon pix-moment-composer-panel-icon pix-moment-attachment-icon"><i class="ri-add-circle-line"></i></div>
                <div class="pix-moment2-copy pix-moment-composer-panel-copy pix-moment-attachment-copy">
                    <span>添加附件</span>
                    <small>图片可多选和排序，视频 / 文件每次添加一个，也支持外链图片和我的媒体库</small>
                </div>
                <button type="button" class="pix-moment2-close pix-moment-composer-panel-close pix-moment-attachment-close" aria-label="收起附件面板"><i class="ri-close-line"></i></button>
            </div>
            <div id="pix-moment-uploader"
                class="pix-moment-attachment-uploader"
                data-pix-uploader
                data-context="moment_asset"
                data-limit="<?php echo esc_attr(max((int)$ga_num, (int)$file_num, 1)); ?>"></div>
        </div>

        <div class="mo-tool-box pix-moment-composer-toggle-boxes">
            <div class="mo-music-box mo-audio-box mo-toggle-box pix-moment-composer-toggle pix-moment-composer-audio">
                <div class="cancel-toggle-box pix-moment-composer-toggle-close"><i class="ri-close-line"></i></div>
                <div class="mo-netease pix-moment-composer-inline-form">
                    <input type="text" name="netease_mo" class="netease_mo pix-moment-composer-input" placeholder="输入网易云单曲ID">
                    <a class="preview-music pix-moment-composer-inline-submit">生成音乐</a>
                </div>
                <div class="audio-preview-box pix-moment-composer-audio-preview"></div>
                <!-- <span class="mo-tips"><i class="ri-error-warning-line"></i>必须安装pixmusic插件后才可使用</span> -->
            </div>

            <div class="mo-card-box mo-toggle-box pix-moment-composer-toggle pix-moment-composer-cardbox">
                <div class="cancel-toggle-box pix-moment-composer-toggle-close"><i class="ri-close-line"></i></div>
                <span class="mo-tips pix-moment-composer-tip"><i class="ri-error-warning-line"></i>输入本站网址,自动生成链接卡片<small>支持文章, <?php echo esc_html($moment_label); ?>, 页面等网址</small></span>
                <div class="mo-card-content pix-moment-composer-inline-form">
                    <input class="pix-moment-composer-input" type="text" placeholder="本站网址" name="mo_card_link" id="mo_card_link" required="required">
                    <a class="push_card pix-moment-composer-inline-submit">生成</a>
                </div>
                <div class="card-wrap pix-moment-composer-card-preview"></div>
            </div>
        </div>

        <div class="bottom-tools pix-moment-composer-actions">
            <div class="mo-cat pix-moment-composer-taxonomy">
                <?php echo get_moment_cat_btn(); ?>
                <!-- <a class="mo-cir-btn"><div class="cat-thum"><i class="ri-outlet-line"></i></div> <span>圈子</span></a> -->
                <div class="pix-moment-topic-dropdown-wrap hs-dropdown [--placement:bottom-start] [--offset:8] [--auto-close:inside] [--strategy:static]">
                <a class="mo-tag-btn pix-moment-tool-button pix-moment-tag-button pix-moment-topic-button hs-dropdown-toggle" aria-haspopup="menu" aria-expanded="false"><i class="ri-hashtag"></i> <span>话题</span></a>

                <div class="motag-drop mo-cat-drop pix-moment-dropdown pix-moment-tag-dropdown pix-moment-topic-dropdown hs-dropdown-menu hidden" role="menu">
                <div class="pix-dropdown-motion pix-dropdown-animated pix-dropdown-slide-down" data-hs-dropdown-transition>
                <?php echo get_moment_tag(); ?>
                </div>
                </div>
                </div>
            </div>
            <div class="push-box pix-moment-composer-submit-group">
                <a class="mo-setting pix-moment-composer-setting"><i class="ri-settings-line"></i></a>
                <a class="push-mo-btn pix-moment-composer-submit" type="text" action="publish">发布</a>
            </div>
        </div>

        <?php
                }
            } ?>

    </div>
</div>
