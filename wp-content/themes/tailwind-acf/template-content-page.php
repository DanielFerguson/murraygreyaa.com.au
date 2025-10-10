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
			<header class="relative isolate overflow-hidden bg-slate-900 text-white">
				<?php if ($featured_image_id) : ?>
					<div class="absolute inset-0">
						<?php
						echo wp_get_attachment_image(
							$featured_image_id,
							'full',
							false,
							array(
								'class' => 'h-full w-full object-cover opacity-35',
								'alt'   => esc_attr($featured_image_alt ?: get_the_title()),
							)
						);
						?>
						<div class="absolute inset-0 bg-slate-900/80 mix-blend-multiply"></div>
					</div>
				<?php endif; ?>

				<div class="relative mx-auto max-w-5xl px-6 py-16 sm:px-10 sm:py-24 lg:px-12">
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

			<div class="bg-slate-50">
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
