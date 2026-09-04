main_content = $('.moment_type_main');
function remove_image_temp(){
    if(!$('.image_edit_temp .add_img_box').length > 0){
        var temp = $('.add_img_box');
        $('.image_edit_temp').html(temp);
    }
    main_content.empty();
}

function back_image_temp(){
    var temp = $('.add_img_box');
    main_content.html(temp);
    $('.image_edit_temp').empty();
}

//发布卡片
$('body').on('click', '.moment_card_type a', function() {
    $('.push_item').attr('type','card');
    main_content = $('.moment_type_main');
    if($('.add_card_box').length > 0){
        return false;
    }
    remove_image_temp();
    $.ajax({
        type: 'POST',
        //dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action':'card_type_box',
        },
        beforeSend: function () {
            main_content.html('<div class="loading_box"><div uk-spinner></div></div>');
		},
        success: function (data) {
            $('.moment_type_main .loading_box').remove();
            main_content.append(data);
        }
    });
});    

$('body').on('click', '.edit_content .push_card', function() {
    var card_url = $('.edit_card_box .edit_content input').val();
    var num = $('.card_sortble .moment_card_item').length;
    if(num > 2) {
        cocoMessage.error('最多插入3个卡片');
        $('.edit_card_box .edit_content input').val('');
        return false;
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action':'get_moment_card',
            card_url:card_url
        },
        beforeSend: function () {
            cocoMessage.info('生成中...');
		},
        success: function (data) {
            $('.edit_card_box .edit_content input').val('');
            if(data.state == '1'){
                $('.show_card').show();
                $('.card_sortble').append(data.html);
                cocoMessage.success('已生成卡片');
            } else {
                cocoMessage.error(data.html);
            }
            
        }
    });
}); 

$(document).on('click', '.de_card', function() {
    var msg = "确认删除此卡片？"; 
    var num = $('.moment_card_item').length;
    if (confirm(msg)==true){ 
        $(this).parents('.moment_card_item').remove();
    } else { 
     return false; 
    } 

    if(num == 1){
        $(".show_card").hide();
    }
});    

//图文编辑 返回图文编辑区域
$('body').on('click', '.moment_image_type a', function() {
    back_image_temp();
    $('.push_item').attr('type','image');
}); 

//发布音乐--------------------------------------------------------------
$('body').on('click', '.moment_audio_type a', function() {
    $('.push_item').attr('type','audio');
    main_content = $('.moment_type_main');
    if($('.add_audio_box').length > 0){
        return false;
    }
    remove_image_temp();
    $.ajax({
        type: 'POST',
        //dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action':'audio_type_box',
        },
        beforeSend: function () {
            main_content.html('<div class="loading_box"><div uk-spinner></div></div>');
		},
        success: function (data) {
            $('.moment_type_main .loading_box').remove();
            main_content.append(data);
        }
    });
});   

//音乐和视频 封面上传
$(document).on('change','#moment_img_up',function(){
    //e.preventDefault();
    if($('#moment_img_up').val() == '')
       return;

    var f = this.files[0];
    var lookImgSrc=getObjectURL(f);
	var formData = new FormData();
	formData.append('moment_img_up',f);

    function getObjectURL(f) {  
		var url = null ;   
		if (window.createObjectURL!=undefined) { // basic  
			url = window.createObjectURL(f) ;  
		} else if (window.URL!=undefined) { // mozilla(firefox)  
			url = window.URL.createObjectURL(f) ;  
		} else if (window.webkitURL!=undefined) { // webkit or chrome  
			url = window.webkitURL.createObjectURL(f) ;  
		}  
		return url ;  
	}

    
    $.ajax({
		type: "POST",
		url:Theme.ajaxurl + "?action=moment_cover_upload",
		dataType:  'json',
		data: formData,	
		processData : false,
		contentType : false,	
		beforeSend: function () {
			cocoMessage.info('上传中...');
            $('.m_media_left i').remove();
            $('.m_media_left').append("<div class='up_loading' uk-spinner='ratio: .6'></div>");
            var imged = $('.m_media_left img');
            if(imged.length > 0){
                imged.remove();
            }
		},
		success: function(data){
			if(data.code == '0'){
                var img = data.msg;
                $('.m_media_left .up_loading').remove();
                $('.m_media_left').append('<img src="'+lookImgSrc+'" data="'+img.thumb+'" att_id="'+img.attach_id+'">');
                cocoMessage.success('上传成功');    
            } else if(data.code == '1'){
                cocoMessage.error(data.msg);
            } 
	
		}	
	});

});   

