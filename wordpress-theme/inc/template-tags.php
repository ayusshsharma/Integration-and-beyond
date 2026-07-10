<?php
/**
 * Custom template tags
 *
 * @package Ayush_Integration_Lab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fallback menu when no menu is assigned.
 */
function ail_fallback_menu() {
	echo '<ul>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'ayush-integration-lab' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About', 'ayush-integration-lab' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html__( 'Blog', 'ayush-integration-lab' ) . '</a></li>';
	echo '</ul>';
}
