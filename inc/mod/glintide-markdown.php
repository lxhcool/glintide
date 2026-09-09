<?php
/** Opt-in Markdown rendering for article bodies. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function glintide_markdown_html( $source ) {
	if ( ! class_exists( 'Parsedown' ) ) {
		require_once GLINTIDE_DIR . '/inc/vendor/parsedown/Parsedown.php';
	}
	$parser = new Parsedown();
	$parser->setSafeMode( true );
	return wp_kses_post( $parser->text( $source ) );
}

function glintide_filter_markdown_content( $content ) {
	if ( 'markdown' !== get_post_meta( get_the_ID(), '_glintide_card_body_format', true ) ) {
		return $content;
	}
	return glintide_markdown_html( $content );
}
add_filter( 'the_content', 'glintide_filter_markdown_content', 8 );

/** AJAX detail rendering needs the same post context as the normal loop. */
function glintide_render_post_body( $post_id ) {
	$previous = $GLOBALS['post'] ?? null;
	$GLOBALS['post'] = get_post( $post_id );
	try {
		return apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
	} finally {
		$GLOBALS['post'] = $previous;
	}
}

function glintide_markdown_excerpt( $excerpt, $post ) {
	if ( '' === $post->post_excerpt && 'markdown' === get_post_meta( $post->ID, '_glintide_card_body_format', true ) ) {
		return wp_trim_words( wp_strip_all_tags( glintide_markdown_html( $post->post_content ) ), 55 );
	}
	return $excerpt;
}
add_filter( 'get_the_excerpt', 'glintide_markdown_excerpt', 20, 2 );

function glintide_article_code_assets() {
	wp_enqueue_script( 'glintide-prism', GLINTIDE_URL . '/assets/vendor/prism/prism.js', array(), filemtime( GLINTIDE_DIR . '/assets/vendor/prism/prism.js' ), true );
	wp_add_inline_script( 'glintide-prism', 'window.Prism = window.Prism || {}; window.Prism.manual = true;', 'before' );
	wp_enqueue_script( 'glintide-article-code', GLINTIDE_URL . '/assets/js/glintide-article-code.js', array( 'glintide-prism' ), filemtime( GLINTIDE_DIR . '/assets/js/glintide-article-code.js' ), true );
	wp_enqueue_style( 'glintide-article-code', GLINTIDE_URL . '/assets/css/glintide-article-code.css', array( 'glintide-style' ), filemtime( GLINTIDE_DIR . '/assets/css/glintide-article-code.css' ) );
}
add_action( 'wp_enqueue_scripts', 'glintide_article_code_assets' );
