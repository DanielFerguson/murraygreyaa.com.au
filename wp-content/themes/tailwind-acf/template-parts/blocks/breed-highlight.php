<?php

/**
 * Tailwind Breed Highlight block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_field')) {
	return;
}

$eyebrow   = get_field('eyebrow');
$headline  = get_field('headline');
$intro     = get_field('intro');
$features  = get_field('features');
$image     = get_field('image');
$caption   = get_field('image_caption');

if (! $headline) {
	return;
}

$block_id = 'tailwind-breed-highlight-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-breed-highlight bg-white';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

$image_id  = $image['ID'] ?? 0;
$image_alt = $image_id ? ($image['alt'] ?? get_the_title($image_id)) : '';

?>
<section id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
	<div class="overflow-hidden py-24 sm:py-32">
		<div class="mx-auto max-w-7xl md:px-6 lg:px-8">
			<div class="grid grid-cols-1 gap-x-8 gap-y-16 sm:gap-y-20 lg:grid-cols-2 lg:items-center">
				<div class="px-6 md:px-0 lg:pr-8">
					<div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-xl">
						<?php if ($eyebrow) : ?>
							<p class="text-xs font-semibold tracking-[0.3em] uppercase text-brand">
								<?php echo esc_html($eyebrow); ?>
							</p>
						<?php endif; ?>

						<h2 class="mt-3 text-pretty text-4xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
							<?php echo esc_html($headline); ?>
						</h2>

						<?php if ($intro) : ?>
							<p class="mt-6 text-slate-700">
								<?php echo wp_kses_post(nl2br($intro)); ?>
							</p>
						<?php endif; ?>

						<?php if (! empty($features) && is_array($features)) : ?>
							<dl class="mt-10 max-w-xl space-y-4 text-base leading-7 text-slate-600 lg:max-w-none">
								<?php foreach ($features as $feature) : ?>
									<?php
									$title       = $feature['title'] ?? '';
									$description = $feature['description'] ?? '';
									if (! $title && ! $description) {
										continue;
									}
									?>
									<div class="relative pl-10">
										<dt class="inline font-semibold text-slate-900">
											<svg class="absolute left-0 top-1 h-5 w-5 text-brand" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M10 2a8 8 0 1 0 .001 16.001A8 8 0 0 0 10 2Zm2.78 6.22a.75.75 0 0 0-1.06-1.06L9 9.88 8.28 9.17a.75.75 0 0 0-1.06 1.06l1.25 1.25c.293.293.767.293 1.06 0L12.78 8.22Z" />
											</svg>
											<?php echo esc_html($title); ?>
										</dt>
										<?php if ($description) : ?>
											<dd class="mt-1 inline text-slate-600">
												<?php echo wp_kses_post($description); ?>
											</dd>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>
					</div>
				</div>

				<div class="sm:px-6 lg:px-0">
					<div class="mx-auto max-w-2xl sm:mx-0 sm:max-w-none">
						<div class="overflow-hidden rounded bg-white/5 ring-1 ring-white/10">
							<?php if ($image_id) : ?>
								<?php
								echo wp_get_attachment_image(
									$image_id,
									'large',
									false,
									array(
										'class' => 'w-full object-cover sm:h-96 rounded-lg',
										'alt'   => esc_attr($image_alt),
									)
								);
								?>
							<?php else : ?>
								<div class="flex h-72 items-center justify-center bg-gradient-to-br from-white/10 to-white/5 text-white">
									<span class="text-sm font-semibold uppercase tracking-[0.3em] text-white/70">
										<?php esc_html_e('Upload breed photography', 'tailwind-acf'); ?>
									</span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>