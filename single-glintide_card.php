<?php
/**
 * 内容卡片单页。
 *
 * @package glintide
 */

get_header();
?>

<main id="primary" class="glintide-content">
	<div class="glintide-site-layout">
		<aside class="glintide-site-column glintide-site-column--left" aria-label="左侧栏">
			<?php echo glintide_render_left_navigation(); ?>
		</aside>

		<section class="glintide-site-column glintide-site-column--center" aria-label="内容卡片详情">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php
				$post_id   = get_the_ID();
				$type      = glintide_card_get_type( $post_id );
				$type_data = glintide_card_get_type_data( $post_id );
				?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( array( 'glintide-card-single', 'glintide-card-single--' . $type ) ); ?>>
					<?php if ( 'photo' !== $type ) : ?>
					<header class="glintide-card-single-header">
						<span class="glintide-card-type glintide-card-type--<?php echo esc_attr( $type ); ?>">
							<i class="<?php echo esc_attr( $type_data['icon'] ); ?>" aria-hidden="true"></i>
							<span><?php echo esc_html( $type_data['label'] ); ?></span>
						</span>
						<h1 class="glintide-card-single-title"><?php the_title(); ?></h1>
						<div class="glintide-card-single-meta">
							<span><i class="ri-calendar-line" aria-hidden="true"></i><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></span>
							<span><i class="ri-user-3-line" aria-hidden="true"></i><?php echo esc_html( get_the_author() ); ?></span>
						</div>
					</header>
					<?php endif; ?>

					<?php echo glintide_card_media_html( $post_id, 'single' ); ?>

						<?php if ( ! in_array( $type, array( 'quote', 'code', 'photo' ), true ) ) : ?>
							<div class="glintide-card-single-content">
								<?php
								the_content();
								wp_link_pages(
									array(
										'before' => '<div class="page-links">' . esc_html__( '分页:', 'glintide' ),
										'after'  => '</div>',
									)
								);
								?>
							</div>
						<?php endif; ?>
				</article>
			<?php endwhile; ?>
		</section>

		<aside class="glintide-site-column glintide-site-column--right" aria-label="右侧栏">
			<?php echo glintide_render_right_tools(); ?>
		</aside>
	</div>
</main>

<?php get_footer(); ?>
