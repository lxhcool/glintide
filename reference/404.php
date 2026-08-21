<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package pix
 */

get_header();

$recent_posts = new WP_Query(array(
    'post_type' => 'post',
    'posts_per_page' => 3,
    'post_status' => 'publish',
    'ignore_sticky_posts' => true,
));

$hot_terms = get_terms(array(
    'taxonomy' => 'category',
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 8,
    'hide_empty' => true,
));
?>

<div class="pix-content home-box home-classic pix-404-wrap">
    <div class="pix-page-layout">
        <div class="center-content full-width pix-width-expand">
            <main id="primary" class="site-main pix-404-page cls-content">
                <section class="pix-404-hero">
                    <div class="pix-404-orbit"></div>
                    <div class="pix-404-card">
                        <div class="pix-404-code">404</div>
                        <div class="pix-search-kicker">Page Not Found</div>
                        <h1>这个页面走丢了</h1>
                        <p>链接可能已经失效，或者内容被移动。你可以搜索关键词，也可以回到首页继续浏览。</p>
                        <form class="pix-search-page-form pix-404-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <i class="ri-search-2-line"></i>
                            <input type="search" name="s" placeholder="搜索站内内容" autocomplete="off">
                            <button type="submit">搜索</button>
                        </form>
                        <div class="pix-404-actions">
                            <a class="pix-404-primary" href="<?php echo esc_url(home_url('/')); ?>"><i class="ri-home-4-line"></i> 回到首页</a>
                            <a class="pix-404-secondary" href="javascript:history.back();"><i class="ri-arrow-go-back-line"></i> 返回上一页</a>
                        </div>
                    </div>
                </section>

                <section class="pix-404-suggest">
                    <?php if ($recent_posts->have_posts()) : ?>
                        <div class="pix-search-section-head">
                            <div>
                                <span>Latest</span>
                                <h2>最近更新</h2>
                            </div>
                            <a href="<?php echo esc_url(home_url('/')); ?>">查看更多</a>
                        </div>
                        <div id="blog-item" class="pix-search-results-grid p-item box-p">
                            <?php
                            while ($recent_posts->have_posts()) :
                                $recent_posts->the_post();
                                get_template_part('tpl/content', 'grid');
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!is_wp_error($hot_terms) && !empty($hot_terms)) : ?>
                        <div class="pix-404-cats">
                            <span>热门分类</span>
                            <div class="pix-search-chip-group">
                                <?php foreach ($hot_terms as $term) : ?>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
        </div>

    </div>
</div>

<?php
get_footer();
