<?php
/**
 * Cattle Registration Custom Post Type and ACF Fields.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the cattle_registration custom post type.
 */
function tailwind_register_cattle_registration_cpt() {
	$labels = array(
		'name'                  => __( 'Cattle Registrations', 'tailwind-acf' ),
		'singular_name'         => __( 'Cattle Registration', 'tailwind-acf' ),
		'menu_name'             => __( 'Cattle', 'tailwind-acf' ),
		'add_new'               => __( 'Add New', 'tailwind-acf' ),
		'add_new_item'          => __( 'Add New Registration', 'tailwind-acf' ),
		'edit_item'             => __( 'Edit Registration', 'tailwind-acf' ),
		'new_item'              => __( 'New Registration', 'tailwind-acf' ),
		'view_item'             => __( 'View Registration', 'tailwind-acf' ),
		'view_items'            => __( 'View Registrations', 'tailwind-acf' ),
		'search_items'          => __( 'Search Registrations', 'tailwind-acf' ),
		'not_found'             => __( 'No registrations found', 'tailwind-acf' ),
		'not_found_in_trash'    => __( 'No registrations found in Trash', 'tailwind-acf' ),
		'all_items'             => __( 'All Registrations', 'tailwind-acf' ),
		'archives'              => __( 'Registration Archives', 'tailwind-acf' ),
		'filter_items_list'     => __( 'Filter registrations list', 'tailwind-acf' ),
		'items_list_navigation' => __( 'Registrations list navigation', 'tailwind-acf' ),
		'items_list'            => __( 'Registrations list', 'tailwind-acf' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'cattle' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 25,
		'menu_icon'          => 'dashicons-tag',
		'supports'           => array( 'title', 'author' ),
	);

	register_post_type( 'cattle_registration', $args );
}
add_action( 'init', 'tailwind_register_cattle_registration_cpt' );

/**
 * Flush rewrite rules on theme activation to ensure CPT permalinks work.
 */
function tailwind_flush_rewrite_rules_on_activation() {
	// Register CPT first so rules are included.
	tailwind_register_cattle_registration_cpt();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'tailwind_flush_rewrite_rules_on_activation' );

/**
 * Create the cattle registration page if it doesn't exist.
 */
function tailwind_create_cattle_registration_page() {
	// Only run once per request, and only in admin or on init.
	if ( get_page_by_path( 'register-cattle' ) ) {
		return;
	}

	// Check if we've already tried to create this page.
	if ( get_option( 'tailwind_cattle_page_created' ) ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'     => __( 'Register Cattle', 'tailwind-acf' ),
			'post_name'      => 'register-cattle',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'page_template'  => 'page-register-cattle.php',
		)
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'tailwind_cattle_page_created', $page_id, false );
	}
}
add_action( 'init', 'tailwind_create_cattle_registration_page', 20 );

/**
 * Create the animal search page if it doesn't exist.
 */
function tailwind_create_animal_search_page() {
	if ( get_page_by_path( 'animal-search' ) ) {
		return;
	}

	if ( get_option( 'tailwind_animal_search_page_created' ) ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'     => __( 'Animal Search', 'tailwind-acf' ),
			'post_name'      => 'animal-search',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'page_template'  => 'page-animal-search.php',
		)
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'tailwind_animal_search_page_created', $page_id, false );
	}
}
add_action( 'init', 'tailwind_create_animal_search_page', 20 );

