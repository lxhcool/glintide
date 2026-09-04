<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package pix
 */

 //$body_c = body_class_set();

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
<div class="player_box"></div>
<div id="page" class="site main_wrap">

	<div class="main_body uk-flex uk-flex-center uk-grid-collapse" uk-grid >
	<div class="left_nav uk-width-1-3@m uk-visible@m">
            <div class="left_nav_inner">
			<div uk-sticky="offset: 0;">
				<?php echo site_logo(); echo m_site_logo() ?>

				<div class="left_menu_box">
					<?php
					
					if (has_nav_menu('left')) {
						wp_nav_menu( array(
							'theme_location' => 'left',
							'menu_id'        => 'left_menu',
							'walker' => new pix_Walker_Nav_Menu()
						) );
					  }
					
					?>
				</div>
				

			<div class="sidebar">
            <?php if(is_active_sidebar( 'sidebar' )){ ?>
                    <div class="widget_inner sidebar_inner">
                        <?php dynamic_sidebar( 'sidebar' ); ?>
                    </div>
			    <?php } ?>
            </div>	
		

            </div>
			
			</div>
        </div>

	
	<div class="page_main uk-width-2-3@m">

	<header id="masthead" class="site-header">
		<div class="top_bar" uk-sticky="offset: 0;">	
			<div class="mobile_logo"><a href="<?php echo home_url(); ?>"><img src="<?php echo get_op('mobile_logo'); ?>"></a></div>
			<div class="top_left">		
				<?php get_template_part('layouts/header','search'); ?>	
			</div>		

			<?php 
				$mod_array = array('mod_third','mod_third_s');
					if(!in_array(get_op('layout_set'),$mod_array) || wp_is_mobile()){
						get_template_part('layouts/header','tool');
				} 
			?>

		</div>

		
	</header><!-- #masthead -->
	
	<div class="index_banner" data-src="<?php echo top_banner(); ?>" uk-img="loading: eager">
         
         <div class="user_info">
             <div class="top">
                 <div class="left">
                    <div class="name"><?php echo get_nickname(); ?></div>
                    <div class="des"><?php echo get_admin_des(); ?></div>
                 </div>
                
                <div class="avatar"><?php echo  get_user_avatar(); ?></div>
             </div> 
         </div>
        </div>
	
	<div id="pjax-container">	