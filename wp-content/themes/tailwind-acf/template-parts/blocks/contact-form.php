<?php

/**
 * Tailwind Contact Form block template.
 *
 * @package Tailwind_ACF
 */

if (! function_exists('get_field')) {
	return;
}

$heading          = get_field('heading');
$intro            = get_field('intro');
$submit_label     = get_field('submit_label') ?: __('Send message', 'tailwind-acf');
$success_message  = get_field('success_message') ?: __('Thanks for reaching out. We will respond shortly.', 'tailwind-acf');

$block_id = 'tailwind-contact-form-' . ($block['id'] ?? uniqid());
if (! empty($block['anchor'])) {
	$block_id = $block['anchor'];
}

$class_name = 'tailwind-contact-form bg-white';
if (! empty($block['className'])) {
	$class_name .= ' ' . $block['className'];
}
if (! empty($block['align'])) {
	$class_name .= ' align' . $block['align'];
}

$nonce_field = wp_create_nonce('tailwind_contact_form');

?>
<section id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($class_name); ?>">
	<div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-slate-900 text-white">
		<div class="grid gap-12 px-6 py-16 sm:px-10 lg:grid-cols-[1fr,1.3fr] lg:px-12">
			<div class="space-y-6">
				<?php if ($heading) : ?>
					<h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">
						<?php echo esc_html($heading); ?>
					</h2>
				<?php endif; ?>

				<?php if ($intro) : ?>
					<p class="text-base leading-7 text-slate-200">
						<?php echo wp_kses_post(nl2br($intro)); ?>
					</p>
				<?php endif; ?>

				<ul class="space-y-4 text-sm">
					<li class="flex items-start gap-3">
						<span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand/20 text-brand">
							<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.25 9.5 8 13l4.75-3.5M4.5 3.5h7M3.25 6.5H12.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</span>
						<div>
							<p class="font-semibold text-white"><?php esc_html_e('Office hours', 'tailwind-acf'); ?></p>
							<p class="text-slate-300"><?php esc_html_e('Monday to Friday, 9:00am – 5:00pm AEDT', 'tailwind-acf'); ?></p>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand/20 text-brand">
							<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.5 4h9M4 12h8.5M12.5 7.75l-4.5-3-4.5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</span>
						<div>
							<p class="font-semibold text-white"><?php esc_html_e('Email us', 'tailwind-acf'); ?></p>
							<p class="text-slate-300">
								<a class="text-slate-200 underline decoration-slate-400 underline-offset-2 hover:text-white" href="mailto:info@murraygreys.org.au">info@murraygreys.org.au</a>
							</p>
						</div>
					</li>
					<li class="flex items-start gap-3">
						<span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand/20 text-brand">
							<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M4.75 2.5h6.5c.69 0 1.25.56 1.25 1.25v8.5c0 .69-.56 1.25-1.25 1.25h-6.5c-.69 0-1.25-.56-1.25-1.25v-8.5c0-.69.56-1.25 1.25-1.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								<path d="M10.25 3.75H5.75m1.25 6.25h1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</span>
						<div>
							<p class="font-semibold text-white"><?php esc_html_e('Call us', 'tailwind-acf'); ?></p>
							<p class="text-slate-300">
								<a class="text-slate-200 hover:text-white" href="tel:+61267733420">02 6773 3420</a>
							</p>
						</div>
					</li>
				</ul>
			</div>

			<div class="rounded-3xl bg-white p-6 text-slate-900 shadow-md shadow-slate-900/10 sm:p-8">
				<form class="space-y-5" method="post" data-success-message="<?php echo esc_attr($success_message); ?>">
					<input type="hidden" name="action" value="tailwind_contact_submit">
					<input type="hidden" name="tailwind_contact_nonce" value="<?php echo esc_attr($nonce_field); ?>">

					<div>
						<label class="block text-sm font-semibold text-slate-700" for="<?php echo esc_attr($block_id); ?>-name">
							<?php esc_html_e('Full name', 'tailwind-acf'); ?>
						</label>
						<input class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/40" type="text" name="name" id="<?php echo esc_attr($block_id); ?>-name" required placeholder="<?php esc_attr_e('Jane Doe', 'tailwind-acf'); ?>">
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700" for="<?php echo esc_attr($block_id); ?>-email">
							<?php esc_html_e('Email address', 'tailwind-acf'); ?>
						</label>
						<input class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/40" type="email" name="email" id="<?php echo esc_attr($block_id); ?>-email" required placeholder="name@example.com">
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700" for="<?php echo esc_attr($block_id); ?>-phone">
							<?php esc_html_e('Phone number', 'tailwind-acf'); ?>
						</label>
						<input class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/40" type="tel" name="phone" id="<?php echo esc_attr($block_id); ?>-phone" placeholder="+61 2 1234 5678">
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700" for="<?php echo esc_attr($block_id); ?>-message">
							<?php esc_html_e('How can we help?', 'tailwind-acf'); ?>
						</label>
						<textarea class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/40" name="message" id="<?php echo esc_attr($block_id); ?>-message" rows="5" required placeholder="<?php esc_attr_e('Share a few details about your enquiry…', 'tailwind-acf'); ?>"></textarea>
					</div>

					<button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/50">
						<?php echo esc_html($submit_label); ?>
					</button>

					<p class="contact-form__notice hidden rounded-xl bg-brand/10 px-4 py-3 text-sm font-semibold text-brand" data-success>
						<?php echo esc_html($success_message); ?>
					</p>
					<p class="contact-form__notice hidden rounded-xl bg-rose-100 px-4 py-3 text-sm font-semibold text-rose-700" data-error>
						<?php esc_html_e('Something went wrong. Please try again or reach us directly.', 'tailwind-acf'); ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</section>