<?php
/**
 * Sidebar template
 *
 * @package Ayush_Integration_Lab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<aside class="sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'ayush-integration-lab' ); ?>">

	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php else : ?>

		<section class="widget widget_categories">
			<h2 class="widget-title"><?php esc_html_e( 'Categories', 'ayush-integration-lab' ); ?></h2>
			<ul>
				<?php
				$categories = array(
					'IBM API Connect',
					'Kong Gateway',
					'Middleware & Integration',
					'POS Modernization',
					'n8n Automation',
					'Tech Experiments / POCs',
				);

				foreach ( $categories as $cat_name ) {
					$term = get_term_by( 'name', $cat_name, 'category' );
					if ( $term ) {
						printf(
							'<li class="cat-item"><a href="%s">%s</a> <span class="category-count">(%d)</span></li>',
							esc_url( get_category_link( $term->term_id ) ),
							esc_html( $cat_name ),
							(int) $term->count
						);
					} else {
						printf(
							'<li class="cat-item"><a href="#">%s</a></li>',
							esc_html( $cat_name )
						);
					}
				}
				?>
			</ul>
		</section>

		<section class="widget widget_recent_entries">
			<h2 class="widget-title"><?php esc_html_e( 'Recent Posts', 'ayush-integration-lab' ); ?></h2>
			<ul>
				<?php
				$recent = new WP_Query( array(
					'posts_per_page' => 5,
					'post_status'    => 'publish',
				) );

				if ( $recent->have_posts() ) :
					while ( $recent->have_posts() ) :
						$recent->the_post();
						?>
						<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
						<?php
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</ul>
		</section>

	<?php endif; ?>

</aside>
