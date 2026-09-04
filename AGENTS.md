# Glintide 项目协作规则

本文件只描述 Glintide 主题的实际结构、数据契约和前台交互约束。先确认当前根目录的运行链路，再修改代码；不要根据 `reference/`、`backup/` 或文件名猜测当前行为。

## 1. 当前项目边界

- 当前运行代码是仓库根目录：`functions.php`、`style.css`、`header.php`、`footer.php`、`index.php`、`single.php`、`tpl/`、`inc/` 和 `assets/`。
- `reference/` 是旧主题/参考实现，`backup/` 是备份。除非任务明确要求“参考”或“移植”，只读，不把其中的文件当成当前实现，也不直接覆盖根目录文件。
- `inc/assets/codestar-framework/` 是随主题保存的 Codestar Framework 第三方代码。不要在里面重构、格式化或升级框架；通过现有配置接入。
- `inc/mod/glintide-widget.php` 是旧的小工具实现，当前根目录 `functions.php` 不加载它。新增或修改小工具使用 `inc/widgets/` 的自动扫描机制；只有明确做旧功能移植时才处理旧模块。
- `AGENTS.md` 与 `AI-DEVELOPMENT-RULES.md` 都可能包含历史规则；如果文档和当前根目录代码冲突，以用户要求和实际加载链路为准，并在交付时说明差异。
- 当前没有发现 npm、构建、测试或打包配置。不要为小改动擅自引入依赖或建立新的前端构建链。

## 2. 当前加载链路

`functions.php` 是主题启动入口。修改功能前，优先沿着这条链路确认依赖：

```text
functions.php
├── Codestar Framework + inc/options/theme-option.php
├── inc/mod/glintide-rest.php
├── inc/widgets/class-glintide-widgets.php
│   ├── inc/widgets/class-glintide-widget.php
│   └── inc/widgets/class-widget-*.php
├── inc/frontend/navigation.php
├── inc/frontend/site-tools.php
├── inc/options/home-option.php
├── inc/frontend/home-banner.php
└── inc/mod/glintide-content-cards.php
```

前台模板职责：

- `header.php` 和 `footer.php` 是最小页面骨架，必须保留 `wp_head()`、`wp_body_open()`、`wp_footer()` 以及现有容器 ID/class。
- `index.php` 是当前首页内容卡片流，负责左栏导航、中栏卡片流、右栏工具和无限加载哨兵。
- `single.php` 是统一文章和内容类型的独立页面模板。
- `tpl/content-card.php` 负责单张卡片的结构，媒体输出统一调用 `glintide_card_media_html()`。
- 根目录目前没有使用中的 `archive.php`、`single.php`、`page.php` 等模板；`reference/` 或 `backup/` 中有这些文件，不代表当前主题已加载它们。

## 3. 主题的稳定契约

### 3.1 命名和兼容层

- 新 PHP 函数使用 `glintide_` 前缀；新类使用 `Glintide_` 前缀；新前台 CSS 优先使用 `.glintide-组件`、`.glintide-组件__元素`、`.glintide-组件--状态`。
- 现有兼容名称不能顺手改掉：`THEME_DIR`、`THEME_URL`、`PIX_VERSION`、`pix-*`、`pix_`、`ppo-*` 仍被 Codestar、旧样式或音乐小工具使用。修改这些名称必须同时检查所有读取方并给出迁移理由。
- 现有浏览器全局契约包括 `window.glintide_card_ajax.url`、`window.glintideMusic.restUrl`、`window.glintideStyleVersion`。新配置优先通过 enqueue/localize 传入，不要散落硬编码 URL。

### 3.2 设置和布局变量

- 主题设置统一存储在 `glintide_options`，读取优先使用 `glintide_get_option( $key, $default )`。
- 不得重命名已有 option key、字段 ID 或依赖关系。特别是站点 Logo、头像、首页封面 `cls_banner_*`、三栏宽度、`card_radius`、`card_shadow` 等字段可能被后台和前台多个模块读取。
- `functions.php` 会在 `wp_head` 输出由设置驱动的 CSS 变量：`--glintide-radius`、`--glintide-shadow-card`、`--glintide-sidebar-left-width`、`--glintide-center-width`、`--glintide-sidebar-right-width`、`--glintide-layout-width`。新增布局或卡片样式先复用这些变量。
- 左栏/右栏小工具区域 ID 是 `sidebar-left` 和 `sidebar-right`；顶部主菜单位置是 `top`。不要依赖旧主题的 sidebar ID 或菜单位置。

