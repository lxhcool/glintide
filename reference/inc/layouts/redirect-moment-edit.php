<?php
get_header();
$pid = isset($_GET['pid']) ? absint($_GET['pid']) : 0;
$moment_label = ppo_moment_label('moment');
$moments_label = ppo_moment_label('moments');
// 鉴权
?>

<div class="pix-content moment-edit-warp pix-modern pix-modern-moment pix-moment-edit-page">
    
    <div class="moment-edit-content pix-moment-edit-content">
        <div class="pix-moment-edit-mobile-head">
            <a class="pix-moment-edit-mobile-close pix-moment-edit-back" href="" aria-label="返回<?php echo esc_attr($moments_label); ?>"><i class="ri-close-line"></i></a>
            <div class="pix-moment-edit-mobile-title">编辑<?php echo esc_html($moment_label); ?></div>
            <button type="button" class="pix-moment-edit-mobile-submit">更新</button>
        </div>
        <div class="edit-page-title pix-moment-edit-title">编辑<?php echo esc_html($moment_label); ?></div>
        <div class="moment-edit-inner pix-moment-edit-inner pix-moment-mobile-compose-body">
            <?php get_template_part('inc/layouts/moment','form'); ?>
        </div>
        <div class="edit-page-bottom pix-moment-edit-bottom"><a class="pix-moment-edit-back" href=""><i class="ri-arrow-go-back-line"></i>返回<?php echo esc_html($moments_label); ?></a></div>
    </div>

    
    
</div>

<?php
get_footer();
?>

