<?php
/**
 * Static page template
 *
 * @package Ayush_Integration_Lab
 */

get_header();
?>

<main class="site-main" role="main">
	<div class="content-wrapper content-wrapper--full">

		<div class="content-area">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>

					<h1 class="page-content__title"><?php the_title(); ?></h1>

					<div class="page-content__body">
						<?php
						the_content();

						wp_link_pages( array(
							'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'ayush-integration-lab' ),
							'after'  => '</nav>',
						) );
						?>
					</div>

				</article>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>

			<?php endwhile; ?>

		</div>

	</div>
</main>

<?php
get_footer();
