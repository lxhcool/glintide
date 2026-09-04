var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
var storage = window.localStorage;

var lazyLoadInstance = new LazyLoad({

});

//代码高亮
hljs.highlightAll();

$(document).on('click','.left_menu_box ul li a , .widget_nav_menu ul li a',function(){
    var t = $(this);
    t.siblings("ul").slideToggle(200);
    t.parent().siblings().find("ul").hide(200);
    if(t.parent().hasClass('has_children')){
        t.find('.drop_icon').toggleClass('up');
    }
    $('.left_menu_box ul li').removeClass('current-pjax-item current-menu-item current-menu-parent current-menu-ancestor');
    t.parent().addClass('current-pjax-item');
});

//全局loading
function loading_template() {
    tag = '<div class="loader" uk-spinner></div>';
    tag += '</div>';

    return tag;
}

function loading_start(target) {
    target.append( loading_template() );
}

function loading_done(target) {
    target.children('.loader').remove();
}

//话题表情添加
$(document).on('click','a.smile_btn',function(){
    var a = $(this).html();
	var textarea = $("textarea#topic_content");
	var content = textarea.val();
	textarea.val(content+a);
	textarea.focus();
});

$(document).on('click','.smile_box i',function(){
    var t = $(".smile_show");
    t.slideToggle(100);
});

//选择话题
$(document).on('click','.t_cat_box li',function(){
    var id = $(this).attr('catid');
    var name = $(this).find('.c_name span').html();
    $('.de_cat').attr('catid',id);
    $('.t_cat_toogle span').html(name);

    UIkit.dropdown('.t_cat_box').hide(false);
});

$(document).on('click','.up_cat_btn',function(){
    var name = $('input#add_cat').val();
    if(!name){
        cocoMessage.error("请填写话题");
        return false;
    }
    $('.t_cat_toogle span').text(name).attr('catid','');
    $('.de_cat').attr('catid','');
    UIkit.dropdown('.t_cat_box').hide(false);
});

$(document).on('click','.simi a',function(){
    var state = $(this).attr('state');
    if(state == '1'){
        $(this).html('<i class="ri-lock-line"></i>').attr('state','0');
        $(this).children().css({"background":"#ddd","color":"#c6c6c6"});
    } else {
        $(this).html('<i class="ri-lock-unlock-line"></i>').attr('state','1');
        $(this).children().css({"background":"#e3efe7","color":"#66c187"});
    }
});

//消息通知
cocoMessage.config({
    duration: 2000,
});

  

//ajax上传图片到媒体库
$(document).on('change','#topic_img_up', function(e) {
            e.preventDefault();
            if($('#topic_img_up').val() == ''){
                return;
            }
                
            var data = new FormData($('topic_img_up')[0]);
            var imgtot = 0;
			$.each($('#topic_img_up')[0].files, function(i, file) {
                data.append('topic_img_up[]', file);
                imgtot += file['size'];
            });

            //计算总大小
            var tot = imgtot/1024000;
            $(".up_img_btn i").remove();
            $(".up_img_btn").prepend("<div class='img_load_text'><div uk-spinner='ratio: .6'></div><span>"+tot.toFixed(2)+"MB</span></div>");

            var num = $('.add_img_box .t_media_item').length;
            
        $.ajax({
            type: "POST",
            url:Theme.ajaxurl + "?action=upload_topic_img",
            dataType:  'json',
            data: data,	
            processData : false,
            contentType : false,
            beforeSend: function () {
                if(check_image_num()){
                    return false;
                };
                $('input#topic_img_up').attr('disabled','disabled');
            },
            success: function(data){
               if(data.code == '0'){
                   var list = data.msg;
                   $.each(list,function(i,value){
                    var thum = value['thumb'];
                    var src = value['src'];
                    var media = '<div class="t_media_item" data-src="'+src+'" data-thum="'+thum+'">';
                    media += '<a class="topic-img-de"><i class="ri-subtract-line"></i></a>';
                    media += '<img src="'+thum+'">';//图片预览 
                    media += '</div>';
                    $(".img_show").prepend(media);
                   });  

                                 
               } else if(data.code == '1'){
                    cocoMessage.error(data.msg);
               }   
               
               $(".img_load_text").remove();
               $(".up_img_btn").prepend("<i class='ri-add-line'></i>");
               if(num == '8'){$("a.up_img_btn").hide();}
               $('input#topic_img_up').removeAttr('disabled');
            },      
        });    
});  

//检查上传图片数量
function check_image_num(){
    var num = $('.add_img_box .t_media_item').length;
    if(num > 8) {
        cocoMessage.error("最多上传9张图片");
        return true;
    }
    //if(num == '8'){$("a.up_img_btn").hide();}
}

//sortable事件 隐藏input
UIkit.util.on('.img_show', 'start', function (item) {
    $(".up_img_btn").hide();
  });
UIkit.util.on('.img_show', 'moved', function (item) {
    var img_num = $('.add_img_box .t_media_item').length;
    if(img_num < 9){
        $(".up_img_btn").show(); 
    }

    });  
