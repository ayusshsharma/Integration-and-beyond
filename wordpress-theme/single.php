<?php
/**
 * Single post template
 *
 * @package Ayush_Integration_Lab
 */

get_header();
?>

<main class="site-main" role="main">
	<div class="content-wrapper">

		<div class="content-area">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post' ); ?>>

					<header class="single-post__header">
						<h1 class="single-post__title"><?php the_title(); ?></h1>

						<div class="single-post__meta">
							<?php
							$categories = get_the_category();
							if ( ! empty( $categories ) ) :
								?>
								<span class="single-post__category">
									<a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
										<?php echo esc_html( $categories[0]->name ); ?>
									</a>
								</span>
							<?php endif; ?>

							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>

							<span class="single-post__author">
								<?php esc_html_e( 'By', 'ayush-integration-lab' ); ?>
								<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
									<?php the_author(); ?>
								</a>
							</span>
						</div>

						<?php
						$tags = get_the_tags();
						if ( $tags ) :
							?>
							<div class="single-post__tags">
								<?php foreach ( $tags as $tag ) : ?>
									<a class="tag-link" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
										#<?php echo esc_html( $tag->name ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</header>

					<div class="single-post__content">
						<?php
						$content = get_the_content();
						$content = apply_filters( 'the_content', $content );
						echo ail_generate_toc( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</div>

					<?php
					wp_link_pages( array(
						'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'ayush-integration-lab' ),
						'after'  => '</nav>',
					) );
					?>

				</article>

				<?php
				the_post_navigation( array(
					'prev_text' => '&larr; %title',
					'next_text' => '%title &rarr;',
				) );

				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>

			<?php endwhile; ?>

		</div>

		<?php get_sidebar(); ?>

	</div>
</main>

<?php
get_footer();