/**
 * Get cattle grade options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_grade_options() {
	return array(
		'PB' => __( 'PB: Pure Breed', 'tailwind-acf' ),
		'A'  => __( 'A: A Grade', 'tailwind-acf' ),
		'B'  => __( 'B: B Grade', 'tailwind-acf' ),
		'C'  => __( 'C: C Grade', 'tailwind-acf' ),
	);
}

/**
 * Get cattle grade labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_grade_labels() {
	return array(
		'PB' => __( 'Pure Breed', 'tailwind-acf' ),
		'A'  => __( 'A Grade', 'tailwind-acf' ),
		'B'  => __( 'B Grade', 'tailwind-acf' ),
		'C'  => __( 'C Grade', 'tailwind-acf' ),
	);
}

/**
 * Get cattle sex options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_sex_options() {
	return array(
		'M' => __( 'M: Male', 'tailwind-acf' ),
		'F' => __( 'F: Female', 'tailwind-acf' ),
		'S' => __( 'S: Steer', 'tailwind-acf' ),
	);
}

/**
 * Get cattle sex labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_sex_labels() {
	return array(
		'M' => __( 'Male', 'tailwind-acf' ),
		'F' => __( 'Female', 'tailwind-acf' ),
		'S' => __( 'Steer', 'tailwind-acf' ),
	);
}

/**
 * Get cattle colour options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_colour_options() {
	return array(
		'G' => __( 'G: Grey', 'tailwind-acf' ),
		'S' => __( 'S: Silver', 'tailwind-acf' ),
		'B' => __( 'B: Black', 'tailwind-acf' ),
		'D' => __( 'D: Dun', 'tailwind-acf' ),
	);
}

/**
 * Get cattle colour labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_colour_labels() {
	return array(
		'G' => __( 'Grey', 'tailwind-acf' ),
		'S' => __( 'Silver', 'tailwind-acf' ),
		'B' => __( 'Black', 'tailwind-acf' ),
		'D' => __( 'Dun', 'tailwind-acf' ),
	);
}

/**
 * Get cattle calving ease options for forms.
 *
 * @return array
 */
function tailwind_cattle_get_calving_ease_options() {
	return array(
		'1' => __( '1: Unassisted', 'tailwind-acf' ),
		'2' => __( '2: Assisted', 'tailwind-acf' ),
		'3' => __( '3: Fully Assisted', 'tailwind-acf' ),
		'4' => __( '4: Caesarean', 'tailwind-acf' ),
		'5' => __( '5: Breach', 'tailwind-acf' ),
	);
}

/**
 * Get cattle calving ease labels for display.
 *
 * @return array
 */
function tailwind_cattle_get_calving_ease_labels() {
	return array(
		'1' => __( 'Unassisted', 'tailwind-acf' ),
		'2' => __( 'Assisted', 'tailwind-acf' ),
		'3' => __( 'Fully Assisted', 'tailwind-acf' ),
		'4' => __( 'Caesarean', 'tailwind-acf' ),
		'5' => __( 'Breach', 'tailwind-acf' ),
	);
}

/**
 * Register ACF field group for cattle registrations.
 */