UIkit.util.on('.img_show', 'stop', function (item) {
    var img_num = $('.add_img_box .t_media_item').length;
    if(img_num < 9){
        $(".up_img_btn").show(); 
    }
    });  

//获取媒体库图片
$(document).on('click','.up_from_media a',function(){
    $(".attch_nav .pre").hide();
    $(".attch_nav .nex").hide();
    $(".attch_nav").attr('paged','1');
    $(".wp_get_media_list").empty();
    $(".show_media_box").show();
    $.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'get_media_imglist',
		
			},	

		beforeSend: function () {
			
		},
		success: function(data){
            if(data.code == '0'){
                var list = data.list;
                $.each(list,function(i,value){
                    var thum = value['thumb'];
                    var src = value['src'];
                    var wf_media = '<li data-src="'+src+'"><img src="'+thum+'"></li>';//图片预览                   
                    $(".wp_get_media_list").append(wf_media);
                   }); 
                var max = data.max_page;   
                if(max>1){
                    $(".attch_nav .nex").show();
                } else {
                    $(".attch_nav .nex").hide();
                }
            }
    
		}	
	});
});   

//媒体库分页
$(document).on('click','.attch_nav a',function(){
    $(".wp_get_media_list").empty();
    var type = $(this).attr('class');
    var paged = $(".attch_nav").attr('paged');
   
    if(type == 'nex'){
        var paged = parseInt(paged) +1;
    } else if(type == 'pre' && paged !==''){
        var paged = parseInt(paged) - 1;
    }

    $(".attch_nav").attr('paged',paged);
    $.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'get_media_imglist',
            paged:paged
			},	

		beforeSend: function () {
			
		},
		success: function(data){
            if(data.code == '0'){
                $(".wp_get_media_list").empty();
                var list = data.list;
                $.each(list,function(i,value){
                    var thum = value['thumb'];
                    var src = value['src'];
                    var wf_media = '<li data-src="'+src+'"><img src="'+thum+'"></li>';//图片预览                   
                    $(".wp_get_media_list").append(wf_media);
                   }); 

                var max = data.max_page;
                if(max <= paged){
                    $(".attch_nav .nex").hide();
                } else {
                    $(".attch_nav .nex").show();
                }   

                if(paged > 1){
                    $(".attch_nav .pre").show();
                } else {
                    $(".attch_nav .pre").hide();
                }
            }
    
		}	
	});
});  

//收起媒体库
$(document).on('click','.show_media_box .souqi',function(){
    $(".show_media_box").hide();
    $(".wp_get_media_list").empty();

});   

//从媒体库插入图片
$(document).on('click','.wp_get_media_list li',function(){
    var num = $('.add_img_box .t_media_item').length;
    if(num == '8'){$("a.up_img_btn").hide();}
    
    if(check_image_num()){
        return false;
    };
   var thum = $(this).children().attr('src');
   var src = $(this).attr('data-src');
   var media = '<div class="t_media_item" data-src="'+src+'" data-thum="'+thum+'">';
       media += '<a class="topic-img-de"><i class="ri-subtract-line"></i></a>';
       media += '<img src="'+thum+'">';//图片预览 
       media += '</div>';                 
   $(".img_show").prepend(media);

});   

//删除图片
$(document).on('click','.topic-img-de',function(){                    
    var msg = "确认删除此图片？"; 
    if (confirm(msg)==true){ 
        $(this).parent().remove();
    } else { 
     return false; 
    } 

    var num = $('.add_img_box .t_media_item').length;
    if(num < 9) {
        $("a.up_img_btn").show();
    } 

});	

//插入外部图片链接
$(document).on('click','.up_from_cdn a',function(){ 
    //$(".show_media_box .souqi").click();                   
    $(".show_cdn_media").show();

});	

//取消
$(document).on('click','a.img_link_cancel',function(){                   
    $(".show_cdn_media").hide();
});	

//插入
$(document).on('click','a.img_link_btn',function(){                   
        var img_url = $("#img_link_up").val();
        //获得上传文件名
        if (!/\.(jpg|png|gif|webp|jpeg)$/.test(img_url) || img_url == '') {
            cocoMessage.error("图片格式不正确！");
            return false;
         } 

        if(check_image_num()){
            return false;
        };

        var num = $('.add_img_box .t_media_item').length;
        if(num == '8'){$("a.up_img_btn").hide();}

         var src = img_url;
         var media = '<div class="t_media_item" data-src="'+src+'" data-thum="'+src+'">';
             media += '<a class="topic-img-de"><i class="ri-subtract-line"></i></a>';
             media += '<img src="'+src+'">';//图片预览 
             media += '</div>';                  
         $(".img_show").prepend(media);

         $("#img_link_up").val('');
});	

