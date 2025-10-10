<?php

/**
 * Theme bootstrap.
 *
 * @package Tailwind_ACF
 */

define('TAILWIND_ACF_THEME_VERSION', '0.1.0');

add_action('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('editor-styles');
	add_theme_support('menus');
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
			'primary' => __('Primary Menu', 'tailwind-acf'),
			'footer'  => __('Footer Menu', 'tailwind-acf'),
			'social'  => __('Social Links', 'tailwind-acf'),
		)
	);
});

/**
 * Enqueue Tailwind CDN for the front end.
 */
function tailwind_acf_enqueue_frontend_assets()
{
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

	$carousel_js = get_template_directory() . '/assets/js/carousel.js';
	if (file_exists($carousel_js)) {
		wp_enqueue_script(
			'tailwind-acf-carousel',
			get_template_directory_uri() . '/assets/js/carousel.js',
			array(),
			filemtime($carousel_js),
			true
		);
	}

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

	wp_add_inline_script('tailwind-acf-cdn', $config, 'before');
}
add_action('wp_enqueue_scripts', 'tailwind_acf_enqueue_frontend_assets');

/**
 * Ensure Tailwind is available inside the block editor preview.
 */
function tailwind_acf_enqueue_block_editor_assets()
{
	wp_enqueue_script(
		'tailwind-acf-editor-cdn',
		'https://cdn.tailwindcss.com?plugins=forms,typography',
		array(),
		null,
		false
	);

	$carousel_js = get_template_directory() . '/assets/js/carousel.js';
	if (file_exists($carousel_js)) {
		wp_enqueue_script(
			'tailwind-acf-carousel',
			get_template_directory_uri() . '/assets/js/carousel.js',
			array(),
			filemtime($carousel_js),
			true
		);
	}

	$editor_css = get_template_directory() . '/assets/css/editor.css';
	if (file_exists($editor_css)) {
		wp_enqueue_style(
			'tailwind-acf-editor-style',
			get_template_directory_uri() . '/assets/css/editor.css',
			array(),
			filemtime($editor_css)
		);
	}

	$config = 'tailwind.config = window.tailwindConfig ?? ' . wp_json_encode(
		array(
			'theme' => array(
				'extend' => array(
					'fontFamily' => array(
						'sans' => array('Inter', 'ui-sans-serif', 'system-ui'),
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

	wp_add_inline_script('tailwind-acf-editor-cdn', $config, 'before');
}
add_action('enqueue_block_editor_assets', 'tailwind_acf_enqueue_block_editor_assets');

$block_loader = __DIR__ . '/inc/acf-blocks.php';
if (file_exists($block_loader)) {
	require_once $block_loader;
}

add_action(
	'customize_register',
	function ($wp_customize) {
		$wp_customize->add_section(
			'tailwind_acf_footer',
			array(
				'title'       => __('Footer Content', 'tailwind-acf'),
				'description' => __('Control the headline, description, and CTA link shown on the left side of the footer.', 'tailwind-acf'),
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
				'label'   => __('Headline', 'tailwind-acf'),
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
				'label'   => __('Description', 'tailwind-acf'),
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
				'label'   => __('Link Text', 'tailwind-acf'),
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
				'label'       => __('Link URL', 'tailwind-acf'),
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
	function ($classes) {
		if (is_singular()) {
			$post = get_post();
			if ($post && has_block('acf/tailwind-hero', $post)) {
				$blocks = parse_blocks($post->post_content);
				foreach ($blocks as $block) {
					if (empty($block['blockName'])) {
						continue;
					}

					if ('acf/tailwind-hero' === $block['blockName']) {
						$classes[] = 'has-hero-first';
					}

					break;
				}
			}
		}

		return array_unique($classes);
	}
);

add_action(
	'login_enqueue_scripts',
	function () {
		$logo_id = get_theme_mod('custom_logo');
		$logo    = $logo_id ? wp_get_attachment_image_src($logo_id, 'full') : false;

		if (! $logo) {
			return;
		}

		list($url, $width, $height) = $logo;

		printf(
			'<style>
				:root .login h1 a {
					background-image: url(%1$s);
					background-size: contain;
					width: auto;
					height: 200px;
				}
			</style>',
			esc_url($url),
			(int) $width,
			(int) $height
		);
	}
);

add_filter(
	'login_headerurl',
	function () {
		return home_url();
	}
);

add_filter(
	'login_headertext',
	function () {
		return get_bloginfo( 'name' );
	}
);

add_action(
	'init',
	function () {
		if ( ! get_role( 'tailwind_pending' ) ) {
			add_role( 'tailwind_pending', __( 'Pending Approval', 'tailwind-acf' ), array() );
		}
	}
);

add_action(
	'user_register',
	function ( $user_id ) {
		$user = new WP_User( $user_id );

		$user->set_role( 'tailwind_pending' );
		update_user_meta( $user_id, 'tailwind_account_status', 'pending' );

		wp_mail(
			get_option( 'admin_email' ),
			__( 'New account awaiting approval', 'tailwind-acf' ),
			sprintf(
				/* translators: 1: user login, 2: user email address */
				__( "A new user registered and is awaiting approval:\n\nUsername: %1\$s\nEmail: %2\$s\n\nApprove via Users → All Users.\n", 'tailwind-acf' ),
				$user->user_login,
				$user->user_email
			)
		);
	}
);

add_filter(
	'authenticate',
	function ( $user, $username ) {
		if ( $user instanceof WP_User && 'pending' === get_user_meta( $user->ID, 'tailwind_account_status', true ) ) {
			return new WP_Error(
				'tailwind_pending',
				__( 'Thanks! An administrator will approve your account soon.', 'tailwind-acf' )
			);
		}

		return $user;
	},
	20,
	2
);

add_action(
	'set_user_role',
	function ( $user_id, $role ) {
		if ( 'tailwind_pending' !== $role ) {
			delete_user_meta( $user_id, 'tailwind_account_status' );
		}
	},
	10,
	2
);

add_filter(
	'user_row_actions',
	function ( $actions, $user ) {
		if ( ! current_user_can( 'promote_users' ) ) {
			return $actions;
		}

		if ( 'pending' !== get_user_meta( $user->ID, 'tailwind_account_status', true ) ) {
			return $actions;
		}

		$approve_url = wp_nonce_url(
			add_query_arg(
				array(
					'tailwind-approve-user' => $user->ID,
				),
				admin_url( 'users.php' )
			),
			'tailwind-approve-user_' . $user->ID
		);

		$actions['tailwind-approve'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $approve_url ),
			esc_html__( 'Approve', 'tailwind-acf' )
		);

		return $actions;
	},
	10,
	2
);

add_action(
	'admin_init',
	function () {
		if ( ! current_user_can( 'promote_users' ) ) {
			return;
		}

		$user_param = filter_input( INPUT_GET, 'tailwind-approve-user', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $user_param ) ) {
			return;
		}

		$user_id = absint( $user_param );
		check_admin_referer( 'tailwind-approve-user_' . $user_id );

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'tailwind-approved' => '0',
					),
					admin_url( 'users.php' )
				)
			);
			exit;
		}

		$default_role = get_option( 'default_role', 'subscriber' );
		wp_update_user(
			array(
				'ID'   => $user_id,
				'role' => $default_role,
			)
		);

		delete_user_meta( $user_id, 'tailwind_account_status' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'tailwind-approved' => '1',
				),
				admin_url( 'users.php' )
			)
		);
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		$status = filter_input( INPUT_GET, 'tailwind-approved', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( null === $status ) {
			return;
		}

		if ( '1' === $status ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'User approved and activated.', 'tailwind-acf' )
			);
		} else {
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				esc_html__( 'User could not be approved. Please try again.', 'tailwind-acf' )
			);
		}
	}
);

add_filter(
	'login_redirect',
	function ( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $user instanceof WP_User ) {
			return $redirect_to;
		}

		if ( in_array( 'tailwind_pending', (array) $user->roles, true ) ) {
			return $redirect_to;
		}

		return admin_url( 'index.php' );
	},
	PHP_INT_MAX,
	3
);

add_action(
	'wp_dashboard_setup',
	function () {
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}
);

add_filter(
	'user_has_cap',
	function ( $allcaps, $caps, $args, $user ) {
		if ( empty( $args[0] ) || 'edit_posts' !== $args[0] ) {
			return $allcaps;
		}

		if ( empty( $GLOBALS['pagenow'] ) || 'index.php' !== $GLOBALS['pagenow'] ) {
			return $allcaps;
		}

		if ( in_array( 'tailwind_pending', (array) $user->roles, true ) ) {
			return $allcaps;
		}

		if ( array_intersect( (array) $user->roles, array( 'subscriber' ) ) ) {
			$allcaps['edit_posts'] = true;
		}

		return $allcaps;
	},
	10,
	4
);
