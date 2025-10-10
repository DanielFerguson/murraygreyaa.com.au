<?php

/**
 * Theme footer template.
 *
 * @package Tailwind_ACF
 */
?>
</main>

<?php
$tagline           = get_bloginfo('description', 'display');
$menu_locations    = get_nav_menu_locations();
$footer_columns    = array();
$social_links      = array();
$footer_headline   = trim(get_theme_mod('tailwind_acf_footer_headline'));
$footer_description = trim(wp_kses_post(get_theme_mod('tailwind_acf_footer_description')));
$footer_link_text  = trim(get_theme_mod('tailwind_acf_footer_link_text'));
$footer_link_url   = esc_url(get_theme_mod('tailwind_acf_footer_link_url'));

if (has_nav_menu('footer') && ! empty($menu_locations['footer'])) {
	$footer_menu_items = wp_get_nav_menu_items($menu_locations['footer']);
	if ($footer_menu_items) {
		foreach ($footer_menu_items as $item) {
			if ((int) $item->menu_item_parent === 0) {
				$footer_columns[$item->ID] = array(
					'title'    => $item->title,
					'url'      => $item->url,
					'children' => array(),
				);
			}
		}

		foreach ($footer_menu_items as $item) {
			$parent_id = (int) $item->menu_item_parent;
			if ($parent_id && isset($footer_columns[$parent_id])) {
				$footer_columns[$parent_id]['children'][] = $item;
			}
		}

		foreach ($footer_columns as $column_id => $column) {
			if (empty($column['title'])) {
				unset($footer_columns[$column_id]);
			}
		}

		$footer_columns = array_values($footer_columns);
	}
}

if (! function_exists('tailwind_acf_get_social_network')) {
	/**
	 * Infer social network key for a menu item.
	 *
	 * @param WP_Post $item Menu item.
	 * @return string
	 */
	function tailwind_acf_get_social_network($item)
	{
		$candidates = array();

		if (! empty($item->attr_title)) {
			$candidates[] = $item->attr_title;
		}

		if (! empty($item->title)) {
			$candidates[] = $item->title;
		}

		if (! empty($item->url)) {
			$host = wp_parse_url($item->url, PHP_URL_HOST);
			if ($host) {
				$candidates[] = $host;
			}
		}

		$candidates = array_filter($candidates);

		$known = array('facebook', 'instagram', 'twitter', 'x', 'github', 'youtube', 'linkedin');
		foreach ($candidates as $candidate) {
			$candidate = strtolower($candidate);
			foreach ($known as $network) {
				if (false !== strpos($candidate, $network)) {
					return ('twitter' === $network) ? 'x' : $network;
				}
			}
		}

		return 'default';
	}
}

