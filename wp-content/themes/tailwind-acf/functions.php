<?php
/**
 * Theme bootstrap.
 *
 * @package Tailwind_ACF
 */

define( 'TAILWIND_ACF_THEME_VERSION', '0.1.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'menus' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'tailwind-acf' ),
			'footer'  => __( 'Footer Menu', 'tailwind-acf' ),
		)
	);
} );

/**
 * Enqueue Tailwind CDN for the front end.
 */
function tailwind_acf_enqueue_frontend_assets() {
	wp_enqueue_style(
		'tailwind-acf-style',
		get_stylesheet_uri(),
		array(),
		TAILWIND_ACF_THEME_VERSION
	);

	wp_enqueue_script(
		'tailwind-acf-cdn',
		'https://cdn.tailwindcss.com?plugins=forms,typography',
		array(),
		null,
		false
	);

	$config = <<<'JS'
tailwind.config = {
	theme: {
		extend: {
			fontFamily: {
				sans: ["Inter", "ui-sans-serif", "system-ui"],
			},
			colors: {
				brand: {
					DEFAULT: "#2563eb",
					light: "#60a5fa",
					dark: "#1d4ed8"
				}
			}
		}
	}
};
JS;

	wp_add_inline_script( 'tailwind-acf-cdn', $config, 'before' );
}
add_action( 'wp_enqueue_scripts', 'tailwind_acf_enqueue_frontend_assets' );

/**
 * Ensure Tailwind is available inside the block editor preview.
 */
function tailwind_acf_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'tailwind-acf-editor-cdn',
		'https://cdn.tailwindcss.com?plugins=forms,typography',
		array(),
		null,
		false
	);

	$config = 'tailwind.config = window.tailwindConfig ?? ' . wp_json_encode(
		array(
			'theme' => array(
				'extend' => array(
					'fontFamily' => array(
						'sans' => array( 'Inter', 'ui-sans-serif', 'system-ui' ),
					),
					'colors' => array(
						'brand' => array(
							'DEFAULT' => '#2563eb',
							'light'   => '#60a5fa',
							'dark'    => '#1d4ed8',
						),
					),
				),
			),
		)
	) . ';';

	wp_add_inline_script( 'tailwind-acf-editor-cdn', $config, 'before' );
}
add_action( 'enqueue_block_editor_assets', 'tailwind_acf_enqueue_block_editor_assets' );

$block_loader = __DIR__ . '/inc/acf-blocks.php';
if ( file_exists( $block_loader ) ) {
	require_once $block_loader;
}

add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && has_block( 'acf/tailwind-hero', $post ) ) {
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $block ) {
					if ( empty( $block['blockName'] ) ) {
						continue;
					}

					if ( 'acf/tailwind-hero' === $block['blockName'] ) {
						$classes[] = 'has-hero-first';
					}

					break;
				}
			}
		}

		return array_unique( $classes );
	}
);
