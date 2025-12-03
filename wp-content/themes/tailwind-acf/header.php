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
<style>
	/* Navigation link animations */
	.nav-animated ul {
		display: flex;
		gap: 2rem;
	}

	.nav-animated a {
		position: relative;
		padding: 0.5rem 0;
		transition: color 0.2s ease;
	}

	.nav-animated a::after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 0;
		width: 0;
		height: 2px;
		background: linear-gradient(90deg, #fbbf24, #f59e0b);
		transition: width 0.3s ease;
		border-radius: 1px;
	}

	.nav-animated a:hover::after,
	.nav-animated .current-menu-item a::after {
		width: 100%;
	}

	.nav-animated a:hover {
		color: #fde68a;
	}

	.nav-animated .current-menu-item a {
		color: #fde68a;
	}
</style>

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
					)
				);
				?>
			</nav>
		</div>
	</header>
	<main id="site-content" class="<?php echo esc_attr($main_classes); ?>">