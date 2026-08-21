( function( $ ) {

  function pixAuthNonce() {
    return (window.PixAuthAdmin && PixAuthAdmin.nonce) ? PixAuthAdmin.nonce : '';
  }

  function pixEnsureUpdateModal() {
    var $modal = $('#pix-update-modal');
    if ($modal.length) {
      return $modal;
    }

    $modal = $(
      '<div id="pix-update-modal" class="pix-update-modal" aria-hidden="true">' +
        '<div class="pix-update-modal-card">' +
          '<div class="pix-update-modal-icon"><i class="ri-loader-4-line"></i></div>' +
          '<div class="pix-update-modal-kicker">PIXPRO UPDATE</div>' +
          '<h3 class="pix-update-modal-title">正在处理</h3>' +
          '<p class="pix-update-modal-desc">请稍候，正在连接授权服务器...</p>' +
          '<div class="pix-update-progress"><span></span></div>' +
          '<div class="pix-update-modal-note">更新过程中请不要关闭页面或刷新浏览器。</div>' +
        '</div>' +
      '</div>'
    );
    $('body').append($modal);
    return $modal;
  }

  function pixShowUpdateModal(options) {
    var $modal = pixEnsureUpdateModal();
    pixSetUpdateModal(options || {});
    $modal.addClass('is-visible').attr('aria-hidden', 'false');
  }

  function pixSetUpdateModal(options) {
    var $modal = pixEnsureUpdateModal();
    if (options.state) {
      $modal.removeClass('is-loading is-success is-error').addClass('is-' + options.state);
    }
    if (options.title) {
      $modal.find('.pix-update-modal-title').text(options.title);
    }
    if (options.desc) {
      $modal.find('.pix-update-modal-desc').text(options.desc);
    }
    if (options.note) {
      $modal.find('.pix-update-modal-note').text(options.note);
    }
    if (typeof options.progress !== 'undefined') {
      $modal.find('.pix-update-progress span').css('width', options.progress + '%');
    }
  }

  function pixHideUpdateModal(delay) {
    setTimeout(function() {
      $('#pix-update-modal').removeClass('is-visible').attr('aria-hidden', 'true');
    }, delay || 0);
  }

  function pixNormalizeGuideText(value) {
    return $.trim(String(value || '')).replace(/\s+/g, '').replace(/[&＆]/g, '');
  }

  function pixFindGuideTabLink(targetTitle) {
    var wanted = pixNormalizeGuideText(targetTitle);
    var $links = $('.pix-options .csf-nav a[data-tab-id], .csf-nav-options a[data-tab-id]');
    var $match = $();

    $links.each(function() {
      var $link = $(this);
      var $clone = $link.clone();
      $clone.find('i, .csf-label-error').remove();
      var text = pixNormalizeGuideText($clone.text());
      if (!text) {
        return;
      }
      if (text === wanted) {
        $match = $link;
        return false;
      }
    });

    if ($match.length) {
      return $match.first();
    }

    $links.each(function() {
      var $link = $(this);
      var $clone = $link.clone();
      $clone.find('i, .csf-label-error').remove();
      var text = pixNormalizeGuideText($clone.text());
      if (text && text.indexOf(wanted) !== -1) {
        $match = $link;
        return false;
      }
    });

    return $match.first();
  }

  function pixOpenGuideTab(targetTitle) {
    var $link = pixFindGuideTabLink(targetTitle);
    if (!$link.length) {
      return false;
    }

    var tabId = $link.data('tab-id') || $link.attr('data-tab-id');
    if (tabId) {
      window.location.hash = 'tab=' + tabId;
    }

    $(window).trigger('csf.hashchange');

    window.setTimeout(function() {
      var $content = $('.pix-options .csf-content').first();
      if ($content.length) {
        $('html, body').animate({
          scrollTop: Math.max($content.offset().top - 28, 0)
        }, 220);
      }
    }, 80);

    return true;
  }

  $('body').on('click', '.pix-guide-jump', function(event) {
    event.preventDefault();
    var targetTitle = $(this).attr('data-target-title');
    pixOpenGuideTab(targetTitle);
  });

  $('body').on('click', '.pix-guide-nav-link', function(event) {
    var target = $(this).attr('href');
    var $target = target ? $(target) : $();

    if (!$target.length) {
      return;
    }

    event.preventDefault();

    $('html, body').animate({
      scrollTop: Math.max($target.offset().top - 32, 0)
    }, 260);
  });

  function pixCustomizeNavGroups() {
    var $nav = $('#customize-theme-controls .customize-pane-parent');
    if (!$nav.length) {
      return;
    }

    var $ppoPanels = $nav.find('> li[id^="accordion-panel-global_setting"], > li[id^="accordion-panel-classic_setting"]');
    if (!$ppoPanels.length) {
      return;
    }

    var $firstPpo = $ppoPanels.first();
    var $ppoGroup = $nav.find('> .pix-customize-nav-group-ppo').first();
    if (!$ppoGroup.length) {
      $ppoGroup = $('<li class="pix-customize-nav-group pix-customize-nav-group-ppo" role="presentation"><span>PPO 主题设置</span></li>');
    }
    if ($firstPpo.prev()[0] !== $ppoGroup[0]) {
      $ppoGroup.detach().insertBefore($firstPpo);
    }

    var $coreAnchor = $nav.children('li[id="accordion-section-title_tagline"], li[id$="-title_tagline"]').first();
    if (!$coreAnchor.length) {
      $coreAnchor = $nav.children('li').filter(function() {
        var title = $(this).find('.accordion-section-title, h3').first().text().trim();
        return title.indexOf('站点身份') !== -1;
      }).first();
    }

    // WordPress 原生菜单异步渲染完成前不要回退到顶部，避免分组短暂插错位置。
    if (!$coreAnchor.length) {
      return;
    }

    var $coreGroup = $nav.find('> .pix-customize-nav-group-core').first();
    if (!$coreGroup.length) {
      $coreGroup = $('<li class="pix-customize-nav-group pix-customize-nav-group-core" role="presentation"><span>WordPress 核心</span></li>');
    }
    if ($coreAnchor.prev()[0] !== $coreGroup[0]) {
      $coreGroup.detach().insertBefore($coreAnchor);
    }
  }

  function pixMarkLastCsfControl() {
    $('#customize-theme-controls .customize-control.customize-control-csf').removeClass('pix-csf-control-last').each(function() {
      var $control = $(this);
      if (!$control.nextAll('.customize-control.customize-control-csf').length) {
        $control.addClass('pix-csf-control-last');
      }
    });
  }

  function pixLimitUserMenuSorter() {
    $('.csf-field-sorter').each(function() {
      var $field = $(this);
      var $sorter = $field.find('.csf-sorter[data-depend-id="user_menu_items"]');
      var $enabled = $field.find('.csf-enabled');
      var $disabled = $field.find('.csf-disabled');

      if (!$sorter.length || !$enabled.length || !$disabled.length || !$enabled.hasClass('ui-sortable')) {
        return;
      }

      var rememberOrigin = function(event, ui) {
        ui.item.data('pixUserMenuOrigin', ui.item.parent());
      };

      var restoreIfOverLimit = function(event, ui) {
        var $item = ui.item;
        var $origin = $item.data('pixUserMenuOrigin');

        if (!$origin || !$origin.length || $item.parent()[0] !== $enabled[0]) {
          return;
        }

        if ($enabled.children('li').length > 8 && $origin[0] !== $enabled[0]) {
          $item.appendTo($origin);
          $item.find('input').first().attr('name', function(index, name) {
            return name.replace('[enabled]', '[disabled]');
          });
        }

        $item.removeData('pixUserMenuOrigin');
      };

      $enabled.sortable('option', 'start', rememberOrigin);
      $enabled.sortable('option', 'stop', restoreIfOverLimit);
      $disabled.sortable('option', 'start', rememberOrigin);
      $disabled.sortable('option', 'stop', restoreIfOverLimit);
    });
  }

  $(function() {
    pixCustomizeNavGroups();
    pixMarkLastCsfControl();
    pixLimitUserMenuSorter();
    window.setTimeout(pixCustomizeNavGroups, 120);
    window.setTimeout(pixCustomizeNavGroups, 600);
    window.setTimeout(pixMarkLastCsfControl, 120);
    window.setTimeout(pixMarkLastCsfControl, 600);
    window.setTimeout(pixLimitUserMenuSorter, 120);
    window.setTimeout(pixLimitUserMenuSorter, 600);

    if (window.MutationObserver) {
      var customizeNav = document.querySelector('#customize-theme-controls .customize-pane-parent');
      if (customizeNav) {
        new MutationObserver(function() {
          pixCustomizeNavGroups();
          pixMarkLastCsfControl();
          pixLimitUserMenuSorter();
        }).observe(customizeNav, { childList: true, subtree: true });
      }
    }
  });

  $(document).on('csf-reload-script.pixUserMenuLimit', function() {
    pixLimitUserMenuSorter();
  });

    $( ".csf-field.no-drag .csf-cloneable-wrapper" ).sortable( {disabled: true});

  // 圈子重建
  $('body').on('click', '.rebuild-btn',function(){
    var id = $('input.moments_rebuild').val();
    var t = $(this);
    if(id == ''){
      return false;
    }
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: {
          'action':'rebuild_moments_data',
          'id': id,
      },
      beforeSend: function () {
        t.siblings('.tips').text('正在重建...'); 
      },
      success: function (data) {
        t.siblings('.tips').text(data.msg);     
        $('input.moments_rebuild').val('');                 
      }
  });
  })

  // 授权验证
  $('body').on('click', '#pix-btn-verify', function() {
    var t = $(this);
    var uid_or_email = $('#pix_verify_input').val().trim();

    if (!uid_or_email) {
      $('#pix-ov-msg').html('<span style="color:#d63638;">请输入 UID 或邮箱</span>');
      return;
    }

    t.text('验证中...').addClass('disabled');
    $('#pix-ov-msg').html('<span style="color:#666;">正在验证，请稍候...</span>');

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: {
        'action': 'pix_theme_verify_license',
        'uid_or_email': uid_or_email,
        'nonce': pixAuthNonce(),
      },
      success: function(data) {
        if (data.success) {
          $('#pix-ov-msg').html('<span style="color:#46b450;">' + data.data.msg + '</span>');
          setTimeout(function() {
            location.reload();
          }, 1500);
        } else {
          $('#pix-ov-msg').html('<span style="color:#d63638;">' + data.data.msg + '</span>');
          if (data.data.code === 'domain_removed') {
            setTimeout(function() { location.reload(); }, 2000);
          }
        }
      },
      error: function() {
        $('#pix-ov-msg').html('<span style="color:#d63638;">请求失败，请重试</span>');
      },
      complete: function() {
        t.text('验证授权').removeClass('disabled');
      }
    });
  })

  // 同步授权信息
  $('body').on('click', '#pix-btn-refresh', function() {
    var t = $(this);
    var uid_or_email = t.data('uid');

    if (!uid_or_email) {
      $('#pix-ov-msg').html('<span style="color:#d63638;">缺少验证信息，请重新输入 UID/邮箱</span>');
      return;
    }

    t.text('同步中...').addClass('disabled');
    $('#pix-ov-msg').html('<span style="color:#666;">正在同步授权信息...</span>');

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: {
        'action': 'pix_theme_verify_license',
        'uid_or_email': uid_or_email,
        'nonce': pixAuthNonce(),
      },
      success: function(data) {
        if (data.success) {
          $('#pix-ov-msg').html('<span style="color:#46b450;">同步成功</span>');
          setTimeout(function() {
            location.reload();
          }, 1000);
        } else {
          $('#pix-ov-msg').html('<span style="color:#d63638;">' + data.data.msg + '</span>');
          if (data.data.code === 'domain_removed') {
            setTimeout(function() {
              location.reload();
            }, 2000);
          }
        }
      },
      error: function() {
        $('#pix-ov-msg').html('<span style="color:#d63638;">请求失败，请重试</span>');
      },
      complete: function() {
        t.text('同步授权信息').removeClass('disabled');
      }
    });
  })

  // 检查主题更新
  $('body').on('click', '#pix-btn-check-update', function() {
    var t = $(this);
    t.text('检查中...').addClass('disabled');
    $('#pix-ov-update-msg').html('<span style="color:#666;">正在检查更新...</span>');
    pixShowUpdateModal({
      state: 'loading',
      title: '正在检查更新',
      desc: '正在连接授权服务器并读取最新版本信息...',
      note: '这通常只需要几秒钟。',
      progress: 35
    });

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: { 'action': 'pix_force_check_update', 'nonce': pixAuthNonce() },
      success: function(data) {
        if (data.success) {
          if (data.data.update) {
            localStorage.removeItem('pix_changelog_cache_v2');
          }
          $('#pix-ov-update-msg').html('<span style="color:#46b450;">' + data.data.msg + '</span>');
          pixSetUpdateModal({
            state: 'success',
            title: data.data.update ? '发现新版本' : '已是最新版本',
            desc: data.data.msg,
            note: data.data.update ? '页面即将刷新，刷新后可以点击立即更新。' : '当前主题已经是最新状态。',
            progress: 100
          });
          setTimeout(function() { location.reload(); }, 1500);
        } else {
          $('#pix-ov-update-msg').html('<span style="color:#d63638;">' + data.data.msg + '</span>');
          pixSetUpdateModal({
            state: 'error',
            title: '检查更新失败',
            desc: data.data.msg,
            note: '请稍后重试，或检查授权服务器连接。',
            progress: 100
          });
          pixHideUpdateModal(2600);
        }
      },
      error: function() {
        $('#pix-ov-update-msg').html('<span style="color:#d63638;">请求失败，请重试</span>');
        pixSetUpdateModal({
          state: 'error',
          title: '请求失败',
          desc: '无法连接到本站 AJAX 接口，请稍后重试。',
          note: '如果持续失败，可以检查浏览器控制台或服务器错误日志。',
          progress: 100
        });
        pixHideUpdateModal(2600);
      },
      complete: function() {
        t.text('检查更新').removeClass('disabled');
      }
    });
  })

  // 立即更新主题
  $('body').on('click', '#pix-btn-do-update', function() {
    var t = $(this);
    var msgEl = $('#pix-ov-update-msg');
    var updateTimers = [];

    if (!confirm('确定要更新主题吗？更新过程中请不要关闭页面。')) {
      return;
    }

    t.text('更新中...').addClass('disabled');
    msgEl.html('<span style="color:#666;">正在下载并安装更新...</span>');
    pixShowUpdateModal({
      state: 'loading',
      title: '正在更新主题',
      desc: '正在验证授权并准备下载更新包...',
      note: '更新过程中请不要关闭页面或刷新浏览器。',
      progress: 18
    });

    updateTimers.push(setTimeout(function() {
      pixSetUpdateModal({
        state: 'loading',
        title: '正在下载更新包',
        desc: '已开始从授权服务器获取主题更新包...',
        progress: 46
      });
    }, 700));

    updateTimers.push(setTimeout(function() {
      pixSetUpdateModal({
        state: 'loading',
        title: '正在安装更新',
        desc: 'WordPress 正在解压并覆盖主题文件...',
        progress: 72
      });
    }, 2200));

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: { 'action': 'pix_do_theme_update', 'nonce': pixAuthNonce() },
      success: function(data) {
        updateTimers.forEach(clearTimeout);
        if (data.success) {
          msgEl.html('<span style="color:#46b450;">' + data.data.msg + '</span>');
          pixSetUpdateModal({
            state: 'success',
            title: '更新成功',
            desc: data.data.msg,
            note: '页面即将刷新，请稍候...',
            progress: 100
          });
          setTimeout(function() { location.reload(); }, 2000);
        } else {
          msgEl.html('<span style="color:#d63638;">' + data.data.msg + '</span>');
          pixSetUpdateModal({
            state: 'error',
            title: '更新失败',
            desc: data.data.msg,
            note: '主题文件未完成更新，请根据错误提示处理后重试。',
            progress: 100
          });
          pixHideUpdateModal(3200);
        }
      },
      error: function() {
        updateTimers.forEach(clearTimeout);
        msgEl.html('<span style="color:#d63638;">请求失败，请重试</span>');
        pixSetUpdateModal({
          state: 'error',
          title: '请求失败',
          desc: '更新请求没有完成，请稍后重试。',
          note: '如果网络正常但仍失败，请检查服务器 PHP 错误日志。',
          progress: 100
        });
        pixHideUpdateModal(3200);
      },
      complete: function() {
        t.text('立即更新').removeClass('disabled');
      }
    });
  })

  // 下载主题包
  $('body').on('click', '#pix-btn-download-package', function() {
    var t = $(this);
    var msgEl = $('#pix-ov-update-msg');

    if (!confirm('确定要下载主题包吗？下载后可手动上传到「外观→主题→上传主题」进行安装。\n\n提示：如果当前已是最新版本，将下载最新可用版本。')) {
      return;
    }

    t.text('获取中...').addClass('disabled');
    msgEl.html('<span style="color:#666;">正在获取下载链接...</span>');
    pixShowUpdateModal({
      state: 'loading',
      title: '正在准备下载',
      desc: '正在验证授权并生成短期下载链接...',
      note: '下载会在当前页面直接触发，不会离开后台。',
      progress: 42
    });

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: { 'action': 'pix_get_download_url', 'nonce': pixAuthNonce() },
      success: function(data) {
        if (data.success) {
          var version = data.data.version || '';
          var filename = data.data.filename || 'pixpro.zip';
          var downloadUrl = data.data.url;
          if (window.location.protocol === 'https:' && downloadUrl.indexOf('http://') === 0) {
            downloadUrl = downloadUrl.replace(/^http:\/\//, 'https://');
          }

          msgEl.html('<span style="color:#46b450;">正在下载 v' + version + ' 主题包...</span>');
          pixSetUpdateModal({
            state: 'success',
            title: '下载已开始',
            desc: '正在下载 v' + version + ' 主题包...',
            note: '请查看浏览器下载列表，文件名：' + filename,
            progress: 100
          });

          var iframeId = 'pix-theme-package-download-frame';
          var iframe = document.getElementById(iframeId);
          if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = iframeId;
            iframe.name = iframeId;
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
          }
          iframe.src = downloadUrl;

          setTimeout(function() {
            msgEl.html('<span style="color:#46b450;">✓ 已开始下载，请检查浏览器下载列表</span>');
            pixHideUpdateModal(800);
          }, 1000);
        } else {
          msgEl.html('<span style="color:#d63638;">' + data.data.msg + '</span>');
          pixSetUpdateModal({
            state: 'error',
            title: '获取下载链接失败',
            desc: data.data.msg,
            note: '请确认当前域名仍在授权列表中。',
            progress: 100
          });
          pixHideUpdateModal(3000);
        }
      },
      error: function() {
        msgEl.html('<span style="color:#d63638;">请求失败，请重试</span>');
        pixSetUpdateModal({
          state: 'error',
          title: '请求失败',
          desc: '下载请求没有完成，请稍后重试。',
          note: '如果持续失败，请检查授权服务器连接。',
          progress: 100
        });
        pixHideUpdateModal(3000);
      },
      complete: function() {
        t.html('<i class="ri-download-2-line"></i> 下载主题包').removeClass('disabled');
      }
    });
  })

  // 更新日志侧滑面板
  var changelogDrawer = $('#pix-changelog-drawer');
  var changelogContent = $('#pix-changelog-content');
  var changelogOverlay = changelogDrawer.find('.pix-changelog-overlay');
  var changelogPanel = changelogDrawer.find('.pix-changelog-panel');
  var isChangelogLoading = false;

  function getChangelogCache() {
    var cached = localStorage.getItem('pix_changelog_cache_v2');
    if (!cached) return null;
    try {
      var parsed = JSON.parse(cached);
      if (parsed.expires < Date.now()) {
        localStorage.removeItem('pix_changelog_cache_v2');
        return null;
      }
      return parsed.data;
    } catch(e) {
      localStorage.removeItem('pix_changelog_cache_v2');
      return null;
    }
  }

  function setChangelogCache(data) {
    localStorage.setItem('pix_changelog_cache_v2', JSON.stringify({
      data: data,
      expires: Date.now() + 6 * 60 * 60 * 1000
    }));
  }

  function openChangelog() {
    changelogDrawer.show();
    $('body').css('overflow', 'hidden');

    var cached = getChangelogCache();
    if (cached) {
      renderChangelogs(cached);
      return;
    }

    if (isChangelogLoading) return;
    isChangelogLoading = true;

    changelogContent.html('<div class="pix-changelog-loading"><i class="ri-loader-4-line spin"></i> 正在加载更新日志...</div>');

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: ajaxurl,
      data: { 'action': 'pix_get_changelogs', 'nonce': pixAuthNonce() },
      success: function(data) {
        if (data.success) {
          setChangelogCache(data.data);
          renderChangelogs(data.data);
        } else {
          changelogContent.html('<div class="pix-changelog-empty"><i class="ri-error-warning-line"></i><div>' + (data.data.msg || '加载失败') + '</div></div>');
        }
      },
      error: function() {
        changelogContent.html('<div class="pix-changelog-empty"><i class="ri-wifi-off-line"></i><div>网络错误，请检查网络连接</div></div>');
      },
      complete: function() {
        isChangelogLoading = false;
      }
    });
  }

  function closeChangelog() {
    if (!changelogDrawer.is(':visible')) return;
    changelogOverlay.addClass('pix-closing');
    changelogPanel.addClass('pix-closing');
    setTimeout(function() {
      changelogDrawer.hide();
      changelogOverlay.removeClass('pix-closing');
      changelogPanel.removeClass('pix-closing');
      $('body').css('overflow', '');
    }, 200);
  }

  // 打开面板
  $('body').on('click', '#pix-btn-show-changelog', function() {
    openChangelog();
  });

  // 关闭面板
  $('body').on('click', '#pix-changelog-close-btn, .pix-changelog-overlay', function() {
    closeChangelog();
  });

  // ESC键关闭
  $(document).on('keydown', function(e) {
    if (e.which === 27 && changelogDrawer.is(':visible')) {
      closeChangelog();
    }
  });

  // 渲染更新日志列表
  function renderChangelogs(data) {
    var current_version = data.current_version || '';

    $('#pix-changelog-current-ver').text(current_version ? '(当前 v' + current_version + ')' : '');

    var changelogs = data.changelogs || [];
    if (changelogs.length === 0) {
      changelogContent.html('<div class="pix-changelog-empty"><i class="ri-file-text-line"></i><div>暂无更新日志</div></div>');
      return;
    }

    var html = '';
    $.each(changelogs, function(i, item) {
      var version = item.version || '';
      var changelog = item.changelog || '';
      var date = item.released_date || '';

      if (!changelog) return;

      var dateHtml = date ? '<span class="pix-changelog-date">' + date.substring(0, 10) + '</span>' : '';

      html += '<div class="pix-changelog-item">';
      html += '<div class="pix-changelog-ver"><span>v' + version + '</span>' + dateHtml + '</div>';
      html += '<div class="pix-changelog-text">' + formatChangelog(changelog) + '</div>';
      html += '</div>';
    });

    changelogContent.html(html);
  }

  // 格式化更新日志：解析"类别|内容"格式 + 渲染HTML
  function formatChangelog(text) {
    var categoryMap = {
      '新增': 'new',
      '新功能': 'new',
      '优化': 'improve',
      '改进': 'improve',
      '增强': 'improve',
      '修复': 'fix',
      '修正': 'fix',
      '安全': 'security',
      '移除': 'remove',
      '删除': 'remove',
      '重构': 'refactor',
      '性能': 'perf',
      '依赖': 'deps'
    };

    var categoryLabels = {
      'new': '新增',
      'improve': '优化',
      'fix': '修复',
      'security': '安全',
      'remove': '移除',
      'refactor': '重构',
      'perf': '性能',
      'deps': '依赖'
    };

    var source = String(text).replace(/<br\s*\/?\s*>/gi, '\n');
    var categoryPattern = /(新增|新功能|优化|改进|增强|修复|修正|安全|移除|删除|重构|性能|依赖)\s*\|\s*/g;
    var matches = [];
    var match;
    var html = '';

    while ((match = categoryPattern.exec(source)) !== null) {
      matches.push({ keyword: match[1], start: match.index, contentStart: categoryPattern.lastIndex });
    }

    function renderEntry(keyword, content) {
      content = $.trim(content);
      if (!content) return;

      var type = keyword ? (categoryMap[keyword] || 'new') : 'new';
      var label = keyword ? (categoryLabels[type] || keyword) : '更新';

      html += '<div class="pix-changelog-entry">';
      html += '<span class="cl-tag cl-tag-' + type + '">' + label + '</span>';
      html += '<div class="pix-changelog-entry-content">' + content + '</div>';
      html += '</div>';
    }

    if (matches.length) {
      $.each(matches, function(index, item) {
        var next = matches[index + 1];
        renderEntry(item.keyword, source.slice(item.contentStart, next ? next.start : source.length));
      });
    } else {
      $.each(source.split(/\r?\n/), function(index, entry) {
        renderEntry('', entry);
      });
    }

    return html;
  }

})(jQuery);
