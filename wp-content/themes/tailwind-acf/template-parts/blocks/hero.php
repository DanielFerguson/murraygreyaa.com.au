<?php
/**
 * Tailwind Hero block template.
 *
 * @package Tailwind_ACF
 */

if ( ! function_exists( 'get_field' ) ) {
	return;
}

$eyebrow           = get_field( 'eyebrow' );
$headline          = get_field( 'headline' );
$content           = get_field( 'content' );
$background_image  = get_field( 'background_image' );
$cta_label         = get_field( 'cta_label' );
$cta_url           = get_field( 'cta_url' );

$block_id = 'tailwind-hero-' . ( $block['id'] ?? uniqid() );
if ( ! empty( $block['anchor'] ) ) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-hero relative isolate overflow-hidden';
if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$class_name .= ' align' . $block['align'];
}

$bg_url = '';
if ( is_array( $background_image ) && ! empty( $background_image['url'] ) ) {
	$bg_url = esc_url( $background_image['url'] );
}
?>
<section id="<?php echo esc_attr( $block_id ); ?>" class="<?php echo esc_attr( $class_name ); ?> bg-green-950">
	<div class="absolute inset-0">
		<?php if ( $bg_url ) : ?>
			<img class="h-full w-full object-cover" src="<?php echo $bg_url; ?>" alt="">
			<div class="absolute inset-0 bg-slate-900/70 mix-blend-multiply"></div>
		<?php else : ?>
			<div class="absolute inset-0 bg-gradient-to-br from-brand/80 via-slate-900 to-slate-950"></div>
		<?php endif; ?>
	</div>

	<div class="relative mx-auto flex max-w-7xl flex-col gap-6 px-6 py-24 text-white sm:px-8 lg:px-12 lg:py-32">
		<?php if ( $eyebrow ) : ?>
			<p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-light"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $headline ) : ?>
			<h2 class="text-3xl font-bold tracking-tight text-white sm:text-5xl"><?php echo wp_kses_post( nl2br( $headline ) ); ?></h2>
		<?php endif; ?>

		<?php if ( $content ) : ?>
			<p class="max-w-2xl text-lg leading-8 text-slate-200"><?php echo wp_kses_post( nl2br( $content ) ); ?></p>
		<?php endif; ?>

		<?php if ( $cta_label && $cta_url ) : ?>
			<div>
				<a class="inline-flex items-center justify-center rounded-full bg-brand px-6 py-3 text-base font-semibold text-white shadow-lg shadow-brand/30 transition hover:bg-brand-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="<?php echo esc_url( $cta_url ); ?>">
					<?php echo esc_html( $cta_label ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