//ajax获取我的地理位置
$(document).on('click','.loca .laqu',function(){
    $.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'get_myip',
			},	

		beforeSend: function () {
			$(".loca_text").text("数据拉取中..");
		},
		success: function(data){
            var city = data.city;
            var province = data.province;
            var loca = ""+province+" · "+city+"";
            var status = data.status;
            if(status == 1){
                $.cookie('mylocal', loca, { expires: 30, path: '/' });
                $(".loca_text").html(loca).attr("state",'1'); //设置为开启
            } else if(status == 0) {
                cocoMessage.error(data.info);
            }
            
		}	
	});
}); 

//插入自定义位置
$(document).on('click','a.set_local_btn',function(){
    var loca = $("#set_local").val();
    var state = $(".loca_text").attr('state');
    
    //$("a.close_local").text('位置已开启').css('color','#8890cc');
   
    if(loca == ''){
        cocoMessage.error("为空则不显示位置");
        $(".loca_text").text('').attr("state",'0');
        $("a.close_local").text('位置已关闭').css('color','#ddd');
        $.removeCookie('mylocal',{ path: '/'});
    } else {
        $(".loca_text").text(loca).attr("state",'1');
        $("a.close_local").text('位置已开启').css('color','#8890cc');
        $.cookie('mylocal', loca, { expires: 30, path: '/' });
    }
    
});   

//关闭地理位置
$(document).on('click','a.close_local',function(){
    $(".loca_text").text('').attr("state",'0');
    $("a.close_local").text('位置已关闭').css('color','#ddd');
    $.removeCookie('mylocal',{ path: '/'});
});   

$(document).on('click','.loca_text',function(){
    $("a.close_local").text('位置已开启').css('color','#8890cc');
});     

//发布文章
$(document).on('click','.push_item',function(){
	var content = $("#topic_content").val(); //内容
	var catid = $(".de_cat").attr('catid'); //分类id
    var catname = $(".t_cat_toogle span").text(); //分类名
	var title = $("#topic-title").val(); //标题
    var loca = $(".loca_text").text(); //位置
    var simi = $(".simi a").attr('state'); //私密 1：公开 0：私密
    var act = $('.push_item').attr('action');
    var pid = $('.push_item').attr('pid');
    var moment_type = $('.push_item').attr('type'); //片刻类型

    if(get_moment_error(moment_type) === false){
        cocoMessage.error("请填写必要参数！");
        return false;
    }
	
    var min = Theme.min_push;

    if(min > 0){
        if(content==''){
            cocoMessage.error("请输入内容");
            return false; 
        }	
    
        if(content.length < min){
            cocoMessage.error("内容不得少于"+min+"个字");
            return false; 
        }
    }

    get_moment_data(moment_type);

	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			action: 'push_topic',
			content:content,
			catid:catid,
			moment_data:moment_data,
			title:title,
            catname:catname,
            loca:loca,
            simi:simi,
            act:act,
            pid:pid,
            moment_type:moment_type
			},	

		beforeSend: function () {
			cocoMessage.info("发布中..");
		},
		success: function(data){
			if(data.status == '0') {
				cocoMessage.error(data.msg);
				return false;
			} 
            cocoMessage.success('发布成功！');
				location.reload();
			
		
		}	
	});
});	

//获取片刻类型参数
function get_moment_data(type){
    moment_data = [];  
    switch(type)
	{
		case "image":
            $(".t_media_item").each(function(){
                var src = $(this).attr('data-src');
                var thum = $(this).attr('data-thum');
                var obj = {
                    src:src,
                    thum:thum,
                }
                moment_data.push(obj); //图片
            });
			break;
		case "card": 
            $(".card_sortble .moment_card_item").each(function(){
                var pid = $(this).attr('pid');
                moment_data.push(pid); //卡片
            });  
			break;
        case "audio": 
            get_audio_data(); 
            break;
        case "video": 
            get_video_data();     
            break;    
	}
}

//片刻音乐参数
function get_audio_data(){
    var type = $('.audio_choose li.uk-active a').attr('au_type');
    if(type == 'local'){
        var url = $('input#moment_audio_url').val();
        var title = $('input#moment_audio_name').val();
        var author = $('input#moment_audio_author').val();
        var cover = $('.loacl_audio .audio_left img').attr('data');
        var obj = {
            url:url,
            title:title,
            author:author,
            cover:cover,
            type:type
        }

    } else {
        var n_id = $('input#moment_audio_api').val();
        var obj = {
            n_id:n_id,
            type:type
        }

    }

    moment_data.push(obj);
     
}

//片刻视频参数
function get_video_data(){
    var type = $('.video_choose li.uk-active a').attr('vi_type');
    if(type == 'local'){
        var url = $('input#moment_video_url').val();
        var cover = $('.m_media_left img').attr('data');
        var att_id = $('.m_media_left img').attr('att_id');
        var obj = {
            url:url,
            cover:cover,
            att_id:att_id,
            type:type
        }

    } else if(type == 'bili'){
        var bvid = $('input#moment_video_bili').val();
        var obj = {
            bvid:bvid,
            type:type
        }

    }

    moment_data.push(obj);
     
}

