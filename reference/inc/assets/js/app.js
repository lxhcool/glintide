var $body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');
var storage = window.localStorage;
var pixStickyFrame = null;

// timeago初始化
jQuery(document).ready(function() {
    jQuery("time.timeago").timeago();
});

// 懒加载初始化
var lazyLoadInstance = new LazyLoad({});

function pix_init_sticky(scope) {
    var root = scope && scope.querySelectorAll ? scope : document;
    var items = Array.prototype.slice.call(root.querySelectorAll('[data-pix-sticky]'));

    if (!items.length) return;

    items.forEach(function(item) {
        if (item.dataset.pixStickyReady === '1') return;

        var start = parseInt(item.getAttribute('data-pix-sticky-start'), 10) || 0;
        var mode = item.getAttribute('data-pix-sticky') || 'top';
        var useNativeSticky = false;
        var usePlaceholder = true;
        var placeholder = null;

        if (!useNativeSticky && usePlaceholder) {
            placeholder = document.createElement('div');
            placeholder.className = 'pix-sticky-placeholder';
            item.parentNode.insertBefore(placeholder, item);
        }

        item.dataset.pixStickyReady = '1';
        item.classList.add('pix-sticky');
        item.classList.toggle('pix-sticky-native', useNativeSticky);
        item._pixSticky = {
            placeholder: placeholder,
            start: start,
            mode: mode,
            useNativeSticky: useNativeSticky,
            usePlaceholder: usePlaceholder,
            originTop: item.getBoundingClientRect().top + (window.pageYOffset || document.documentElement.scrollTop || 0),
            lastScroll: window.pageYOffset || document.documentElement.scrollTop || 0,
            hidden: false
        };

        if (item._pixSticky.mode === 'showup') {
            item.classList.add('pix-sticky-showup');
        }
    });

    pix_update_sticky();
}

function pix_update_sticky() {
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;

    document.querySelectorAll('[data-pix-sticky][data-pix-sticky-ready="1"]').forEach(function(item) {
        var state = item._pixSticky;
        if (!state) return;

        if (state.useNativeSticky) {
            item.classList.toggle('pix-sticky-fixed', scrollTop > state.originTop);
            state.lastScroll = scrollTop;
            return;
        }

        var rect = state.usePlaceholder && item.classList.contains('pix-sticky-fixed') ? state.placeholder.getBoundingClientRect() : item.getBoundingClientRect();
        var triggerTop = state.originTop + state.start;
        var active = scrollTop >= triggerTop;
        var height = item.offsetHeight;
        var delta = scrollTop - state.lastScroll;
        var hasIntent = Math.abs(delta) >= 8;

        if (state.usePlaceholder && state.placeholder) {
            state.placeholder.style.height = active && !(state.mode === 'showup' && state.hidden) ? height + 'px' : '0px';
            state.placeholder.classList.toggle('is-active', active);
        }

        if (active) {
            rect = state.usePlaceholder && state.placeholder ? state.placeholder.getBoundingClientRect() : item.getBoundingClientRect();
            item.style.setProperty('--pix-sticky-left', rect.left + 'px');
            item.style.setProperty('--pix-sticky-width', rect.width + 'px');
            item.classList.add('pix-sticky-fixed');

            if (state.mode === 'showup') {
                if (!state.hidden && hasIntent && delta > 0 && scrollTop > triggerTop + height + 16) {
                    state.hidden = true;
                    item.classList.add('pix-sticky-hidden');
                    if (state.usePlaceholder && state.placeholder) {
                        state.placeholder.style.height = '0px';
                    }
                } else if (state.hidden && hasIntent && delta < 0) {
                    state.hidden = false;
                    item.classList.remove('pix-sticky-hidden');
                    if (state.usePlaceholder && state.placeholder) {
                        state.placeholder.style.height = height + 'px';
                    }
                } else if (state.hidden && scrollTop <= triggerTop + height) {
                    state.hidden = false;
                    item.classList.remove('pix-sticky-hidden');
                    if (state.usePlaceholder && state.placeholder) {
                        state.placeholder.style.height = height + 'px';
                    }
                }
            }
        } else {
            item.classList.remove('pix-sticky-fixed', 'pix-sticky-hidden');
            item.style.removeProperty('--pix-sticky-left');
            item.style.removeProperty('--pix-sticky-width');
            state.hidden = false;
        }

        state.lastScroll = scrollTop;
    });
}

function pix_request_sticky_update() {
    if (pixStickyFrame) return;

    pixStickyFrame = window.requestAnimationFrame(function() {
        pixStickyFrame = null;
        pix_update_sticky();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    pix_init_sticky(document);
    pix_update_sticky();
});

window.addEventListener('scroll', function() {
    pix_request_sticky_update();
}, { passive: true });

window.addEventListener('resize', function() {
    document.querySelectorAll('[data-pix-sticky][data-pix-sticky-ready="1"]').forEach(function(item) {
        var state = item._pixSticky;
        if (!state || !state.useNativeSticky) return;
        state.originTop = item.getBoundingClientRect().top + (window.pageYOffset || document.documentElement.scrollTop || 0);
    });
    pix_request_sticky_update();
});

function pix_init_post_toc() {
    if (window.matchMedia && !window.matchMedia('(min-width: 961px)').matches) return;

    var links = Array.prototype.slice.call(document.querySelectorAll('[data-pix-toc-link]'));
    if (!links.length) return;

    var headings = [];
    var linksById = {};

    links.forEach(function(link) {
        var href = link.getAttribute('href') || '';
        if (href.charAt(0) !== '#') return;

        var id = href.slice(1);
        var heading = document.getElementById(id);
        if (!heading) return;

        headings.push(heading);
        linksById[id] = link;

        link.addEventListener('click', function(event) {
            event.preventDefault();

            heading.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', '#' + id);
            }

            pix_set_post_toc_active(id);
        });
    });

    if (!headings.length) return;

    function pix_set_post_toc_active(id) {
        links.forEach(function(link) {
            var item = link.closest('.pix-post-toc-item');
            if (!item) return;

            item.classList.toggle('is-active', link === linksById[id]);
        });
    }

    var tocFrame = null;
    function pix_update_post_toc_active() {
        tocFrame = null;

        var currentId = headings[0].id;
        var offset = 120;

        headings.forEach(function(heading) {
            if (heading.getBoundingClientRect().top <= offset) {
                currentId = heading.id;
            }
        });

        pix_set_post_toc_active(currentId);
    }

    function pix_request_post_toc_update() {
        if (tocFrame) return;

        tocFrame = window.requestAnimationFrame(pix_update_post_toc_active);
    }

    window.addEventListener('scroll', pix_request_post_toc_update, { passive: true });
    window.addEventListener('resize', pix_request_post_toc_update);
    pix_update_post_toc_active();
}

document.addEventListener('DOMContentLoaded', pix_init_post_toc);

function pix_scroll_tab_into_view(tab, behavior) {
    if (!tab || !window.matchMedia || !window.matchMedia('(max-width: 960px)').matches) return;

    var container = tab.closest('.pix-user-home-tabs, .pix-dashboard-dynamic-tabs, .pix-user-home-collect-nav, .pix-moment-filter');
    if (!container || container.scrollWidth <= container.clientWidth + 2) return;

    var targetLeft = tab.offsetLeft - ((container.clientWidth - tab.offsetWidth) / 2);
    var maxLeft = container.scrollWidth - container.clientWidth;

    targetLeft = Math.max(0, Math.min(maxLeft, targetLeft));

    if (Math.abs(container.scrollLeft - targetLeft) > 1) {
        container.scrollTo({
            left: targetLeft,
            behavior: behavior || 'smooth'
        });
    }
}

function pix_tab_key_from_url() {
    var params = new URLSearchParams(window.location.search || '');
    return params.get('tab') || '';
}

function pix_tab_selector_key(value) {
    if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
    }

    return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
}

function pix_resolve_active_tab(container) {
    if (!container) return null;

    var urlTab = pix_tab_key_from_url();
    var urlActive = urlTab ? container.querySelector('.user-' + pix_tab_selector_key(urlTab) + '-tab') : null;
    var active = urlActive || container.querySelector('.active');

    if (urlActive && !urlActive.classList.contains('active')) {
        container.querySelectorAll('.active').forEach(function(item) {
            item.classList.remove('active');
        });
        urlActive.classList.add('active');
    }

    return active;
}

function pix_sync_active_tabs(scope, behavior) {
    var root = scope && scope.querySelectorAll ? scope : document;
    var containers = root.querySelectorAll('.pix-user-home-tabs, .pix-dashboard-dynamic-tabs, .pix-user-home-collect-nav, .pix-moment-filter');

    containers.forEach(function(container) {
        var active = pix_resolve_active_tab(container);
        if (active) {
            pix_scroll_tab_into_view(active, behavior || 'auto');
        }
    });
}

function pix_activate_scrolled_tab(tab) {
    if (!tab) return;

    var container = tab.closest('.pix-user-home-tabs, .pix-dashboard-dynamic-tabs, .pix-user-home-collect-nav');
    if (!container) return;

    container.querySelectorAll('.active').forEach(function(item) {
        item.classList.remove('active');
    });
    tab.classList.add('active');
    window.setTimeout(function() {
        pix_scroll_tab_into_view(tab, 'smooth');
    }, 0);
}

document.addEventListener('DOMContentLoaded', function() {
    window.setTimeout(function() {
        pix_sync_active_tabs(document, 'auto');
    }, 60);
});

document.addEventListener('click', function(event) {
    var momentFilterTab = event.target.closest('.pix-moment-filter-item');
    if (momentFilterTab) {
        window.setTimeout(function() {
            pix_scroll_tab_into_view(momentFilterTab, 'smooth');
        }, 0);
    }

    var tab = event.target.closest('.pix-user-home-tab, .pix-dashboard-dynamic-tab, .pix-user-home-collect-tab');
    if (tab) {
        pix_activate_scrolled_tab(tab);
    }
});

