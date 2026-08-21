<?php
// 创建圈子
$mo_tag = get_moments_tag_arr();
$all_lv = all_lv_merge();
$moments_label = ppo_moment_label('moments');
?>

<div class="cr-momenmt-wrap pix-moment-create-form">
    <div class="cr-title pix-moment-create-title"><span></span>封面和图标</div>
    <p class="tips pix-moment-create-tip">*选择上传，不上传则显示默认的封面和图标</p>
    <div class="mos-banner mos-block pix-moment-create-cover">
        <button type="button" class="upload-mos-banner pix-moment-create-cover-button" data-url="">
            <i class="ri-landscape-line"></i>
            <img class="mos-banner-pre" alt="">
            <span>上传封面</span>
            <input type="file" class="mos-local-file mos-banner-file" name="bannerFile" accept="image/*">
        </button>
        <button type="button" class="upload-mos-logo pix-moment-create-logo-button" data-url="">
            <i class="ri-image-add-line"></i>
            <img class="mos-logo-pre" alt="">
            <input type="file" class="mos-local-file mos-logo-file" name="logoFile" accept="image/*">
        </button>
    </div>
    <div class="cr-title pix-moment-create-title"><span></span>基础信息</div>
    <div class="mos-base mos-block pix-moment-create-fields">
        <label>
            <input type="text" placeholder="<?php echo esc_attr($moments_label); ?>名称" name="cr-mos-title" id="cr-mos-title">
            <p class="tips">*建议2-10字</p>
        </label>
        <label>
            <input type="text" placeholder="<?php echo esc_attr($moments_label); ?>别名" name="cr-mos-slug" id="cr-mos-slug">
            <p class="tips">*用于网址后缀，填写<?php echo esc_html($moments_label); ?>名称的英文或拼音，必须为字母</p>
        </label>
        <label>
            <textarea placeholder="<?php echo esc_attr($moments_label); ?>简介" name="cr-mos-des" id="cr-mos-des"></textarea>
            <p class="tips">*简单描述<?php echo esc_html($moments_label); ?>，建议10-50字</p>
        </label>
    </div>

    <div class="cr-title pix-moment-create-title"><span></span><?php echo esc_html($moments_label); ?>类别</div>
    <div class="mos-ccat mos-block pix-moment-create-options">
            <div class="mos-cat">
                <?php 
                $i = 0;
                foreach ($mo_tag as $key => $val) { 
                    if($i === 0) {
                        echo '<button class="mos-cat-btn mos-btn pix-moment-create-choice active" data="'.$val.'">'.$val.'</button>';
                    } else {
                        echo '<button class="mos-cat-btn mos-btn pix-moment-create-choice" data="'.$val.'">'.$val.'</button>';
                    }
                    
                    $i++;
                 } ?>
                
            </div>
    </div>

    <div class="cr-title pix-moment-create-title"><span></span><?php echo esc_html($moments_label); ?>类型</div>
    <div class="mos-type mos-block pix-moment-create-options">
            <div class="mos-join">
                <button class="mos-type-btn mos-btn pix-moment-create-choice active" data="free">免费</button>
                <button class="mos-type-btn mos-btn pix-moment-create-choice" data="pay">付费</button>
                <button class="mos-type-btn mos-btn pix-moment-create-choice" data="limits">权限</button>
            </div>
            
            <div class="join-limits"><?php echo esc_html($moments_label); ?>加入规则</div>

            <div>
                <div class="mos-type-info" action="free">
                    <button class="mos-join-btn mos-btn pix-moment-create-choice active" data="free">直接加入</button>
                    <button class="mos-join-btn mos-btn pix-moment-create-choice" data="verify">审核后加入</button>
                </div>

                <div class="mos-type-info" action="pay" style="display:none;">
                    <label for="mp">
                        <p>月付金额</p>
                        <input type="text" placeholder="输入金额" name="mos-mp" id="mos-mp" data="mp">
                    </label>
                    <label for="qp">
                        <p>季付金额</p>
                        <input type="text" placeholder="输入金额" name="mos-qp" id="mos-qp" data="qp">
                    </label>
                    <label for="hp">
                        <p>半年金额</p>
                        <input type="text" placeholder="输入金额" name="mos-hp" id="mos-hp" data="hp">
                    </label>
                    <label for="op">
                        <p>年付金额</p>
                        <input type="text" placeholder="输入金额" name="mos-op" id="mos-op" data="op">
                    </label>
                    <label for="fp">
                        <p>永久金额</p>
                        <input type="text" placeholder="输入金额" name="mos-fp" id="mos-fp" data="fp">
                    </label>
                    <label class="pix-moment-create-pay-credit">
                        <input type="checkbox" id="mos-pay-credit-only">
                        <span>仅使用积分支付</span>
                    </label>
                    <p class="tips">*至少填写一个金额类别，也可填写多个</p>
                    <p class="tips">*开启后，用户只能使用积分购买该圈子</p>
                </div>

                <div class="mos-type-info" action="limits" style="display:none;">
                
                    <?php
                        foreach ($all_lv as $key => $val) {
                            echo '<label><input value="'.$key.'" class="mos-limits-input pix-moment-create-checkbox" type="checkbox">'.$val.'</label>';
                        }

                    ?>
                    <p class="tips">*多选，选择允许加入<?php echo esc_html($moments_label); ?>的用户组</p>
                </div>
            </div>
    </div>

    <div class="cr-title pix-moment-create-title"><span></span><?php echo esc_html($moments_label); ?>展示</div>
    <div class="mos-type mos-block pix-moment-create-options">
            <div class="mos-show">
                <button class="mos-show-btn mos-btn pix-moment-create-choice active" data="show"><?php echo esc_html($moments_label); ?>内容公开展示</button>
                <button class="mos-show-btn mos-btn pix-moment-create-choice" data="join"><?php echo esc_html($moments_label); ?>公开前几篇，加入后查看</button>
                <button class="mos-show-btn mos-btn pix-moment-create-choice" data="private"><?php echo esc_html($moments_label); ?>内容加入后查看</button>
            </div>
            <div class="mos-show-preview pix-moment-create-field" style="display:none;">
                <label for="cr-mos-show-num">未加入用户可查看数量</label>
                <input id="cr-mos-show-num" type="number" min="0" step="1" value="3">
                <p class="tips">*设置为 0 时，未加入用户无法查看内容</p>
            </div>
    </div>
</div>
