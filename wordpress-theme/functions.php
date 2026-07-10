<?php
/**
 * Ayush Integration Lab Theme Functions
 *
 * @package Ayush_Integration_Lab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIL_THEME_VERSION', '1.0.0' );
define( 'AIL_THEME_DIR', get_template_directory() );
define( 'AIL_THEME_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function ail_theme_setup() {
	load_theme_textdomain( 'ayush-integration-lab', AIL_THEME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );

	set_post_thumbnail_size( 1200, 630, true );
	add_image_size( 'ail-card', 600, 340, true );

	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'ayush-integration-lab' ),
		'footer'  => esc_html__( 'Footer Menu', 'ayush-integration-lab' ),
	) );
}
add_action( 'after_setup_theme', 'ail_theme_setup' );

/**
 * Register widget areas.
 */
function ail_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'ayush-integration-lab' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Main sidebar with categories and widgets.', 'ayush-integration-lab' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer', 'ayush-integration-lab' ),
		'id'            => 'footer-1',
		'description'   => esc_html__( 'Footer widget area.', 'ayush-integration-lab' ),
		'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'ail_widgets_init' );

/**
 * Enqueue styles and scripts.
 */
function ail_enqueue_assets() {
	// Google Fonts
	wp_enqueue_style(
		'ail-google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap',
		array(),
		null
	);

	// Main stylesheet
	wp_enqueue_style(
		'ail-style',
		get_stylesheet_uri(),
		array( 'ail-google-fonts' ),
		AIL_THEME_VERSION
	);

	// Additional CSS
	wp_enqueue_style(
		'ail-main',
		AIL_THEME_URI . '/assets/css/main.css',
		array( 'ail-style' ),
		AIL_THEME_VERSION
	);

	// Prism.js for syntax highlighting
	wp_enqueue_style(
		'prism-theme',
		'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css',
		array(),
		'1.29.0'
	);

	wp_enqueue_script(
		'prism-core',
		'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js',
		array(),
		'1.29.0',
		true
	);

	$prism_languages = array(
		'prism-clike'        => 'components/prism-clike.min.js',
		'prism-markup'       => 'components/prism-markup.min.js',
		'prism-markup-templating' => 'components/prism-markup-templating.min.js',
		'prism-json'         => 'components/prism-json.min.js',
		'prism-yaml'         => 'components/prism-yaml.min.js',
		'prism-bash'         => 'components/prism-bash.min.js',
		'prism-javascript'   => 'components/prism-javascript.min.js',
		'prism-http'         => 'components/prism-http.min.js',
	);

	foreach ( $prism_languages as $handle => $path ) {
		wp_enqueue_script(
			$handle,
			'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/' . $path,
			array( 'prism-core' ),
			'1.29.0',
			true
		);
	}

	// Theme JavaScript
	wp_enqueue_script(
		'ail-main',
		AIL_THEME_URI . '/assets/js/main.js',
		array(),
		AIL_THEME_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ail_enqueue_assets' );

/**
 * Add language class to code blocks for Prism.js.
 */
function ail_code_block_language_class( $output, $attributes ) {
	if ( ! empty( $attributes['language'] ) ) {
		$lang = sanitize_html_class( $attributes['language'] );
		$output = str_replace( '<code>', '<code class="language-' . esc_attr( $lang ) . '">', $output );
	}
	return $output;
}
add_filter( 'render_block_core/code', 'ail_code_block_language_class', 10, 2 );

/**
 * Wrap pre/code blocks with language labels.
 */
function ail_enhance_code_blocks( $content ) {
	if ( ! is_singular() ) {
		return $content;
	}

	$content = preg_replace_callback(
		'/<pre([^>]*)><code([^>]*)class="([^"]*language-([a-zA-Z0-9_-]+)[^"]*)"([^>]*)>(.*?)<\/code><\/pre>/s',
		function ( $matches ) {
			$lang  = strtoupper( esc_html( $matches[4] ) );
			$label = '<span class="code-block-label">' . $lang . '</span>';
			return $label . '<pre' . $matches[1] . '><code' . $matches[2] . 'class="' . $matches[3] . '"' . $matches[5] . '>' . $matches[6] . '</code></pre>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'ail_enhance_code_blocks', 20 );

/**
 * Generate table of contents from post headings.
 */
function ail_generate_toc( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	preg_match_all( '/<h([2-3])[^>]*id="([^"]*)"[^>]*>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER );

	if ( empty( $matches ) ) {
		return '';
	}

	$toc  = '<nav class="table-of-contents" aria-label="' . esc_attr__( 'Table of Contents', 'ayush-integration-lab' ) . '">';
	$toc .= '<h2 class="table-of-contents__title">' . esc_html__( 'Table of Contents', 'ayush-integration-lab' ) . '</h2>';
	$toc .= '<ol>';

	foreach ( $matches as $match ) {
		$level = (int) $match[1];
		$id    = esc_attr( $match[2] );
		$title = wp_strip_all_tags( $match[3] );

		if ( 3 === $level ) {
			$toc .= '<ol><li><a href="#' . $id . '">' . esc_html( $title ) . '</a></li></ol>';
		} else {
			$toc .= '<li><a href="#' . $id . '">' . esc_html( $title ) . '</a></li>';
		}
	}

	$toc .= '</ol></nav>';

	return $toc;
}

/**
 * Add IDs to headings for TOC anchors.
 */
function ail_add_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}

	$content = preg_replace_callback(
		'/<h([2-3])([^>]*)>(.*?)<\/h\1>/i',
		function ( $matches ) {
			if ( preg_match( '/id="/', $matches[2] ) ) {
				return $matches[0];
			}
			$slug = sanitize_title( wp_strip_all_tags( $matches[3] ) );
			return '<h' . $matches[1] . ' id="' . esc_attr( $slug ) . '"' . $matches[2] . '>' . $matches[3] . '</h' . $matches[1] . '>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'ail_add_heading_ids', 10 );

/**
 * Excerpt length.
 */
function ail_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'ail_excerpt_length' );

/**
 * Excerpt more text.
 */
function ail_excerpt_more( $more ) {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'ail_excerpt_more' );

/**
 * Register default categories on theme activation.
 */
function ail_register_default_categories() {
	$categories = array(
		'IBM API Connect',
		'Kong Gateway',
		'Middleware & Integration',
		'POS Modernization',
		'n8n Automation',
		'Tech Experiments / POCs',
	);

	foreach ( $categories as $cat_name ) {
		if ( ! term_exists( $cat_name, 'category' ) ) {
			wp_insert_term( $cat_name, 'category' );
		}
	}
}

/**
 * Import demo content on theme activation.
 */
function ail_theme_activation() {
	ail_register_default_categories();

	if ( get_option( 'ail_demo_content_imported' ) ) {
		return;
	}

	require_once AIL_THEME_DIR . '/inc/demo-import.php';
	ail_import_demo_content();

	update_option( 'ail_demo_content_imported', true );
}
add_action( 'after_switch_theme', 'ail_theme_activation' );

/**
 * Include additional files.
 */
require_once AIL_THEME_DIR . '/inc/template-tags.php';