window.addEventListener('resize', function() {
    window.setTimeout(function() {
        pix_sync_active_tabs(document, 'auto');
    }, 80);
});

// cocomessage
cocoMessage.config({
    //配置全局参数    
    duration: 500000,
});


//代码高亮
hljs.highlightAll();
hljs.initCopyButtonOnLoad();
/*================================
 Loading
================================*/

function page_loader2(){
    var html = '<div class="bouncing-loader pix-page-loader">';
        html += '<div></div>';
        html += '<div></div>';
        html += '<div></div>';
        html += '</div>';
    
        return html;
    }

function loading_start(target) {
    target.append( page_loader2() );
}

function loading_done(target) {
    target.children('.pix-page-loader').remove();
}

//导航2showup
$(function(){
    //页面初始化的时候，获取滚动条的高度（上次高度）
    var start_height = $(document).scrollTop();
    //获取导航栏的高度(包含 padding 和 border)
    var navigation_height = $('.top-header.nav2.showup').outerHeight();
    var top_nav_h = $('.top-nav-box').outerHeight();
    var mainav = $('.top-header.nav2.showup');

    //var width = $('.top-header.nav2.showup').outerWidth();
    
    $(window).scroll(function() {
        //触发滚动事件后，滚动条的高度（本次高度）
        var end_height = $(document).scrollTop();
        //触发后的高度 与 元素的高度对比
        if (end_height > top_nav_h){
            mainav.css('top', -top_nav_h);
            mainav.addClass('active');
        } else {
            mainav.css('top', '0');
            mainav.removeClass('active');
        }
        //触发后的高度 与 上次触发后的高度
        if (end_height < start_height){
            mainav.css('top', '0');
            mainav.removeClass('active');
        }
        //再次获取滚动条的高度，用于下次触发事件后的对比
        start_height = $(document).scrollTop();
    });
});

//导航5 菜单折叠
$("#left_menu li.menu-item-has-children > a").on("click", function(i){
    i.preventDefault();
    if( ! $(this).parent().hasClass("active-nav") ){
        $("#left_menu li ul").slideUp(200);
        $(this).next().slideToggle(200);
        $("#left_menu li").removeClass("active-nav");
        $(this).parent().addClass("active-nav");
    }
    else{
        $(this).next().slideToggle(200);
        $("#left_menu li").removeClass("active-nav");
        }
});

// 灯箱 fancybox 5.0
Fancybox.bind("[data-fancybox]", {
    Hash: false,
});


//顶部菜单链接
$(".primary-nav.high li ul li , .primary-nav.main_high #primary_menu > li , .classic-nav.main_high #classic_menu > li").hover(function(){
    $(this).siblings().css('opacity','.4');
},function(){
    $(this).siblings().css('opacity','1');
});


//pix按钮点击跳转
$('body').on('click','.pix-btn',function(){
    var url = $(this).attr('href');
    window.open(url,'_blank');
});

/*================================
 Mobile bottom navigation
================================*/
$(function(){
    var $body = $('body');

    function pix_mobile_publish_sheet(open) {
        var $sheet = $('.pix-mobile-publish-sheet');
        var $trigger = $('.pix-mobile-bottom-nav-action');

        if (!$sheet.length) return;

        $sheet.toggleClass('is-open', !!open).attr('aria-hidden', open ? 'false' : 'true');
        $trigger.attr('aria-expanded', open ? 'true' : 'false');
        $body.toggleClass('pix-mobile-publish-sheet-open', !!open);
    }

    $body.on('click', '.pix-mobile-bottom-nav-action', function(event){
        event.preventDefault();
        pix_mobile_publish_sheet(!$('.pix-mobile-publish-sheet').hasClass('is-open'));
    });

    $body.on('click', '.pix-mobile-publish-backdrop', function(){
        pix_mobile_publish_sheet(false);
    });

    $(document).on('keydown', function(event){
        if (event.key === 'Escape') {
            pix_mobile_publish_sheet(false);
        }
    });

    $body.on('click', '.pix-mobile-publish-option[data-pix-mobile-publish="moment"]', function(event){
        var href = $(this).attr('href');

        if (window.Theme && parseInt(Theme.uid, 10) <= 0) {
            event.preventDefault();
            pix_mobile_publish_sheet(false);
            var $login = $('[data-pix-auth-open="login"]').first();
            if ($login.length) {
                $login.trigger('click');
            } else if (href) {
                window.location.href = href;
            }
            return;
        }

        var $composeTrigger = $('.pix-moment-mobile-compose-trigger').first();
        if ($composeTrigger.length && window.matchMedia && window.matchMedia('(max-width: 767px)').matches) {
            event.preventDefault();
            pix_mobile_publish_sheet(false);
            $composeTrigger.trigger('click');
        }
    });
});

/*================================
 Mobile top navigation
================================*/
$(function(){
    var $body = $('body');
    var mobileTopbarQuery = window.matchMedia ? window.matchMedia('(max-width: 767px)') : null;

    function pix_mobile_drawer(open) {
        var $drawer = $('.pix-mobile-drawer');
        var $trigger = $('.pix-mobile-menu-trigger');

        if (!$drawer.length) return;

        $drawer.toggleClass('is-open', !!open).attr('aria-hidden', open ? 'false' : 'true');
        $trigger.attr('aria-expanded', open ? 'true' : 'false');
        $body.toggleClass('pix-mobile-drawer-open', !!open);
    }

    function pix_search_overlay(open) {
        var $overlay = $('.pix-search-overlay');

        if (!$overlay.length) return;

        $overlay.toggleClass('is-active', !!open).attr('aria-hidden', open ? 'false' : 'true');
        $body.toggleClass('pix-search-open', !!open);

        if (open) {
            window.setTimeout(function(){
                $overlay.find('.pix-search-input').trigger('focus');
            }, 120);
        }
    }

    function pix_mobile_topbar_scroll_state() {
        var isMobile = mobileTopbarQuery ? mobileTopbarQuery.matches : window.innerWidth <= 767;
        var scrollTop = $(window).scrollTop();
        var isAway = isMobile && scrollTop > 16;
        var isScrolled = isMobile && scrollTop > 96;

        $body.toggleClass('pix-mobile-topbar-away', isAway && !isScrolled);
        $body.toggleClass('pix-mobile-topbar-scrolled', isScrolled);
    }

    function pix_mobile_dashboard_type() {
        var match = window.location.pathname.match(/\/dashboard(?:\/([^\/?#]+))?/);

        if (!match) return '';

        return match[1] || 'center';
    }

    function pix_mobile_is_user_home_path() {
        return /\/user\/[^\/?#]+\/?$/.test(window.location.pathname);
    }

    function pix_mobile_topbar_mode_state() {
        var dashboardType = pix_mobile_dashboard_type();
        var isBackMode = !!dashboardType || pix_mobile_is_user_home_path();

        $body.toggleClass('pix-mobile-topbar-back-mode', isBackMode);
        $('.pix-mobile-topbar').toggleClass('is-back-mode', isBackMode).toggleClass('is-menu-mode', !isBackMode);
    }

    pix_mobile_topbar_scroll_state();
    pix_mobile_topbar_mode_state();

    $(window).on('scroll.pixMobileTopbar resize.pixMobileTopbar orientationchange.pixMobileTopbar', pix_mobile_topbar_scroll_state);
    $(window).on('popstate.pixMobileTopbar', pix_mobile_topbar_mode_state);
    $(document.body).on('htmx:afterSwap htmx:pushedIntoHistory htmx:replacedInHistory', function(){
        window.setTimeout(pix_mobile_topbar_mode_state, 0);
        window.setTimeout(function() {
            pix_sync_active_tabs(document, 'auto');
        }, 60);
    });

    $body.on('click', '.pix-modern-dashboard .user-left-nav a, .pix-mobile-drawer-menu a', function(){
        window.setTimeout(pix_mobile_topbar_mode_state, 120);
    });

    if (mobileTopbarQuery && mobileTopbarQuery.addEventListener) {
        mobileTopbarQuery.addEventListener('change', pix_mobile_topbar_scroll_state);
    } else if (mobileTopbarQuery && mobileTopbarQuery.addListener) {
        mobileTopbarQuery.addListener(pix_mobile_topbar_scroll_state);
    }

    $body.on('click', '.pix-mobile-menu-trigger', function(event){
        event.preventDefault();
        pix_mobile_drawer(true);
    });

    $body.on('click', '.pix-mobile-topbar-back', function(event){
        var dashboardType = pix_mobile_dashboard_type();
        var homeUrl = $(this).data('home-url') || '/';
        var dashboardUrl = $(this).data('dashboard-url') || homeUrl;
        var fallbackUrl = dashboardType && dashboardType !== 'center' ? dashboardUrl : homeUrl;

        event.preventDefault();

        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = fallbackUrl;
        }
    });

    $body.on('click', '.pix-mobile-drawer-backdrop, .pix-mobile-drawer-close, .pix-mobile-drawer-menu a, .pix-mobile-drawer-footer a, .pix-mobile-drawer-login', function(){
        pix_mobile_drawer(false);
    });

    $body.on('click', '.pix-search-trigger', function(event){
        event.preventDefault();
        pix_mobile_drawer(false);
        pix_search_overlay(true);
    });

    $body.on('click', '.pix-search-backdrop, .pix-search-close', function(){
        pix_search_overlay(false);
    });

    $(document).on('keydown', function(event){
        if (event.key === 'Escape') {
            pix_mobile_drawer(false);
            pix_search_overlay(false);
        }
    });
});

/*================================
 ajax加载内容 文章|片刻
================================*/
var PixMomentListOptimizer = (function() {
    var desktopLimit = 60;
    var desktopKeep = 42;
    var mobileLimit = 36;
    var mobileKeep = 24;
    var desktopSectionSize = 20;
    var mobileSectionSize = 16;
    var observer = null;

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function ensureObserver() {
        if (observer || !('IntersectionObserver' in window)) return observer;
        observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    restore($(entry.target));
                }
            });
        }, {
            rootMargin: '900px 0px'
        });
        return observer;
    }

    function restore($placeholder) {
        if (!$placeholder || !$placeholder.length || !$placeholder.hasClass('moment-virtual-placeholder')) return;
        var htmlList = $placeholder.data('momentHtmlList');
        var html = $placeholder.data('momentHtml');
        if (!htmlList && html) {
            htmlList = [html];
        }
        if (!htmlList || !htmlList.length) return;
        var $items = $(htmlList.join(''));
        var currentHeight = $placeholder.outerHeight();
        if (currentHeight) {
            $items.first().css('min-height', currentHeight + 'px');
        }
        if (observer) {
            observer.unobserve($placeholder[0]);
        }
        $placeholder.replaceWith($items);
        window.setTimeout(function() {
            $items.first().css('min-height', '');
            pix_refresh_inserted_runtime($items);
        }, 30);
    }

    function canCollapse($item) {
        if (!$item || !$item.length || $item.hasClass('is-moment-virtualized')) return false;
        if ($item.find('.moment_comments_wrap:visible').length) return;
        if ($item.find('.pix-dropdown-panel.is-open, .jconfirm').length) return;
        return true;
    }

    function collapseSection($items, sectionIndex, listType) {
        $items = $items.filter(function() {
            return canCollapse($(this));
        });
        if (!$items.length) return;

        var height = 0;
        var htmlList = [];
        var firstTitle = '';
        $items.each(function(index) {
            var $item = $(this);
            height += Math.max(180, Math.ceil($item.outerHeight() || 0));
            htmlList.push($item.prop('outerHTML'));
            if (!index) {
                firstTitle = $.trim($item.find('.momoent_title a').text() || $item.find('.entry-title a').text() || $item.find('.mos-content p').text() || '内容');
            }
        });

        var $placeholder = $('<div class="moment-virtual-placeholder moment-virtual-section" role="button" tabindex="0"></div>');
        $placeholder.css('min-height', height + 'px');
        $placeholder.data('momentHtmlList', htmlList);
        $placeholder.attr('data-list-type', listType);
        $placeholder.attr('data-count', htmlList.length);
        $placeholder.html('<div class="moment-virtual-inner"><i class="ri-stack-line"></i><span>已折叠第 ' + sectionIndex + ' 段，共 ' + htmlList.length + ' 条较早' + (listType === 'post' ? '文章' : '片刻') + '</span><small>' + escapeHtml(firstTitle.substring(0, 28)) + '</small></div>');

        $items.first().before($placeholder);
        $items.remove();
        var io = ensureObserver();
        if (io) io.observe($placeholder[0]);
    }

    function collapseTargets($targets, sectionSize, listType) {
        var group = [];
        var sectionIndex = 1;

        function flushGroup() {
            if (!group.length) return;
            collapseSection($(group), sectionIndex, listType);
            sectionIndex++;
            group = [];
        }

        $targets.each(function() {
            var $item = $(this);
            if (!canCollapse($item)) {
                flushGroup();
                return;
            }

            group.push(this);
            if (group.length >= sectionSize) {
                flushGroup();
            }
        });

        flushGroup();
    }

    function run(selector) {
        var $box = $(selector || '#moment-item');
        if (!$box.length) return;
        var $items = $box.children('.moment_item, .post-item');
        var isMobile = window.matchMedia && window.matchMedia('(max-width: 640px)').matches;
        var isPostList = $items.first().hasClass('post-item');
        var limit = isPostList ? (isMobile ? 36 : 60) : (isMobile ? mobileLimit : desktopLimit);
        var keep = isPostList ? (isMobile ? 24 : 42) : (isMobile ? mobileKeep : desktopKeep);
        var sectionSize = isPostList ? (isMobile ? 12 : 18) : (isMobile ? mobileSectionSize : desktopSectionSize);
        if ($items.length <= limit) return;

        var collapseCount = $items.length - keep;
        var $targets = $items.slice(0, collapseCount);
        collapseTargets($targets, sectionSize, isPostList ? 'post' : 'moment');
    }

    return {
        run: run,
        restore: restore
    };
})();