### 3.3 内容卡片数据模型

- 统一内容实体是 WordPress 默认文章 `post`，文章编辑页通过 `_glintide_card_type` 选择文章、照片、音乐、视频或链接，并使用 `category`、`post_tag`。
- 卡片类型只有 `text`、`photo`、`music`、`video`、`link`。未知类型回退为 `text`。
- 关键 meta key 由 `inc/mod/glintide-content-cards.php` 统一读写：

  - `_glintide_card_type`
  - `_glintide_card_article_cover`
  - `_glintide_card_gallery`
  - `_glintide_card_music_source`、`_glintide_card_music_title`、`_glintide_card_music_artist`、`_glintide_card_music_url`、`_glintide_card_music_cover`
  - `_glintide_card_video_url`
  - `_glintide_card_video_source`（`bilibili`、`youtube` 或 `upload`）
  - `_glintide_card_link_url`、`_glintide_card_link_label`
  - `likes_count` 以及登录用户的 `glintide_card_likes`

  - `music` 先选择音频地址或自己上传：网易云地址自动获取歌名、作者和封面，自上传音频再手动填写这些信息，且不要求正文；直链不可用时提供网易云官方歌曲页播放入口。`video` 先选择 Bilibili、YouTube 或自己上传，链接使用嵌入播放器，上传视频使用自定义控制层，视频类型不要求正文；`link` 使用网页 iframe 预览，悬停时缓慢滚动，点击在新窗口打开。

- `glintide_card_seed_version` 和 seed 标记用于演示文章初始化。修改 seed 或版本标记前，先确认是否会补数据或影响已有文章。
- 首次运行的演示卡片创建逻辑是幂等的，但可能写入站点数据。不要为了测试反复改变 seed 版本，也不要把演示数据逻辑复制到模板中。
- 修改 meta、option 或卡片类型时，先用 `rg` 找出所有读写方；不能只改后台字段而忘记前台渲染器、单页、AJAX 和 JS。

## 4. WordPress 与 PHP 约束

- 主题功能挂在 WordPress 生命周期：主题支持和菜单用 `after_setup_theme`，小工具区域用 `widgets_init`，前台资源用 `wp_enqueue_scripts`，后台卡片资源用 `admin_enqueue_scripts`，REST 路由用 `rest_api_init`。
- CSS/JS 必须通过 enqueue 加载。当前资源版本主要使用 `filemtime()`；修改资源加载时保留稳定 handle、依赖关系和加载位置。
- 当前内容类型后台只在 `post` 编辑/列表页面加载 `glintide-card-admin.css` 和 `glintide-card-admin.js`。不要把媒体库和后台内容类型脚本无条件加载到所有后台页面。
- 模板只组织结构；卡片类型判断、meta 读取、媒体 URL 解析、AJAX 输出放在 `inc/mod/glintide-content-cards.php` 或对应功能模块，不在模板里堆数据库查询和业务流程。
- 被直接加载的 PHP 模块保留 `ABSPATH` 防护。输出边界按数据类型使用 `esc_html()`、`esc_attr()`、`esc_url()`、`wp_kses_post()`；写入前使用 `wp_unslash()`、`sanitize_key()`、`sanitize_text_field()`、`esc_url_raw()`、`absint()` 等合适处理。
- 后台保存卡片必须保留 nonce、`current_user_can( 'edit_post', $post_id )` 和字段级校验。客户端隐藏字段或 JS 校验不能代替服务端校验。
- 新增写操作必须考虑对象类型、权限、nonce、失败返回和重复提交。已有游客可用的卡片 feed、详情、点赞、评论 AJAX 行为属于现行产品契约，不要在没有产品要求的情况下任意改成登录专用。

## 5. REST、AJAX 与第三方媒体

### 5.1 REST

当前 REST 命名空间是 `/glintide/v1`，已有公开 GET 路由：

