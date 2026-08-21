<?php if (!defined('ABSPATH')) exit; // 视图模板 — 不加密 ?>
<div class="notice notice-warning is-dismissible" style="border-left-color:#667eea;">
  <p style="font-size:14px;">
    <strong>⚠️ Glintide 主题未授权</strong> — 主题功能可正常使用，但请尽快前往 
    <a href="<?php echo esc_url(PIX_THEME_OFFICIAL); ?>" target="_blank" style="color:#667eea;font-weight:600;"><?php echo esc_html(parse_url(PIX_THEME_OFFICIAL, PHP_URL_HOST)); ?></a> 
    购买正版授权以获取完整的主题更新和技术支持服务。
  </p>
</div>