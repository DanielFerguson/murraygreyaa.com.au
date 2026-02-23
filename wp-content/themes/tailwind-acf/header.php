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
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preconnect" href="https://www.googletagmanager.com">
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

	// Mobile menu
	var mobileOverlay = document.getElementById('mobile-menu-overlay');
	var mobileButton = document.getElementById('mobile-menu-button');
	var mobileClose = document.getElementById('mobile-menu-close');
	var mobileBackdrop = document.getElementById('mobile-menu-backdrop');

	function openMobileMenu() {
		mobileOverlay.classList.remove('hidden');
		// Force reflow for transition
		mobileOverlay.offsetHeight;
		mobileOverlay.classList.add('is-open');
		mobileButton.setAttribute('aria-expanded', 'true');
		document.body.style.overflow = 'hidden';
	}

	function closeMobileMenu() {
		mobileOverlay.classList.remove('is-open');
		mobileButton.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
		mobileButton.focus();
		setTimeout(function() {
			mobileOverlay.classList.add('hidden');
		}, 300);
	}

	if (mobileButton) {
		mobileButton.addEventListener('click', openMobileMenu);
	}
	if (mobileClose) {
		mobileClose.addEventListener('click', closeMobileMenu);
	}
	if (mobileBackdrop) {
		mobileBackdrop.addEventListener('click', closeMobileMenu);
	}

	// Escape key closes mobile menu too
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && mobileOverlay && !mobileOverlay.classList.contains('hidden')) {
			closeMobileMenu();
		}
	});

	// Close mobile menu on resize to desktop
	window.addEventListener('resize', function() {
		if (window.innerWidth >= 640 && mobileOverlay && !mobileOverlay.classList.contains('hidden')) {
			closeMobileMenu();
		}
	});

	// Mobile accordion submenus
	var mobileDropdowns = document.querySelectorAll('.mobile-nav-menu .has-dropdown');
	mobileDropdowns.forEach(function(dropdown) {
		var link = dropdown.querySelector(':scope > a');
		link.addEventListener('click', function(e) {
			e.preventDefault();
			var expanded = dropdown.classList.toggle('is-open');
			link.setAttribute('aria-expanded', expanded ? 'true' : 'false');
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
			<!-- Mobile menu button -->
			<button
				type="button"
				class="sm:hidden inline-flex items-center justify-center rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/20"
				aria-label="<?php esc_attr_e( 'Open menu', 'tailwind-acf' ); ?>"
				aria-expanded="false"
				id="mobile-menu-button"
			>
				<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
				</svg>
			</button>
		</div>
	</header>
	<!-- Mobile navigation drawer -->
	<div id="mobile-menu-overlay" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-label="<?php esc_attr_e( 'Site navigation', 'tailwind-acf' ); ?>">
		<div class="mobile-menu-backdrop fixed inset-0 bg-black/50" id="mobile-menu-backdrop"></div>
		<nav class="mobile-menu-panel fixed inset-y-0 right-0 w-full max-w-xs bg-green-950 px-6 py-6 shadow-xl overflow-y-auto">
			<div class="flex items-center justify-between mb-8">
				<?php if ( has_site_icon() ) : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img class="h-8 w-auto" src="<?php echo esc_url( get_site_icon_url( 192 ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					</a>
				<?php else : ?>
					<a class="text-lg font-semibold text-white" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>
				<button
					type="button"
					class="inline-flex items-center justify-center rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white"
					aria-label="<?php esc_attr_e( 'Close menu', 'tailwind-acf' ); ?>"
					id="mobile-menu-close"
				>
					<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_class'     => 'mobile-nav-menu space-y-1',
					'fallback_cb'    => false,
					'container'      => false,
				)
			);
			?>
		</nav>
	</div>
	<main id="site-content" class="<?php echo esc_attr($main_classes); ?>">