```text
/wp-json/glintide/v1/netease-song?url=...
/wp-json/glintide/v1/netease-playlist?url=...
```

- 网易云请求在 `inc/mod/glintide-rest.php`，使用 WordPress HTTP API、15 秒超时和 transient 缓存；不要在模板或 JS 中直接拼第三方 API 请求。
- 单曲返回的 `id`、`audioUrl`、`embedUrl`、`title`、`artist`、`cover` 和歌单返回的 `tracks` 等字段被音乐卡片使用。改字段时必须同步 PHP、卡片 JS 和小工具播放器。
- 直链可能因版权或时效为空；前台必须保留“重新解析”和网易云官方 iframe 降级路径，不要把空 `audioUrl` 当成接口成功但播放器永远无反馈。
- 新 REST 路由要明确方法、参数、校验、权限和错误状态；已有路由的公开权限和返回结构视为兼容约束。

### 5.2 卡片 AJAX

卡片模块当前注册以下 `admin-ajax.php` action，前后端名称必须一致：

```text
glintide_card_feed       # 首页分页 HTML
glintide_card_detail     # 详情弹窗数据
glintide_card_comment    # 弹窗发表评论
glintide_card_like       # 点赞/取消点赞
```

- 前台地址通过 `glintide_card_ajax.url` 提供；不要在脚本中新增另一套 AJAX 地址配置。
- 评论使用按卡片生成的 nonce；点赞同时涉及 `glintide_liked_{post_id}` cookie、`likes_count` 和用户 meta。修改点赞逻辑时要同时检查游客、登录用户、重复点击和取消点赞。
- feed 返回的是 `tpl/content-card.php` 生成的 HTML。新增卡片 DOM 属性或 class 时，必须检查无限加载、弹窗、照片、音乐、视频和点赞脚本是否仍能识别。

## 6. 小工具扩展方式

- `Glintide_Widgets::init()` 会自动加载 `inc/widgets/class-widget-*.php`，并注册所有继承 `Glintide_Widget` 的类。新增小工具通常只需新增符合命名的类文件，不要修改管理器或手工重复 require。
- 小工具类按现有约定提供静态 `$id`、`$title`、`$description`、`$classname`、`fields()` 和 `render( $instance )`；前台包装函数沿用已有文件的 `before_widget` / `after_widget` 结构。
- 现有小工具包含广告、菜单、图标网格、画廊、一言、评论、分类推荐和音乐播放器。小工具实例配置来自 Codestar，不要在前台模板中重新定义同一份字段。
- 音乐小工具仍使用 `pix-music-*` 和 `data-music-*` 选择器，并在实例内使用内联脚本；这是现有兼容表面。不要未经验证把它和内容卡片音乐播放器合并或批量改名。
- 小工具缺少配置时应使用现有的空状态/提示结构，不要输出 PHP warning，也不要静默伪造一个已配置成功的结果。

## 7. 前台卡片与交互约束

### 7.1 三栏和 PJAX

- 当前桌面布局是左栏固定、中栏内容滚动、右栏工具/小工具固定；`style.css` 在 `min-width: 901px` 使用固定侧栏，在 `max-width: 900px` 收为单列。
- PJAX 只替换 `.glintide-site-column--left` 和 `.glintide-site-column--center`，右栏不会替换，以便音乐播放器持续播放。不要改变这两个容器的结构或把需要保留的播放器放进被替换区域。
- 需要完整跳转的链接使用 `data-glintide-no-pjax`（或现有 `data-no-pjax`）。下载、外部资源、媒体控件、弹窗独立页等行为不能被 PJAX 拦截。
- PJAX、无限加载和媒体脚本在内容替换/追加后依靠 `glintide:cards-appended` 重新初始化。初始化必须幂等，保留现有的元素标记或等价保护，不能重复绑定事件、重复创建 Swiper 或重复发请求。

### 7.2 各卡片类型