$('body').on('click', '.audio_choose a', function() {
    var text = '# 请插入封面,歌名以及歌曲外链';
    var type = $(this).attr('au_type');
    if(type != 'local'){
        var text = '# 请输入歌曲ID';
    }
    $('.edit_audio_box .tips').text(text);
});    

//发布视频--------------------------------------------------------------
$('body').on('click', '.moment_video_type a', function() {
    $('.push_item').attr('type','video');
    main_content = $('.moment_type_main');
    if($('.add_video_box').length > 0){
        return false;
    }
    remove_image_temp();
    
        $.ajax({
            type: 'POST',
            //dataType: 'json',
            url: Theme.ajaxurl,
            data: {
                'action':'video_type_box',
            },
            beforeSend: function () {
                main_content.html('<div class="loading_box"><div uk-spinner></div></div>');
            },
            success: function (data) {
                $('.moment_type_main .loading_box').remove();
                main_content.append(data);
            }
        });
    
}); 

$('body').on('click', '.video_choose a', function() {
    var text = '# 请插入封面(可不设置),视频外链';
    var type = $(this).attr('vi_type');
    if(type == 'bili'){
        var text = '# 请输入B站视频bvid';
    }
    $('.edit_video_box .tips').text(text);
}); 

//图文类型
//$('body').on('click', '.moment_video_type a', function() {

//});    

//删除片刻文章
$('body').on('click', '.control_delete_post', function() {
    var pid = $(this).parent().attr('pid');

    var msg = "把此片刻放入回收站？"; 
    if (confirm(msg)==true){ 
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            data: {
                'action':'trash_moment',
                pid:pid
            },
            beforeSend: function () {
                cocoMessage.info('处理中..'); 
            },
            success: function (data) {
                if(data.state == '1'){
                    cocoMessage.success('删除成功'); 
                    location.reload();
                } else {
                    cocoMessage.error('删除失败'); 
                }
            }
        });
    } else { 
     return false; 
    } 

});  

//置顶片刻
$('body').on('click', '.sticky_btn', function() {
    var pid = $(this).parent().attr('pid');
    var stick = $(this).hasClass('stick');
    if(stick){
        var state = 'stick';
    } else {
        var state = 'unstick';
    }
    var msg = "确定执行此操作？"; 
    if (confirm(msg)==true){ 
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            data: {
                'action':'stick_moment',
                pid:pid,
                state:state
            },
            beforeSend: function () {
                cocoMessage.info('处理中..'); 
            },
            success: function (data) {
                if(data.state == '1'){
                    cocoMessage.success(data.msg); 
                    if(data.type == 'stick'){
                        var post_item = $('#post-'+pid+'.moment_item');
                        post_item.remove();
                        $('.moment_list').prepend(post_item.prop('outerHTML'));
                        $('#post-'+pid+'.moment_item').find('.post_footer_meta .right').prepend('<span class="sticky_icon"><i class="ri-fire-line"></i> TOP</span>');
                        $('#post-'+pid+'.moment_item').find('.post_control').remove();
                    } else {
                        $('#post-'+pid+'.moment_item').find('.sticky_icon').remove();
                    }

                } else {
                    cocoMessage.error('操作失败'); 
                }
            }
        });
    } else { 
     return false; 
    } 

}); 

//编辑片刻
$('body').on('click', '.control_edit_post', function() {
    $('.t_media_item').remove();
    $('#moment_audio_api').remove();
    var pid = $(this).parent().attr('pid');
    $('.push_item').html('<i class="ri-edit-box-line"></i>更新');
    $('.push_item').attr('pid',pid).attr('action','update');
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action':'moment_edit_modal',
            pid:pid
        },
        beforeSend: function () {
            cocoMessage.info('数据拉取中'); 
            //$('.t_form').before('<div class="edit_overlay"></div>');
        },
        success: function (data) {
            var type = data.m_type;
            if(type == 'ga'){
                var type = 'image';
            }

            $('#topic_content').val(data.content);
            $('#topic-title').val(data.title);
            $('.de_cat').attr('catid',data.cid);
            $('.t_cat_toogle span').text(data.cat);
            $('.loca_text').text(data.mylocal);

            switch(type)
            {
                case "image":
                  //if(data.moment_data.length > 0){ 文字片刻没有数据
                    edit_image(data);
                  //}                  
                    break;
                case "card": 
                    edit_card(data);
                    break;
                case "audio": 
                    edit_audio(data);
                    break;
                case "video": 
                    edit_video(data);    
                    break;    
            }

            //$('.edit_overlay').remove();
            cocoMessage.success('拉取完成');
        }
    });
});

