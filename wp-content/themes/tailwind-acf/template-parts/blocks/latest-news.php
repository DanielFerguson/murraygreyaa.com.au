<?php

/**
 * Tailwind Latest News block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_posts')) {
	return;
}

$section_heading = get_field('section_heading');
$section_intro   = get_field('section_intro');
$show_date       = (bool) get_field('show_date');
$show_excerpt    = (bool) get_field('show_excerpt');

$block_id = 'tailwind-latest-news-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-latest-news';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

$posts = get_posts(array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
));

?>
<section id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?> bg-white">
	<div class="mx-auto max-w-7xl px-6 py-16 sm:px-8 lg:px-12">
		<div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
			<div class="max-w-2xl">
				<?php if ($section_heading) : ?>
					<h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-3xl"><?php echo esc_html($section_heading); ?></h2>
				<?php endif; ?>
				<?php if ($section_intro) : ?>
					<p class="mt-3 text-lg leading-8 text-slate-600"><?php echo wp_kses_post(nl2br($section_intro)); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if (! empty($posts)) : ?>
			<div class="mt-10 grid gap-6 md:grid-cols-3">
				<?php foreach ($posts as $post) : ?>
					<article class="group flex h-full flex-col rounded-xl border border-slate-200/70 p-6 shadow-sm shadow-slate-200/60 bg-slate-50 transition hover:-translate-y-1 hover:border-brand/40 hover:shadow-lg hover:shadow-brand/20">
						<?php if (has_post_thumbnail($post)) : ?>
							<a class="relative mb-5 block overflow-hidden rounded-2xl" href="<?php echo esc_url(get_permalink($post)); ?>">
								<?php echo get_the_post_thumbnail($post, 'large', array('class' => 'aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]')); ?>
								<span class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-black/5"></span>
							</a>
						<?php endif; ?>
						<div class="flex flex-1 flex-col">
							<?php if ($show_date) : ?>
								<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $post)); ?>" class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">
									<?php echo esc_html(get_the_date('j M Y', $post)); ?>
								</time>
							<?php endif; ?>
							<h3 class="mt-3 text-xl font-semibold leading-tight text-slate-900">
								<a class="transition hover:text-brand" href="<?php echo esc_url(get_permalink($post)); ?>">
									<?php echo esc_html(get_the_title($post)); ?>
								</a>
							</h3>
							<?php if ($show_excerpt) : ?>
								<p class="mt-3 text-sm leading-6 text-slate-600">
									<?php echo esc_html(wp_trim_words(get_the_excerpt($post), 26)); ?>
								</p>
							<?php endif; ?>
							<div class="mt-6 flex items-center justify-between text-sm font-semibold text-brand">
								<a class="inline-flex items-center gap-2" href="<?php echo esc_url(get_permalink($post)); ?>">
									<span><?php esc_html_e('Read more', 'tailwind-acf'); ?></span>
									<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M3.5 8h9m0 0L8.75 4.25M12.5 8l-3.75 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</a>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="mt-10 text-base text-slate-600"><?php esc_html_e('No news posts found.', 'tailwind-acf'); ?></p>
		<?php endif; ?>
	</div>
</section>