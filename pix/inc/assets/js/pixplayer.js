/*
* pix主题音乐播放器 
*/

var rem=[];
rem.audio = $('<audio id="pix_player"></audio>');

var audiobox = $('<audio id="pix_player"></audio>');
var au = $('#pix_player');
var volume = 1;
var playlist = [];
var post_playlist = [];
var Paused = true;


var m_cover = $('.player_mod .m_cover img');
var m_title	= $('.player_mod .m_info h2');
var m_artist = $('.player_mod .m_info small');


$(document).on('click','.play_btn',function(){
	$('.player_box').append(audiobox);
	$('.pix_player').removeClass('playing');
    $(this).parents('.pix_player').addClass('playing');
    var url = $(this).attr('data'); 
    var audio_s = $('#pix_player').attr('src');
	//var play = new Audio(url);
	
	var meta = $(this).siblings('.player_meta').find('.title');
	m_title.text(meta.find('.name').text());
	m_artist.text(meta.find('.author').text());
	m_cover.attr('src',$('.pix_player.playing .player_thum img').attr('src'));

    if(audio_s == '' || audio_s !== url){
		$('#pix_player').attr('src',url);
		audiobox[0].play();
    } else {
		pasued();
    }

	$(".play_btn").html("<i class='ri-play-line'></i>");
	
	initAudio();
});

// 初始化函数
function initAudio(){

	// 给音乐标签绑定事件之后所触发的函数
	audiobox[0].addEventListener("play",audioplay);
	audiobox[0].addEventListener("pause",audiopause);
	// timeupdate,代表音乐播放过程中只要发生变化就触发updateProcess
	audiobox[0].addEventListener("timeupdate",updateProcess);
	if(Theme.bgm_open == true){
		audiobox[0].addEventListener("ended",endplay);
	}
}

//音乐结束触发
function endplay(){
	var index = $('.musci_list_box li.active').index() + 1;
	var max = $('.musci_list_box li').length;
	var new_mid = index > (max - 1) ? 0 : index;
	mulist_play(new_mid);
}

// 当音乐标签加载完成之后所触发的函数
audiobox[0].oncanplay=function(){
	// 音乐的总时间
	var duration=handleTime(this.duration);
	// 音乐播放的当前时间
	var currenttime=handleTime(this.currentTime);
	$(".timer .total_time").text(duration);
	$(".timer .current_time").text(currenttime);
	initAudio();
}
	
// 音乐播放暂停的函数
function pasued(){
	// paused,保存音乐播放和暂停的状态
	if(Paused === false){//音乐是一个播放状态
		audiobox[0].pause();
	}else{
		audiobox[0].play();
	}
}

// 音乐播放之后触发的函数
function audioplay(){
	//var height = $('.footer_menu').height();
	var mheight = $('.footer_nav_box').height();
	$('.footer_nav_box').animate({top:-mheight},200,'linear');
	//music_bar.lock(false);//取消进度条的锁定
	Paused=false;//更新音乐的播放状态(暂停)
	$(".pix_player.playing .play_btn").html("<i class='ri-pause-line'></i>");//暂停按钮
	$('.m_play').html('<i class="ri-pause-circle-fill"></i>');
}

// 音乐暂停之后触发的函数
function audiopause(){
	//music_bar.lock(true);//添加进度条的锁定
	Paused=true;  //播放的状态
	$(".pix_player.playing .play_btn").html("<i class='ri-play-line'></i>");
	$('.m_play').html('<i class="ri-play-circle-fill"></i>');
}

// 更新进度条
function updateProcess(){
	// 如果音乐不是暂停状态，则继续执行函数的代码块
	if(Paused!==false) return true;
	// 音乐的总时间
	var duration = handleTime(this.duration);
	// 音乐播放的当前时间
	var currenttime = handleTime(this.currentTime);
	var percent = audiobox[0].currentTime/audiobox[0].duration;
	$(".player_mod .player_bar .progress").css('width',(percent)*100+"%");
	$(".timer .total_time").text(duration);
	$(".timer .current_time").text(currenttime);
}

// 时间处理的函数
function handleTime(seconedTime){
	// 定义一个变量保存分钟
	var minute=parseInt(seconedTime/60,10);
	if(minute<10){minute="0"+minute};
	//console.log(minute);
	// 定义变量存放秒数
	var second=(seconedTime-minute*60).toFixed(2).split(".")[0];
	//console.log(second);
	if(second<10){second="0"+second};
	var Time=minute+":"+second;
	// 返回最终的时间数值
	return Time;
}