//片刻空值提示
function get_moment_error(type){
    switch(type)
	{
		case "card": 
            var v = $('.show_card .moment_card_item'); 
            if(v.length == 0){
                return false;
            }
			break;
        case "audio": 
            var au_type = $('.audio_choose li.uk-active a').attr('au_type');
            var url = $('input#moment_audio_url').val(); //歌曲链接
            var title = $('input#moment_audio_name').val(); //标题
            var cover = $('.loacl_audio .audio_left img').length; //封面
            if(au_type == 'local'){
                if(url == '' || title == '' || cover == 0){
                    return false;
                }
            } else {
                var n_id = $('input#moment_audio_api').val();
                if(n_id == ''){
                    return false;
                }
            }
        break;    
        case "video": 
            var vi_type = $('.video_choose li.uk-active a').attr('vi_type');
            var url = $('input#moment_video_url').val();
            //var cover = $('.m_media_left img').length;
            if(vi_type == 'local'){
                if(url == ''){
                    return false;
                }
            } else {
                var bvid = $('input#moment_video_bili').val();
                if(bvid == ''){
                    return false;
                }
            }
        break;
	}
}

//ajax 分类筛选 moment
//readmore_data =[];//定义数组
$(document).on('click','.moment_cat_nav ul li a',function(){
    var t = $('.moment_cat_nav ul li a');
    if(t.hasClass('disabled')){
        return false;
    }
    $('.moment_cat_nav ul li a').addClass('disabled');
    
    //readmore_data.splice(0,readmore_data.length);//清空数组
    //5.22增加 移动回复表单
    var temp = $("#comment_form_reset");
    var form = $(".respond_box");		
    var form = $("#t_commentform").prop('outerHTML');
    temp.html(form );
    
    $(".moment_list").empty();
    $('#t_pagination a').hide();
    $(this).addClass('active');
    $(this).parent().siblings().children().removeClass('active');
    var cat = $(this).attr('data');
    var name = $(this).find('span').html();
    
    
    $.ajax({
        type: "post",
        url:Theme.ajaxurl,
        dataType:  'json',
        data: {
            'action':'moment_cat_filter',
            cat:cat,
            },	
    
        beforeSend: function () {
            $('.moment_list').html('<div class="loading_box"><div uk-spinner></div></div>');
        },
        success: function(data){
            $('.moment_list .loading_box').remove();
            $('#t_pagination a').text( 'LOAD MORE' );
            Theme.current_page = 1;
            Theme.posts = data.posts;
            Theme.max_page = data.max_page;
            var result = $(data.content);
            $(".moment_list").append(result.fadeIn(300));
    
            if ( data.max_page < 2 ) {
                $('#t_pagination a').hide();
            } else {
                $('#t_pagination a').show();
            }
       
            lazyLoadInstance.update();
            $('.moment_cat_nav ul li a').removeClass('disabled');
        
        }	
    });
    });	



//ajax加载片刻
$(document).on('click', '#t_pagination a', function() {
	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'moment_list_load',
			query:Theme.posts,
			page:Theme.current_page
			},	

		beforeSend: function () {
			$('#t_pagination .post-paging').html( '<div uk-spinner></div>' );
		},
		success: function(posts){
			
			if( posts ) {
				var result = $(posts.content);
				$('#t_pagination .post-paging').html( '<a>LOAD MORE</a>' );
                $(".moment_list").append(result.fadeIn(300));
			    $body.animate({scrollTop: result.offset().top - 58}, 500 );
				Theme.current_page++;
                
				if ( Theme.current_page == Theme.max_page ) 
					$('#t_pagination a').hide(); 

                lazyLoadInstance.update();
			} else {
				$('#t_pagination a').hide(); 
			}

            
		}	
	});
	return false;
	
});

//阅读更多按钮
$(document).on('click', 'a.show-more-btn', function() {
    var t = $(this);
    t.prev().show();
    t.next().show();
    t.siblings('.dotd').hide();
    t.hide();
});   

$(document).on('click', 'a.read-less-btn', function() {
    var t = $(this);
    t.siblings('.rm_hidden').hide();
    t.siblings('.dotd').show();
    t.prev().show();
    t.hide();
}); 


//自动加载moment列表
function autoload_topic(){
	var a = $(".moment_cat_nav ul li a.active");
	if(a.length > 0){
        if(a){
            a.click();
        }    
    }

}
autoload_topic();


//加载阅读全文
/*
function load_readmore(post_data){
   var max = 80;
   var moretext = '更多';
   var lesstext = '收起';
   var data = $(post_data).find('.t_content p');
    $.each(data, function(index, value) {
    //console.log(value);
    var t =  $(this);
    var str = t.text();
    //if (str.length > max) {
    var excerpt = str.substring(0, max);
    var secdHalf = str.substring(max, str.length);
    var strtoadd = excerpt + "<span class='second-section'>" + secdHalf + "</span><a class='show-more-btn'  title='Click to Show More'>" + moretext + "</a><span class='read-less' title='Click to Show Less'>" + lesstext + "</span>";
    $(this).html(excerpt);
    
    //}
    console.log(excerpt);
});

}
*/