//发布片刻重置
$('body').on('click', '.normal_edit', function() {
    $('.push_item').attr('action','push').html('<i class="ri-send-plane-2-line"></i>发布');
    $('.moment_image_type a').click();
    $('#topic_content').val('');
    $('#topic-title').val('');
    $('.t_media_item').remove();
});    

//编辑片刻类型------------------------------------------------------------------------------
function edit_image(data){
    var m_data = data.moment_data;
    $('.t_media_item').remove();
    $('.moment_image_type a').click();
    if(m_data.length > 0){
        var new_data = m_data.reverse();
        $.each(new_data, function(index, value) {
            var thum = value.thum;
            var src = value.src;
            var media = '<div class="t_media_item" data-src="'+src+'" data-thum="'+thum+'">';
                media += '<a class="topic-img-de"><i class="ri-subtract-line"></i></a>';
                media += '<img src="'+thum+'">';//图片预览 
                media += '</div>';                 
            $(".img_show").prepend(media);
        });
    }
}

function edit_video(data){
    var m_data = data.moment_data[0];
    var type = m_data.type;
    //var url = m_data.url;
    $('.moment_video_type a').click();

    var add = setInterval(function() {
        if(type == 'local'){
            var url = m_data.url;
            var cover = m_data.cover ? m_data.cover : '';
            var att_id = m_data.att_id;
       
            $('input#moment_video_url').val(url);      
            if(cover !== ''){
                $('.m_media_left i').remove();
                $('.m_media_left').append('<img src="'+cover+'" data="'+cover+'" att_id="'+att_id+'">');
            }
             
        } else if(type == 'bili') {
            if($('.video_choose').length > 0){
                var bvid = m_data.bvid;
                UIkit.switcher('.video_choose').show(1);
                $('input#moment_video_bili').val(bvid);
            }
       
        }
        
        if($('.edit_video_box').length == 1){
            clearInterval(add);
        }

       }, 100); 
}

function edit_audio(data){
    var m_data = data.moment_data[0];
    var type = m_data.type;
    $('.moment_audio_type a').click();
    var add = setInterval(function() {
        if(type == 'local'){
            var url = m_data.url;
            var cover = m_data.cover;
            var author = m_data.author ? m_data.author : '';
            var title = m_data.title;
            $('.m_media_left i').remove();
            $('.m_media_left').append('<img src="'+cover+'" data="'+cover+'" att_id="">');
            $('input#moment_audio_name').val(title);
            $('input#moment_audio_author').val(author);
            $('input#moment_audio_url').val(url);
             
        } else {
            if($('.audio_choose').length > 0){
                var n_id = m_data.n_id;
                var index = $('.audio_choose li.'+type+'').index();
                UIkit.switcher('.audio_choose').show(index);
                $('.'+type+'_audio').append('<input type="text" placeholder="歌曲ID" name="moment_audio_api" id="moment_audio_api" class="required" required="required">');
                $('input#moment_audio_api').val(n_id);
            }    
        }
        
        if($('.edit_audio_box').length == 1){
            clearInterval(add);
        }

       }, 100);
}

function edit_card(data){
    var m_data = data.moment_data;
    $('.moment_card_type a').click();
    var pid = data.pid;
    var card_list = $("#post-"+pid+"").find('.moment_card_item');
    var add = setInterval(function() {
        $('.show_card').show();
        $.each(card_list, function(index, value) {
            $('.card_sortble').append(card_list);
            $(this).removeClass('loop_card_item');
            $(this).find('a').removeAttr('href');
            if(!$(this).find('.de_card').length > 0){
                $(this).find('.right').after('<span class="de_card"><i class="ri-close-line"></i></span>');
            }
            
        });

        if($('.edit_card_box').length == 1){
            clearInterval(add);
        }
    }, 100);
}   

