<?php

/**
 * Tailwind Photo Carousel block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_field')) {
	return;
}

$heading  = get_field('heading');
$autoplay = (bool) get_field('autoplay');
$images   = get_field('images');

if (empty($images) || ! is_array($images)) {
	return;
}

$slides = array();
foreach ($images as $row) {
	if (empty($row['image']) || empty($row['image']['ID'])) {
		continue;
	}

	$slides[] = array(
		'id'      => $row['image']['ID'],
		'alt'     => $row['image']['alt'] ?? '',
		'caption' => $row['caption'] ?? '',
	);
}

if (empty($slides)) {
	return;
}

$block_id = 'tailwind-photo-carousel-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-photo-carousel';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

?>
<section
	id="<?php echo esc_attr($block_id); ?>"
	class="<?php echo esc_attr($class_name); ?> bg-slate-50"
	data-carousel-root
	data-carousel-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
	data-carousel-interval="3000">
	<div class="mx-auto max-w-7xl px-6 py-16 sm:px-10 lg:px-12">
		<?php if ($heading) : ?>
			<div class="mb-10 text-center">
				<h2 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
					<?php echo esc_html($heading); ?>
				</h2>
			</div>
		<?php endif; ?>

		<div class="relative">
			<div class="overflow-hidden" data-carousel-viewport>
				<div class="flex gap-6 transition-transform duration-700 ease-in-out will-change-transform" data-carousel-track>
					<?php foreach ($slides as $slide) : ?>
						<figure class="relative flex h-72 flex-col overflow-hidden rounded-xl bg-slate-900/5" data-carousel-slide>
							<?php
							echo wp_get_attachment_image(
								$slide['id'],
								'large',
								false,
								array(
									'class' => 'h-full w-full object-cover',
									'alt'   => esc_attr($slide['alt']),
								)
							);
							?>
							<?php if (! empty($slide['caption'])) : ?>
								<figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-900/80 to-slate-900/0 px-5 pb-5 pt-16 text-sm font-semibold text-white">
									<?php echo esc_html($slide['caption']); ?>
								</figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="mt-8 flex flex-col items-center gap-6 md:flex-row md:justify-center md:gap-8" data-carousel-controls>
				<button
					type="button"
					class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm shadow-slate-200 transition hover:border-brand hover:text-brand focus:outline-none focus-visible:ring focus-visible:ring-brand/40"
					data-carousel-prev
					aria-label="<?php esc_attr_e('Previous photos', 'tailwind-acf'); ?>">
					<svg class="h-5 w-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 13 5 8l5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>

				<div class="flex items-center gap-3" data-carousel-dots-container>
					<?php foreach ($slides as $index => $slide) : ?>
						<button
							type="button"
							class="h-3 w-3 rounded-full border border-slate-300 transition focus:outline-none focus-visible:ring focus-visible:ring-brand/40"
							data-carousel-dot="<?php echo esc_attr($index); ?>"
							aria-label="<?php printf(esc_html__('Go to photo %d', 'tailwind-acf'), $index + 1); ?>"></button>
					<?php endforeach; ?>
				</div>

				<button
					type="button"
					class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm shadow-slate-200 transition hover:border-brand hover:text-brand focus:outline-none focus-visible:ring focus-visible:ring-brand/40"
					data-carousel-next
					aria-label="<?php esc_attr_e('Next photos', 'tailwind-acf'); ?>">
					<svg class="h-5 w-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			</div>
		</div>
	</div>
</section>