//正则替换链接 无用
function replaceReg(str){
        var regexp = /((http|ftp|https|file):\/\/([\w\-]+\.)+[\w\-]+(\/[\w\u4e00-\u9fa5\-\.\/?\@\%\!\&=\+\~\:\#\;\,]*)?)/ig;
        return str.replace(regexp,function(m){return '<a class="mo_link" target="_blank" href="'+m+'"><i class="ri-links-line"></i>'+m+'</a>';})
}

//ajax加载评论
$(document).on('click','.show_comment',function(){
    $('.blog_list_inner').css('min-height','calc(100vh)');
	var pid = $(this).attr('pid');
	var temp = $('#comment_form_reset');
	var respond = $(".toi_respond_"+pid+"");
	var wrap = $(".t_com_"+pid);
	var form = $("#t_commentform").prop('outerHTML');
    wrap.toggle();
	
	if(!wrap.find('form').length > 0){//判断是否存在打开的评论列表
		respond.prepend(form);
		respond.after("<div class='comment_box_"+pid+" commentshow'></div>");
		//$("textarea#comment").focus();
		temp.empty();
	} else {
		temp.append(form);
		respond.empty();
		$(".comment_box_"+pid+"").remove();
	}

	var other = $(this).parents('.moment_item').siblings('div');
	$.each(other, function(name, value) {

		//5.22修改 遍历每个 回复表单
		var otherid = $(this).find('.show_comment').attr('pid');
		$(".toi_respond_"+otherid+"").empty();
		$(".comment_box_"+otherid+"").remove();
		$(".t_com_"+otherid).hide();
   
   });

   //$body.animate({scrollTop: $(this).parents(".post_footer_meta").offset().top - 58}, 500 );

	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		//dataType:  'json',
		data: {
			'action':'load_t_comment',
			pid:pid,
			},	

		beforeSend: function () {
			$('.commentshow').html('<div class="loading_box"><div uk-spinner></div></div>');
		},
		success: function(data){
			$(".comment_box_"+pid+"").append(data);
			$("#comment_post_ID").val(pid);
			$("#comment_parent").val('0');
			$("#cancel-comment-reply-link").css("display","none");
            $('.commentshow .loading_box').remove();
		}	
	});

});	

//片刻评论提交
$(document).on("submit", "#t_commentform , #commentform",function(){
	var t = $(this);
	var parent = $('#comment_parent').val();
	var pid = $('#comment_post_ID').val();
    if(t.attr('id') == 't_commentform'){
        var respond = $("#t_commentform");
    } else {
        var respond = $(".comment-respond");
    }
	
	var _list = $('.comment-list');
	var cancel = $('#cancel-comment-reply-link');
	$.ajax({
		url:Theme.ajaxurl,
		data: $(this).serialize() + "&action=ajax_comment",
		type: $(this).attr('method'),	

		beforeSend: function () {
            cocoMessage.info("提交中....");
		},
		error: function(request) {
            cocoMessage.error(request.responseText);
		},
		success: function(data){
			if(parent !='0'){
				respond.before('<ul class="children">' + data + '</ul>');
			} else if(!_list.length){
				respond.after('<ul class="comment-list">' + data + '</ul>');
			} else {
				if (Theme.order == 'asc') {
					_list.append(data); 
				} else {
					_list.prepend(data); 
				}
			}
			cocoMessage.success("提交成功");
			cancel.click();
            $(".comment-list .nodata").remove();
		}	
	});
	return false;
});		

//回复按钮
$(document).on('click','.comment-reply-link',function(event){
	var t = $(this);
	event.preventDefault();
    var comid = t.attr('data-commentid');
    var at = t.parents(".commeta").find(".author a").text();
    var type = t.parents('.comments-area').attr('data');
    if(type == 'normal'){
        var form = $(".comment-respond").prop('outerHTML');
        $(".comment-respond").remove();
    } else {
        var form = $("#t_commentform").prop('outerHTML');
        t.parents(".toi_comments_main").find("#t_commentform").remove();
        
    } 
	
	var pid = t.attr('data-postid');
	var cancel = $("#cancel-comment-reply-link");
	$("#comment-"+comid+"").after(form);
	$("#comment_parent").val(comid);
	$("textarea#comment").focus().attr('placeholder','回复'+at);
	$("#cancel-comment-reply-link").css("display","");	
	
});	

//取消回复
$(document).on('click','#cancel-comment-reply-link',function(event){
	var t = $(this);
	event.preventDefault();
    var pid = $("#comment_post_ID").val();
    var cancel = $("#cancel-comment-reply-link");
    var type = t.parents('.comments-area').attr('data');
    if(type == 'normal'){
        var form = $(".comment-respond").prop('outerHTML');
        var respond_box = $("#respond_box"); 
        var temp = $("#wp-temp-form-div");
        t.parents(".comment-respond").remove();
        respond_box.append(form);
        $("#cancel-comment-reply-link").css("display","none");
        $("#comment_parent").val('0');
        $("textarea#comment").focus().attr('placeholder','不准备说点什么？');

    }else{
        var form = $("#t_commentform").prop('outerHTML');
        var respond_box = $(".toi_respond_"+pid+"");
        t.parents("form").remove();
        respond_box.append(form);
        $("#cancel-comment-reply-link").css("display","none");
        $("#comment_parent").val('0');
        $("textarea#comment").focus().attr('placeholder','不准备说点什么？');
    }
       
	

});	

//插入评论表情
$(document).on('click','.com_smile_show a.smile_btn',function(){
    var a = $(this).html();
	var textarea = $("textarea#comment");
	var content = textarea.val();
	textarea.val(content+a);
	textarea.focus();
}); 

//ajax评论翻页
$(document).on("click", ".commentnav a",
    function() {
        var baseUrl = $(this).attr("href"),
        commentsHolder = $(".commentshow"),
        id = $(this).parent().data("fuck"),
        page = 1,
        concelLink = $("#cancel-comment-reply-link");
        /comment-page-/i.test(baseUrl) ? page = baseUrl.split(/comment-page-/i)[1].split(/(\/|#|&).*jQuery/)[0] : /cpage=/i.test(baseUrl) && (page = baseUrl.split(/cpage=/)[1].split(/(\/|#|&).*jQuery/)[0]);
        concelLink.click();
        var ajax_data = {
            action: "ajax_comment_page_nav",
            post_id: id,
            paged: page
        };
		$('ul.comment-list').html('');
        loading_start($('ul.comment-list'));
        jQuery.post(Theme.ajaxurl, ajax_data,
        function(data) {
            commentsHolder.html(data);
            //remove loading
            $("body, html").animate({
                scrollTop: commentsHolder.offset().top - 50
            },
            1e3)
        });
        return false;
    });


//ajax获取有课评论头像
$(document).on('blur','input#email',function(){
	var _email = $(this).val();
    var _name = $(this).siblings("input#author").val();
	if (_email != '') {
		$.ajax({
			type: "POST",
			url: Theme.ajaxurl,
            dataType:  'json',
			data: {
				action: 'ajax_avatar_get', 
				email: _email,
                name:_name,
			},
			success: function(data) {
				$('.v-avatar').attr('src', data.avatar); // 替换头像链接到img标签
                $('a.edit-profile small').text(data.name+" , 编辑");
			}
		}); // end ajax
	}

	});

//片刻点赞
$(document).on('click','.up_like ',function(){
    if ($(this).hasClass('done')) {
		cocoMessage.info("您已经点过赞了");
        return false;
    } else {
        $(this).addClass('done');
        var pid = $(this).data("id");
        var like_action = $(this).data('action'),
        rateHolder = $(this).children('span');
        iconHolder = $(this).children('i');
        $.ajax({
			type: "POST",
			url: Theme.ajaxurl,
			data: {
				action: 'pix_ajax_like', 
                like_action:like_action,
                pid:pid
			},
			success: function(data) {
                $(iconHolder).toggleClass('ri-heart-2-fill');
				$(rateHolder).html(data);
                cocoMessage.success("感谢您的支持");
			}
		}); // end ajax
        
    }
});

//ajax分类筛选 posts
$(document).on('click','.posts_cat_nav ul li a',function(){
    var t = $('.posts_cat_nav ul li a');
    if(t.hasClass('disabled')){
        return false;
    }
    $('.posts_cat_nav ul li a').addClass('disabled');
    
    pager_next = $('.pager_inner .next');
    pager_prev = $('.pager_inner .prev');
    $('#post_pager .c_paged span').html('1');
    $('#post_pager').hide();
	$(".norpost_list").empty();
	$('#pagination a').hide();
	$(this).addClass('active');
	$(this).parent().siblings().children().removeClass('active');
	var cat = $(this).attr('data');
	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'blog_mod_loop',
			cat:cat,
			},	

		beforeSend: function () {

			$('.norpost_list').html('<div class="loading_box"><div uk-spinner></div></div>');
		},
		success: function(data){
			$('#pagination a').text( 'LOAD MORE' );
			Theme.current_page = 1;
			Theme.posts = data.posts;
			Theme.max_page = data.max_page;
			var result = $(data.content);
			$(".norpost_list").append(result.fadeIn(300));

			if ( data.max_page < 2 ) {
				$('#pagination a').hide();
			} else {
				$('#pagination a').show();
			}

            if($('#post_pager').length && $('#post_pager').length > 0){
                if(data.max_page < 2){
                    $('#post_pager').hide();
                } else {
                    $('#post_pager').show();
                    pager_prev.hide();
                    pager_next.show();
                    $('.pager_inner').attr('paged','1');
                }
                
            }
            
            $('.loading_box').remove();
            lazyLoadInstance.update();
            $('.posts_cat_nav ul li a').removeClass('disabled');
		}	
	});
});	