function tailwind_register_cattle_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cattle_registration',
			'title'    => __( 'Cattle Registration Details', 'tailwind-acf' ),
			'fields'   => array(
				// Calf Information Section.
				array(
					'key'   => 'field_cattle_calf_name',
					'label' => __( 'Calf Name', 'tailwind-acf' ),
					'name'  => 'calf_name',
					'type'  => 'text',
					'required' => 1,
					'maxlength' => 100,
				),
				array(
					'key'     => 'field_cattle_grade',
					'label'   => __( 'Grade', 'tailwind-acf' ),
					'name'    => 'grade',
					'type'    => 'select',
					'required' => 1,
					'choices' => tailwind_cattle_get_grade_options(),
					'default_value' => 'PB',
				),
				array(
					'key'       => 'field_cattle_year_letter',
					'label'     => __( 'Year Letter', 'tailwind-acf' ),
					'name'      => 'year_letter',
					'type'      => 'text',
					'required'  => 1,
					'maxlength' => 1,
					'instructions' => __( 'Single uppercase letter (A-Z, excluding I and O)', 'tailwind-acf' ),
				),
				array(
					'key'      => 'field_cattle_tattoo_number',
					'label'    => __( 'Tattoo Number', 'tailwind-acf' ),
					'name'     => 'tattoo_number',
					'type'     => 'text',
					'required' => 1,
				),
				// Registration Details Section.
				array(
					'key'          => 'field_cattle_registration_number',
					'label'        => __( 'Registration Number', 'tailwind-acf' ),
					'name'         => 'registration_number',
					'type'         => 'text',
					'instructions' => __( 'Full registration number (e.g., RIB G70)', 'tailwind-acf' ),
				),
				array(
					'key'   => 'field_cattle_stud_name',
					'label' => __( 'Stud Name', 'tailwind-acf' ),
					'name'  => 'stud_name',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cattle_herd_book',
					'label' => __( 'Herd Book', 'tailwind-acf' ),
					'name'  => 'herd_book',
					'type'  => 'number',
				),
				array(
					'key'          => 'field_cattle_brand_tattoo',
					'label'        => __( 'Brand Tattoo', 'tailwind-acf' ),
					'name'         => 'brand_tattoo',
					'type'         => 'text',
					'instructions' => __( 'Tattoo prefix (e.g., RIB, WIN)', 'tailwind-acf' ),
				),
				// Birth Details Section.
				array(
					'key'           => 'field_cattle_date_of_birth',
					'label'         => __( 'Date of Birth', 'tailwind-acf' ),
					'name'          => 'date_of_birth',
					'type'          => 'date_picker',
					'required'      => 1,
					'display_format' => 'd/m/Y',
					'return_format' => 'Y-m-d',
				),
				array(
					'key'     => 'field_cattle_birth_weight',
					'label'   => __( 'Birth Weight (kg)', 'tailwind-acf' ),
					'name'    => 'birth_weight',
					'type'    => 'number',
					'min'     => 15,
					'max'     => 80,
					'step'    => 0.1,
					'append'  => 'kg',
				),
				array(
					'key'      => 'field_cattle_sex',
					'label'    => __( 'Sex', 'tailwind-acf' ),
					'name'     => 'sex',
					'type'     => 'select',
					'required' => 1,
					'choices'  => tailwind_cattle_get_sex_options(),
				),
				array(
					'key'     => 'field_cattle_colour',
					'label'   => __( 'Colour', 'tailwind-acf' ),
					'name'    => 'colour',
					'type'    => 'select',
					'required' => 1,
					'choices' => tailwind_cattle_get_colour_options(),
				),
				array(
					'key'      => 'field_cattle_calving_ease',
					'label'    => __( 'Calving Ease', 'tailwind-acf' ),
					'name'     => 'calving_ease',
					'type'     => 'select',
					'required' => 1,
					'choices'  => tailwind_cattle_get_calving_ease_options(),
					'default_value' => '1',
				),
				// Boolean Flags.
				array(
					'key'   => 'field_cattle_is_ai',
					'label' => __( 'A.I. (Artificial Insemination)', 'tailwind-acf' ),
					'name'  => 'is_ai',
					'type'  => 'true_false',
					'ui'    => 1,
				),
				array(
					'key'   => 'field_cattle_is_et',
					'label' => __( 'E.T. (Embryo Transfer)', 'tailwind-acf' ),
					'name'  => 'is_et',
					'type'  => 'true_false',
					'ui'    => 1,
				),
				array(
					'key'   => 'field_cattle_is_twin',
					'label' => __( 'Twin', 'tailwind-acf' ),
					'name'  => 'is_twin',
					'type'  => 'true_false',
					'ui'    => 1,
				),
				// Parentage Section.
				array(
					'key'   => 'field_cattle_sire_name',
					'label' => __( "Sire's Name", 'tailwind-acf' ),
					'name'  => 'sire_name',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cattle_sire_tattoo',
					'label' => __( "Sire's Registration Number/Tattoo", 'tailwind-acf' ),
					'name'  => 'sire_tattoo',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cattle_dam_name',
					'label' => __( "Dam's Name", 'tailwind-acf' ),
					'name'  => 'dam_name',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cattle_dam_tattoo',
					'label' => __( "Dam's Registration Number/Tattoo", 'tailwind-acf' ),
					'name'  => 'dam_tattoo',
					'type'  => 'text',
				),
				// Parentage Relationships (post object links).
				array(
					'key'           => 'field_cattle_sire_id',
					'label'         => __( 'Sire (Linked Animal)', 'tailwind-acf' ),
					'name'          => 'sire_id',
					'type'          => 'post_object',
					'post_type'     => array( 'cattle_registration' ),
					'return_format' => 'id',
					'allow_null'    => 1,
					'ui'            => 1,
				),
				array(
					'key'           => 'field_cattle_dam_id',
					'label'         => __( 'Dam (Linked Animal)', 'tailwind-acf' ),
					'name'          => 'dam_id',
					'type'          => 'post_object',
					'post_type'     => array( 'cattle_registration' ),
					'return_format' => 'id',
					'allow_null'    => 1,
					'ui'            => 1,
				),
				// Extended Parentage Details.
				array(
					'key'   => 'field_cattle_sire_herd_book',
					'label' => __( 'Sire Herd Book', 'tailwind-acf' ),
					'name'  => 'sire_herd_book',
					'type'  => 'number',
				),
				array(
					'key'   => 'field_cattle_sire_grade',
					'label' => __( 'Sire Grade', 'tailwind-acf' ),
					'name'  => 'sire_grade',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_cattle_dam_herd_book',
					'label' => __( 'Dam Herd Book', 'tailwind-acf' ),
					'name'  => 'dam_herd_book',
					'type'  => 'number',
				),
				array(
					'key'   => 'field_cattle_dam_grade',
					'label' => __( 'Dam Grade', 'tailwind-acf' ),
					'name'  => 'dam_grade',
					'type'  => 'text',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'cattle_registration',
					),
				),
			),
			'menu_order' => 0,
			'position'   => 'normal',
			'style'      => 'default',
		)
	);
}
add_action( 'acf/init', 'tailwind_register_cattle_acf_fields' );

