<?php

/**
 * Tailwind CTA Banner block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_field')) {
	return;
}

$heading         = get_field('heading');
$body_text       = get_field('body_text');
$primary_label   = get_field('primary_label');
$primary_url     = get_field('primary_url');
$secondary_label = get_field('secondary_label');
$secondary_url   = get_field('secondary_url');
$background_image = get_field('background_image');

$block_id = 'tailwind-cta-banner-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-cta-banner relative overflow-hidden bg-green-950 text-white mt-16';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

$has_primary_cta = $primary_label && $primary_url;
$has_secondary_cta = $secondary_label && $secondary_url;

?>
<section id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
	<?php if ($background_image && ! empty($background_image['ID'])) : ?>
		<div class="pointer-events-none absolute inset-0">
			<?php
			echo wp_get_attachment_image(
				$background_image['ID'],
				'full',
				false,
				array(
					'class' => 'h-full w-full object-cover opacity-80',
					'alt'   => esc_attr($background_image['alt'] ?? ''),
				)
			);
			?>
			<span class="absolute inset-0 bg-green-950/85 mix-blend-multiply"></span>
		</div>
	<?php endif; ?>

	<div class="relative mx-auto flex max-w-5xl flex-col items-center gap-6 px-6 py-24 text-center sm:px-8 lg:px-10">
		<?php if ($heading) : ?>
			<h2 class="text-2xl font-semibold tracking-tight sm:text-3xl lg:text-4xl">
				<?php echo esc_html($heading); ?>
			</h2>
		<?php endif; ?>

		<?php if ($body_text) : ?>
			<p class="max-w-xl text-base leading-7 text-white/90 sm:text-lg">
				<?php echo wp_kses_post(nl2br($body_text)); ?>
			</p>
		<?php endif; ?>

		<?php if ($has_primary_cta || $has_secondary_cta) : ?>
			<div class="flex flex-wrap items-center justify-center gap-3">
				<?php if ($has_primary_cta) : ?>
					<a
						class="inline-flex items-center gap-3 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-green-950 shadow-sm transition hover:bg-slate-100 focus:outline-none focus-visible:ring focus-visible:ring-white/70"
						href="<?php echo esc_url($primary_url); ?>">
						<span><?php echo esc_html($primary_label); ?></span>
					</a>
				<?php endif; ?>

				<?php if ($has_secondary_cta) : ?>
					<a
						class="inline-flex items-center gap-3 rounded-lg px-6 py-3 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10 focus:outline-none focus-visible:ring focus-visible:ring-white/60"
						href="<?php echo esc_url($secondary_url); ?>">
						<span><?php echo esc_html($secondary_label); ?></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>