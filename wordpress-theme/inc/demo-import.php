<?php
/**
 * Demo content importer
 *
 * Creates sample posts and an About page on theme activation.
 *
 * @package Ayush_Integration_Lab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import all demo posts and pages.
 */
function ail_import_demo_content() {
	$post_files = array(
		'api-connect-lifecycle.php',
		'kong-gateway-admin.php',
		'middleware-debugging.php',
		'pos-modernization.php',
		'n8n-automation.php',
		'nodejs-api-proxy.php',
	);

	foreach ( $post_files as $file ) {
		$path = AIL_THEME_DIR . '/demo-content/posts/' . $file;

		if ( ! file_exists( $path ) ) {
			continue;
		}

		$post_data = include $path;

		if ( ! is_array( $post_data ) || empty( $post_data['title'] ) ) {
			continue;
		}

		// Skip if post already exists.
		$existing = get_page_by_title( $post_data['title'], OBJECT, 'post' );
		if ( $existing ) {
			continue;
		}

		$category_id = 0;
		if ( ! empty( $post_data['category'] ) ) {
			$term = get_term_by( 'name', $post_data['category'], 'category' );
			if ( ! $term ) {
				$result = wp_insert_term( $post_data['category'], 'category' );
				if ( ! is_wp_error( $result ) ) {
					$category_id = $result['term_id'];
				}
			} else {
				$category_id = $term->term_id;
			}
		}

		$post_id = wp_insert_post( array(
			'post_title'   => $post_data['title'],
			'post_content' => $post_data['content'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_date'    => ! empty( $post_data['date'] ) ? $post_data['date'] : current_time( 'mysql' ),
		) );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			if ( $category_id ) {
				wp_set_post_categories( $post_id, array( $category_id ) );
			}

			if ( ! empty( $post_data['tags'] ) && is_array( $post_data['tags'] ) ) {
				wp_set_post_tags( $post_id, $post_data['tags'] );
			}
		}
	}

	// Create About page if it doesn't exist.
	if ( ! get_page_by_path( 'about' ) ) {
		wp_insert_post( array(
			'post_title'   => 'About Me',
			'post_name'    => 'about',
			'post_content' => '<p>Welcome to Ayush Integration Lab. I write about IBM API Connect, Kong Gateway, enterprise middleware, POS modernization, and automation experiments from the field.</p><p>With over a decade of integration experience across retail, banking, and logistics, I share practical notes, POCs, and lessons learned — not marketing fluff.</p><h2>What You\'ll Find Here</h2><ul><li>Step-by-step API lifecycle guides</li><li>Gateway configuration and plugin deep-dives</li><li>Middleware debugging playbooks</li><li>Automation workflows with n8n</li><li>Hands-on tech experiments</li></ul>',
			'post_status'  => 'publish',
			'post_type'    => 'page',
		) );
	}

	// Set site title and tagline.
	update_option( 'blogname', 'Ayush Integration Lab' );
	update_option( 'blogdescription', 'Real-world notes on API Connect, Kong Gateway, middleware, and integration experiments.' );
}