/**
 * Auto-generate post title from calf name and tattoo number.
 *
 * @param array $data    Post data.
 * @param array $postarr Post array.
 * @return array
 */
function tailwind_cattle_auto_title( $data, $postarr ) {
	if ( 'cattle_registration' !== $data['post_type'] ) {
		return $data;
	}

	// Only auto-generate if we have ACF data.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by ACF/WordPress.
	if ( empty( $_POST['acf'] ) ) {
		return $data;
	}

	$calf_name = '';
	$tattoo    = '';

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified by ACF/WordPress.
	$acf_data = map_deep( wp_unslash( $_POST['acf'] ), 'sanitize_text_field' );

	// Find the calf name and tattoo from ACF fields.
	foreach ( $acf_data as $key => $value ) {
		if ( 'field_cattle_calf_name' === $key ) {
			$calf_name = $value;
		}
		if ( 'field_cattle_tattoo_number' === $key ) {
			$tattoo = $value;
		}
	}

	if ( $calf_name && $tattoo ) {
		$data['post_title'] = $calf_name . ' (' . $tattoo . ')';
		$data['post_name']  = sanitize_title( $data['post_title'] );
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'tailwind_cattle_auto_title', 10, 2 );

/**
 * Add custom columns to the cattle registrations admin list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function tailwind_cattle_admin_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;

		// Add custom columns after title.
		if ( 'title' === $key ) {
			$new_columns['registration_number'] = __( 'Registration', 'tailwind-acf' );
			$new_columns['stud_name']           = __( 'Stud', 'tailwind-acf' );
			$new_columns['tattoo']              = __( 'Tattoo', 'tailwind-acf' );
			$new_columns['sex']                 = __( 'Sex', 'tailwind-acf' );
			$new_columns['grade']               = __( 'Grade', 'tailwind-acf' );
			$new_columns['submitter']           = __( 'Submitter', 'tailwind-acf' );
		}
	}

	return $new_columns;
}
add_filter( 'manage_cattle_registration_posts_columns', 'tailwind_cattle_admin_columns' );

/**
 * Render custom column content for cattle registrations.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function tailwind_cattle_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'registration_number':
			$regn = get_field( 'registration_number', $post_id );
			echo esc_html( $regn ?: '—' );
			break;

		case 'stud_name':
			$stud = get_field( 'stud_name', $post_id );
			echo esc_html( $stud ?: '—' );
			break;

		case 'tattoo':
			$tattoo = get_field( 'tattoo_number', $post_id );
			echo esc_html( $tattoo ?: '—' );
			break;

		case 'sex':
			$sex = get_field( 'sex', $post_id );
			$sex_labels = tailwind_cattle_get_sex_labels();
			echo esc_html( $sex_labels[ $sex ] ?? '—' );
			break;

		case 'grade':
			$grade = get_field( 'grade', $post_id );
			echo esc_html( $grade ?: '—' );
			break;

		case 'submitter':
			$post   = get_post( $post_id );
			$author = get_userdata( $post->post_author );
			if ( $author ) {
				printf(
					'<a href="%s">%s</a>',
					esc_url( get_edit_user_link( $author->ID ) ),
					esc_html( $author->display_name )
				);
			} else {
				echo '—';
			}
			break;
	}
}
add_action( 'manage_cattle_registration_posts_custom_column', 'tailwind_cattle_admin_column_content', 10, 2 );

/**
 * Add quick approve action for pending cattle registrations.
 *
 * @param array   $actions Existing actions.
 * @param WP_Post $post    Current post.
 * @return array
 */
function tailwind_cattle_row_actions( $actions, $post ) {
	if ( 'cattle_registration' !== $post->post_type ) {
		return $actions;
	}

	if ( 'pending' !== $post->post_status ) {
		return $actions;
	}

	if ( ! current_user_can( 'publish_posts' ) ) {
		return $actions;
	}

	$approve_url = wp_nonce_url(
		add_query_arg(
			array(
				'action'  => 'tailwind_approve_cattle',
				'post_id' => $post->ID,
			),
			admin_url( 'admin-post.php' )
		),
		'tailwind_approve_cattle_' . $post->ID
	);

	$actions['approve'] = sprintf(
		'<a href="%s" style="color: #2271b1; font-weight: 600;">%s</a>',
		esc_url( $approve_url ),
		esc_html__( 'Approve', 'tailwind-acf' )
	);

	return $actions;
}
add_filter( 'post_row_actions', 'tailwind_cattle_row_actions', 10, 2 );

/**
 * Handle cattle registration approval.
 */
function tailwind_handle_cattle_approval() {
	if ( ! current_user_can( 'publish_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to approve registrations.', 'tailwind-acf' ) );
	}

	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_die( esc_html__( 'Missing post ID.', 'tailwind-acf' ) );
	}

	check_admin_referer( 'tailwind_approve_cattle_' . $post_id );

	$post = get_post( $post_id );

	if ( ! $post || 'cattle_registration' !== $post->post_type ) {
		wp_die( esc_html__( 'Invalid registration.', 'tailwind-acf' ) );
	}

	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		)
	);

	/**
	 * Fires when a cattle registration is approved.
	 *
	 * @param int     $post_id The approved post ID.
	 * @param WP_Post $post    The post object.
	 */
	do_action( 'tailwind_cattle_approved', $post_id, $post );

	// Optionally notify the member.
	$author = get_userdata( $post->post_author );
	if ( $author && $author->user_email ) {
		$calf_name = get_field( 'calf_name', $post_id );
		$tattoo    = get_field( 'tattoo_number', $post_id );

		wp_mail(
			$author->user_email,
			__( 'Your cattle registration has been approved', 'tailwind-acf' ),
			sprintf(
				/* translators: 1: calf name, 2: tattoo number, 3: view URL */
				__( "Great news! Your cattle registration for %1\$s (%2\$s) has been approved.\n\nView it here: %3\$s", 'tailwind-acf' ),
				$calf_name,
				$tattoo,
				get_permalink( $post_id )
			)
		);
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type' => 'cattle_registration',
				'approved'  => '1',
			),
			admin_url( 'edit.php' )
		)
	);
	exit;
}
add_action( 'admin_post_tailwind_approve_cattle', 'tailwind_handle_cattle_approval' );

