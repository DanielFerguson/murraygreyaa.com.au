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
 * Set default page template to 'Content Page' for new pages.
 *
 * @param string $template The default template.
 * @return string The modified default template.
 */
function tailwind_acf_default_page_template($template)
{
	return 'template-content-page.php';
}
add_filter('default_page_template_title', function () {
	return __('Content Page', 'tailwind-acf');
});

/**
 * Set the default template meta when a new page is created.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 */
function tailwind_acf_set_default_page_template($post_id, $post, $update)
{
	// Only for new pages, not updates.
	if ($update) {
		return;
	}

	// Only for pages.
	if ('page' !== $post->post_type) {
		return;
	}

	// Check if template is already set.
	$current_template = get_post_meta($post_id, '_wp_page_template', true);
	if (! empty($current_template) && 'default' !== $current_template) {
		return;
	}

	// Set the default template.
	update_post_meta($post_id, '_wp_page_template', 'template-content-page.php');
}
add_action('wp_insert_post', 'tailwind_acf_set_default_page_template', 10, 3);

/**
 * Enqueue compiled Tailwind CSS and theme scripts for the front end.
 */
function tailwind_acf_enqueue_frontend_assets()
{
	$dist_css = get_template_directory() . '/dist/main.css';
	if (file_exists($dist_css)) {
		wp_enqueue_style(
			'tailwind-acf-style',
			get_template_directory_uri() . '/dist/main.css',
			array(),
			filemtime($dist_css)
		);
	}

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

	$hero_parallax_js = get_template_directory() . '/assets/js/hero-parallax.js';
	if (file_exists($hero_parallax_js)) {
		wp_enqueue_script(
			'tailwind-acf-hero-parallax',
			get_template_directory_uri() . '/assets/js/hero-parallax.js',
			array(),
			filemtime($hero_parallax_js),
			true
		);
	}
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

	$hero_parallax_js = get_template_directory() . '/assets/js/hero-parallax.js';
	if (file_exists($hero_parallax_js)) {
		wp_enqueue_script(
			'tailwind-acf-hero-parallax',
			get_template_directory_uri() . '/assets/js/hero-parallax.js',
			array(),
			filemtime($hero_parallax_js),
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

/**
 * Render breadcrumb navigation.
 */
function tailwind_render_breadcrumbs() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb(
			'<nav class="mb-4 text-sm text-slate-500" aria-label="' . esc_attr__( 'Breadcrumb', 'tailwind-acf' ) . '">',
			'</nav>'
		);
	}
}

$block_loader = __DIR__ . '/inc/acf-blocks.php';
if (file_exists($block_loader)) {
	require_once $block_loader;
}

$member_module = __DIR__ . '/inc/members.php';
if (file_exists($member_module)) {
	require_once $member_module;
}

$cattle_module = __DIR__ . '/inc/cattle-registration.php';
if (file_exists($cattle_module)) {
	require_once $cattle_module;
}

$cattle_import_module = __DIR__ . '/inc/cattle-import.php';
if (file_exists($cattle_import_module)) {
	require_once $cattle_import_module;
}

$turnstile_module = __DIR__ . '/inc/turnstile.php';
if (file_exists($turnstile_module)) {
	require_once $turnstile_module;
}

$dropdown_walker = __DIR__ . '/inc/class-dropdown-nav-walker.php';
if (file_exists($dropdown_walker)) {
	require_once $dropdown_walker;
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
		return get_bloginfo('name');
	}
);

add_action(
	'init',
	function () {
		if (! get_role('tailwind_pending')) {
			add_role('tailwind_pending', __('Pending Approval', 'tailwind-acf'), array());
		}
	}
);

add_action(
	'set_user_role',
	function ($user_id, $role) {
		if ('tailwind_pending' !== $role) {
			delete_user_meta($user_id, TAILWIND_MEMBER_STATUS_META);
		}
	},
	10,
	2
);

add_action(
	'wp_dashboard_setup',
	function () {
		remove_meta_box('dashboard_activity', 'dashboard', 'normal');
		remove_meta_box('dashboard_primary', 'dashboard', 'side');
		remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
		remove_meta_box('dashboard_quick_press', 'dashboard', 'side');

		if (current_user_can('promote_users')) {
			add_meta_box(
				'tailwind-dashboard-pending-members',
				__('Pending Member Approvals', 'tailwind-acf'),
				'tailwind_acf_dashboard_pending_members',
				'dashboard',
				'normal',
				'high'
			);

			add_meta_box(
				'tailwind-dashboard-pending-cattle',
				__('Pending Cattle Registrations', 'tailwind-acf'),
				'tailwind_acf_dashboard_pending_cattle',
				'dashboard',
				'normal',
				'high'
			);
		}
	}
);

add_filter(
	'user_has_cap',
	function ($allcaps, $caps, $args, $user) {
		if (empty($args[0]) || 'edit_posts' !== $args[0]) {
			return $allcaps;
		}

		if (empty($GLOBALS['pagenow']) || 'index.php' !== $GLOBALS['pagenow']) {
			return $allcaps;
		}

		if (in_array('tailwind_pending', (array) $user->roles, true)) {
			return $allcaps;
		}

		if (array_intersect((array) $user->roles, array('subscriber'))) {
			$allcaps['edit_posts'] = true;
		}

		return $allcaps;
	},
	10,
	4
);