//首页ajax加载文章
$('body').on('click', '#pagination a', function() {
	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'blog_mod_load',
			query:Theme.posts,
			page:Theme.current_page
			},	

		beforeSend: function () {
			$('#pagination .post-paging').html( '<div uk-spinner></div>' );
		},
		success: function(posts){
			if( posts ) {
				result = $(posts.content);
				$('#pagination .post-paging').html( '<a>LOAD MORE</a>' );
				$('.norpost_list').append($(result).fadeIn(400));
			    $body.animate({scrollTop: result.offset().top - 58}, 500 );
				Theme.current_page++;

				if ( Theme.current_page == Theme.max_page ) 
					$('#pagination a').hide(); 

			} else {
				$('#pagination a').hide(); 
			}
            lazyLoadInstance.update();
		}	
	});
	return false;
});

function autoload_posts(){
    var a = $(".posts_cat_nav ul li a.active");   
    if(a.length > 0){
        if(a){
            a.click();
        }  
    }
}
	  
autoload_posts();    


//上下页
$('body').on('click', '#post_pager a', function() {
    $('.norpost_list').empty();
    pager_next = $('.pager_inner .next');
    pager_prev = $('.pager_inner .prev');
    pager_next.show();
    pager_type = $(this).attr('type');
    navpage = $(this).parent().attr('paged');
    var cat = $('.posts_cat_nav ul li a.active').attr('data');
    if(pager_type == 'next'){
        navpage++;
    } else if(pager_type == 'prev') {
        navpage--;
    }
    $(this).parent().attr('paged',navpage);
	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'blog_mod_load',
			query:Theme.posts,
			page:navpage,
            pager_type:pager_type,
            cat:cat
			},	

		beforeSend: function () {
			$('.norpost_list').html('<div class="loading_box"><div uk-spinner></div></div>');
		},
		success: function(posts){
			if( posts ) {
				result = $(posts.content);
				//$('#pagination .post-paging').html( '<a>LOAD MORE</a>' );
				$('.norpost_list').append($(result).fadeIn(400));
			$body.animate({scrollTop: result.offset().top - 58}, 500 );
            

				if ( navpage == Theme.max_page ){
                    pager_next.hide();
                } 

                if( navpage > 1) {
                    pager_prev.show();
                } else {
                    pager_prev.hide();
                }
                
                $('#post_pager .c_paged span').html(navpage);
			} 
            $('.loading_box').remove();
            lazyLoadInstance.update();
		}	
	});
	return false;
});

