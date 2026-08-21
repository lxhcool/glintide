<?php
if (!defined('ABSPATH')) exit;
// 视图模板 — 不加密
$pix_xload_missing = function_exists('pix_xload_should_show_missing_state') && pix_xload_should_show_missing_state() && function_exists('pix_xload_is_loaded') && !pix_xload_is_loaded();
$pix_xload_info = ($pix_xload_missing && function_exists('pix_xload_runtime_info')) ? pix_xload_runtime_info() : array();
$pix_page_title = $pix_xload_missing ? '需要安装 XLOAD 扩展' : '主题未授权';
$pix_page_sub = $pix_xload_missing ? '当前服务器尚未启用 Glintide 运行所需的 XLOAD PHP 扩展' : '当前使用的 <span class="highlight">Glintide</span> 主题尚未激活授权';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php bloginfo('name'); ?> — <?php echo esc_html($pix_page_title); ?></title>
<?php $pix_unauthorized_css = get_template_directory() . '/inc/assets/css/pix-unauthorized.css'; ?>
<link rel="stylesheet" href="<?php echo THEME_URL; ?>/inc/assets/css/pix-unauthorized.css?ver=<?php echo esc_attr(file_exists($pix_unauthorized_css) ? filemtime($pix_unauthorized_css) : PIX_VERSION); ?>">
</head>
<body>
<div class="wrap <?php echo $pix_xload_missing ? 'is-xload' : ''; ?>">
  <div class="icon-wrap"><img src="<?php echo THEME_URL; ?>/img/pixcap.svg" alt="pixcap" style="width:32px;height:32px;filter:brightness(0) saturate(100%) invert(30%) sepia(80%) saturate(2211%) hue-rotate(223deg) brightness(101%) contrast(102%)"></div>
  <h1><?php echo esc_html($pix_page_title); ?></h1>
  <p class="sub"><?php echo wp_kses_post($pix_page_sub); ?></p>
  <div class="divider"></div>
  <?php if ($pix_xload_missing): ?>
  <div class="info-box">
    <div class="label">安装说明</div>
    <p>请下载与当前服务器匹配的 XLOAD 扩展文件，上传到 PHP 扩展目录，并在 php.ini 中添加配置后重启 PHP 服务。</p>
  </div>
  <div class="xload-grid">
    <div><span>扩展文件</span><strong><?php echo esc_html($pix_xload_info['filename'] ?? '-'); ?></strong></div>
    <div><span>PHP 版本</span><strong><?php echo esc_html($pix_xload_info['php_version'] ?? PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?></strong></div>
    <div><span>系统环境</span><strong><?php echo esc_html($pix_xload_info['system'] ?? PHP_OS); ?></strong></div>
    <div><span>配置行</span><strong><?php echo esc_html($pix_xload_info['ini_line'] ?? 'extension=XLoader'); ?></strong></div>
  </div>
  <?php if (!empty($pix_xload_info['download_url'])): ?>
  <a class="btn" href="<?php echo esc_url($pix_xload_info['download_url']); ?>" target="_blank" rel="noopener noreferrer">下载匹配扩展</a>
  <?php endif; ?>
  <?php if (current_user_can('manage_options')): ?>
  <div class="footer">管理员可进入后台「Glintide 安装引导」查看扩展目录、php.ini 路径和完整安装步骤</div>
  <?php endif; ?>
  <?php else: ?>
  <div class="info-box">
    <div class="label">授权说明</div>
    <p>请前往 <span class="highlight"><?php echo esc_html(parse_url(PIX_THEME_OFFICIAL, PHP_URL_HOST)); ?></span> 购买正版授权后即可正常使用本主题的所有功能。</p>
  </div>
  <a class="btn" href="<?php echo esc_url(PIX_THEME_OFFICIAL); ?>" target="_blank">前往购买授权</a>
  <?php if (current_user_can('manage_options')): ?>
  <div class="footer">管理员可前往后台「Glintide 设置」进行主题配置</div>
  <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