/**
 * Show admin notice after approval.
 */
function tailwind_cattle_approval_notice() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-cattle_registration' !== $screen->id ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only, no state change.
	if ( ! isset( $_GET['approved'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['approved'] ) ) ) {
		return;
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html__( 'Cattle registration approved and published.', 'tailwind-acf' )
	);
}
add_action( 'admin_notices', 'tailwind_cattle_approval_notice' );

/**
 * Send email notification to admin when new registration is submitted.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update.
 */
function tailwind_cattle_notify_admin_on_submission( $post_id, $post, $update ) {
	// Only for new cattle registrations.
	if ( 'cattle_registration' !== $post->post_type ) {
		return;
	}

	// Only on initial creation, not updates.
	if ( $update ) {
		return;
	}

	// Only for pending status (front-end submissions).
	if ( 'pending' !== $post->post_status ) {
		return;
	}

	$author    = get_userdata( $post->post_author );
	$calf_name = get_field( 'calf_name', $post_id );
	$tattoo    = get_field( 'tattoo_number', $post_id );
	$grade     = get_field( 'grade', $post_id );
	$sex       = get_field( 'sex', $post_id );

	$sex_labels = tailwind_cattle_get_sex_labels();

	$edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

	$message = sprintf(
		/* translators: Cattle registration notification email */
		__(
			"A new cattle registration has been submitted and is awaiting your approval.\n\n" .
			"Calf Details:\n" .
			"- Name: %1\$s\n" .
			"- Tattoo: %2\$s\n" .
			"- Grade: %3\$s\n" .
			"- Sex: %4\$s\n\n" .
			"Submitted by: %5\$s (%6\$s)\n\n" .
			"Review and approve: %7\$s",
			'tailwind-acf'
		),
		$calf_name,
		$tattoo,
		$grade,
		$sex_labels[ $sex ] ?? $sex,
		$author ? $author->display_name : __( 'Unknown', 'tailwind-acf' ),
		$author ? $author->user_email : '',
		$edit_url
	);

	wp_mail(
		get_option( 'admin_email' ),
		__( 'New cattle registration awaiting approval', 'tailwind-acf' ),
		$message
	);
}
add_action( 'wp_insert_post', 'tailwind_cattle_notify_admin_on_submission', 10, 3 );

