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
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="stylesheet" href="https://npm.elemecdn.com/gahotx-cdn@1.0.14/fonts/harmony/regular.min.css" media="all" onload="this.media='all'" />
	<link rel="stylesheet" href="https://npm.elemecdn.com/gahotx-cdn@1.0.14/fonts/harmony/medium.min.css" media="all" onload="this.media='all'" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<div id="page" class="site">

	<div class="app-container">

	<?php if ( get_cu( 'header_mode', 'floating' ) === 'classic' ) : ?>
	<!-- 原头部模式 -->
	<div class="main_header">
		<?php get_template_part( 'inc/layouts/header', 'layout' ); ?>
	</div>
	<?php else : ?>
	<!-- 悬浮模式：保留原顶部间距 -->
	<div class="header-spacer" aria-hidden="true"></div>
	<?php endif; ?>

	<div id="app-warp" class="main-warp">

	

