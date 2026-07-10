<?php
/**
 * Blog home template (when a static front page is set)
 *
 * @package Ayush_Integration_Lab
 */

get_header();
?>

<section class="hero-section">
	<div class="hero-section__inner">
		<h1><?php bloginfo( 'name' ); ?></h1>
		<p><?php bloginfo( 'description' ); ?></p>
		<a class="btn" href="#latest-posts"><?php esc_html_e( 'Browse Latest Posts', 'ayush-integration-lab' ); ?></a>
	</div>
</section>

<main class="site-main" id="latest-posts" role="main">
	<div class="content-wrapper">

		<div class="content-area">

			<?php if ( have_posts() ) : ?>

				<div class="posts-grid">

					<?php while ( have_posts() ) : the_post(); ?>

						<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>

							<div class="post-card__meta">
								<?php
								$categories = get_the_category();
								if ( ! empty( $categories ) ) :
									?>
									<a class="post-card__category" href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
										<?php echo esc_html( $categories[0]->name ); ?>
									</a>
								<?php endif; ?>

								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
									<?php echo esc_html( get_the_date() ); ?>
								</time>
							</div>

							<h2 class="post-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<div class="post-card__excerpt">
								<?php the_excerpt(); ?>
							</div>

							<a class="read-more" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Read more', 'ayush-integration-lab' ); ?>
							</a>

						</article>

					<?php endwhile; ?>

				</div>

				<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts pagination', 'ayush-integration-lab' ); ?>">
					<?php
					the_posts_pagination( array(
						'mid_size'  => 2,
						'prev_text' => '&larr; ' . __( 'Previous', 'ayush-integration-lab' ),
						'next_text' => __( 'Next', 'ayush-integration-lab' ) . ' &rarr;',
					) );
					?>
				</nav>

			<?php else : ?>

				<div class="post-card">
					<h2><?php esc_html_e( 'No posts yet', 'ayush-integration-lab' ); ?></h2>
					<p><?php esc_html_e( 'Check back soon for new technical articles.', 'ayush-integration-lab' ); ?></p>
				</div>

			<?php endif; ?>

		</div>

		<?php get_sidebar(); ?>

	</div>
</main>

<?php
get_footer();