/**
 * Get cattle registration status label.
 *
 * @param string $status Post status.
 * @return string
 */
function tailwind_get_cattle_status_label( $status ) {
	$labels = array(
		'pending' => __( 'Pending Approval', 'tailwind-acf' ),
		'publish' => __( 'Approved', 'tailwind-acf' ),
		'draft'   => __( 'Draft', 'tailwind-acf' ),
	);

	return $labels[ $status ] ?? $status;
}

/**
 * Get cattle registration status badge HTML.
 *
 * @param string $status Post status.
 * @return string
 */
function tailwind_get_cattle_status_badge( $status ) {
	$label = tailwind_get_cattle_status_label( $status );

	$classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';

	switch ( $status ) {
		case 'publish':
			$classes .= ' bg-green-100 text-green-800';
			break;
		case 'pending':
			$classes .= ' bg-yellow-100 text-yellow-800';
			break;
		default:
			$classes .= ' bg-slate-100 text-slate-800';
	}

	return sprintf(
		'<span class="%s">%s</span>',
		esc_attr( $classes ),
		esc_html( $label )
	);
}

/**
 * Add "Change Owner" to bulk actions dropdown for cattle registrations.
 *
 * @param array $actions Existing bulk actions.
 * @return array
 */
function tailwind_cattle_bulk_actions( $actions ) {
	$actions['change_owner'] = __( 'Change Owner', 'tailwind-acf' );
	return $actions;
}
add_filter( 'bulk_actions-edit-cattle_registration', 'tailwind_cattle_bulk_actions' );

/**
 * Render a user dropdown above the cattle list table for bulk owner assignment.
 *
 * @param string $which Top or bottom.
 */