function pix_refresh_inserted_runtime($scope) {
    var scopeNode = $scope && $scope.length ? $scope.get(0) : document;
    pix_init_sticky(scopeNode);

    if (typeof window.refresh_user_runtime === 'function') {
        window.refresh_user_runtime($scope && $scope.length ? $scope : $(document));
        return;
    }

    if (window.lazyLoadInstance && typeof lazyLoadInstance.update === 'function') {
        lazyLoadInstance.update();
    }
    if ($.fn.timeago) {
        ($scope && $scope.length ? $scope.find('time.timeago') : $("time.timeago")).timeago();
    }
    if (window.ReadMore && typeof ReadMore.init === 'function') {
        ReadMore.init('.ppo-rich-text-content');
    }
}

$('body').on('click keydown', '.moment-virtual-placeholder', function(event) {
    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    PixMomentListOptimizer.restore($(this));
});

(function(){
    function closePixDropdowns(except) {
        $('.pix-dropdown-panel.is-open').each(function(){
            var panel = $(this);
            if (except && panel.is(except)) return;
            panel.removeClass('is-open');
            panel.closest('.item, .user-has-login').find('.pix-dropdown-toggle').attr('aria-expanded', 'false');
        });
    }

    $('body').on('click', '.pix-dropdown-toggle', function(e){
        e.preventDefault();
        e.stopPropagation();

        var toggle = $(this);
        var wrap = toggle.closest('.item, .user-has-login');
        var panel = wrap.find('.pix-dropdown-panel[data-pix-dropdown="click"]').first();
        if (!panel.length) return;

        var willOpen = !panel.hasClass('is-open');
        closePixDropdowns(panel);
        panel.toggleClass('is-open', willOpen);
        toggle.attr('aria-expanded', willOpen ? 'true' : 'false');
    });

    $('body').on('keydown', '.pix-dropdown-toggle', function(e){
        if (e.key !== 'Enter' && e.key !== ' ') return;
        e.preventDefault();
        $(this).trigger('click');
    });

    $(document).on('click', function(e){
        if ($(e.target).closest('.pix-dropdown-panel, .pix-dropdown-toggle').length) return;
        closePixDropdowns();
    });

    $(document).on('keydown', function(e){
        if (e.key === 'Escape') {
            closePixDropdowns();
        }
    });
})();

$('body').on('click','.loadmore-btn',function(){
    var t = $(this),
        append_box = t.attr('data-append'),
        max_page = parseInt(t.attr('data-max'), 10) || 1,
        current_page = parseInt(t.attr('data-paged'), 10) || parseInt(Theme.current_page, 10) || 1,
        action = t.attr('data-action'),
        cat = t.attr('data-cat'),
        tag = t.attr('data-tag'),
        data = {
            'action': action,
            'paged': current_page,
            'max': max_page,
            'cat': cat,
            'tag': tag,
        };

    if(action == 'cls_load_moments'){
        data.security = Theme.moment_nonce;
        data.filter_type = $('.moment-cat-filter .filter-item.active').attr('type') || 'new';
    } else if(action == 'cls_load_posts'){
        data.security = Theme.post_nonce;
    }
        
    if(t.hasClass('protect')){
        return false;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: data,
        beforeSend: function () {
            t.text('LOADING..').addClass('protect');
            $('.pager-info .go-top a').hide();
            $('.pager-info .go-top').append('<div class="pager-loader pix-spinner pix-spinner-sm"></div>');
		},
        success: function (data) {
            if( data && data.content ) { 
                var result = $(data.content);
                t.text( '加载更多' ).removeClass('protect'); 
                $(append_box).append(result.fadeIn(300));

                $('.pager-info .go-top a').show().siblings('.pager-loader').remove();

                //$body.animate({scrollTop: result.offset().top - 58}, 500 );
                pix_refresh_inserted_runtime(result);
                if (action == 'cls_load_moments') {
                    PixMomentListOptimizer.run(append_box);
                } else if (action == 'cls_load_posts') {
                    PixMomentListOptimizer.run(append_box);
                }
                current_page++; //页面增加
                Theme.current_page = current_page;
                $('.paged-number .current').text(current_page);
                t.attr('data-paged',current_page);
                if ( current_page >= max_page ) {
                    t.hide().before('<div class="no-more">暂无更多</div>');
                    
                }
               

            } else {
                t.hide().before('<div class="no-more">暂无更多</div>');
            }
        },
        error: function () {
            t.text('加载更多').removeClass('protect');
            $('.pager-info .go-top a').show().siblings('.pager-loader').remove();
            if (typeof toastfy === 'function') {
                toastfy('加载失败，请稍后重试', 'error');
            }
        }
    });
}); 

/*================================
 文章 片刻 无限加载
================================*/
if(Theme.moment_nav == 'scroll' && $('.loadmore-btn[data-action="cls_load_moments"]').length > 0 ){
    $window = $(window);
$(window).scroll(function(){
    var $btn = $('.loadmore-btn[data-action="cls_load_moments"]:visible').first(),
    max_page = parseInt($btn.attr('data-max'), 10) || 1,
    paged = parseInt($btn.attr('data-paged'), 10) || 1;
    if(!$btn.length || $btn.hasClass('protect')) return;
    if( $window.scrollTop() + $window.height() > $(document).height() - 500 && paged < max_page ){
       $btn.trigger('click');
    }
}); 
}

if(Theme.post_nav == 'scroll' && $('.loadmore-btn[data-action="cls_load_posts"]').length > 0 ){
    $window = $(window);
    $(window).scroll(function(){
    var $btn = $('.loadmore-btn[data-action="cls_load_posts"]:visible').first(),
    max_page = parseInt($btn.attr('data-max'), 10) || 1,
    paged = parseInt($btn.attr('data-paged'), 10) || 1;
    if(!$btn.length || $btn.hasClass('protect')) return;
    if( $window.scrollTop() + $window.height() > $(document).height() - 500 && paged < max_page ){
       $btn.trigger('click');
    }
}); 
}

