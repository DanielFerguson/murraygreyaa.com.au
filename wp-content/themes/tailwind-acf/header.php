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
$body_classes   = get_body_class(
	array(
		'bg-slate-50',
		'text-slate-900',
		'antialiased',
	)
);
$has_hero_first = in_array('has-hero-first', $body_classes, true);

$header_classes = $has_hero_first
	? 'site-header absolute inset-x-0 top-0 z-50 bg-transparent pt-10'
	: 'site-header border-b border-slate-200/60 bg-white/80 backdrop-blur';

$inner_classes  = 'mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-6 sm:px-8 lg:px-12';
$inner_classes .= $has_hero_first ? ' text-white' : ' text-slate-600';

$brand_classes = $has_hero_first
	? 'text-xl font-semibold text-white transition hover:text-white/80'
	: 'text-xl font-semibold text-slate-900 transition hover:text-brand';

$nav_classes = $has_hero_first
	? 'hidden sm:block font-medium text-white'
	: 'hidden sm:block font-medium text-slate-600';

$main_classes = $has_hero_first ? 'pt-0' : 'pt-24';
?>

<body class="<?php echo esc_attr(implode(' ', $body_classes)); ?>">
	<?php
	wp_body_open();
	?>
	<header class="<?php echo esc_attr($header_classes); ?>">
		<div class="<?php echo esc_attr($inner_classes); ?>">
			<div class="flex items-center gap-3">
				<?php if (has_custom_logo()) : ?>
					<div class="inline-flex items-center">
						<?php the_custom_logo(); ?>
					</div>
				<?php elseif (has_site_icon()) : ?>
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