<script type="text/javascript">
    $(document).ready(function(){
        function pixEditKind(type){
            if(type === 'gallery'){
                return 'image';
            }
            if(type === 'video'){
                return 'video';
            }
            if(type === 'file'){
                return 'file';
            }
            if(type === 'card'){
                return 'card';
            }
            return '';
        }

        function loadPixEditItems(type, items){
            if(!items || !items.length){
                return false;
            }
            var uploader = $('#pix-moment-uploader').data('pixUploader');
            if(!uploader || typeof uploader.setItems !== 'function'){
                return false;
            }
            uploader.setItems(items, pixEditKind(type));
            $('.mo-card-box').slideUp(200);
            $('.mo-card-btn').removeClass('active');
            $('.pix-moment-attach-trigger').removeClass('is-active');
            var kind = pixEditKind(type);
            if(kind === 'image' || kind === 'video' || kind === 'file'){
                $('.pix-moment-attach-trigger[data-kind="'+kind+'"]').addClass('is-active');
            }
            return true;
        }

        function fileNameFromUrl(url){
            if(!url){
                return '附件';
            }
            var clean = String(url).split('?')[0];
            return decodeURIComponent(clean.substring(clean.lastIndexOf('/') + 1) || '附件');
        }

        function normalizePixEditItems(type, moData){
            moData = moData || [];
            if(type === 'gallery'){
                return moData.map(function(item, index){
                    var src = item.src || item.url || item.thum || '';
                    if(!src) return null;
                    var name = fileNameFromUrl(src);
                    return {
                        id: item.attach_id || ('legacy-gallery-' + index),
                        attachment_id: item.attach_id || 0,
                        kind: 'image',
                        type: 'image',
                        source: item.attach_id ? 'library' : 'external',
                        status: 'done',
                        url: src,
                        thumb: item.thum || src,
                        preview: item.thum || src,
                        title: name,
                        name: name,
                        mime: 'image',
                        size: 0
                    };
                }).filter(Boolean);
            }

            if(type === 'video' && moData[0]){
                var video = moData[0];
                if((video.type || video.video_type) === 'bili'){
                    var bvid = video.bvid || '';
                    return bvid ? [{
                        id: 'bili-' + bvid,
                        attachment_id: 0,
                        kind: 'video',
                        type: 'video',
                        source: 'bili',
                        status: 'done',
                        bvid: bvid,
                        url: '//player.bilibili.com/player.html?bvid=' + encodeURIComponent(bvid) + '&page=1',
                        title: video.title || ('B站视频 ' + bvid),
                        name: video.title || ('B站视频 ' + bvid),
                        thumb: video.thumb || video.cover || '',
                        cover: video.cover || '',
                        mime: 'bilibili',
                        size: 0
                    }] : [];
                }

                var url = video.url || video.file || '';
                var name = video.name || video.title || fileNameFromUrl(url);
                return url ? [{
                    id: video.video_id || video.attach_id || 'legacy-video',
                    attachment_id: video.video_id || video.attach_id || 0,
                    kind: 'video',
                    type: 'video',
                    source: 'library',
                    status: 'done',
                    url: url,
                    preview: url,
                    poster_id: video.att_id || video.cover || 0,
                    poster: video.poster || video.thumb || video.cover_url || '',
                    thumb: video.poster || video.thumb || video.cover_url || '',
                    title: name,
                    name: name,
                    mime: video.type || 'video',
                    size: video.size || 0
                }] : [];
            }

            if(type === 'file'){
                return moData.map(function(item, index){
                    var url = item.url || item.file || '';
                    if(!url) return null;
                    var name = item.file_title || item.name || item.title || fileNameFromUrl(url);
                    return {
                        id: item.attach_id || item.file_id || ('legacy-file-' + index),
                        attachment_id: item.attach_id || item.file_id || 0,
                        kind: 'file',
                        type: 'file',
                        source: 'library',
                        status: 'done',
                        url: url,
                        preview: url,
                        title: name,
                        name: name,
                        mime: item.type || item.mime || 'file',
                        size: item.size || 0
                    };
                }).filter(Boolean);
            }

            return [];
        }

        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'get_single_moment_data',
                'security': Theme.moment_nonce,
                'pid': '<?php echo $pid; ?>',
                },	
            beforeSend: function () {
                $('.moment-edit-inner').append('<div class="overlay pix-moment-edit-loading"><span class="pix-spinner pix-moment-edit-loading-spinner" aria-hidden="true"></span><span>数据拉取中..</span></div>');
                $('.push-mo-btn').addClass('protect');
            },
            success: function(data){
                if(data && data.status == 0){
                    toastfy(data.msg || '片刻数据拉取失败','error');
                    return;
                }
                var editor = $('#pix-moment-editor').data('pixEditor');
                if(editor && typeof editor.setContent === 'function'){
                    editor.setContent(data.content || '', {
                        id: data.tag_id || '',
                        name: data.tag_name || ''
                    });
                } else {
                    $('textarea#moment_content').val(data.content);
                    if(editor && editor.el){
                        editor.el.innerHTML = data.content || '';
                        editor.sync();
                    }
                }
                $('input#moment-title').val(data.title);
                $('.cat-thum').html('<img src="'+data.term_img+'" alt="" />');
                $('.mo-cir-btn')
                    .attr('catid', data.term_id)
                    .addClass('active is-selected')
                    .find('span')
                    .text(data.term_name);
                if(data.term_link){
                    $('.pix-moment-edit-back').attr('href', data.term_link);
                }
                if(data.tag_id){
                    $('.mo-tag-btn span').text(data.tag_name);
                }

                Theme.tid = data.term_id;

                $('.push-mo-btn').attr({
                    'type':data.type,
                    'catid': data.term_id,
                    'tagid': data.tag_id,
                    'action': data.action,
                    'pid': data.pid
                });

                $('.push-mo-btn').text('更新');
                
                switch (data.type) {
                    case 'gallery':
                        if(!loadPixEditItems(data.type, (data.pix_items && data.pix_items.length) ? data.pix_items : normalizePixEditItems(data.type, data.mo_data))){
                            toastfy('图片数据回填失败，请刷新后重试','error');
                        }
                        break;
                
                    case 'card':
                        // 卡片
                        if(loadPixEditItems(data.type, data.pix_items || [])){
                            break;
                        }
                        var card_html = '';
                        $.each(data.mo_data, function(index, value) {
                            // 此处重复定义了card_html变量，应使用字符串拼接方式添加到原card_html变量中
                            //console.log(value.thum);
                            card_html += `<div class="card-box mo-card-item" pid=`+value.pid+`><div class="card-img"><img class="post-thum" src="`+value.image+`" alt="" loading="lazy"></div>
                                            <div class="card-info"><div class="title">`+value.title+`</div><div class="des">`+value.des+`</div></div>
                                            <a class="card-url" target="_blank" href="`+value.url+`"></a>
                                            <span class="de_card"><i class="ri-close-line"></i></span>
                                            </div>`;
                        });
                        $('.card-wrap').append(card_html);
                        $('.mo-card-box').slideDown();
                        break;

                    case 'video':
                        // 视频
                        if(!loadPixEditItems(data.type, (data.pix_items && data.pix_items.length) ? data.pix_items : normalizePixEditItems(data.type, data.mo_data))){
                            toastfy('视频数据回填失败，请刷新后重试','error');
                        }
                        break;    

                    case 'file':
                        if(!loadPixEditItems(data.type, (data.pix_items && data.pix_items.length) ? data.pix_items : normalizePixEditItems(data.type, data.mo_data))){
                            toastfy('文件数据回填失败，请刷新后重试','error');
                        }
                    break; 

                    case 'audio':
                
                        var aid = data.mo_data[0].aid;
                        //console.log(item);
                        $('input.netease_mo').val(aid);
                        $('.mo-audio-box').slideDown();
                    break; 

                    default:
                        break;
                }

                $('.mo-'+data.type+'-btn').addClass('active');
                  
            },
            complete: function(data){
                $('.overlay').remove();
                $('.push-mo-btn').removeClass('protect');
            }
        });

        $.ajax({
            type: "post",
            url:Theme.ajaxurl,
            dataType:  'json',
            data: {
                'action':'get_current_mo_data',
                'pid': '<?php echo $pid; ?>',
                security: Theme.moment_nonce,
                },
            beforeSend: function () {

            },
            success: function(data){
                if(typeof apply_current_moment_data === 'function'){
                    apply_current_moment_data(data);
                }


            }

                 
        });

    });
</script>
