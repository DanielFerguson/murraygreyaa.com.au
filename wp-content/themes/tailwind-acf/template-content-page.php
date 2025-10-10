<?php

/**
 * Template Name: Content Page
 * Description: Adds a hero header and Tailwind Typography styling for long-form policy or documentation pages.
 *
 * @package Tailwind_ACF
 */

add_filter(
	'body_class',
	function ($classes) {
		$classes[] = 'has-hero-first';
		return $classes;
	}
);

get_header();

if (have_posts()) :
	while (have_posts()) :
		the_post();

		$featured_image_id  = has_post_thumbnail() ? get_post_thumbnail_id() : 0;
		$featured_image_alt = $featured_image_id ? get_post_meta($featured_image_id, '_wp_attachment_image_alt', true) : '';
		$excerpt            = has_excerpt() ? get_the_excerpt() : '';
?>

		<article <?php post_class('page-entry'); ?>>
			<header class="relative isolate overflow-hidden bg-green-950 text-white">
				<div class="relative mx-auto max-w-5xl px-6 py-16 sm:px-10 sm:pt-8 sm:py-16 lg:px-12">
					<h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
						<?php the_title(); ?>
					</h1>

					<?php if ($excerpt) : ?>
						<p class="mt-6 max-w-3xl text-lg leading-8 text-slate-200">
							<?php echo esc_html($excerpt); ?>
						</p>
					<?php endif; ?>
				</div>
			</header>

			<div>
				<div class="mx-auto max-w-5xl px-6 py-16 sm:px-10 lg:px-12">
					<div class="prose prose-slate max-w-none">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="mt-12 flex gap-2 text-sm font-semibold text-slate-600">',
								'after'  => '</nav>',
							)
						);
						?>
					</div>
				</div>
			</div>
		</article>

	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