function tailwind_cattle_owner_dropdown() {
	$screen = get_current_screen();

	if ( ! $screen || 'edit-cattle_registration' !== $screen->id ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Get approved members + administrators so admins always appear.
	$approved = get_users(
		array(
			'meta_key'   => 'tailwind_member_status',
			'meta_value' => 'approved',
			'orderby'    => 'display_name',
			'order'      => 'ASC',
		)
	);
	$admins = get_users(
		array(
			'role'    => 'administrator',
			'orderby' => 'display_name',
			'order'   => 'ASC',
		)
	);

	// Merge and deduplicate by user ID.
	$seen  = array();
	$users = array();
	foreach ( array_merge( $admins, $approved ) as $u ) {
		if ( ! isset( $seen[ $u->ID ] ) ) {
			$seen[ $u->ID ] = true;
			$users[]        = $u;
		}
	}
	usort( $users, function ( $a, $b ) {
		return strcasecmp( $a->display_name, $b->display_name );
	} );

	?>
	<div id="cattle-owner-wrap" style="display:none; margin-left:6px;">
		<select name="cattle_new_owner" id="cattle_new_owner">
			<option value=""><?php esc_html_e( '— Select Owner —', 'tailwind-acf' ); ?></option>
			<option value="0"><?php esc_html_e( 'No Owner (Unassigned)', 'tailwind-acf' ); ?></option>
			<?php foreach ( $users as $user ) : ?>
				<option value="<?php echo esc_attr( $user->ID ); ?>">
					<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<script>
	(function(){
		var wrap   = document.getElementById('cattle-owner-wrap');
		var select = document.getElementById('bulk-action-selector-top');
		if ( ! wrap || ! select ) return;

		// Move the owner dropdown next to the bulk action Apply button.
		var applyBtn = document.getElementById('doaction');
		if ( applyBtn ) {
			applyBtn.parentNode.insertBefore( wrap, applyBtn );
		}

		function toggle() {
			wrap.style.display = ( select.value === 'change_owner' ) ? 'inline-block' : 'none';
		}
		select.addEventListener( 'change', toggle );
		toggle();
	})();
	</script>
	<?php
}
add_action( 'admin_footer', 'tailwind_cattle_owner_dropdown' );

/**
 * Render filter dropdowns (Stud, Sex, Grade) on the cattle admin list.
 *
 * @param string $which Top or bottom.
 */
function tailwind_cattle_admin_filters( $post_type, $which ) {
	if ( 'cattle_registration' !== $post_type || 'top' !== $which ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only filter.
	$current_stud  = isset( $_GET['filter_stud'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_stud'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_sex   = isset( $_GET['filter_sex'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_sex'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_grade = isset( $_GET['filter_grade'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_grade'] ) ) : '';

	// Get distinct stud names from the database.
	global $wpdb;
	$studs = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s AND pm.meta_value != '' AND p.post_type = %s
			ORDER BY pm.meta_value ASC",
			'stud_name',
			'cattle_registration'
		)
	);

	// Stud dropdown.
	?>
	<select name="filter_stud">
		<option value=""><?php esc_html_e( 'All Studs', 'tailwind-acf' ); ?></option>
		<?php foreach ( $studs as $stud ) : ?>
			<option value="<?php echo esc_attr( $stud ); ?>" <?php selected( $current_stud, $stud ); ?>>
				<?php echo esc_html( $stud ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php

	// Sex dropdown.
	$sex_options = tailwind_cattle_get_sex_options();
	?>
	<select name="filter_sex">
		<option value=""><?php esc_html_e( 'All Sex', 'tailwind-acf' ); ?></option>
		<?php foreach ( $sex_options as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_sex, $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php

	// Grade dropdown.
	$grade_options = tailwind_cattle_get_grade_options();
	?>
	<select name="filter_grade">
		<option value=""><?php esc_html_e( 'All Grades', 'tailwind-acf' ); ?></option>
		<?php foreach ( $grade_options as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_grade, $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'tailwind_cattle_admin_filters', 10, 2 );

/**
 * Apply admin filters and extend search to registration_number and tattoo_number.
 *
 * @param WP_Query $query The current query.
 */
function tailwind_cattle_admin_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'cattle_registration' !== ( $query->get( 'post_type' ) ) ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' ) ?: array();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list filter.
	$filter_stud = isset( $_GET['filter_stud'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_stud'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$filter_sex = isset( $_GET['filter_sex'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_sex'] ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$filter_grade = isset( $_GET['filter_grade'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_grade'] ) ) : '';

	if ( $filter_stud ) {
		$meta_query[] = array(
			'key'     => 'stud_name',
			'value'   => $filter_stud,
			'compare' => '=',
		);
	}

	if ( $filter_sex ) {
		$meta_query[] = array(
			'key'     => 'sex',
			'value'   => $filter_sex,
			'compare' => '=',
		);
	}

	if ( $filter_grade ) {
		$meta_query[] = array(
			'key'     => 'grade',
			'value'   => $filter_grade,
			'compare' => '=',
		);
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}

	// Extend the default search to include registration_number and tattoo_number.
	$search = $query->get( 's' );
	if ( $search ) {
		$query->set( 's', '' );
		$query->set( 'tailwind_cattle_search', $search );
	}
}
add_action( 'pre_get_posts', 'tailwind_cattle_admin_filter_query' );

/**
 * Modify the WHERE clause to search registration_number and tattoo_number in addition to post title.
 *
 * @param string   $where The WHERE clause.
 * @param WP_Query $query The current query.
 * @return string
 */
function tailwind_cattle_admin_search_where( $where, $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return $where;
	}

	$search = $query->get( 'tailwind_cattle_search' );
	if ( ! $search ) {
		return $where;
	}

	global $wpdb;
	$like = '%' . $wpdb->esc_like( $search ) . '%';

	$where .= $wpdb->prepare(
		" AND ({$wpdb->posts}.post_title LIKE %s
		OR {$wpdb->posts}.ID IN (
			SELECT post_id FROM {$wpdb->postmeta}
			WHERE (meta_key = 'registration_number' OR meta_key = 'tattoo_number')
			AND meta_value LIKE %s
		))",
		$like,
		$like
	);

	return $where;
}
add_filter( 'posts_where', 'tailwind_cattle_admin_search_where', 10, 2 );

/**
 * Handle the "Change Owner" bulk action.
 *
 * @param string $redirect_url The redirect URL.
 * @param string $action       The bulk action being taken.
 * @param array  $post_ids     The post IDs to act on.
 * @return string
 */
function tailwind_cattle_handle_bulk_change_owner( $redirect_url, $action, $post_ids ) {
	if ( 'change_owner' !== $action ) {
		return $redirect_url;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return $redirect_url;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WP handles bulk action nonce.
	$new_owner = isset( $_GET['cattle_new_owner'] ) ? intval( $_GET['cattle_new_owner'] ) : '';

	if ( '' === $new_owner && ! isset( $_GET['cattle_new_owner'] ) ) {
		return add_query_arg( 'owner_error', '1', $redirect_url );
	}

	$updated = 0;
	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );
		if ( $post && 'cattle_registration' === $post->post_type ) {
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_author' => $new_owner,
				)
			);
			$updated++;
		}
	}

	return add_query_arg( 'owner_updated', $updated, $redirect_url );
}
add_filter( 'handle_bulk_actions-edit-cattle_registration', 'tailwind_cattle_handle_bulk_change_owner', 10, 3 );

/**
 * Show admin notice after bulk owner change.
 */
function tailwind_cattle_bulk_owner_notices() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-cattle_registration' !== $screen->id ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only.
	if ( isset( $_GET['owner_updated'] ) ) {
		$count = absint( $_GET['owner_updated'] );
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of posts updated */
					_n( '%d registration updated.', '%d registrations updated.', $count, 'tailwind-acf' ),
					$count
				)
			)
		);
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['owner_error'] ) ) {
		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html__( 'Please select an owner from the dropdown before applying the "Change Owner" action.', 'tailwind-acf' )
		);
	}
}
add_action( 'admin_notices', 'tailwind_cattle_bulk_owner_notices' );

