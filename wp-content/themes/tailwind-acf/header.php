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
</head>

<body <?php body_class('bg-slate-50 text-slate-900 antialiased'); ?>>
	<?php
	wp_body_open();
	?>
	<header class="site-header border-b border-green-950 bg-green-950 backdrop-blur">
		<div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-6 sm:px-8 lg:px-12">
			<div class="flex items-center gap-3">
				<?php if (has_site_icon()) : ?>
					<a class="inline-flex items-center" href="<?php echo esc_url(home_url('/')); ?>">
						<img class="h-16 w-auto" src="<?php echo esc_url(get_site_icon_url(128)); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
					</a>
				<?php else : ?>
					<a class="text-xl font-semibold text-slate-900 hover:text-brand" href="<?php echo esc_url(home_url('/')); ?>">
						<?php bloginfo('name'); ?>
					</a>
				<?php endif; ?>
			</div>
			<nav class="text-white font-medium">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'menu_class'     => 'flex gap-8',
						'fallback_cb'    => false,
						'container'      => false,
					)
				);
				?>
			</nav>
		</div>
	</header>
	<main id="site-content">