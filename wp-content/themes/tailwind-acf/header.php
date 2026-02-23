<?php

/**
 * Theme header template.
 *
 * @package Tailwind_ACF
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>

	<script async src="https://www.googletagmanager.com/gtag/js?id=G-GRE2XLYWYM"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'G-GRE2XLYWYM');
	</script>
</head>
<?php
$body_classes = get_body_class(
	array(
		'bg-white',
		'text-slate-900',
		'antialiased',
	)
);

$header_classes = 'site-header inset-x-0 top-0 z-50 bg-green-950 border-b border-green-900/50';

$inner_classes  = 'mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-3 sm:px-8 lg:px-12';
$inner_classes .= ' text-white';

$brand_classes = 'text-xl font-semibold text-white transition-all duration-300 hover:text-white/80 hover:scale-105';

$nav_classes = 'hidden sm:block font-medium text-white nav-animated';

$main_classes = 'pt-0';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var dropdowns = document.querySelectorAll('.nav-animated .has-dropdown');

	dropdowns.forEach(function(dropdown) {
		var link = dropdown.querySelector(':scope > a');

		link.addEventListener('click', function(e) {
			if (window.innerWidth < 1024) {
				e.preventDefault();
				dropdown.classList.toggle('is-open');

				dropdowns.forEach(function(other) {
					if (other !== dropdown) {
						other.classList.remove('is-open');
					}
				});
			}
		});
	});

	document.addEventListener('click', function(e) {
		if (!e.target.closest('.has-dropdown')) {
			dropdowns.forEach(function(dropdown) {
				dropdown.classList.remove('is-open');
			});
		}
	});

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			dropdowns.forEach(function(dropdown) {
				dropdown.classList.remove('is-open');
				var link = dropdown.querySelector(':scope > a');
				if (link) {
					link.setAttribute('aria-expanded', 'false');
				}
			});
		}
	});

	dropdowns.forEach(function(dropdown) {
		var link = dropdown.querySelector(':scope > a');
		dropdown.addEventListener('mouseenter', function() {
			link.setAttribute('aria-expanded', 'true');
		});
		dropdown.addEventListener('mouseleave', function() {
			if (!dropdown.classList.contains('is-open')) {
				link.setAttribute('aria-expanded', 'false');
			}
		});
	});
});
</script>

<body class="<?php echo esc_attr(implode(' ', $body_classes)); ?>">
	<?php
	wp_body_open();
	?>
	<header class="<?php echo esc_attr($header_classes); ?>">
		<div class="<?php echo esc_attr($inner_classes); ?>">
			<div class="flex items-center gap-3">
				<?php if (has_site_icon()) : ?>
					<a class="inline-flex items-center transition-transform duration-300 hover:scale-105" href="<?php echo esc_url(home_url('/')); ?>">
						<img class="h-12 w-auto" src="<?php echo esc_url(get_site_icon_url(192)); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
					</a>
				<?php else : ?>
					<a class="<?php echo esc_attr($brand_classes); ?>" href="<?php echo esc_url(home_url('/')); ?>">
						<?php bloginfo('name'); ?>
					</a>
				<?php endif; ?>
			</div>
			<nav class="<?php echo esc_attr($nav_classes); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_class'     => '',
						'fallback_cb'    => false,
						'container'      => false,
						'walker'         => class_exists( 'Dropdown_Nav_Walker' ) ? new Dropdown_Nav_Walker() : null,
					)
				);
				?>
			</nav>
		</div>
	</header>
	<main id="site-content" class="<?php echo esc_attr($main_classes); ?>">