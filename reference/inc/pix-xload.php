<?php
if (!defined('ABSPATH')) exit;

function pix_xload_extension_names() {
  return apply_filters('pix_xload_extension_names', array('xload', 'XLoad', 'xloader', 'XLoader'));
}

function pix_xload_is_loaded() {
  foreach (pix_xload_extension_names() as $extension) {
    if (extension_loaded($extension)) {
      return true;
    }
  }

  return false;
}

function pix_xload_runtime_info() {
  $php_tag = PHP_MAJOR_VERSION . PHP_MINOR_VERSION;
  $is_windows = defined('PHP_OS_FAMILY') ? PHP_OS_FAMILY === 'Windows' : stripos(PHP_OS, 'WIN') === 0;
  $arch = PHP_INT_SIZE >= 8 ? 'x64' : 'x86';
  $thread = defined('PHP_ZTS') && PHP_ZTS ? 'ts' : 'nts';
  $filename = $is_windows
    ? 'XLoader_Win_php' . $php_tag . '_' . $thread . '_' . $arch . '.dll'
    : 'XLoader_Lin_php' . $php_tag . '_' . $arch . '.so';

  return array(
    'loaded'        => pix_xload_is_loaded(),
    'system'        => $is_windows ? 'Windows' : 'Linux',
    'php_version'   => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
    'architecture'  => $arch === 'x64' ? 'x64(64位)' : 'x86(32位)',
    'thread_safety' => $thread,
    'filename'      => $filename,
    'download_url'  => 'http://soft.phpxload.com/ext/ExtDown.asp?e=ExtCheck&r=' . rawurlencode($filename),
    'extension_dir' => ini_get('extension_dir') ?: '-',
    'php_ini'       => php_ini_loaded_file() ?: '-',
    'ini_line'      => 'extension=' . $filename,
  );
}

function pix_xload_distribution_requires_extension() {
  if (defined('PIX_REQUIRE_XLOAD')) {
    return (bool) PIX_REQUIRE_XLOAD;
  }

  if (file_exists(get_theme_file_path('inc/.xload-required'))) {
    return true;
  }

  return false;
}

function pix_xload_preview_requested() {
  return isset($_GET['pix_xload_preview'])
    && (string) wp_unslash($_GET['pix_xload_preview']) === '1'
    && current_user_can('manage_options');
}

function pix_xload_should_show_missing_state() {
  return pix_xload_distribution_requires_extension() || pix_xload_preview_requested();
}

function pix_xload_bootstrap_should_stop() {
  if (!pix_xload_should_show_missing_state() || (pix_xload_is_loaded() && !pix_xload_preview_requested())) {
    return false;
  }

  add_filter('template_include', 'pix_xload_frontend_page', 0);
  add_action('admin_menu', 'pix_xload_admin_menu', 0);
  add_action('admin_notices', 'pix_xload_admin_notice');

  return true;
}

function pix_xload_frontend_page($template) {
  if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
    return $template;
  }

  status_header(503);
  include get_theme_file_path('inc/mod/pix-auth-unauthorized.php');
  exit;
}

function pix_xload_admin_menu() {
  add_menu_page(
    'Glintide 安装引导',
    'Glintide 安装引导',
    'manage_options',
    'pix-xload-guide',
    'pix_xload_admin_page',
    'dashicons-admin-tools',
    2
  );
}

function pix_xload_admin_notice() {
  if (!current_user_can('manage_options')) {
    return;
  }

  $screen = get_current_screen();
  if ($screen && $screen->id === 'toplevel_page_pix-xload-guide') {
    return;
  }

  echo '<div class="notice notice-error"><p><strong>Glintide 主题需要安装 XLOAD 扩展。</strong> 请进入 <a href="' . esc_url(admin_url('admin.php?page=pix-xload-guide')) . '">Glintide 安装引导</a> 查看当前服务器匹配的扩展文件和安装步骤。</p></div>';
}