//分类页面文章加载
$('body').on('click', '.arc_pagenav a', function() {
	$(this).hide();
	var content = $('.norpost_list');
    var href = $(this).attr('href');
    if(href !=undefined){
        $.ajax({
            type: "GET",
            url:href,
            dataType: 'html',	
            beforeSend: function () {
                $('.arc_pagenav').append( '<div uk-spinner></div>' );			
            },
            success: function(data){  
                var post = $(data).find(".norpost_list .p_item");
                content.append(post.fadeIn(300));	
                var newhref = $(data).find(".arc_pagenav a").attr("href"); //找出新的下一页链接
                if (newhref != undefined) {
                    $(".arc_pagenav a").attr("href", newhref);
                    $('.arc_pagenav a').show();
                } else {
                    $(".arc_pagenav a").remove(); //如果没有下一页了，隐藏
                }

                $('.arc_pagenav .uk-spinner').remove();
                
                $body.animate({scrollTop: post.offset().top - 58}, 500 );
                lazyLoadInstance.update();
            }	
        });	
    }

    return false;
	
});	

//ajax登录
$('body').on('submit', 'form#login', function(e) {
    action = 'ajaxlogin';
    username = 	$('form#login #username').val();
    password = $('form#login #password').val();
    security = $('form#login #security').val();
    
    if(username == '' || password == ''){
        cocoMessage.error('请填写用户名或密码');
        return false;
    }
    

    ctrl = $(this);
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action': action,
            'username': username,
            'password': password,
            'security': security,
        },
        beforeSend: function () {
			cocoMessage.info('登陆中...');
		},
        success: function (data) {
            if (data.loggedin == true) {
                cocoMessage.success('登陆成功');
                document.location.href = Theme.redirecturl;
            } else {
                cocoMessage.error(data.message);
            }
        }
    });
    e.preventDefault();
});
   


$(document).on('mouseenter', '.lbc .left_menu_box ul li a , body.mod_third_s .left_menu_box ul li a', function(event) {
    var title = $(this).find('.nav_title').text();
    $(this).append("<span class='menu_tips'>"+title+"</span>");
});    

$(document).on('mouseleave', '.lbc .left_menu_box ul li a , body.mod_third_s .left_menu_box ul li a', function(event) {
    $(this).find('.menu_tips').remove();

});  

//滚动
$(document).ready(function(e) {
    $(window).scroll(function(){

      var b=$(window).scrollTop(); //监控窗口已滚动的距离;

      if(b>190){

        $('.top_bar').addClass('mobile_active');
    
        } else {
            $('.top_bar').removeClass('mobile_active');
        }

        if(b>200){
            $('a.go_top').addClass('show');
        } else {
            $('a.go_top').removeClass('show');
        }
    
        });
});    