//视频播放
var video = $('#pix_video_player');
$(document).on('click','.video_play_btn',function(){
	var video = $(this).siblings('#pix_video_player');
	video.attr('controls','controls'); //显示控制条
	$(this).remove(); //去除覆盖层
	video[0].play();
});

function stopOtherMedia(element) {

    $("video").not(element).each(function(index, video) {
        video.pause();
    });

}




//背景音乐
function autoload_music() {
	var state = Theme.bgm_open;
	if(state == true){
		$.ajax({
			type: "post",
			url:Theme.ajaxurl,
			dataType:  'json',
			data: {
				action:'bg_music_autoload',
				},	
			success: function (res) {
				if(res != '0'){
					var f = res[0];		
					var playnum = 1;
					//$(".m_play").html('<i class="ri-pause-circle-line"></i>');
		
					//默认播放第一首
					audiobox.attr('src',f.mid);
					$('.player_mod .m_cover img').attr('src',f.pid);
					$('.player_mod .m_info h2').text(f.title);
					$('.player_mod .m_info small').text(f.artist);
					$('.player_box').append(audiobox);
					
					
					//存储一个播放列表
					playlist.push(res);
		
					//插入播放列表
					$.each(res, function(key, data) {
						$(".musci_list_box").append('<li class="item" id='+ key +'>'  + playnum++ + '. ' + data.title + ' - ' + data.artist + '</li>');
					});
		
					$(".musci_list_box li").removeClass('active').eq(0).addClass('active');
					//console.log(playlist);
				} else {
					$(".musci_list_box").append('<div class="nodata">背景音乐未设置</div>');
				}
				
			}
		});
	}

}


//播放选中音乐
function mulist_play(index){
	audiobox[0].removeEventListener("play",audioplay);
	audiobox[0].removeEventListener("pause",audiopause);
	$(".pix_player.playing .play_btn").html("<i class='ri-play-line'></i>");
	$(".m_play").html('<i class="ri-pause-circle-fill"></i>');
	$('.musci_list_box li').eq(index).addClass('active').siblings().removeClass('active');
	var data = playlist[0][index];
	//console.log(data);
	audiobox.attr('src',data.mid);
	m_cover.attr('src',data.pid);
	m_title.text(data.title);
	m_artist.text(data.artist);
	audiobox[0].play();

	Paused = false;
}

//点击列表播放
$(document).on('click','.musci_list_box li',function(){
	$('.musci_list_box li').removeClass('active');
	$(this).addClass('active');
	var id = $(this).attr('id');
	mulist_play(id)
});

//上一首
$(document).on('click','.m_prev',function(){
	var id = $('.musci_list_box li.active').index() - 1;
	var new_mid = id < 0 ? $('.musci_list_box li').length - 1 : id;
	mulist_play(new_mid);
});

//下一首
$(document).on('click','.m_next',function(){
	var id = $('.musci_list_box li.active').index() + 1;
	var max = $('.musci_list_box li').length;
	var new_mid = id > (max - 1) ? 0 : id;
	mulist_play(new_mid);
	//alert(new_mid);

});


//播放和暂停
function m_play() {

	if(Paused === false){//音乐是一个播放状态
		audiobox[0].pause();
		$('.m_play').html('<i class="ri-play-circle-fill"></i>');
		Paused = true;
	}else{
		audiobox[0].play();
		$('.m_play').html('<i class="ri-pause-circle-fill"></i>');
		Paused = false;
	}
	
}

//播放按钮
$(document).on('click','.m_play',function(){
	if(!$("#pix_player").length > 0){
		cocoMessage.error('没有音乐可播放');
		return false;
	} else {
		m_play();
	}
	
});

/*
document.addEventListener('click', musicPlay);
function musicPlay() {
    audiobox[0].play();
    document.removeEventListener('click', musicPlay);
}
*/

//触发显示播放器
var trigger;
function mu_box_show(){
	clearTimeout(trigger);
	var mheight = $('.footer_nav_box').height();
	$('.footer_nav_box').animate({top:-mheight},200,'linear');
}

function mu_box_hide(){
	$('.footer_nav_box').animate({top:"0px"},200,'linear');
}

$(document).on('mouseenter', '.player_hand , .footer_nav_box', function(event) {
    mu_box_show();

});    

