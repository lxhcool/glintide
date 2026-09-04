<?php
/*
	Template Name: 落地页
*/

$feature = get_post_meta( get_the_ID(), 'land_feature', true );
$logo = get_post_meta( get_the_ID(), 'land_logo', true );
$title = get_post_meta( get_the_ID(), 'land_title', true );
$des = get_post_meta( get_the_ID(), 'land_des', true );
//$btns = get_post_meta( get_the_ID(), 'land_btn', true );
$diy = get_post_meta( get_the_ID(), 'land_diy', true );
$diy_html = get_post_meta( get_the_ID(), 'land_html', true );
$beian_text = get_op('beian_text');
$beian_link = get_op('beian_link');
$dark = get_post_meta( get_the_ID(), 'land_dark', true );
$dark_type = '';
$dark_type = $dark ? 'darkl' : '';

$type = get_post_meta( get_the_ID(), 'land_type', true );

function get_land_nav(){
	$btns = get_post_meta( get_the_ID(), 'land_btn', true );
	if($btns){
		foreach($btns as $i => $btn){
			$data = $btn['link'];
			if($btn['icon']){$icon = '<i class="'.$btn['icon'] .'"></i>';}
			echo '<a class="bt_'.$i.'" href="'.$data['url'].'" target="'.$data['target'].'">'.$icon.'<span>'.$data['text'].'</span></a>';
		}
	}
}

?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
	<meta name="keywords" content="<?php getKeywords(); ?>">
	<meta name="description" content="<?php getDescription(); ?>">
	<title><?php gettitle(); ?></title>
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="shortcut icon" href="<?php echo get_op('favicon'); ?>" title="Favicon">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>


	<?php if($type == 'card'){ ?>
		<div class="land_page <?php echo $dark_type ?>" uk-height-viewport="offset-top: true">
		<div class="land_content">
			<div class="land_left" style = "background-image:url('<?php echo $feature ?>')"></div>
			<div class="land_right uk-animation-slide-bottom-small">
				<div class="land_logo"><img src="<?php echo $logo ?>"></div>
				<div class="title"><p><?php echo $title ?></p></div>
				<div class="land_des"><?php echo $des ?></div>
				<div class="land_nav">
					<?php get_land_nav(); ?>
					
				</div>
				<div class="footer"><div class="beian"><a href="<?php echo $beian_link ?>" class="beian" target="_blank"><?php echo $beian_text ?></a></div><div class="copyright"><?php echo $diy ?></div></div>
			</div>
		</div>
		</div>
	<?php } else if($type == 'simple') { ?>
		<div class="land_page sim <?php echo $dark_type ?>" uk-height-viewport="offset-top: true">
		<div class="land_content">
			<div class="land_top" style = "background-image:url('<?php echo $feature ?>')">
				<div class="land_meta">
					<div class="land_logo"><img src="<?php echo $logo ?>"></div>
					<div class="title"><?php echo $title ?></div>
				</div>
				
			</div>
			<div class="land_bottom">
				<div class="land_des"><?php echo $des ?></div>
				<div class="land_nav">
					<?php get_land_nav(); ?>
					
				</div>
				<div class="diy_html"><?php echo $diy_html ?></div>
				<div class="footer"><div class="beian"><a href="<?php echo $beian_link ?>" class="beian" target="_blank"><?php echo $beian_text ?></a></div><div class="copyright"><?php echo $diy ?></div></div>
			</div>
		</div>
		</div>
	<?php } ?>	
	



<?php
wp_footer();