- `text` 是文章/随笔排版卡片；`photo` 使用照片拼贴和 Swiper lightbox；`music` 支持自上传音频和网易云解析/官方播放器降级；`video` 使用自定义控制层并暂停其他视频；`link` 使用网页 iframe 预览。
- 媒体统一由 `glintide_card_media_html( $post_id, 'card'|'single' )` 输出。空媒体优先走 `glintide_card_media_empty()`，不要每种类型另造一套占位结构。
- 照片画廊当前最多渲染 9 张。若改变数量、缩略图/大图字段或 `data-glintide-photo-*` 属性，要同步后台字段、PHP 输出、lightbox 和移动端布局。
- 卡片中的 `<a>` 用于导航，`<button>` 用于播放、点赞、关闭、切换等动作；弹窗、照片预览、视频控制和主题切换必须保留键盘操作、焦点可见、`aria-label`/`aria-expanded`/`aria-pressed` 等状态。
- loading、空数据、网络失败、音频直链失效、图片加载失败和移动端窄屏都是真实状态。失败后要能重试或进入明确降级路径，不能只留下不可点击的 loading。

## 8. CSS 与前端资源

- `style.css` 是当前主样式，文件较大且包含历史样式和后部针对当前三栏/内容卡片的覆盖规则。修改选择器前先搜索全部定义，理解级联和加载顺序；不要全文件格式化或删除“看起来重复”的旧规则。
- 颜色、表面、文字、边线、圆角和阴影优先复用已有 `--color-glintide-*`、`--glintide-radius*`、`--glintide-shadow-*` 变量。深色模式同时依赖 `html.glintide-dark` 和 `html[data-glintide-theme="dark"]`，新增颜色不能只修浅色。
- `html` 是桌面端主要滚动容器；现有 CSS 特意避免让 `body` 形成第二个滚动容器。修改 `overflow`、`height`、固定侧栏或 PJAX 内容高度时，要验证不会出现双滚动条。
- 当前主题同时加载 Remix Icon、主题 iconfont、Swiper CSS/JS 以及 `assets/css/glintide-music-backup.css`。文件名带 backup 的音乐样式仍在 `functions.php` 中加载，不能仅凭文件名删除。
- 主题切换使用 localStorage key `glintide-theme`，并写入 `html.glintide-dark` / `data-glintide-theme`。不要将主题状态只放在某个被 PJAX 替换的局部节点。
- 响应式至少检查 320px、768px、900px、1024px、1440px；重点看长标题、长链接、无图卡片、九图画廊、播放器控制和左右栏是否遮挡内容。

## 9. 修改和验证流程

修改前：

1. 查看 `git status --short` 和相关 `git diff`，把已有未提交修改当作用户资产。
2. 用 `rg` 找入口、调用方、option/meta key、REST/AJAX action、CSS/JS data attribute。
3. 确认改动属于当前根目录，还是明确的参考代码移植；不要顺手修改 `reference/`、`backup/` 或 Codestar。

修改中：

- 只改完成任务所需的文件，不顺手升级依赖、重命名公共契约、清理整个旧主题或重排巨型 CSS。
- 不提交、推送、发布、部署或删除备份，除非用户明确要求。
- 修改资源、option/meta、REST 返回字段、AJAX action 或卡片 data attribute 时，把所有消费者作为一个整体更新。

验证时，按改动范围执行：

- 文档或 CSS 改动至少运行 `git diff --check`。
- PHP 改动对每个变更文件运行 `php -l`；如果没有 WordPress 运行环境，不要声称已完成页面运行验证。
- JS 改动对每个独立 JS 文件运行 `node --check`；PHP 内联脚本还需要在实际页面或浏览器控制台检查。
- 前台改动应验证首页、统一文章单页、空数据、无限加载、PJAX 返回、照片预览、音乐直链/降级、视频控制、点赞/评论和主题切换；涉及后台时再验证文章编辑器、媒体库、设置页和小工具注册。
- 最终说明改了哪些文件、做了哪些验证、哪些部分没有真实运行条件。没有浏览器或 WordPress 页面证据时，不要把“语法通过”写成“页面已可用”。

交付判断以当前产品契约为准：主题仍能被 WordPress 识别，`wp_head`/`wp_body_open`/`wp_footer` 完整，后台设置/小工具/REST/AJAX 契约未被意外破坏，卡片在桌面和移动端的正常、空、加载、失败状态都有明确行为。