if (! function_exists('tailwind_acf_get_social_icon_svg')) {
	/**
	 * Return SVG markup for a social network.
	 *
	 * @param string $network Social network key.
	 * @return string
	 */
	function tailwind_acf_get_social_icon_svg($network)
	{
		switch ($network) {
			case 'facebook':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" fill-rule="evenodd"/></svg>';
			case 'instagram':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" fill-rule="evenodd"/></svg>';
			case 'x':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z"/></svg>';
			case 'github':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" fill-rule="evenodd"/></svg>';
			case 'youtube':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" fill-rule="evenodd"/></svg>';
			case 'linkedin':
				return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-6 w-6"><path d="M4.5 3C3.12 3 2 4.12 2 5.5S3.12 8 4.5 8 7 6.88 7 5.5 5.88 3 4.5 3zM3 9h3v12H3V9zm6 0h2.882v1.65h.04c.402-.76 1.384-1.562 2.852-1.562 3.05 0 3.612 2.008 3.612 4.62V21h-3v-6.422c0-1.532-.028-3.5-2.134-3.5-2.138 0-2.465 1.67-2.465 3.385V21H9V9z"/></svg>';
			default:
				return '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-6 w-6"><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		}
	}
}

if (has_nav_menu('social') && ! empty($menu_locations['social'])) {
	$social_menu_items = wp_get_nav_menu_items($menu_locations['social']);
	if ($social_menu_items) {
		foreach ($social_menu_items as $item) {
			$social_links[] = array(
				'label'   => $item->title,
				'url'     => $item->url,
				'network' => tailwind_acf_get_social_network($item),
			);
		}
	}
}
?>

<footer class="bg-white">
	<div class="mx-auto max-w-7xl px-6 pb-8 pt-16 sm:pt-24 lg:px-8 lg:pt-32">
		<div class="xl:grid xl:grid-cols-3 xl:gap-8">
			<div class="space-y-8">
				<?php if ($footer_headline || $footer_description || ($footer_link_text && $footer_link_url)) : ?>
					<?php if ($footer_headline) : ?>
						<h2 class="text-lg font-semibold text-slate-900">
							<?php echo esc_html($footer_headline); ?>
						</h2>
					<?php endif; ?>

					<?php if ($footer_description) : ?>
						<p class="text-balance text-sm leading-6 text-slate-600">
							<?php echo wp_kses_post(nl2br($footer_description)); ?>
						</p>
					<?php endif; ?>

					<?php if ($footer_link_text && $footer_link_url) : ?>
						<a class="inline-flex items-center gap-2 text-sm font-semibold text-brand transition hover:text-brand-dark" href="<?php echo esc_url($footer_link_url); ?>">
							<span><?php echo esc_html($footer_link_text); ?></span>
							<svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3.5 8h9m0 0L8.75 4.25M12.5 8l-3.75 3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<?php if (has_custom_logo()) : ?>
						<div class="flex items-center">
							<?php the_custom_logo(); ?>
						</div>
					<?php else : ?>
						<span class="text-2xl font-semibold text-slate-900">About MGAA</span>
					<?php endif; ?>

					<p class="text-balance text-sm leading-6 text-slate-600">
						<?php echo esc_html($tagline); ?>
						The Murray Grey Association Australia provides affordable, no-frills registration services for Murray Grey cattle breeders across Australia.
					</p>
				<?php endif; ?>

				<?php if (! empty($social_links)) : ?>
					<div class="flex gap-x-6">
						<?php foreach ($social_links as $link) : ?>
							<a href="<?php echo esc_url($link['url']); ?>" class="text-slate-500 transition hover:text-slate-800">
								<span class="sr-only"><?php echo esc_html($link['label']); ?></span>
								<?php echo tailwind_acf_get_social_icon_svg($link['network']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
								?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if (! empty($footer_columns)) : ?>
				<div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
					<?php
					$column_groups = array_chunk($footer_columns, 2);
					foreach ($column_groups as $group_index => $group) :
					?>
						<div class="md:grid md:grid-cols-2 md:gap-8">
							<?php foreach ($group as $index => $column) : ?>
								<div class="<?php echo (0 !== $index) ? 'mt-10 md:mt-0' : ''; ?>">
									<h3 class="text-sm font-semibold leading-6 text-slate-900">
										<?php if (! empty($column['url'])) : ?>
											<a class="hover:text-brand" href="<?php echo esc_url($column['url']); ?>">
												<?php echo esc_html($column['title']); ?>
											</a>
										<?php else : ?>
											<?php echo esc_html($column['title']); ?>
										<?php endif; ?>
									</h3>
									<?php if (! empty($column['children'])) : ?>
										<ul role="list" class="mt-6 space-y-4">
											<?php foreach ($column['children'] as $child) : ?>
												<li>
													<a class="text-sm leading-6 text-slate-600 transition hover:text-slate-900" href="<?php echo esc_url($child->url); ?>">
														<?php echo esc_html($child->title); ?>
													</a>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php elseif (has_nav_menu('footer')) : ?>
				<nav class="mt-16 xl:col-span-2 xl:mt-0">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'menu_class'     => 'grid grid-cols-2 gap-4 text-sm text-slate-600 sm:grid-cols-3',
							'fallback_cb'    => false,
							'container'      => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>

		<div class="mt-16 border-t border-slate-200 pt-8 sm:mt-20 lg:mt-24 flex flex-row justify-between">
			<p class="text-sm leading-6 text-slate-600">
				&copy; <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'tailwind-acf'); ?>
			</p>
			<div class="flex flex-row gap-6">
				<a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" class="text-sm leading-6 text-slate-600">
					Privacy Policy
				</a>
				<a href="<?php echo esc_url(home_url('/contact-us')); ?>" class="text-sm leading-6 text-slate-600">
					Contact Us
				</a>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>

</html>