<?php
/**
 * The template for displaying search results pages
 *
 * @package pix
 */

get_header();

global $wp_query;

$sidebar = pix_sidebar();
$layout_classes = array('pix-home-layout');
if ($sidebar['left']) {
    $layout_classes[] = 'pix-home-layout--has-left';
}
if ($sidebar['right']) {
    $layout_classes[] = 'pix-home-layout--has-right';
}

$search_query = get_search_query();
$found_posts = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
$paged = max(1, (int) get_query_var('paged'));
$max_pages = isset($wp_query->max_num_pages) ? (int) $wp_query->max_num_pages : 1;
$hot_terms = get_terms(array(
    'taxonomy' => 'category',
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 8,
    'hide_empty' => true,
));
?>

<div class="pix-content home-box home-classic pix-modern pix-modern-home pix-modern-search">
    <div class="<?php echo esc_attr(implode(' ', $layout_classes)); ?>">

        <?php if ($sidebar['left']) : ?>
            <aside class="left left-widget pix-home-sidebar pix-home-sidebar-left" aria-label="博客左侧栏">
                <div class="widget_inner blog_left_inner pix-home-widget-stack">
                    <?php dynamic_sidebar('blog-left'); ?>
                </div>
            </aside>
        <?php endif; ?>

        <div class="center-content pix-home-main<?php
            if (!$sidebar['left'] || !$sidebar['right']) echo ' center-half';
        ?>">
            <main id="primary" class="site-main pix-search-page">
                <section class="pix-search-hero">
                    <div class="pix-search-hero-inner">
                        <div class="pix-search-kicker">Search Results</div>
                        <h1><?php echo $search_query ? '搜索：' . esc_html($search_query) : '站内搜索'; ?></h1>
                        <p>
                            <?php
                            if ($search_query) {
                                printf('为你找到 %s 条相关内容。', esc_html(number_format_i18n($found_posts)));
                            } else {
                                echo '输入关键词，快速找到文章、资源和灵感。';
                            }
                            ?>
                        </p>
                        <form class="pix-search-page-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <i class="ri-search-2-line"></i>
                            <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="继续搜索关键词" autocomplete="off">
                            <button type="submit">搜索</button>
                        </form>
                    </div>
                </section>

                <section class="pix-search-body pix-home-blog-content">
                    <?php if (have_posts()) : ?>
                        <div class="pix-search-section-head">
                            <div>
                                <span>Results</span>
                                <h2>搜索结果</h2>
                            </div>
                            <p data-pix-search-page-label>第 <?php echo esc_html(number_format_i18n($paged)); ?> 页 / 共 <?php echo esc_html(number_format_i18n($max_pages)); ?> 页</p>
                        </div>

                        <div id="blog-item" class="pix-search-results pix-home-post-list" data-pix-search-results>
                            <?php
                            while (have_posts()) :
                                the_post();
                                if ('post' === get_post_type()) {
                                    get_template_part('tpl/content');
                                } elseif ('moment' === get_post_type()) {
                                    get_template_part('tpl/content', 'mgrid');
                                } else {
                                    get_template_part('tpl/content', 'search');
                                }
                            endwhile;
                            ?>
                        </div>

                        <?php
                        $pagination = paginate_links(array(
                            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                            'format' => '?paged=%#%',
                            'current' => $paged,
                            'total' => $max_pages,
                            'end_size' => 1,
                            'mid_size' => 1,
                            'add_args' => array(
                                's' => $search_query,
                            ),
                            'prev_text' => '<i class="ri-arrow-left-s-line"></i>',
                            'next_text' => '<i class="ri-arrow-right-s-line"></i>',
                            'type' => 'list',
                        ));

                        if ($pagination) :
                        ?>
                            <div class="pix-search-pagination pix-home-pagination-box" data-pix-search-pagination>
                                <?php echo wp_kses_post($pagination); ?>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="pix-search-empty">
                            <div class="pix-search-empty-icon"><i class="ri-search-eye-line"></i></div>
                            <h2>没有找到相关内容</h2>
                            <p>换一个更短的关键词试试，或者从热门分类里继续探索。</p>
                            <?php if (!is_wp_error($hot_terms) && !empty($hot_terms)) : ?>
                                <div class="pix-search-chip-group">
                                    <?php foreach ($hot_terms as $term) : ?>
                                        <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>

        <?php if ($sidebar['right']) : ?>
            <aside class="right right-widget pix-home-sidebar pix-home-sidebar-right" aria-label="博客右侧栏">
                <div class="widget_inner blog_right_inner pix-home-widget-stack">
                    <?php dynamic_sidebar('blog-right'); ?>
                </div>
            </aside>
        <?php endif; ?>

    </div>
</div>

<?php
get_footer();
