<?php

/**
 * Template for the posts page (Blog / News archive).
 *
 * Shows the latest post prominently, followed by a grid of recent posts.
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

$paged = max(1, get_query_var('paged'), get_query_var('page'));

$featured_query = null;
$featured_post  = null;

if (1 === $paged) {
	$featured_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);

	if ($featured_query->have_posts()) {
		$featured_query->the_post();
		$featured_post = get_post();
	}

	wp_reset_postdata();
}

$rest_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => get_option( 'posts_per_page' ),
	'paged'          => $paged,
);
if ( $featured_post ) {
	$rest_args['post__not_in'] = array( $featured_post->ID );
}
$rest_query = new WP_Query( $rest_args );

$page_title   = get_the_title(get_option('page_for_posts')) ?: __('Latest News', 'tailwind-acf');
$page_excerpt = '';
$page_id      = get_option('page_for_posts');
if ($page_id && has_excerpt($page_id)) {
	$page_excerpt = get_the_excerpt($page_id);
}
?>

<section class="relative isolate overflow-hidden bg-green-950 text-white">
	<div class="relative mx-auto max-w-5xl px-6 py-16 sm:px-10 sm:pt-8 sm:py-16 lg:px-12">
		<h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl lg:text-5xl"><?php echo esc_html($page_title); ?></h1>

		<?php if ($page_excerpt) : ?>
			<p class="mt-6 max-w-3xl text-lg leading-8 text-slate-200"><?php echo esc_html($page_excerpt); ?></p>
		<?php endif; ?>
	</div>
</section>

<div>
	<div class="mx-auto max-w-5xl px-6 py-16 sm:px-10 lg:px-12">
		<?php if ($featured_post) : ?>
			<section class="mb-16 rounded-3xl bg-white p-8 shadow-lg shadow-slate-200/60 sm:p-12">
				<?php
				$post_id   = $featured_post->ID;
				$permalink = get_permalink($post_id);
				$thumb_id  = get_post_thumbnail_id($post_id);
				$thumb_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
				?>

				<div class="grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-center">
					<div class="space-y-6">
						<div class="flex flex-wrap items-center gap-3 text-sm uppercase tracking-wide text-brand-light">
							<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post_id)); ?>" class="font-semibold text-brand-light">
								<?php echo esc_html(get_the_date('j F Y', $post_id)); ?>
							</time>
							<?php
							$categories = get_the_category($post_id);
							if ($categories) :
							?>
								<span class="inline-flex flex-wrap gap-2">
									<?php foreach ($categories as $category) : ?>
										<a class="inline-flex items-center gap-1 rounded-full bg-brand/10 px-3 py-1 text-xs font-semibold text-brand transition hover:bg-brand/20" href="<?php echo esc_url(get_category_link($category)); ?>">
											<?php echo esc_html($category->name); ?>
										</a>
									<?php endforeach; ?>
								</span>
							<?php endif; ?>
						</div>

						<h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
							<a class="transition hover:text-brand" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a>
						</h2>

						<p class="text-lg leading-8 text-slate-600"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 40)); ?></p>

						<div>
							<a class="inline-flex items-center gap-2 rounded-full bg-brand py-3 text-sm font-semibold transition hover:bg-brand-dark" href="<?php echo esc_url($permalink); ?>">
								<span><?php esc_html_e('Read the latest', 'tailwind-acf'); ?></span>
								<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3.5 8h9m0 0L8.75 4.25M12.5 8l-3.75 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</a>
						</div>
					</div>

					<?php if ($thumb_id) : ?>
						<a class="relative block overflow-hidden rounded-2xl" href="<?php echo esc_url($permalink); ?>">
							<?php echo wp_get_attachment_image($thumb_id, 'large', false, array('class' => 'aspect-[4/3] w-full object-cover transition duration-300 hover:scale-[1.02]')); ?>
							<span class="absolute inset-0 rounded-2xl ring-1 ring-black/5"></span>
						</a>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($rest_query->have_posts()) : ?>
			<section class="space-y-8">
				<header class="flex items-center justify-between">
					<h2 class="text-xl font-semibold text-slate-900"><?php esc_html_e('More stories', 'tailwind-acf'); ?></h2>
				</header>

				<div class="grid gap-6 lg:grid-cols-3">
					<?php
					while ($rest_query->have_posts()) :
						$rest_query->the_post();
					?>
						<article <?php post_class('group rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm shadow-slate-200/60 transition hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/20'); ?>>
							<?php if (has_post_thumbnail()) : ?>
								<a class="relative mb-5 block overflow-hidden rounded-xl" href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail('medium_large', array('class' => 'aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]')); ?>
									<span class="absolute inset-0 rounded-xl ring-1 ring-black/5"></span>
								</a>
							<?php endif; ?>

							<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>" class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">
								<?php echo esc_html(get_the_date('j M Y')); ?>
							</time>

							<h3 class="mt-3 text-lg font-semibold leading-tight text-slate-900">
								<a class="transition hover:text-brand" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>

							<p class="mt-3 text-sm leading-6 text-slate-600"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>

							<a class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-brand" href="<?php the_permalink(); ?>">
								<span><?php esc_html_e('Read more', 'tailwind-acf'); ?></span>
								<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M3.5 8h9m0 0L8.75 4.25M12.5 8l-3.75 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</a>
						</article>
					<?php endwhile; ?>
				</div>

				<?php
				$pagination_links = paginate_links(
					array(
						'total'   => $rest_query->max_num_pages,
						'current' => $paged,
						'type'    => 'array',
					)
				);
				if ($pagination_links) :
				?>
					<nav class="mt-12 flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-600">
						<?php foreach ($pagination_links as $link) : ?>
							<span class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1 transition hover:border-brand/60 hover:text-brand" aria-label="<?php echo esc_attr(wp_strip_all_tags($link)); ?>"><?php echo wp_kses_post($link); ?></span>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</section>
		<?php else : ?>
			<p class="text-base text-slate-600"><?php esc_html_e('No additional stories available at the moment.', 'tailwind-acf'); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php
wp_reset_postdata();
get_footer();
