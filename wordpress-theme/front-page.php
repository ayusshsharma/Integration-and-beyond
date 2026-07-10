<?php
/**
 * Front page template (static homepage)
 *
 * @package Ayush_Integration_Lab
 */

get_header();
?>

<section class="hero-section">
	<div class="hero-section__inner">
		<h1><?php bloginfo( 'name' ); ?></h1>
		<p><?php bloginfo( 'description' ); ?></p>
		<a class="btn" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>">
			<?php esc_html_e( 'Browse Latest Posts', 'ayush-integration-lab' ); ?>
		</a>
	</div>
</section>

<main class="site-main" role="main">
	<div class="content-wrapper content-wrapper--full">
		<div class="content-area">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
					<div class="page-content__body">
						<?php the_content(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
</main>

<?php
get_footer();