$.fn.autoTextarea = function(options) {
    var defaults={
        maxHeight:null,//文本框是否自动撑高，默认：null，不自动撑高；如果自动撑高必须输入数值，该值作为文本框自动撑高的最大高度
        minHeight:50
    };
    var opts = $.extend({},defaults,options);
    return $(this).each(function() {
        $(this).bind("paste cut keydown keyup focus blur",function(){
            var height,style=this.style;
            this.style.height = opts.minHeight + 'px';
            if (this.scrollHeight > opts.minHeight) {
                if (opts.maxHeight && this.scrollHeight > opts.maxHeight) {
                    height = opts.maxHeight;
                    style.overflowY = 'scroll';
                } else {
                    height = this.scrollHeight;
                    style.overflowY = 'hidden';
                }
                style.height = height + 'px';
            }
        });
    });
};

//$("textarea#comment").autoTextarea({
   // maxHeight: 160,//文本框是否自动撑高，默认：null，不自动撑高；如果自动撑高必须输入数值，该值作为文本框自动撑高的最大高度
//});

$('body').on('click', '.com_msg_btn', function() {
    
    var num = $(this).attr('check');
    if(num == 0){
        return false;
    }
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            'action':'up_unread_msg',
        },
        beforeSend: function () {
		
		},
        success: function (data) {
            if (data.code == '0') {
               $('small.f_unread_num').remove();
            } else {
               return false;
            }
        }
    });

});

//搜索类型
$('body').on('click', '.s_set_box a', function(){
    var type = $(this).attr('data');
    $(this).addClass('active').siblings('a').removeClass('active');
    $('form#index_search input[type="hidden"]').val(type);
    $.cookie('s_type', type, { expires: 30, path: '/' });
});   

$('body').on('click', '.top_s_box a', function(){
    UIkit.dropdown('.s_set_box').hide(false);
    var data = $(this).attr('data');
    var type = $(this).text();
    $('.set_text').text(type);
    $('form#top_search input[type="hidden"]').val(data);
}); 


//黑暗模式
$('body').on('click', '.t_dark a', function(){
    var t = $('body');
    if(t.hasClass('dark')){
        $.cookie('dark', 'normal', { expires: 30, path: '/' });
    } else {
        $.cookie('dark', 'dark', { expires: 30, path: '/' });
    }
    t.toggleClass('dark');
    
});  

//片刻管理按钮  

$(document).on('mouseenter', '.post_control_btn', function(event) {
    var a = $(this).siblings('.post_control_box');
    a.addClass('show'); 
});    

$(document).on('mouseleave', '.post_control', function(event) {
    $(this).find('.post_control_box').removeClass('show');
});  


//社交小工具二维码
$(document).on('mouseenter', '.sw_social', function(event) {
    var qrbox = $(this).siblings('.sw_qrcode');
    if(qrbox.length > 0){
         qrbox.addClass('active');
         qrbox.fadeIn(200);
    }
 });    
 
 $(document).on('mouseleave', '.sw_social', function(event) {
    var qrbox = $(this).siblings('.sw_qrcode');
    if(qrbox.length > 0){
         qrbox.removeClass('active');
         qrbox.fadeOut(200);
    }
 });

 //点击收起移动侧栏
$('body').on('click', '.m_offcanvas', function(){
    UIkit.offcanvas('.m_offcanvas').hide();
}); 

//刷新一言
$('body').on('click', '.yiyan_box .change', function() {
    fetch('https://v1.hitokoto.cn')
    .then(response => response.json())
    .then(data => {
      const hitokoto = document.querySelector('.yiyan_box p')
      hitokoto.innerText = data.hitokoto
    })
    .catch(console.error)

});

//pjax
if(Theme.pjax){
    $(document).pjax('a[pjax!=exclude][target!=_blank]', '#pjax-container', { // .page 需要刷新的 DIV 部分的类名
        fragment: '#pjax-container',
        timeout: 8000,
    }).on('pjax:complete', function() {
        lazyLoadInstance.update();
        hljs.highlightAll();
        autoload_topic();
        autoload_posts();       
        autoload_posts_music();

        

    });
    
    $(document).on('pjax:start', function() { NProgress.start(); });
    $(document).on('pjax:end',   function() { NProgress.done(); });
}

//侧边栏固定
/*
$(document).scroll(function() {
    var scroH = $(document).scrollTop();  //滚动高度
    var viewH = $(window).height();  //可见高度 
    var contentH = $('.sidebar_right_inner').height();  //内容高度
    var width = $('.sidebar_right').width();
    var main = $('.page_main').height();

        if(contentH < viewH){
            $('.sidebar_right_inner').attr('uk-sticky','offset:72px');
        } else {
            if ((contentH - viewH) < (scroH - 135)){  //滚动条滑到底部啦
                var st = contentH - viewH;
                $('.sidebar_right_inner').addClass('side_fixed').css({"width":width,"top":-st});
                //$('.sidebar_right_inner').css('top','-500px');
            } else {
                $('.sidebar_right_inner').removeClass('side_fixed').css({"width":"auto","top":"auto"});;
            }
        }
 
});
*/
 


