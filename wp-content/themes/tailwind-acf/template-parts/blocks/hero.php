<?php

/**
 * Tailwind Hero block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_field')) {
	return;
}

$eyebrow           = get_field('eyebrow');
$headline          = get_field('headline');
$content           = get_field('content');
$background_image  = get_field('background_image');
$cta_label         = get_field('cta_label');
$cta_url           = get_field('cta_url');
$secondary_cta_label = get_field('secondary_cta_label');
$secondary_cta_url   = get_field('secondary_cta_url');

$has_primary   = $cta_label && $cta_url;
$has_secondary = $secondary_cta_label && $secondary_cta_url;

$block_id = 'tailwind-hero-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-hero relative isolate overflow-hidden';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

$bg_url = '';
if (is_array($background_image) && ! empty($background_image['url'])) {
	$bg_url = esc_url($background_image['url']);
}
?>
<style>
/* Hero entrance animations */
@keyframes hero-fade-in-up {
	from {
		opacity: 0;
		transform: translateY(30px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}
.hero-animate {
	animation: hero-fade-in-up 0.8s ease-out forwards;
	opacity: 0;
}
.hero-delay-1 { animation-delay: 0.1s; }
.hero-delay-2 { animation-delay: 0.25s; }
.hero-delay-3 { animation-delay: 0.4s; }
.hero-delay-4 { animation-delay: 0.55s; }

/* Primary CTA hover effect */
.hero-cta-primary {
	position: relative;
	overflow: hidden;
	transition: all 0.3s ease;
}
.hero-cta-primary::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
	opacity: 0;
	transition: opacity 0.3s ease;
}
.hero-cta-primary:hover::before {
	opacity: 1;
}
.hero-cta-primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 40px -10px rgba(251, 191, 36, 0.5);
}
.hero-cta-primary span {
	position: relative;
	z-index: 1;
}

/* Secondary CTA */
.hero-cta-secondary {
	position: relative;
	border: 2px solid rgba(255,255,255,0.3);
	transition: all 0.3s ease;
}
.hero-cta-secondary:hover {
	border-color: rgba(255,255,255,0.6);
	background: rgba(255,255,255,0.1);
}
.hero-cta-secondary svg {
	transition: transform 0.3s ease;
}
.hero-cta-secondary:hover svg {
	transform: translateX(4px);
}
</style>

<section id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?> bg-green-950">
	<!-- Background with enhanced gradient overlay -->
	<div class="absolute inset-0 overflow-hidden">
		<?php if ($bg_url) : ?>
			<img
				class="h-full w-full object-cover"
				style="transform: scale(1.05) translateY(var(--parallax-y, 0px))"
				data-parallax-speed="0.15"
				data-parallax-max="36"
				src="<?php echo $bg_url; ?>"
				alt=""
				role="presentation"
				decoding="async"
			>
			<!-- Multi-layer gradient overlay -->
			<div class="absolute inset-0 bg-gradient-to-r from-green-950/90 via-green-950/70 to-green-950/40" aria-hidden="true"></div>
			<div class="absolute inset-0 bg-gradient-to-t from-green-950/80 via-transparent to-green-950/30" aria-hidden="true"></div>
			<!-- Subtle vignette -->
			<div class="absolute inset-0" style="background: radial-gradient(ellipse at center, transparent 0%, rgba(0,0,0,0.3) 100%);" aria-hidden="true"></div>
		<?php else : ?>
			<div class="absolute inset-0 bg-gradient-to-br from-green-900 via-green-950 to-slate-950"></div>
		<?php endif; ?>
	</div>

	<div class="relative mx-auto flex max-w-7xl flex-col gap-6 px-6 py-28 text-white sm:px-8 lg:px-12 lg:py-52">
		<?php if ($eyebrow) : ?>
			<p class="hero-animate hero-delay-1 text-sm font-semibold uppercase tracking-[0.25em] text-amber-400">
				<?php echo esc_html($eyebrow); ?>
			</p>
		<?php endif; ?>

		<?php if ($headline) : ?>
			<h2 class="hero-animate hero-delay-2 max-w-3xl text-4xl font-bold text-white sm:text-5xl lg:text-6xl leading-[1.1]" style="text-shadow: 0 4px 30px rgba(0,0,0,0.3);">
				<?php echo wp_kses_post(nl2br($headline)); ?>
			</h2>
		<?php endif; ?>

		<?php if ($content) : ?>
			<p class="hero-animate hero-delay-3 max-w-2xl text-lg sm:text-xl leading-relaxed text-slate-200/90">
				<?php echo wp_kses_post(nl2br($content)); ?>
			</p>
		<?php endif; ?>

		<?php if ($has_primary || $has_secondary) : ?>
			<div class="hero-animate hero-delay-4 flex flex-wrap items-center gap-4 mt-4">
				<?php if ($has_primary) : ?>
					<a 
						class="hero-cta-primary inline-flex items-center justify-center rounded-xl bg-white px-7 py-3.5 text-base font-bold text-green-950 shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400" 
						href="<?php echo esc_url($cta_url); ?>"
					>
						<span><?php echo esc_html($cta_label); ?></span>
					</a>
				<?php endif; ?>
				<?php if ($has_secondary) : ?>
					<a 
						class="hero-cta-secondary inline-flex items-center justify-center gap-2 rounded-xl px-7 py-3.5 text-base font-semibold text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" 
						href="<?php echo esc_url($secondary_cta_url); ?>"
					>
						<?php echo esc_html($secondary_cta_label); ?>
						<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
						</svg>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
