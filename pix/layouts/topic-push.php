<?php
$de_cat = get_op('topics_de_cat');
$cat = get_term_by('id',$de_cat,'moments');
if(!empty($cat)){
    $cat_name = $cat->name;
} else {
    $cat_name = '日常';
}
?>

<div id="create_post_box" class="uk-flex-top" uk-modal="bg-close:false">

    <div class="modal_inner uk-modal-dialog uk-modal-body round8 uk-margin-auto-vertical">
    
        <div class="t_form">
            <div class="edit_text">
                <div class="cat_header">
                    <input type="text" placeholder="标题（选填）" name="topic-title" id="topic-title">
                    <div class="de_cat" catid="<?php echo $de_cat ?>">
                        <span class="t_cat_toogle">
                            <i class="ri-hashtag"></i><span><?php echo $cat_name; ?></span>
                        </span>
                        <div class="t_cat_box round8 shadow" uk-dropdown="mode: click;toggle:.t_cat_toogle;boundary: .tool_box; stretch: x;pos: top-center">
                            <div class="set_cat"><input type="text" placeholder="创建话题" name="add_cat" id="add_cat"><a class="up_cat_btn">创建</a></div>
                            <ul><?php echo get_t_cat(); ?></ul>
                        </div>
                    </div>                   
                </div>
                <textarea id="topic_content" name="topic_content" placeholder="今日份分享 ! " maxlength="800"></textarea>
            </div>          
        </div>
        <div class="topic_tool">

            <div class="tool_box">
                <div class="left">
                    <div class="moment_image_type moment_type_btn" uk-tooltip="title:图文; pos:top; offset:2;">
                        <a><i class="ri-image-line"></i></a>
                    </div>

                    <div class="moment_video_type moment_type_btn" uk-tooltip="title:视频; pos:top; offset:2;">
                        <a><i class="ri-movie-line"></i></a>
                    </div>

                    <div class="moment_audio_type moment_type_btn" uk-tooltip="title:音乐; pos: top; offset:2;">
                        <a><i class="ri-disc-line"></i></a>
                    </div>

                    <div class="moment_card_type moment_type_btn" uk-tooltip="title:卡片; pos: top; offset:2;">
                        <a><i class="ri-pages-line"></i></a>
                    </div>

                    <span class="moment_btn_line"> | </span>

                    <div class="smile_box" uk-tooltip="title:表情; pos: top; offset:2;">
                        <a><i class="ri-emotion-line"></i></a>                   
                    </div>
                    
                    <div class="simi" uk-tooltip="title:私密; pos: top; offset:2;">
                        <a state="1"><i class="ri-lock-unlock-line"></i></a>
                    </div>
                </div>

                <div class="right">
                    <div class="loca">
                        <a class="loca_text" state="0" uk-tooltip="title:自定义; pos: top; offset:2;"><?php echo get_loca_cookie(); ?></a>
                        <a class="laqu"><i class="ri-map-pin-2-line"></i></a>
                        <div class="local_box round8 shadow" uk-dropdown="mode: click;toggle:.loca_text;boundary: .tool_box; stretch: x;pos: top-center">
                            <div class="inner">
                                <div class="set_local_box"><input type="text" placeholder="输入自定义位置" name="set_local" id="set_local"><a class="set_local_btn">确定</a></div>
                                <a class="close_local">位置已开启</a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="smile_show"><div class="smile_show_inner"><?php echo smile_img(); ?></div></div>

            <div class="moment_type_main">

            <div class="add_img_box">
                <div class="img_show" uk-sortable="handle: .t_media_item;cls-no-drag:up_img_btn;">
                    <a class="up_img_btn"> 
                        <i class="ri-add-line"></i>
                        <input type="file" name="topic_img_up" id="topic_img_up" accept="image/jpg,image/jpeg,image/png,image/gif,image/webp" multiple="multiple" title="上传图片">
                    </a>
                </div>

                <div class="up_img_type">
                    <div class="up_from_media">
                        <a><i class="ri-gallery-line"></i>从媒体库选择</a>
                    </div>
                    <div class="up_from_cdn">
                        <a><i class="ri-image-add-line"></i>插入图床图片</a>
                    </div>
                </div>

                <div class="show_media_box" style="display:none">
                        <div class="wp_get_media_list"></div>
                        <div class="nav_tool">
                            <div class="attch_nav" paged="">
                                <a class="pre"><i class="ri-arrow-left-s-line"></i></a>
                                <a class="nex"><i class="ri-arrow-right-s-line"></i></a>
                            </div>
                            <a class="souqi"><i class="ri-upload-line"></i> 收起</a>
                        </div>
                        
                    </div>

                <div class="show_cdn_media" style="display:none">
                    <div class="inner">
                        <input type="text" placeholder="外部图片链接" name="img_link_up" id="img_link_up">
                        <a class="img_link_btn">插入</a><a class="img_link_cancel">取消</a>
                    </div>
                    <span>支持: jpg | png | gif | webp | jpeg</span>
                </div>    

            </div>

            </div>
        

            <div class="form_footer">
                <div class="admin_tool">
                    <a href="<?php echo home_url('/wp-admin') ?>" target="_blank"><i class="ri-function-line"></i> 控制台</a>
                    <a href="<?php echo wp_logout_url(cst_get_curl());?>"><i class="ri-logout-circle-r-line"></i> 登出</a>
                </div>
                <div class="moment_sure">
                    <button class="uk-modal-close push_close">取消</button>
                    <button class="push_item" type="image" action="push" pid="0"><i class="ri-send-plane-2-line"></i>发布</button>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<div class="image_edit_temp" style="display:none"></div>