function pix_xload_admin_page() {
  $info = pix_xload_runtime_info();
  ?>
  <div class="wrap pix-xload-admin-wrap">
    <style>
      .pix-xload-admin-wrap{max-width:980px}
      .pix-xload-panel{margin-top:20px;background:#fff;border:1px solid #e3e8f0;border-radius:18px;box-shadow:0 16px 36px rgba(15,23,42,.08);overflow:hidden}
      .pix-xload-hero{padding:28px 32px;background:radial-gradient(circle at 100% 0%,#edf4ff 0,rgba(237,244,255,.7) 24%,rgba(255,255,255,0) 52%),#fff}
      .pix-xload-kicker{color:#3157ff;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
      .pix-xload-title{margin:8px 0 8px;color:#111827;font-size:24px;font-weight:700}
      .pix-xload-desc{max-width:680px;color:#667085;font-size:14px;line-height:1.8}
      .pix-xload-body{padding:24px 32px 30px}
      .pix-xload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:20px}
      .pix-xload-item{padding:14px 16px;border:1px solid #edf1f7;border-radius:12px;background:#fbfcff}
      .pix-xload-label{display:block;margin-bottom:6px;color:#667085;font-size:12px}
      .pix-xload-value{color:#172033;font-size:13px;font-weight:600;word-break:break-all}
      .pix-xload-steps{display:grid;gap:10px;margin:0 0 20px;padding:0;list-style:none}
      .pix-xload-steps li{padding:14px 16px;border:1px solid #e5ebf5;border-radius:12px;background:#fff;color:#475467;font-size:13px;line-height:1.7}
      .pix-xload-code{display:inline-flex;max-width:100%;padding:3px 8px;border-radius:8px;background:#eef3ff;color:#2945bb;font-family:Consolas,Monaco,monospace;font-size:12px;word-break:break-all}
      .pix-xload-actions{display:flex;flex-wrap:wrap;gap:10px}
      .pix-xload-actions .button{border-radius:10px;min-height:36px;display:inline-flex;align-items:center}
      @media(max-width:782px){.pix-xload-grid{grid-template-columns:1fr}.pix-xload-hero,.pix-xload-body{padding-left:22px;padding-right:22px}}
    </style>
    <div class="pix-xload-panel">
      <div class="pix-xload-hero">
        <div class="pix-xload-kicker">XLOAD EXTENSION</div>
        <div class="pix-xload-title">安装 XLOAD 扩展后继续使用 Glintide</div>
        <div class="pix-xload-desc">当前 PHP 环境未检测到 XLOAD 扩展。请下载与服务器匹配的扩展文件，放入 PHP 扩展目录，并在 php.ini 中启用后重启 PHP 服务。</div>
      </div>
      <div class="pix-xload-body">
        <div class="pix-xload-grid">
          <div class="pix-xload-item"><span class="pix-xload-label">操作系统</span><span class="pix-xload-value"><?php echo esc_html($info['system']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">PHP 版本</span><span class="pix-xload-value"><?php echo esc_html($info['php_version']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">PHP 架构</span><span class="pix-xload-value"><?php echo esc_html($info['architecture']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">线程安全</span><span class="pix-xload-value"><?php echo esc_html($info['thread_safety']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">扩展文件</span><span class="pix-xload-value"><?php echo esc_html($info['filename']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">扩展目录</span><span class="pix-xload-value"><?php echo esc_html($info['extension_dir']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">php.ini</span><span class="pix-xload-value"><?php echo esc_html($info['php_ini']); ?></span></div>
          <div class="pix-xload-item"><span class="pix-xload-label">配置行</span><span class="pix-xload-value"><?php echo esc_html($info['ini_line']); ?></span></div>
        </div>
        <ol class="pix-xload-steps">
          <li>下载扩展文件：<span class="pix-xload-code"><?php echo esc_html($info['filename']); ?></span></li>
          <li>将文件上传到 PHP 扩展目录：<span class="pix-xload-code"><?php echo esc_html($info['extension_dir']); ?></span></li>
          <li>打开 php.ini：<span class="pix-xload-code"><?php echo esc_html($info['php_ini']); ?></span>，添加 <span class="pix-xload-code"><?php echo esc_html($info['ini_line']); ?></span></li>
          <li>重启 PHP-FPM / Apache / IIS，然后刷新本页面确认状态。</li>
        </ol>
        <div class="pix-xload-actions">
          <a class="button button-primary" href="<?php echo esc_url($info['download_url']); ?>" target="_blank" rel="noopener noreferrer">下载匹配扩展</a>
          <a class="button" href="<?php echo esc_url(admin_url()); ?>">刷新后台</a>
        </div>
      </div>
    </div>
  </div>
  <?php
}