add_action(
	'admin_menu',
	function () {
		if (current_user_can('publish_posts')) {
			return;
		}

		remove_menu_page('edit.php'); // Posts.
		remove_menu_page('edit-comments.php'); // Comments.
		remove_menu_page('tools.php'); // Tools.
	},
	PHP_INT_MAX
);

add_action(
	'admin_bar_menu',
	function ($wp_admin_bar) {
		if (! is_admin_bar_showing()) {
			return;
		}

		if (current_user_can('promote_users')) {
			return;
		}

		$wp_admin_bar->remove_node('wp-logo');
	},
	PHP_INT_MAX
);

if (! function_exists('tailwind_acf_dashboard_pending_members')) {
	/**
	 * Display a quick overview of pending member accounts on the dashboard.
	 */
	function tailwind_acf_dashboard_pending_members()
	{
		$query = new WP_User_Query(
			array(
				'meta_key'   => TAILWIND_MEMBER_STATUS_META,
				'meta_value' => TAILWIND_MEMBER_STATUS_PENDING,
				'fields'     => array('ID', 'user_login', 'user_email', 'user_registered'),
			)
		);

		$users = $query->get_results();

		if (empty($users)) {
			echo '<p>' . esc_html__('No pending members at the moment.', 'tailwind-acf') . '</p>';
			return;
		}

		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Username', 'tailwind-acf') . '</th>';
		echo '<th>' . esc_html__('Email', 'tailwind-acf') . '</th>';
		echo '<th>' . esc_html__('Registered', 'tailwind-acf') . '</th>';
		echo '<th class="column-links">' . esc_html__('Actions', 'tailwind-acf') . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ($users as $user) {
			$approve_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'tailwind_approve_member',
						'user_id' => $user->ID,
					),
					admin_url('admin-post.php')
				),
				'tailwind_approve_member_' . $user->ID
			);

			echo '<tr>';
			echo '<td>' . esc_html($user->user_login) . '</td>';
			echo '<td><a href="mailto:' . esc_attr($user->user_email) . '">' . esc_html($user->user_email) . '</a></td>';
			echo '<td>' . esc_html(get_date_from_gmt($user->user_registered, get_option('date_format') . ' ' . get_option('time_format'))) . '</td>';
			echo '<td><a class="button button-primary" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'tailwind-acf') . '</a></td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}
}

if (! function_exists('tailwind_acf_dashboard_pending_cattle')) {
	/**
	 * Display a quick overview of pending cattle registrations on the dashboard.
	 */
	function tailwind_acf_dashboard_pending_cattle()
	{
		$pending_cattle = get_posts(
			array(
				'post_type'      => 'cattle_registration',
				'post_status'    => 'pending',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);

		if (empty($pending_cattle)) {
			echo '<p>' . esc_html__('No pending cattle registrations at the moment.', 'tailwind-acf') . '</p>';
			return;
		}

		echo '<table class="widefat striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Calf Name', 'tailwind-acf') . '</th>';
		echo '<th>' . esc_html__('Tattoo', 'tailwind-acf') . '</th>';
		echo '<th>' . esc_html__('Submitter', 'tailwind-acf') . '</th>';
		echo '<th>' . esc_html__('Submitted', 'tailwind-acf') . '</th>';
		echo '<th class="column-links">' . esc_html__('Actions', 'tailwind-acf') . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		foreach ($pending_cattle as $cattle) {
			$calf_name = get_field('calf_name', $cattle->ID);
			$tattoo    = get_field('tattoo_number', $cattle->ID);
			$author    = get_userdata($cattle->post_author);

			$approve_url = wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'tailwind_approve_cattle',
						'post_id' => $cattle->ID,
					),
					admin_url('admin-post.php')
				),
				'tailwind_approve_cattle_' . $cattle->ID
			);

			$edit_url = admin_url('post.php?post=' . $cattle->ID . '&action=edit');

			echo '<tr>';
			echo '<td><a href="' . esc_url($edit_url) . '">' . esc_html($calf_name ?: '—') . '</a></td>';
			echo '<td>' . esc_html($tattoo ?: '—') . '</td>';
			echo '<td>' . ($author ? esc_html($author->display_name) : '—') . '</td>';
			echo '<td>' . esc_html(get_the_date('', $cattle)) . '</td>';
			echo '<td><a class="button button-primary" href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'tailwind-acf') . '</a></td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}
}

/**
 * Remove unwanted dashboard widgets.
 */
function tailwind_remove_dashboard_widgets()
{
	// Wordfence widgets.
	remove_meta_box('wordfence_activity_report_widget', 'dashboard', 'normal');
	remove_meta_box('wf_dashboard_widget', 'dashboard', 'normal');

	// Yoast SEO widgets.
	remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal');
	remove_meta_box('wpseo-wincher-dashboard-overview', 'dashboard', 'normal');

	// WPForms widget.
	remove_meta_box('wpforms_reports_widget_lite', 'dashboard', 'normal');
	remove_meta_box('wpforms_reports_widget_pro', 'dashboard', 'normal');

	// Site Health Status widget.
	remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

	// Welcome panel.
	remove_action('welcome_panel', 'wp_welcome_panel');
}
add_action('wp_dashboard_setup', 'tailwind_remove_dashboard_widgets', 999);

/**
 * Disable the welcome panel for all users.
 */
function tailwind_disable_welcome_panel()
{
	$user_id = get_current_user_id();
	if (get_user_meta($user_id, 'show_welcome_panel', true)) {
		update_user_meta($user_id, 'show_welcome_panel', 0);
	}
}
add_action('admin_init', 'tailwind_disable_welcome_panel');
