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
<?php
$body_classes = get_body_class(
	array(
		'text-slate-900',
		'antialiased',
	)
);

$header_classes = 'site-header inset-x-0 top-0 z-50 bg-green-950';

$inner_classes  = 'mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-2 sm:px-8 lg:px-12';
$inner_classes .= ' text-white';

$brand_classes = 'text-xl font-semibold text-white transition hover:text-white/80';

$nav_classes = 'hidden sm:block font-medium text-white';

$main_classes = 'pt-0';
?>

<body class="<?php echo esc_attr(implode(' ', $body_classes)); ?>">
	<?php
	wp_body_open();
	?>
	<header class="<?php echo esc_attr($header_classes); ?>">
		<div class="<?php echo esc_attr($inner_classes); ?>">
			<div class="flex items-center gap-3">
				<?php if (has_site_icon()) : ?>
					<a class="inline-flex items-center" href="<?php echo esc_url(home_url('/')); ?>">
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
						'menu_class'     => 'flex gap-6',
						'fallback_cb'    => false,
						'container'      => false,
					)
				);
				?>
			</nav>
		</div>
	</header>
	<main id="site-content" class="<?php echo esc_attr($main_classes); ?>">