/*================================
 文章 片刻 页码加载
================================*/
$('body').on('click','.pagination-box .page-numbers li a',function(e){
    var t = $(this),
        action = t.parents('.pagination-box').attr('data-action'),
        box = t.parents('.pagination-box');

    if (!action || !box.attr('data-append')) {
        return;
    }

    e.preventDefault();

    if (box.hasClass('is-loading')) {
        return;
    }

    var url = t.attr('href'),
        match = url.match(/\/page\/(\d+)\/?/) || url.match(/[?&]paged=(\d+)/),
        paged = match ? match[1] : 1,
        filter_type = $('.filter-item.active').attr('type'),
        baseurl = box.attr('data-base-url') || window.location.href,
        cat = box.attr('data-cat'),
        tag = box.attr('data-tag'),
        max_page = box.attr('data-max'),
        append_box = box.attr('data-append'),
        append_target = $(append_box),
        is_archive_pagination = box.hasClass('pix-archive-pagination') && box.attr('data-context') === 'archive',
        data = {
            'action': action,
            'paged': paged,
            'max':max_page,
            'cat': cat,
            'tag': tag,
            'filter_type': filter_type,
            'baseurl': baseurl,
        };

        if (box.attr('data-context')) {
            data.context = box.attr('data-context');
            data.archive_type = box.attr('data-archive-type') || '';
            data.term_id = box.attr('data-term-id') || '';
            data.taxonomy = box.attr('data-taxonomy') || '';
            data.year = box.attr('data-year') || '';
            data.monthnum = box.attr('data-monthnum') || '';
            data.day = box.attr('data-day') || '';
            data.post_type = box.attr('data-post-type') || '';
        }

        if(action == 'cls_load_moments'){
            data.security = Theme.moment_nonce;
        } else if(action == 'cls_load_posts'){
            data.security = Theme.post_nonce;
        }
        
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            data: data,
            beforeSend: function () {
                if (is_archive_pagination) {
                    box.addClass('is-loading').attr('aria-busy', 'true');
                    box.find('a').attr({
                        'aria-disabled': 'true',
                        'tabindex': '-1'
                    });
                    append_target.addClass('pix-archive-results-loading');
                    if (!box.find('.pix-archive-pagination-loading').length) {
                        box.append('<span class="pix-archive-pagination-loading" role="status" aria-live="polite"><i class="ri-loader-4-line"></i><span>正在加载</span></span>');
                    }
                }
            },
            success: function (data) {
                if( data ) { 
                    
                    var result = $(data.content);
                    append_target.html(result.fadeIn(300));
                    if(action == 'cls_load_moments'){
                        append_target.css('min-height', '');
                    }
                    box.html(data.pagenav);
                    $body.animate({scrollTop: result.offset().top - 300}, 300 );
                    if(action == 'cls_load_moments' && typeof pix_moment_refresh_runtime === 'function'){
                        pix_moment_refresh_runtime(result, append_target[0]);
                    } else {
                        pix_refresh_inserted_runtime(result);
                    }
                    if(action == 'cls_load_moments' || action == 'cls_load_posts'){
                        PixMomentListOptimizer.run(append_box);
                    }
                    
                   
    
                } else {
                    t.remove(); 
                }
            },
            error: function () {
                if (is_archive_pagination && typeof cocoMessage !== 'undefined') {
                    cocoMessage.error('加载失败，请稍后重试');
                }
            },
            complete: function () {
                if (is_archive_pagination) {
                    box.removeClass('is-loading').removeAttr('aria-busy');
                    box.find('a').removeAttr('aria-disabled tabindex');
                    box.find('.pix-archive-pagination-loading').remove();
                    append_target.removeClass('pix-archive-results-loading');
                }
            }
        });
        
});

/*================================
 搜索结果页 AJAX 分页
================================*/
$('body').on('click', '[data-pix-search-pagination] .page-numbers li a', function(e){
    e.preventDefault();

    var link = $(this),
        href = link.attr('href') || '',
        match = href.match(/\/page\/(\d+)\/?/) || href.match(/[?&]paged=(\d+)/),
        paged = match ? match[1] : 1,
        params = new URLSearchParams(window.location.search || ''),
        searchQuery = params.get('s') || $('.pix-search-page-form input[name="s"]').val() || '',
        results = $('[data-pix-search-results]'),
        pagination = $('[data-pix-search-pagination]').first(),
        label = $('[data-pix-search-page-label]');

    if (!results.length || !searchQuery) {
        window.location.href = href;
        return;
    }

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: Theme.ajaxurl,
        data: {
            action: 'pix_search_page',
            security: Theme.post_nonce,
            s: searchQuery,
            paged: paged
        },
        beforeSend: function () {
            results.empty();
            loading_start(results);
        },
        success: function (response) {
            loading_done(results);

            if (!response || !response.success || !response.data) {
                if (typeof toastfy === 'function') {
                    toastfy('搜索结果加载失败，请稍后重试', 'error');
                }
                return;
            }

            var content = $(response.data.content || '');
            results.html(content.fadeIn(300));

            if (response.data.pagination) {
                pagination.replaceWith(response.data.pagination);
            } else {
                pagination.remove();
            }

            if (label.length && response.data.page_label) {
                label.text(response.data.page_label);
            }

            if (window.history && window.history.pushState) {
                window.history.pushState(null, '', href);
            }

            $body.animate({scrollTop: results.offset().top - 220}, 300);
            pix_refresh_inserted_runtime(results);
        },
        error: function () {
            loading_done(results);
            if (typeof toastfy === 'function') {
                toastfy('搜索结果加载失败，请稍后重试', 'error');
            }
        }
    });
});




/*================================
 文章分类筛选
================================*/
var pixPostFilterRequest = null;
var pixPostFilterSeq = 0;
var pixPostFilterTimer = null;
var pixPostFilterQueued = null;

function render_post_filter_error(message){
    message = message || '文章加载失败，请稍后重试';
    return '<div class="no-moment post-filter-error"><img src="'+Theme.ppo_url+'/img/empty.png"><p>'+message+'</p><button type="button" class="post-filter-retry">重试</button></div>';
}

function is_post_filter_running(){
    return pixPostFilterRequest && pixPostFilterRequest.readyState !== 4;
}

function run_post_filter($item, forceRetry){
    var t = $item,
        append_box = '#blog-item',
        load_btn = '.loadmore-btn[data-action="cls_load_posts"]',
        cat = t.attr('data'),
        baseurl = window.location.href;

    if(!t.length){
        return;
    }

    if(t.hasClass('active') && !forceRetry && !$('.cls-blog-cat-filter').data('filterFailed') && !$('.cls-blog-cat-filter').hasClass('is-filtering')){
        return;
    }

    t.addClass('active');
    t.parent().siblings().children().removeClass('active');

    if(pixPostFilterTimer){
        clearTimeout(pixPostFilterTimer);
        pixPostFilterTimer = null;
    }

    var requestSeq = ++pixPostFilterSeq;
    $('.cls-blog-cat-filter').addClass('is-filtering').removeData('filterFailed');

    if(is_post_filter_running()){
        pixPostFilterQueued = t;
        return;
    }

    pixPostFilterTimer = setTimeout(function(){
        pixPostFilterTimer = null;
        $(append_box).empty();

        var currentRequest = null;
        currentRequest = pixPostFilterRequest = $.ajax({
            type: 'POST',
            dataType: 'json',
            url: Theme.ajaxurl,
            timeout: 15000,
            data: {
                action: 'cls_filter_posts',
                security: Theme.post_nonce,
                cat: cat,
                baseurl: baseurl
            },
            beforeSend: function () {
                $(load_btn).parents('.pagenav-box').hide();
                loading_start($(append_box));
            },
            success: function (data) {
                if(requestSeq !== pixPostFilterSeq){
                    return;
                }

                loading_done($(append_box));
                if(!data || typeof data.content === 'undefined'){
                    $('.cls-blog-cat-filter').data('filterFailed', true);
                    $(append_box).html(render_post_filter_error());
                    $('.pagenav-box').hide();
                    if(typeof toastfy === 'function'){
                        toastfy('文章加载失败，请稍后重试','error');
                    }
                    return;
                }

                Theme.current_page = 1;
                Theme.max_page = data.max_page;

                if(Theme.post_nav == 'pagenav'){
                    $('.pagination-box').attr({
                        'data-cat':cat,
                        'data-max':data.max_page,
                    });

                    if ( data.max_page < 2 ) {
                        $('.pagination-box').empty();
                    } else {
                        $('.pagination-box').html(data.pagenav);
                    }
                } else {
                    $(load_btn).parents('.pagenav-box').show();
                    $(load_btn).attr({
                        'data-max':data.max_page,
                        'data-paged':'1',
                        'data-cat':cat,
                    });
                    $(load_btn).show().siblings('.no-more').remove();
                    $('.paged-number .current').text('1');
                    $('.paged-number .total').text(data.max_page);

                    if ( data.max_page < 2 ) {
                        $('.pagenav-box').hide();
                    } else {
                        $('.pagenav-box').show();
                    }
                }

                var result = $(data.content);
                $(append_box).append(result.fadeIn(300));

                lazyLoadInstance.update();
                $("time.timeago").timeago();
                PixMomentListOptimizer.run(append_box);
            },
            error: function(xhr){
                if(requestSeq !== pixPostFilterSeq || (xhr && xhr.statusText === 'abort')){
                    return;
                }
                loading_done($(append_box));
                $('.cls-blog-cat-filter').data('filterFailed', true);
                var msg = xhr && xhr.statusText === 'timeout' ? '文章筛选请求超时，请重试' : '文章加载失败，请稍后重试';
                $(append_box).html(render_post_filter_error(msg));
                $('.pagenav-box').hide();
                if(typeof toastfy === 'function'){
                    toastfy(msg,'error');
                }
            },
            complete: function(){
                if(currentRequest !== pixPostFilterRequest){
                    return;
                }

                pixPostFilterRequest = null;

                if(pixPostFilterQueued && pixPostFilterQueued.length){
                    var queuedItem = pixPostFilterQueued;
                    pixPostFilterQueued = null;
                    run_post_filter(queuedItem, true);
                    return;
                }

                if(requestSeq === pixPostFilterSeq){
                    loading_done($(append_box));
                    $('.cls-blog-cat-filter').removeClass('is-filtering');
                }
            }
        });
    }, 120);
}