$(document).on('mouseleave', '.footer_nav_box .right_inner', function(event) {
                  
    trigger = setTimeout(function(){
        mu_box_hide();
    },2000);               
	                 
});

//音乐进度条跳转
function getMousePosition(e){
	var e = e || window.event;
	var x = e.pageX;
	var y = e.pageY;
	return {'left':x,'top':y}
}

$(document).on('click','.player_bar',function(){
	// 获取当前鼠标点击的位置
	// console.log(getMousePosition().left)
	// console.log($('.progress').offset())
	var long = (getMousePosition().left) - ($('.progress').offset().left);
	// console.log(long)
	// 将当前点击的长度重新给p标签
	$('.progress').width(long);
	// 获得当前点击长度的时间
	allTime = parseInt(audiobox[0].duration);
	var nowtime = (long/$('.player_bar').width()) * allTime;
	audiobox[0].currentTime = nowtime;
});


//音量调节  m_volume
$(document).on('click','.m_volume',function(){
	if($(this).hasClass('mute')){   // 如果当前是静音
		$(this).removeClass('mute');
		$(this).html('<i class="ri-volume-down-line"></i>');
		audiobox[0].muted = false;
	}else{  // 如果当前不是静音
		$(this).addClass('mute');
		audiobox[0].muted = true;
		$(this).html('<i class="ri-volume-mute-line"></i>');
		
	}
});

$(document).on('click','.vo_bar',function(){
	var long = (getMousePosition().top) - ($(this).offset().top)

	var meter = long / $(this).height();
	var finalLong = 1 - meter;

	$('.vo_size').height(finalLong * $(this).height());
		
	// 将audio音量调整为对应的音量
	//var finalLong = volume;
	audiobox[0].volume = finalLong;
		
	// 改变数字
	//$('.vol b').html(parseInt(finalLong * 100) + '%')
		
	// 点击后音量调整键隐藏
	//$('.vol a').css('display','none')

});

//文章歌曲
function autoload_posts_music() {
	post_playlist.splice(0,post_playlist.length);
	var pid = $('.posts_mu_list').attr('pid');
	if(pid){
		$.ajax({
			type: "post",
			url:Theme.ajaxurl,
			dataType:  'json',
			data: {
				action:'posts_music_autoload',
				pid:pid
				},	
			beforeSend: function () {
				$('.posts_mu_list').html('<div class="loading_box"><div uk-spinner></div></div>');
			},	
			success: function (res) {
			
				if(res != '0' || res != '1'){
					$('.posts_mu_list .loading_box').remove();
						
					var playnum = 1;
	
					if(!$('#pix_player').length > 0){
						$('.player_box').append(audiobox);
					}
					
					
					//存储一个播放列表
					post_playlist.push(res);
		
					//插入播放列表
					$.each(res, function(key, data) {
						$(".posts_mu_list").append('<li class="item" id='+ key +'><div class="mu_id">' + playnum++ + '</div><a class="s_play_btn"><i class="ri-play-circle-line"></i></a><div class="mus_info">'+ data.title +' <span>- ' + data.artist + '</span></div></li>');
						
					});
		
					//$(".musci_list_box li").removeClass('active').eq(0).addClass('active');
					console.log(post_playlist);
				} 
				
			}
		});
	}
    
}

//文章歌曲播放
$(document).on('click','.s_play_btn',function(){
	mu_box_show();
	$('.posts_mu_list li').removeClass('active');
	audiobox[0].removeEventListener("play",audioplay);
	audiobox[0].removeEventListener("pause",audiopause);
	$(".m_play").html('<i class="ri-pause-circle-fill"></i>');
	$(this).parent().addClass('active');

	var id = $(this).parents('li').attr('id');
	var data = post_playlist[0][id];
	//console.log(data);
	audiobox.attr('src',data.mid);
	m_cover.attr('src',data.pid);
	m_title.text(data.title);
	m_artist.text(data.artist);
	audiobox[0].play();

	Paused = false;
});

//播放器按钮
$(document).on('click','a.bg_music',function(){
	mu_box_show();
});



$(function () {
	autoload_music();	
	autoload_posts_music();
});

//播放类型切换
$(document).on('click','.audio_c_btn',function(){
	var type = $(this).attr('au_type');
	var input = $('<input type="text" placeholder="歌曲ID" name="moment_audio_api" id="moment_audio_api" class="required" required="required">');
	$('.type_audio_text').empty();
	$('.'+type+'_audio.type_audio_text').append(input);
});





