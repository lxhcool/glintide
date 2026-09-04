<?php
//文章片刻 海报

//poster裁剪
function postercropper($source_path, $target_width, $target_height,$id)
{
$source_info = getimagesize($source_path);
$source_width = $source_info[0];
$source_height = $source_info[1];
$source_mime = $source_info['mime'];
$source_ratio = $source_height / $source_width;
$target_ratio = $target_height / $target_width;

// 源图过高
if ($source_ratio > $target_ratio)
{
$cropped_width = $source_width;
$cropped_height = $source_width * $target_ratio;
$source_x = 0;
$source_y = ($source_height - $cropped_height) / 2;
}
// 源图过宽
elseif ($source_ratio < $target_ratio)
{
$cropped_width = $source_height / $target_ratio;
$cropped_height = $source_height;
$source_x = ($source_width - $cropped_width) / 2;
$source_y = 0;
}
// 源图适中
else
{
$cropped_width = $source_width;
$cropped_height = $source_height;
$source_x = 0;
$source_y = 0;
}

switch ($source_mime)
{
case 'image/gif':
$source_image = imagecreatefromgif($source_path);
break;

case 'image/jpeg':
$source_image = imagecreatefromjpeg($source_path);
break;

case 'image/png':
$source_image = imagecreatefrompng($source_path);
break;

default:
return false;
break;
}

$target_image = imagecreatetruecolor($target_width, $target_height);
$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);

// 裁剪
imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
// 缩放
imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);

//保存图片到本地(两者选一)

//生成并保存海报
	$upload_dir = wp_upload_dir();
	$poster_dir = $upload_dir['basedir'].'/posterimg';
	if (!is_dir($poster_dir)){
		wp_mkdir_p($poster_dir);
	}
	$filename = '/poster-crop-'.$id.'.png';
	$url = $poster_dir.$filename;

	imagejpeg($target_image,$url);
	$src = $upload_dir['baseurl'].'/posterimg'.$filename;
	error_reporting(0);
	imagedestroy($target_image);

if(is_wp_error($src)){
		return false;
	}
	return $src;
}


//获取海报参数
function get_poster_data($post_id){
	$post = get_post($post_id);
	$title = $post->post_title;
	$content = $post->post_content;
	$logo = get_op('site_logo');
	$qrcode = pix_get_qrcode_base64(get_the_permalink($post_id));
	$banner = cst_get_thum( $post_id, 'large','lock');
	
	if(get_post_type($post_id) == 'moment'){
		$mo_img = get_image_moment_f($post_id);
		$banner = !empty($mo_img) ? $mo_img : $banner;
		$title = !empty($title) ? $title : ''.get_bloginfo('name').' - 片刻';
	}
	$poster_banner = pix_img2base64($banner);

	$res = array(
		'title' => $title,
		'content' => mb_substr(strip_tags(str_replace("\r\n","",$content)), 0, 90, 'utf-8').'...',
		'logo' => pix_img2base64($logo),
		'qrcode' => $qrcode,
		'banner' => $poster_banner,
		'des'   => get_admin_des(),
	);

	return $res;
}

//ajax创建海报
function pix_create_poster() {
	$post_id = isset($_POST['post_id']) ? $_POST['post_id'] : false;
	
	$arr = get_poster_data($post_id);

	echo json_encode($arr);
	exit();
	
}
add_action('wp_ajax_pix_create_poster', 'pix_create_poster');
add_action('wp_ajax_nopriv_pix_create_poster', 'pix_create_poster');

//图片转base64
function pix_img2base64($url){
	$cache_key = md5($url);
    $cache     = wp_cache_get($cache_key, 'image_base64', true);
    if ($cache !== false) {
        return $cache;
    }

    $base64_encode = base64_encode(pix_file_get_content($url));
    $base64 = $base64_encode ? 'data:image/jpeg;base64,' . base64_encode(pix_file_get_content($url)) : false;

	wp_cache_set($cache_key, $base64, 'image_base64');
    return $base64;
}

//生成二维码 base64
function pix_get_qrcode_base64($url){
    //引入phpqrcode类库
    require_once get_theme_file_path('/inc/lib/phpqrcode.php');
	ob_start();
	QRcode::png($url,false,QR_ECLEVEL_M,6,2);
	$data = ob_get_contents();
	ob_end_clean();

	$imageString = base64_encode($data);
	header("content-type:application/json; charset=utf-8");
	return 'data:image/jpeg;base64,' . $imageString;
}


function pix_file_get_content($url)
{

	$stream_opts = [
		"ssl" => [
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		]
	];
	$result = file_get_contents($url,false, stream_context_create($stream_opts));
	return $result;
}










