<?php

/**
 * Template for single blog posts.
 *
 * Applies the content-page presentation with hero header and Tailwind typography.
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
		$back_link          = get_permalink(get_option('page_for_posts'));
		if (! $back_link) {
			$back_link = get_post_type_archive_link('post');
		}
		if (! $back_link) {
			$back_link = home_url('/');
		}
?>

		<article <?php post_class('post-entry'); ?>>
			<header class="relative isolate overflow-hidden bg-green-950 text-white">
				<div class="relative mx-auto max-w-5xl px-6 py-16 sm:px-10 sm:pt-8 sm:py-16 lg:px-12">
					<nav class="mb-6 text-sm text-slate-300">
						<a class="inline-flex items-center gap-2 transition hover:text-white" href="<?php echo esc_url($back_link); ?>">
							<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M6.25 3.5 2 7.75m0 0 4.25 4.25M2 7.75h11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<?php esc_html_e('Back to news', 'tailwind-acf'); ?>
						</a>
					</nav>

					<div class="flex flex-wrap items-center gap-3 text-sm uppercase tracking-wide text-slate-300">
						<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
							<?php echo esc_html(get_the_date('j F Y')); ?>
						</time>
						<?php
						$categories = get_the_category();
						if ($categories) :
						?>
							<span class="inline-flex flex-wrap gap-2">
								<?php foreach ($categories as $category) : ?>
									<a class="inline-flex items-center gap-1 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white transition hover:bg-white/20" href="<?php echo esc_url(get_category_link($category)); ?>">
										<?php echo esc_html($category->name); ?>
									</a>
								<?php endforeach; ?>
							</span>
						<?php endif; ?>
					</div>

					<h1 class="mt-6 text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl">
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

					<footer class="mt-16 border-t border-slate-200 pt-8 text-sm text-slate-600">
						<div class="flex flex-wrap items-center gap-3">
							<?php
							$author_id = get_the_author_meta('ID');
							if ($author_id) :
							?>
								<div class="flex items-center gap-3">
									<?php echo get_avatar($author_id, 48, '', '', array('class' => 'h-10 w-10 rounded-full')); ?>
									<div>
										<div class="font-semibold text-slate-900"><?php echo esc_html(get_the_author()); ?></div>
										<div><?php echo esc_html(get_the_author_meta('description', $author_id)); ?></div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</footer>
				</div>
			</div>
		</article>
<?php
	endwhile;
endif;

get_footer();