$('body').off('click.postFilter', '.posts_cat_nav ul li a').on('click.postFilter','.posts_cat_nav ul li a',function(e){
    e.preventDefault();
    run_post_filter($(this), false);
});

$('body').off('click.postFilterRetry', '.post-filter-retry').on('click.postFilterRetry', '.post-filter-retry', function(){
    run_post_filter($('.posts_cat_nav ul li a.active').first(), true);
});

//片刻评论提交
$(document).on("submit", "#t_commentform , #commentform",function(){
	var t = $(this);
	var parent = t.find('#comment_parent').val();
	var pid = t.find('#comment_post_ID').val();
    var com_type = t.attr('com-type');
    var commentSubmitEl = t.find('#push_comment').get(0) || t.find('.push_comment').get(0) || t.get(0);

    if($('input#push_comment').hasClass('protect')){
        //cocoMessage.error('无法提交');
        return false;
    }

    if(t.attr('id') == 't_commentform'){
        var respond = $("#t_commentform");
    } else {
        var respond = $(".comment-respond");
    }

	var pixCommentUploader = t.find('.pix-comment-uploader').data('pixUploader');
    if (pixCommentUploader && typeof window.syncPixCommentUploaderField === 'function') {
        window.syncPixCommentUploaderField(t, pixCommentUploader.value());
    }

    var submitComment = function(logincaptchaPayload) {
        var $scope = t.closest('.comments-area');
        var $commentBox = $scope.find('.commentshow').first();
        if (!$commentBox.length) {
            $commentBox = t.closest('.commentshow');
        }
        var _list = $commentBox.length ? $commentBox.find('.comment-list').first() : $scope.find('.comment-list').first();
        var cancel = t.find('#cancel-comment-reply-link');
        var formData = t.serialize();
        if (logincaptchaPayload) {
            formData += "&logincaptcha=" + encodeURIComponent(JSON.stringify(logincaptchaPayload));
        }

        $.ajax({
            url:Theme.ajaxurl,
            data: formData + "&action=ajax_comment",
            type: t.attr('method'),

            beforeSend: function () {
                toastfy("提交中....","info");
            },
            error: function(request) {
                toastfy(request.responseText,"error");
            },
            complete: function() {
                if (typeof window.pixcapClearContentVerification === 'function') {
                    window.pixcapClearContentVerification('comment', commentSubmitEl, 1800);
                }
            },
            success: function(data){
                if (pixCommentUploader) {
                    pixCommentUploader.setItems([], 'image');
                    t.find('input[name="comt-uploaded-urls"]').val('');
                    t.find('.img-box-drop').hide();
                    updateCommentImageBadge(t);
                }
                if(com_type == 'reply'){
                    toastfy("回复成功","success");
                    $('textarea#comment').val('');
                    return;
                }

                if(parent !='0'){
                    respond.before('<ul class="children">' + data + '</ul>');
                } else if(!_list.length){
                    if ($commentBox.length) {
                        $commentBox.html('<ul class="comment-list">' + data + '</ul>');
                    } else {
                        respond.after('<ul class="comment-list">' + data + '</ul>');
                    }
                } else {
                    _list.prepend(data);
                }
                lazyLoadInstance.update();
                toastfy("提交成功","success");
                if (window.PixMomentCommentCache && pid) {
                    PixMomentCommentCache.remove(pid);
                }
                if (cancel.length) {
                    cancel.click();
                }
                if ($commentBox.length) {
                    $commentBox.find(".comment-list .nodata").remove();
                } else {
                    $(".comment-list .nodata").remove();
                }
            }
        });
    };

    if ((window.Theme && Theme.content_protect_type) === 'pixcap' && typeof window.pixcapVerifyContent === 'function') {
        window.pixcapVerifyContent('comment', commentSubmitEl).then(function(payload) {
            submitComment(payload);
        }).catch(function(error) {
            toastfy((error && error.message) || '验证失败，请重试', 'error');
        });
    } else {
        submitComment(null);
    }
	return false;
});	

//回复按钮
$(document).on('click','.comment-reply-link',function(event){
	var t = $(this);
	event.preventDefault();
    $('.comment-respond').addClass('respond-reply');
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
    $('.com-footer').slideDown(100);
    $('.cancel-comment-textarea').hide();
	$("#cancel-comment-reply-link").css("display","");	
    update_comt_img('reset');
});	

//取消回复
$(document).on('click','#cancel-comment-reply-link',function(event){
	var t = $(this);
	event.preventDefault();
    var author = $('.com-author input').val();
    var email = $('.com-email input').val();
    var url = $('.com-url input').val();

    $('.comment-respond').removeClass('respond-reply');
    $('.cancel-comment-textarea').show();
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

    }else {
        var form = $("#t_commentform").prop('outerHTML');
        var respond_box = $(".toi_respond_"+pid+"");
        t.parents("form").remove();
        respond_box.append(form);
        $("#cancel-comment-reply-link").css("display","none");
        $("#comment_parent").val('0');
        $("textarea#comment").focus().attr('placeholder','不准备说点什么？');
    } 

    $('.com-author input').val(author)
    $('.com-email input').val(email);
	$('.com-url input').val(url);
    update_comt_img();
});	


function pix_comment_textarea_min_height(textarea) {
    var minHeight = parseFloat(window.getComputedStyle(textarea).minHeight);
    return minHeight && minHeight > 0 ? minHeight : 40;
}

$(document).on('input propertychange','textarea#comment',function(event){ 
    // 自动调整textarea的高度
    var minHeight = pix_comment_textarea_min_height(this);
    this.style.height = minHeight + 'px';
    this.style.height = Math.max(this.scrollHeight, minHeight) + 'px';

    if($(this).val().length > 1){
        $('#push_comment').removeClass('protect');
    } else {
        $('#push_comment').addClass('protect');
    }
  });

$(document).on('focus','textarea#comment',function(event){ 
    $('.com-footer').slideDown(100);
    if($('#comment-author-info input#email').val() == '' || $('#comment-author-info input#author').val() == ''){
        $('#comment-author-info-wrap').slideDown(100);
        $('a.edit-visitor-info').text('确认修改');
    }
});

$(document).on('click','.cancel-comment-textarea',function(event){ 
    $('.com-footer').slideUp(100);
});

$(document).on('click','a.edit-visitor-info',function(){
    var author = $('.com-author input').val();
    var email = $('.com-email input').val();
    if ($(this).text() === '修改资料') {
        $(this).text('确认修改');
      } else {
        if(author == '' || email == ''){
            toastfy('请填写必要信息，昵称和邮箱','error');
            return;
        }
        $(this).text('修改资料');
        $('.visitor-title span').text(author);
      }
      $('#comment-author-info-wrap').slideToggle(100);
});

//ajax获取有课评论头像
$(document).on('blur','.com-email input#email',function(){
	var _email = $(this).val();
    var _name = $(".com-author input#author").val();
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
                //$('a.edit-profile small').text(data.name+" , 编辑");
			}
		}); // end ajax
	} else {
        $('.v-avatar').attr('src', Theme.de_ava);
    }

	});

// ajax自定义翻页
$('body').on('click', '.ppo-pagenav a', function(){
    var baseUrl = $(this).attr("href"),
    Holder = $(this).parent().attr("data-holder"),
    action = $(this).parent().attr("data-action"),
    page = 1,
    queryString = baseUrl.split('?')[1],
    regex = /current_page=(\d+)/;

    if(regex.test(queryString)){
        page = queryString.match(regex)[1];
    }

    var ajax_data = {
        action: action,
        paged: page
    };

    $('.ppo-pagenav').prev('div').html('');
    $.ajax({
        url : Theme.ajaxurl, // AJAX handler, declared before
        data : ajax_data,
        type : 'POST',
        beforeSend : function ( xhr ) {
            loading_start($('.ppo-pagenav').prev('div'));
        },
        success : function( data ){
            loading_done($('.ppo-pagenav').prev('div'));
            $('.'+Holder).html(data);
            $("time.timeago").timeago();
            lazyLoadInstance.update();
            
            
            }
    });
    return false;
    
});

