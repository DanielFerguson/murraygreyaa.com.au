<?php
/**
 * Tailwind Feature Grid block template.
 *
 * @package Tailwind_ACF
 */

if ( ! function_exists( 'have_rows' ) ) {
	return;
}

$heading = get_field( 'heading' );
$intro   = get_field( 'intro' );

$block_id = 'tailwind-feature-grid-' . ( $block['id'] ?? uniqid() );
if ( ! empty( $block['anchor'] ) ) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-feature-grid';
if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
	$class_name .= ' align' . $block['align'];
}

$accent_classes = array(
	'brand'   => 'bg-brand/10 text-brand',
	'slate'   => 'bg-slate-100 text-slate-700',
	'emerald' => 'bg-emerald-100 text-emerald-700',
	'orange'  => 'bg-orange-100 text-orange-700',
);
?>
<section id="<?php echo esc_attr( $block_id ); ?>" class="<?php echo esc_attr( $class_name ); ?>">
	<div class="mx-auto max-w-7xl px-6 py-16 sm:px-8 lg:px-12">
		<div class="mx-auto max-w-3xl text-center">
			<?php if ( $heading ) : ?>
				<h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $intro ) : ?>
				<p class="mt-4 text-lg leading-8 text-slate-600"><?php echo wp_kses_post( nl2br( $intro ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( have_rows( 'features' ) ) : ?>
			<div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_rows( 'features' ) ) :
					the_row();
					$title       = get_sub_field( 'title' );
					$description = get_sub_field( 'description' );
					$accent      = get_sub_field( 'accent' );
					$badge_class = $accent_classes[ $accent ] ?? 'bg-slate-100 text-slate-700';
					?>
					<article class="flex flex-col rounded-3xl border border-slate-200/70 bg-white/80 p-8 shadow-sm shadow-slate-200/60 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-200/70">
						<?php if ( $accent ) : ?>
							<span class="mb-4 inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide <?php echo esc_attr( $badge_class ); ?>">
								<?php echo esc_html( ucfirst( $accent ) ); ?>
							</span>
						<?php endif; ?>
						<?php if ( $title ) : ?>
							<h3 class="text-xl font-semibold text-slate-900"><?php echo esc_html( $title ); ?></h3>
						<?php endif; ?>
						<?php if ( $description ) : ?>
							<p class="mt-3 text-base leading-7 text-slate-600"><?php echo wp_kses_post( nl2br( $description ) ); ?></p>
						<?php endif; ?>
					</article>
					<?php
				endwhile;
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