/**
 * Render a pedigree box for an ancestor.
 *
 * @param array|null $ancestor Ancestor data (id, name, regn, url) or null.
 * @param string     $label    Relationship label (e.g., "Sire", "Dam").
 */
function tailwind_render_pedigree_box( $ancestor, $label ) {
	if ( ! $ancestor ) {
		?>
		<div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-3 text-center">
			<p class="text-xs font-medium text-slate-400"><?php echo esc_html( $label ); ?></p>
			<p class="text-sm text-slate-400 italic"><?php esc_html_e( 'Unknown', 'tailwind-acf' ); ?></p>
		</div>
		<?php
		return;
	}

	$tag   = $ancestor['url'] ? 'a' : 'div';
	$attrs = $ancestor['url'] ? ' href="' . esc_url( $ancestor['url'] ) . '"' : '';
	$hover = $ancestor['url'] ? ' hover:border-green-300 hover:shadow-md transition' : '';
	$link_class = $ancestor['url'] ? ' cursor-pointer' : '';

	?>
	<<?php echo $tag; ?><?php echo $attrs; ?> class="block rounded-lg border border-slate-200 bg-white p-3<?php echo esc_attr( $hover . $link_class ); ?>">
		<p class="text-xs font-medium text-slate-400"><?php echo esc_html( $label ); ?></p>
		<p class="text-sm font-semibold <?php echo $ancestor['url'] ? 'text-green-700' : 'text-slate-900'; ?>">
			<?php echo esc_html( $ancestor['name'] ?: '—' ); ?>
		</p>
		<?php if ( $ancestor['regn'] ) : ?>
			<p class="text-xs font-mono text-slate-500"><?php echo esc_html( $ancestor['regn'] ); ?></p>
		<?php endif; ?>
	</<?php echo $tag; ?>>
	<?php
}

/**
 * Output JSON-LD structured data for cattle registration pages.
 */
function tailwind_cattle_structured_data() {
	if ( ! is_singular( 'cattle_registration' ) ) {
		return;
	}

	$post_id   = get_the_ID();
	$calf_name = get_field( 'calf_name', $post_id );
	$tattoo    = get_field( 'tattoo_number', $post_id );
	$grade     = get_field( 'grade', $post_id );
	$sex       = get_field( 'sex', $post_id );
	$dob       = get_field( 'date_of_birth', $post_id );

	$grade_labels = tailwind_cattle_get_grade_labels();
	$sex_labels   = tailwind_cattle_get_sex_labels();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Thing',
		'name'        => $calf_name,
		'identifier'  => $tattoo,
		'description' => sprintf(
			/* translators: 1: grade, 2: sex, 3: breed */
			__( '%1$s %2$s Murray Grey cattle', 'tailwind-acf' ),
			$grade_labels[ $grade ] ?? $grade,
			$sex_labels[ $sex ] ?? $sex
		),
		'url'         => get_permalink( $post_id ),
	);

	if ( $dob ) {
		$schema['dateCreated'] = $dob;
	}

	$author = get_userdata( get_post_field( 'post_author', $post_id ) );
	if ( $author ) {
		$schema['creator'] = array(
			'@type' => 'Person',
			'name'  => $author->display_name,
		);
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'tailwind_cattle_structured_data' );