//ajax评论翻页
$(document).on("click", ".commentnav a",
    function() {
        var t = $(this),
        baseUrl = t.attr("href"),
        $nav = t.closest('.commentnav'),
        commentsHolder = $nav.closest(".commentshow"),
        id = $nav.data("fuck"),
        page = 1,
        concelLink = $("#cancel-comment-reply-link");
        if (!commentsHolder.length) {
            commentsHolder = $nav.closest(".comments-area");
        }
        /comment-page-/i.test(baseUrl) ? page = baseUrl.split(/comment-page-/i)[1].split(/(\/|#|&).*jQuery/)[0] : /cpage=/i.test(baseUrl) && (page = baseUrl.split(/cpage=/)[1].split(/(\/|#|&).*jQuery/)[0]);
        concelLink.click();
        var ajax_data = {
            action: "ajax_comment_page_nav",
            post_id: id,
            paged: page,
            security: Theme.moment_nonce
        };
        var $list = commentsHolder.find('ul.comment-list').first();
		$list.html('');
        
        $.ajax({
            url : Theme.ajaxurl, // AJAX handler, declared before
            data : ajax_data,
            type : 'POST',
            beforeSend : function ( xhr ) {
                loading_start($list.length ? $list : commentsHolder);
            },
            success : function( data ){
                commentsHolder.html(data);
                $("time.timeago").timeago();
                lazyLoadInstance.update();
                //remove loading
                if (commentsHolder.length) {
                    $("body, html").animate({
                        scrollTop: commentsHolder.offset().top - 50
                    },
                    1e3)
                }
                }
        });
        return false;
    });    


//ajax评论翻页
$(document).on("click", ".commentmore-btn",
function() {
    var t = $(this);
    if (t.data('loading')) {
        return false;
    }
    var post_id = t.attr('post_id');
    var page = parseInt(t.attr('data-page') || t.attr('cpage') || '1', 10) || 1;
    var nextPage = page + 1;
    var $commentsHolder = t.closest('.commentshow');
    if (!$commentsHolder.length) {
        $commentsHolder = t.closest('.comments-area');
    }
    var $list = $commentsHolder.find('ul.comment-list').first();

    $.ajax({
        url : Theme.ajaxurl, // AJAX handler, declared before
        dataType: 'json',
        data : {
            'action': 'cloadmore', // wp_ajax_cloadmore
            'post_id': post_id, // the current post
            'page' : nextPage, // next comment page
            'security': Theme.moment_nonce
        },
        type : 'POST',
        beforeSend : function ( xhr ) {
            t.data('loading', true).addClass('is-loading');
            t.text('Loading...'); // preloader here
        },
        success : function( data ){
            if( data && data.status == 1 && data.html ) {
                t.attr('data-page', data.page);
                $list.append( $(data.html).hide().fadeIn() );
                lazyLoadInstance.update(); //重载懒加载
                $("time.timeago").timeago(); //重载时间
                if (data.has_more) {
                    t.text('加载更多');
                } else {
                    t.parent('.commentmore').remove();
                }
            } else {
                t.parent('.commentmore').remove();
            }
        },
        error: function () {
            if (typeof toastfy === 'function') {
                toastfy('分页加载失败，请稍后重试', 'error');
            }
            t.text('加载更多');
        },
        complete: function () {
            t.data('loading', false).removeClass('is-loading');
        }
    });
    return false;
});



// 评论表情
$(document).on('click','.com-emoji-btn',function(event){
    event.preventDefault();
    var $btn = $(this);
    var $box = $btn.closest('.com-emoji-box').find('.emoji-box').first();
    var $inner = $box.find('.emoji-inner').first();
    $box.fadeToggle(100);
    if ($inner.find('.add-smily').length) {
        return;
    }
    $.ajax({
        url : Theme.ajaxurl, // AJAX handler, declared before
        data : {
            'action': 'showemoji', // wp_ajax_cloadmore
        },
        type : 'POST',
        beforeSend : function ( xhr ) {
            if(!$inner.find('.add-smily').length){
                loading_start($inner);
            }
            //loading_start($('.emoji-inner'));
        },
        success : function( data ){
            $inner.html(data.html);
        }
    });
});

$(document).on('click','a.add-smily',function(){
    var data = $(this).attr('data-smilies');
    var emoji = "[s="+data+"]";
    var textarea = $(this).closest('form').find('.comarea textarea#comment');
    if (!textarea.length) {
        textarea = $('.comarea textarea#comment').first();
    }
    var content = textarea.val();
	textarea.val(content+emoji);
	textarea.focus();
});
window.update_comt_img = window.update_comt_img || function() {};

// 评论图片上传 PixUploader 2.0
if (typeof window.PixUploader !== 'undefined') {
    function pixCommentFormFromNode(node) {
        var $form = $(node).closest('form');
        return $form.length ? $form : $('#t_commentform, #commentform').first();
    }

    function syncCommentUploaderField($form, value) {
        if (!$form || !$form.length) return;
        var urls = [];
        if (value && value.items && value.items.length) {
            value.items.forEach(function(item) {
                if ((item.kind || item.type) === 'image' && item.url) {
                    urls.push(item.url);
                }
            });
        }

        var $field = $form.find('input[name="comt-uploaded-urls"]');
        if (!$field.length) {
            $field = $('<input type="hidden" name="comt-uploaded-urls" class="comt-uploaded-urls">');
            $form.find('.comment-tools').append($field);
        }
        $field.val(urls.join(','));
        updateCommentImageBadge($form);
    }
    window.syncPixCommentUploaderField = syncCommentUploaderField;

    function commentUploadSettings() {
        var settings = window.Theme && Theme.comment_upload ? Theme.comment_upload : {};
        return {
            enabled: settings.enabled !== false,
            limit: Math.max(1, Math.min(12, parseInt(settings.limit, 10) || 4)),
            maxSize: Math.max(1, Math.min(20, parseInt(settings.max_size, 10) || 2))
        };
    }

    function getCommentUploader($form) {
        if (!$form || !$form.length) return null;
        return $form.find('.pix-comment-uploader').data('pixUploader') || null;
    }

    function getCommentUploadCount($form) {
        var uploader = getCommentUploader($form);
        if (!uploader) return 0;
        return uploader.activeItems().filter(function(item) {
            return item.status !== 'error';
        }).length;
    }

    function updateCommentImageBadge($form) {
        if (!$form || !$form.length) return;
        var count = getCommentUploadCount($form);
        var $btn = $form.find('.com-img-btn').first();
        if (!$btn.length) return;
        var $badge = $btn.find('.pix-comment-image-badge');
        if (count > 0) {
            if (!$badge.length) {
                $badge = $('<em class="pix-comment-image-badge"></em>');
                $btn.append($badge);
            }
            $badge.text(count);
            $btn.addClass('has-uploaded-image').attr('title', '已添加 ' + count + ' 张图片');
        } else {
            $badge.remove();
            $btn.removeClass('has-uploaded-image').removeAttr('title');
        }
    }

    function initPixCommentUploader($form) {
        if (!$form || !$form.length) return null;
        var settings = commentUploadSettings();
        if (!settings.enabled) {
            pixToast('评论图片上传已关闭', 'error');
            return null;
        }
        var $box = $form.find('.comt-upload-box').first();
        if (!$box.length) return null;
        $form.find('.img-box-drop').first().addClass('is-pix-comment-upload');

        var $mount = $box.find('.pix-comment-uploader').first();
        if (!$mount.length) {
            $box.html('<div class="pix-comment-uploader"></div>');
            $mount = $box.find('.pix-comment-uploader').first();
        }

        var existing = $mount.data('pixUploader');
        if (existing) {
            existing.activeKind = 'image';
            return existing;
        }

        var uploader = new window.PixUploader($mount[0], {
            context: 'comment_image',
            type: 'image',
            limit: settings.limit,
            maxSize: settings.maxSize,
            multiple: true,
            accept: 'image/*',
            allowExternal: false,
            allowLibrary: true,
            allowBili: false,
            allowCard: false,
            allowedKinds: ['image'],
            libraryContext: '',
            preventOutsideClose: true,
            nonce: Theme.upload_nonce || '',
            onChange: function(value) {
                syncCommentUploaderField($form, value);
            }
        });
        uploader.activeKind = 'image';
        $mount.data('pixUploader', uploader);
        syncCommentUploaderField($form, uploader.value());
        return uploader;
    }

    window.update_comt_img = function() {
        initPixCommentUploader($('#t_commentform, #commentform').first());
    };

    $(document).on('click', '.com-img-btn', function(event) {
        event.preventDefault();
        if (!commentUploadSettings().enabled) {
            pixToast('评论图片上传已关闭', 'error');
            return;
        }
        var $form = pixCommentFormFromNode(this);
        initPixCommentUploader($form);
        $form.find('.img-box-drop').first().fadeToggle(100);
        updateCommentImageBadge($form);
    });

    if (!commentUploadSettings().enabled) {
        $('.com-img-box').hide();
    }

    $(document).on('click', '.com-code-btn', function(event) {
        event.preventDefault();
        pixCommentFormFromNode(this).find('.code-box-drop').first().fadeToggle(100);
    });

    function adjustTextareaHeight() {
        var textarea = $('.comarea textarea#comment');
        if (!textarea.length) return;
        textarea.height(0);
        textarea.height(textarea[0].scrollHeight);
    }

    $(document).on('click', '.insert-code', function() {
        var code = $(this).prev('textarea').val();
        var insert = "\n[code]\n" + code + "\n[/code]\n";
        var textarea = pixCommentFormFromNode(this).find('.comarea textarea#comment');
        var content = textarea.val();
        textarea.val(content + insert);
        adjustTextareaHeight();
        textarea.focus();
    });

    $('body').on('click', function(event) {
        var target = $(event.target);
        var elementToHide = $('.comt-tool-box');
        if (!target.is(elementToHide) && !target.closest(elementToHide).length) {
            elementToHide.hide();
        }
    });

    $(window).on('beforeunload', function(event) {
        var hasPendingCommentImages = false;
        $('#t_commentform, #commentform').each(function() {
            if (getCommentUploadCount($(this)) > 0) {
                hasPendingCommentImages = true;
                return false;
            }
        });
        if (hasPendingCommentImages) {
            event.preventDefault();
            event.returnValue = '';
            return '';
        }
    });
}

// vip权限展开
/* new Readmore('.vip-limits', {
    speed: 75,
    collapsedHeight: 300,
    moreLink: '<a  class="vipmore" href="#"><i class="ri-arrow-down-s-line"></i>更多权益</a>',
    lessLink: '<a class="vipless" href="#"><i class="ri-arrow-up-s-line"></i>收起</a>'
}); */

// 付费预览图切换
function ppo_sync_dd_gallery(box, index){
    var thumbs = box.find('.dd-box-gals');
    var current = thumbs.eq(index);
    if(!current.length) return;
    var img = current.data('full') || current.attr('href') || current.find('img').attr('src');
    thumbs.removeClass('picked');
    current.addClass('picked');
    box.find('.dd-box-fea').attr('href', img).attr('data-index', index);
    box.find('.dd-box-fea').find('img').attr('src', img);
}

function ppo_move_dd_gallery(box, step){
    var thumbs = box.find('.dd-box-gals');
    if(thumbs.length <= 1) return;
    var active = thumbs.index(thumbs.filter('.picked'));
    if(active < 0){
        active = parseInt(box.find('.dd-box-fea').attr('data-index'), 10);
    }
    if(isNaN(active)) active = 0;
    var next = (active + step + thumbs.length) % thumbs.length;
    ppo_sync_dd_gallery(box, next);
}

$('body').on('click','.dd-box-gals', function(e){
    e.preventDefault();
    var t = $(this);
    var box = t.closest('.download-box');
    var index = parseInt(t.attr('data-index'), 10);
    ppo_sync_dd_gallery(box, isNaN(index) ? t.index() : index);
});

$('body').on('click','.dd-box-fea', function(e){
    if($(this).data('swiped')){
        e.preventDefault();
        $(this).removeData('swiped');
        return;
    }

    if(typeof Fancybox === 'undefined' || !Fancybox.show) return;

    var t = $(this);
    var box = t.closest('.download-box');
    var thumbs = box.find('.dd-box-gals');
    var items = [];

    thumbs.each(function(){
        var thumb = $(this);
        var src = thumb.data('full') || thumb.attr('href') || thumb.find('img').attr('src');
        if(src){
            items.push({
                src: src,
                type: 'image',
                thumbSrc: thumb.find('img').attr('src')
            });
        }
    });

    if(!items.length){
        items.push({
            src: t.attr('href'),
            type: 'image',
            thumbSrc: t.find('img').attr('src')
        });
    }

    e.preventDefault();

    var startIndex = parseInt(t.attr('data-index'), 10);
    if(isNaN(startIndex)) startIndex = 0;

    var syncTimer = null;
    Fancybox.show(items, {
        startIndex: startIndex,
        on: {
            ready: function(fancybox){
                syncTimer = setInterval(function(){
                    var slide = fancybox.getSlide && fancybox.getSlide();
                    if(slide && typeof slide.index !== 'undefined'){
                        ppo_sync_dd_gallery(box, slide.index);
                    }
                }, 120);
            },
            close: function(){
                if(syncTimer) clearInterval(syncTimer);
            },
            destroy: function(){
                if(syncTimer) clearInterval(syncTimer);
            }
        }
    });
});

$('body').on('touchstart','.dd-box-fea', function(e){
    var touch = e.originalEvent.touches && e.originalEvent.touches[0];
    if(!touch) return;
    $(this).data('touchStart', {
        x: touch.clientX,
        y: touch.clientY,
        time: Date.now()
    });
});

$('body').on('touchmove','.dd-box-fea', function(e){
    var start = $(this).data('touchStart');
    var touch = e.originalEvent.touches && e.originalEvent.touches[0];
    if(!start || !touch) return;

    var dx = touch.clientX - start.x;
    var dy = touch.clientY - start.y;
    if(Math.abs(dx) > 12 && Math.abs(dx) > Math.abs(dy)){
        e.preventDefault();
    }
});

$('body').on('touchend','.dd-box-fea', function(e){
    var t = $(this);
    var start = t.data('touchStart');
    var touch = e.originalEvent.changedTouches && e.originalEvent.changedTouches[0];
    t.removeData('touchStart');
    if(!start || !touch) return;

    var dx = touch.clientX - start.x;
    var dy = touch.clientY - start.y;
    var dt = Date.now() - start.time;
    var isSwipe = Math.abs(dx) >= 42 && Math.abs(dx) > Math.abs(dy) * 1.35 && dt < 800;
    if(!isSwipe) return;

    e.preventDefault();
    t.data('swiped', true);
    ppo_move_dd_gallery(t.closest('.download-box'), dx < 0 ? 1 : -1);
});

// 模态框基础
function ppo_modal(object){
    var html = '<div id="'+object+'" class="pix-modal pix-hs-modal hidden" role="dialog" tabindex="-1">';
        html += '<div class="pix-modal-dialog">';
        html += '<div class="pix-modal-panel '+object+' pix-animation-scale-up pix-animation-fast">';
        html += '<div class="inner"></div>';
        html += ' <button class="pix-modal-close" type="button" data-pix-modal-close="#'+object+'" aria-label="关闭"><i class="ri-close-line"></i></button>';
        html += '</div></div></div>';

        return html;
}

// 付费下载模态框
function ppo_down_modal(){
    var html = '<div id="ppo-down-modal" class="pix-modal pix-hs-modal pix-down-modal hidden" role="dialog" tabindex="-1" aria-labelledby="ppo-down-modal-title">';
        html += '<div class="pix-modal-dialog">';
        html += '<div class="pix-modal-panel ppo-down-modal">';
        html += '<span id="ppo-down-modal-title" class="screen-reader-text">资源下载</span>';
        html += '<div class="inner"></div>';
        html += '<button class="pix-modal-close" type="button" data-pix-modal-close="#ppo-down-modal" aria-label="关闭"><i class="ri-close-line"></i></button>';
        html += '</div></div></div>';

        return html;
}

// 文章底部工具栏
$(document).ready(function(){
    if(Theme.pid){
        var $target = $('.single-footer');
        if (!$target.length) return;
        var width = $target.width();
        $(window).on('scroll.stickyFooter', function() {
            var offset = $target.offset();
            if (!offset) return;
            var targetTop = offset.top;
            var scrollTop = $(window).scrollTop();
            var d = $(window).height();

            if (scrollTop + d > targetTop) {
                $target.removeClass('sticky', true);
            } else {
                $target.toggleClass('sticky', true);
                $target.children().css('width', width);
            }
        });
    }
});

new ClipboardJS('.code-copy');
$('body').on('click','.code-copy', function(){
    //cocoMessage.info('复制成功');
    toastfy('复制成功','success');
});
  //instantclick预加载
  //InstantClick.init(50);

// 骨架屏模板
var skeletonTemplates = {
    "normal-list": `
    <div class="pix-user-home-skeleton-item pix-user-home-skeleton-item-full">
      <div class="skeleton-container">
        <div class="skeleton skeleton-full-box"></div>
      </div>
    </div>    
    `,
    "follow-list": `
    <div class="pix-user-home-follow-item">
      <div class="skeleton-container">
      <div class="skeleton-row">
        <div class="skeleton skeleton-avatar-square"></div>
        <div class="skeleton-text-group">
            <div class="skeleton skeleton-text-line"></div>
            <div class="skeleton skeleton-text-line short"></div>
        </div>
        </div>
      </div>
    </div>    
    `,
    "post-list": `
    <div class="pix-user-home-skeleton-item pix-user-home-post-wrap">
      <div class="skeleton-container">
          <div class="skeleton skeleton-image"></div>
         <div class="skeleton skeleton-text-line"></div>
         <div class="skeleton skeleton-text-line short"></div>
      </div>
    </div>
    `,
    "user-comment": `
      <div class="pix-user-home-comment-item">
       <div class="skeleton-row">
        <div class="skeleton-text-group">
            <div class="skeleton skeleton-text-line"></div>
            <div class="skeleton skeleton-text-line short"></div>
        </div>
        <div class="skeleton skeleton-avatar-square"></div>
        </div>
      </div>
    `,
    "moments-list": `
      <div class="pix-user-home-moments-item">
       <div class="skeleton-container">
          <div class="skeleton skeleton-image"></div>
      </div>
      </div>
    `,
    "moment-list": `
    <div class="pix-user-home-moment-item">
      <div class="skeleton-container">
      <div class="skeleton-row">
        <div class="skeleton skeleton-avatar-square"></div>
        <div class="skeleton-text-group">
            <div class="skeleton skeleton-text-line"></div>
            <div class="skeleton skeleton-text-line short"></div>
        </div>
        </div>
          <div class="skeleton skeleton-image"></div>
         <div class="skeleton skeleton-text-line"></div>
         <div class="skeleton skeleton-text-line short"></div>
      </div>
    </div>
  `,
  };

var skeletonGridClasses = {
    "normal-list": "pix-dashboard-normal-skeleton-list",
    "follow-list": "pix-user-home-follow-list",
    "post-list": "pix-user-home-posts-grid",
    "user-comment": "pix-user-home-comments-list",
    "moments-list": "pix-user-home-moments-list",
    "moment-list": "pix-user-home-moment-list",
  };

var userHomePanelClasses = [
    'user-index-posts',
    'user-index-moment',
    'user-index-moments',
    'user-index-comments',
    'user-index-collect',
    'user-index-follow',
    'pix-user-home-posts-panel',
    'pix-user-home-moment-panel',
    'pix-user-home-moments-panel',
    'pix-user-home-comments-panel',
    'pix-user-home-collect-panel',
    'pix-user-home-follow-panel',
    'pix-modern-moment'
  ];

var userHomePanelTypeClasses = {
    posts: ['user-index-posts', 'pix-user-home-posts-panel'],
    moment: ['user-index-moment', 'pix-user-home-moment-panel', 'pix-modern-moment'],
    moments: ['user-index-moments', 'pix-user-home-moments-panel'],
    comments: ['user-index-comments', 'pix-user-home-comments-panel'],
    collect: ['user-index-collect', 'pix-user-home-collect-panel'],
    follow: ['user-index-follow', 'pix-user-home-follow-panel']
  };

var userHomeSkeletonPanelTypes = {
    'post-list': 'posts',
    'moment-list': 'moment',
    'moments-list': 'moments',
    'user-comment': 'comments',
    'follow-list': 'follow'
  };

function pix_get_user_home_panel_type(triggerEl, container) {
    if (triggerEl) {
      var skeletonType = triggerEl.getAttribute('data-skeleton');
      var href = triggerEl.getAttribute('hx-push-url') || triggerEl.getAttribute('href') || triggerEl.getAttribute('hx-get') || '';

      if (href) {
        try {
          var url = new URL(href, window.location.origin);
          var tab = url.searchParams.get('tab');
          if (tab) return tab;
        } catch (error) {}
      }

      if (triggerEl.classList.contains('user-moment-tab')) return 'moment';
      if (triggerEl.classList.contains('user-moments-tab')) return 'moments';
      if (triggerEl.classList.contains('user-posts-tab')) return 'posts';
      if (triggerEl.classList.contains('user-comments-tab')) return 'comments';
      if (triggerEl.classList.contains('user-collect-tab')) return 'collect';
      if (skeletonType && userHomeSkeletonPanelTypes[skeletonType]) return userHomeSkeletonPanelTypes[skeletonType];
    }

    if (container) {
      if (container.querySelector('.pix-user-home-moment-list')) return 'moment';
      if (container.querySelector('.pix-user-home-moments-list')) return 'moments';
      if (container.querySelector('.pix-user-home-posts-grid')) return 'posts';
      if (container.querySelector('.pix-user-home-comments-list')) return 'comments';
      if (container.querySelector('.pix-user-home-collect-grid')) return 'collect';
      if (container.querySelector('.pix-user-home-follow-list')) return 'follow';
    }

    return '';
  }

function pix_sync_user_home_panel(triggerEl, container) {
    if (!container || container.id !== 'user-content') return;

    var type = pix_get_user_home_panel_type(triggerEl, container);
    if (!type || !userHomePanelTypeClasses[type]) return;

    container.classList.add('user-index-box', 'pix-user-home-panel');
    userHomePanelClasses.forEach(function(className) {
      container.classList.remove(className);
    });
    userHomePanelTypeClasses[type].forEach(function(className) {
      container.classList.add(className);
    });
  }

document.body.addEventListener("htmx:beforeRequest", function (e) {
    const triggerEl = e.target;
    const skeletonType = triggerEl.getAttribute("data-skeleton");
    const targetSelector = triggerEl.getAttribute("hx-target");
    const container = document.querySelector(targetSelector);
  
    if (skeletonType && skeletonTemplates[skeletonType] && container) {
      pix_sync_user_home_panel(triggerEl, container);
      const scopedGridClass = skeletonGridClasses[skeletonType] || '';
      // 根据类型插入多份骨架（根据需要调整数量）
      container.innerHTML = '<div class="skeleton-grid pix-user-home-skeleton-grid '+ scopedGridClass +'">'
                          + skeletonTemplates[skeletonType]
                          + skeletonTemplates[skeletonType]
                          + skeletonTemplates[skeletonType]
                          + '</div>';
    }
    //e.preventDefault();
  }); 

 
  // htmx 加载完成后重新初始化组件
  document.body.addEventListener("htmx:afterSwap", function(event) {
    var target = event.detail && event.detail.target ? event.detail.target : document;
    var triggerEl = event.detail && event.detail.elt ? event.detail.elt : null;
    pix_sync_user_home_panel(triggerEl, target);
    window.setTimeout(function() {
      pix_sync_active_tabs(document, 'auto');
    }, 60);

    if (typeof window.refresh_user_runtime === 'function') {
      window.refresh_user_runtime($(target));
    } else {
      if (lazyLoadInstance) {
        lazyLoadInstance.update(); // 更新实例，识别新加载的 lazy 图片
      }
      $("time.timeago").timeago();
    }

    var url = event.detail.xhr?.responseURL || '';

    /* // ✅ 判断是否是签到接口
    if (url.includes('action=ppo_checkin')) {
        var $checink = $('.user-signed-btn');
        if (!$checink.length) return;

        var xp = $checink.attr('data-xp');
        var point = $checink.attr('data-point');

        Toastify({
            text: `签到成功！经验+${xp}， 积分+${point}`,
            className: "info",
            style: {
              background: "linear-gradient(to right, #00b09b, #96c93d)",
            }
          }).showToast();
    } */

  });
$('body').on('click','.ppo-navtab a',function(){
    var t = $(this);
    t.addClass('active').siblings().removeClass('active');
});

// 封装toastfy
function toastfy(msg, type) {
  var background;
  var color = '#ffffff';

  switch(type){
    case 'error':
        background = 'linear-gradient(to right, #ff3a3a, #ff5656)'; // 红色
        break;
    case 'info':
        background = 'linear-gradient(to right, #1f213b, #292a3c)'; // 默认
        break;
    case 'success':
        background = 'linear-gradient(to right, #03c956, #3ebe6e)';
        break;
    case 'normal':
        background = 'linear-gradient(to right, #ffffff, #ffffff)'; // 默认
        color = '#454545';
        break;    
  }
  

  Toastify({
    text: msg,
    duration: 5000,
    position: "center",
    stopOnFocus: true,  
    style: {
      background: background,
      color:color
    }
  }).showToast();
}

// 海报模态
function pix_ensure_poster_modal(){
	var modal = $('#poster-modal');
	if(modal.length){
		return modal;
	}

	$('body').append('<div id="poster-modal" class="pix-modal pix-hs-modal pix-poster-modal hidden" role="dialog" tabindex="-1" aria-labelledby="poster-modal-title"><div class="pix-modal-dialog"><div class="pix-modal-panel poster-modal"><button class="pix-modal-close" type="button" data-pix-modal-close="#poster-modal" aria-label="关闭"><i class="ri-close-line"></i></button><span id="poster-modal-title" class="screen-reader-text">分享海报</span><div class="poster-canvas"></div><div class="share-tool"><a href="javascript:;" class="share-qq" aria-label="分享到QQ"><i class="ri-qq-line"></i></a><a href="javascript:;" class="share-zone" aria-label="分享到QQ空间"><i class="ri-chrome-line"></i></a><a href="javascript:;" class="copy-link" aria-label="复制链接"><i class="ri-link"></i></a><a href="javascript:;" class="download-poster" aria-label="下载海报"><i class="ri-download-line"></i></a></div><img id="output" style="display:none;"/></div></div></div>');
	return $('#poster-modal');
}

$('body').on('click','.poster-btn',function(e){
	e.preventDefault();
	var pid = $(this).data('pid') || Theme.pid;
	if(!pid){
		toastfy('内容ID不能为空','error');
		return;
	}
	var modal = pix_ensure_poster_modal();
	var canvas = modal.find('.poster-canvas');
	var requestId = Date.now();

	modal.data('posterRequestId', requestId);
	modal.removeData('posterUrl').removeData('posterTitle');
	canvas.empty();

	if(typeof ppo_show_modal === 'function'){
		ppo_show_modal(modal);
	}else{
		modal.removeClass('hidden').addClass('open opened').attr('aria-modal', 'true');
		$('body').addClass('hs-overlay-body-open');
	}

	$.ajax({
		type: "post",
		url:Theme.ajaxurl,
		dataType:  'json',
		data: {
			'action':'load_poster_modal',
			pid: pid,
		},
		beforeSend: function () {
			loading_start(canvas);
		},
		success: function(data) {
			if (modal.data('posterRequestId') !== requestId) {
				return;
			}
			loading_done(canvas);
			if(!data || !data.html){
				toastfy((data && data.msg) || '海报加载失败','error');
				return;
			}
			modal.data('posterUrl', data.url || '');
			modal.data('posterTitle', data.title || '');
			canvas.html(data.html);
		 },
		error: function() {
			if (modal.data('posterRequestId') !== requestId) {
				return;
			}
			loading_done(canvas);
		},
	});
});

$('body').on('click','.copy-link',function(){
	var modal = $(this).closest('#poster-modal');
	var url = modal.data('posterUrl') || window.location.href;
	var done = function(){
		toastfy('链接已复制','success');
	};

	if(navigator.clipboard && navigator.clipboard.writeText){
		navigator.clipboard.writeText(url).then(done).catch(function(){
			var textarea = $('<textarea readonly></textarea>').val(url).css({position:'fixed', left:'-9999px'}).appendTo('body');
			textarea[0].select();
			document.execCommand('copy');
			textarea.remove();
			done();
		});
		return;
	}

	var textarea = $('<textarea readonly></textarea>').val(url).css({position:'fixed', left:'-9999px'}).appendTo('body');
	textarea[0].select();
	document.execCommand('copy');
	textarea.remove();
	done();
});

$('body').on('click','.share-qq, .share-zone',function(){
	var modal = $(this).closest('#poster-modal');
	var url = encodeURIComponent(modal.data('posterUrl') || window.location.href);
	var title = encodeURIComponent(modal.data('posterTitle') || document.title);
	var shareUrl = $(this).hasClass('share-zone')
		? 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' + url + '&title=' + title
		: 'https://connect.qq.com/widget/shareqq/index.html?url=' + url + '&title=' + title;

	window.open(shareUrl, '_blank', 'noopener,noreferrer,width=760,height=640');
});

// 生成海报
$('body').on('click','.download-poster',function(){
	var element = document.querySelector('.poster-canvas');
	if(!element || !element.children.length){
		toastfy('海报还没有加载完成','error');
		return;
	}
	if(typeof html2canvas !== 'function'){
		toastfy('海报组件未加载，请刷新后重试','error');
		return;
	}
	html2canvas(element,{useCORS: true}).then(canvas => {
		// 将canvas转换为Data URL
		var imgData = canvas.toDataURL('image/png');
		var filename = $('.poster-trim h4').text();

		var link = document.createElement('a');
		link.download = filename+'.png';
		link.href = imgData;
		link.click();
	});
});

// 侧边栏固定
if (typeof $.fn.hcSticky !== 'undefined' && Theme.nav_height) {
  var stickyTop = $('body').hasClass('classic') ? 16 : (parseInt(Theme.nav_height) || 72);

  $( '.widget_inner' ).each(function(){
    var options = {
      top: stickyTop,
    };
    var $stickBoundary = $(this).closest('.pix-home-layout, .pix-single-layout, .pix-moment-shell, .moment-edit-warp');

    if ($stickBoundary.length) {
      options.stickTo = $stickBoundary.get(0);
    }

    $(this).hcSticky(options);
  });

  $( '.user-left-nav' ).each(function(){
    var options = {
      top: stickyTop,
    };

    if ($(this).closest('.pix-modern-user-home').length) {
      options.responsive = {
        960: {
          disable: true,
        },
      };
    }

    $(this).hcSticky(options);
  });
}

$(document).on('click','.pix-sign-tab',function(){
    var t = $(this);
    t.addClass('active').siblings().removeClass('active');
    $('.pix-sign-rank-list[data-tab="'+t.data('tab')+'"]').show().siblings('.pix-sign-rank-list').hide();
});
