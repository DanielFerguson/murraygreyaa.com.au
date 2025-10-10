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
			'social'  => __( 'Social Links', 'tailwind-acf' ),
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

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'tailwind_acf_footer',
			array(
				'title'       => __( 'Footer Content', 'tailwind-acf' ),
				'description' => __( 'Control the headline, description, and CTA link shown on the left side of the footer.', 'tailwind-acf' ),
				'priority'    => 160,
			)
		);

		$wp_customize->add_setting(
			'tailwind_acf_footer_headline',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'tailwind_acf_footer_headline',
			array(
				'label'   => __( 'Headline', 'tailwind-acf' ),
				'section' => 'tailwind_acf_footer',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'tailwind_acf_footer_description',
			array(
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'tailwind_acf_footer_description',
			array(
				'label'   => __( 'Description', 'tailwind-acf' ),
				'section' => 'tailwind_acf_footer',
				'type'    => 'textarea',
			)
		);

		$wp_customize->add_setting(
			'tailwind_acf_footer_link_text',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'tailwind_acf_footer_link_text',
			array(
				'label'   => __( 'Link Text', 'tailwind-acf' ),
				'section' => 'tailwind_acf_footer',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'tailwind_acf_footer_link_url',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'tailwind_acf_footer_link_url',
			array(
				'label'       => __( 'Link URL', 'tailwind-acf' ),
				'section'     => 'tailwind_acf_footer',
				'type'        => 'url',
				'input_attrs' => array(
					'placeholder' => 'https://example.com/about',
				),
			)
		);
	}
);

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
