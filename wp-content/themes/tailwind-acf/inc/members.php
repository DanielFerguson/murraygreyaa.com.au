<?php

/**
 * Member experience helpers.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TAILWIND_MEMBER_STATUS_META' ) ) {
	define( 'TAILWIND_MEMBER_STATUS_META', 'tailwind_member_status' );
}

if ( ! defined( 'TAILWIND_REQUIRED_CAP_META' ) ) {
	define( 'TAILWIND_REQUIRED_CAP_META', 'tailwind_required_capability' );
}

if ( ! defined( 'TAILWIND_MEMBER_STATUS_PENDING' ) ) {
	define( 'TAILWIND_MEMBER_STATUS_PENDING', 'pending' );
}

if ( ! defined( 'TAILWIND_MEMBER_STATUS_APPROVED' ) ) {
	define( 'TAILWIND_MEMBER_STATUS_APPROVED', 'approved' );
}

/**
 * Retrieve the dashboard URL, falling back to /dashboard/.
 *
 * @return string
 */
function tailwind_member_get_dashboard_url() {
	$dashboard = get_page_by_path( 'dashboard' );

	if ( $dashboard instanceof WP_Post ) {
		return get_permalink( $dashboard );
	}

	return home_url( '/dashboard/' );
}

/**
 * Determine whether a redirect target points to the WordPress admin area.
 *
 * @param string $url Redirect candidate.
 * @return bool
 */
function tailwind_member_is_admin_target( $url ) {
	if ( ! $url ) {
		return false;
	}

	$admin_base = trailingslashit( admin_url() );

	if ( 0 === strpos( $url, $admin_base ) ) {
		return true;
	}

	$parsed = wp_parse_url( $url );

	if ( empty( $parsed['host'] ) && isset( $parsed['path'] ) ) {
		$path = '/' . ltrim( $parsed['path'], '/' );

		if ( 0 === strpos( $path, '/wp-admin' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Mark newly registered users as pending and ensure admins are notified.
 *
 * @param int $user_id The newly created user ID.
 */
function tailwind_member_handle_registration( $user_id ) {
	update_user_meta( $user_id, TAILWIND_MEMBER_STATUS_META, TAILWIND_MEMBER_STATUS_PENDING );

	if ( apply_filters( 'tailwind_member_send_admin_notice', false, $user_id ) ) {
		// Core already dispatches the default notifications; this opt-in hook enables custom follow-ups.
		wp_new_user_notification( $user_id, null, 'admin' );
	}
}
add_action( 'user_register', 'tailwind_member_handle_registration', 10, 1 );

/**
 * Redirect new registrants to the dashboard with a pending notice.
 *
 * @param string $redirect_to Original redirect location.
 * @return string
 */
function tailwind_member_registration_redirect( $redirect_to ) {
	return add_query_arg( 'pending', 1, tailwind_member_get_dashboard_url() );
}
add_filter( 'registration_redirect', 'tailwind_member_registration_redirect' );

/**
 * Prevent pending users from logging in until approved.
 *
 * @param WP_User|WP_Error $user Authenticated user object or error.
 * @return WP_User|WP_Error
 */
function tailwind_member_block_pending_login( $user ) {
	if ( ! $user instanceof WP_User ) {
		return $user;
	}

	$status = tailwind_member_get_status( $user->ID );

	if ( TAILWIND_MEMBER_STATUS_PENDING === $status ) {
		return new WP_Error(
			'tailwind_member_pending',
			__( 'Your account is awaiting approval by an administrator.', 'tailwind-acf' )
		);
	}

	return $user;
}
add_filter( 'wp_authenticate_user', 'tailwind_member_block_pending_login' );

/**
 * Ensure approved members land on the dashboard after login.
 *
 * @param string           $redirect_to           Target URL.
 * @param string           $requested_redirect_to User requested URL.
 * @param WP_User|WP_Error $user                  Authenticated user or error.
 * @return string
 */
function tailwind_member_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	if ( ! $user instanceof WP_User ) {
		return $redirect_to;
	}

	$status = tailwind_member_get_status( $user->ID );

	if ( TAILWIND_MEMBER_STATUS_APPROVED !== $status ) {
		return $redirect_to;
	}

	if ( user_can( $user, 'manage_options' ) ) {
		return $redirect_to;
	}

	$requested_redirect_to = $requested_redirect_to ? wp_unslash( $requested_redirect_to ) : '';

	if ( $requested_redirect_to && ! tailwind_member_is_admin_target( $requested_redirect_to ) ) {
		$validated = wp_validate_redirect( $requested_redirect_to, false );

		if ( $validated ) {
			return $validated;
		}
	}

	return tailwind_member_get_dashboard_url();
}
add_filter( 'login_redirect', 'tailwind_member_login_redirect', PHP_INT_MAX, 3 );

/**
 * Force dashboard redirection immediately after login for members.
 *
 * @param string  $user_login Username.
 * @param WP_User $user       User object.
 */
function tailwind_member_force_dashboard_after_login( $user_login, $user ) {
	if ( ! $user instanceof WP_User ) {
		return;
	}

	if ( user_can( $user, 'manage_options' ) ) {
		return;
	}

	if ( TAILWIND_MEMBER_STATUS_APPROVED !== tailwind_member_get_status( $user->ID ) ) {
		return;
	}

	$requested = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';

	if ( $requested ) {
		$validated = wp_validate_redirect( $requested, false );

		if ( $validated && ! tailwind_member_is_admin_target( $validated ) ) {
			wp_safe_redirect( $validated );
			exit;
		}
	}

	wp_safe_redirect( tailwind_member_get_dashboard_url() );
	exit;
}
add_action( 'wp_login', 'tailwind_member_force_dashboard_after_login', 20, 2 );

/**
 * Prevent non-editors from landing on the wp-admin dashboard after login.
 */
function tailwind_member_redirect_admin_dashboard() {
	if ( wp_doing_ajax() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( current_user_can( 'edit_posts' ) ) {
		return;
	}

	$status = tailwind_member_get_status( get_current_user_id() );

	if ( TAILWIND_MEMBER_STATUS_APPROVED !== $status ) {
		return;
	}

	global $pagenow;

	if ( 'index.php' !== $pagenow ) {
		return;
	}

	$dashboard_url = tailwind_member_get_dashboard_url();

	if ( ! $dashboard_url ) {
		return;
	}

	wp_safe_redirect( $dashboard_url );
	exit;
}
add_action( 'admin_init', 'tailwind_member_redirect_admin_dashboard' );

/**
 * Display member status in the Users list table.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function tailwind_member_register_status_column( $columns ) {
	$columns['tailwind_member_status'] = __( 'Member Status', 'tailwind-acf' );
	return $columns;
}
add_filter( 'manage_users_columns', 'tailwind_member_register_status_column' );

/**
 * Render the custom member status column.
 *
 * @param string $output   Current column output.
 * @param string $column   Column name.
 * @param int    $user_id  User ID.
 * @return string
 */
function tailwind_member_render_status_column( $output, $column, $user_id ) {
	if ( 'tailwind_member_status' !== $column ) {
		return $output;
	}

	$status = tailwind_member_get_status( $user_id );

	if ( TAILWIND_MEMBER_STATUS_PENDING === $status ) {
		return __( 'Pending Approval', 'tailwind-acf' );
	}

	return __( 'Approved', 'tailwind-acf' );
}
add_filter( 'manage_users_custom_column', 'tailwind_member_render_status_column', 10, 3 );

/**
 * Append an "Approve member" link to pending users in the list table.
 *
 * @param array   $actions Existing row actions.
 * @param WP_User $user    Current user row.
 * @return array
 */
function tailwind_member_user_row_actions( $actions, $user ) {
	if ( ! current_user_can( 'promote_users' ) ) {
		return $actions;
	}

	$status = tailwind_member_get_status( $user->ID );

	if ( TAILWIND_MEMBER_STATUS_PENDING !== $status ) {
		return $actions;
	}

	$url = add_query_arg(
		array(
			'action'  => 'tailwind_approve_member',
			'user_id' => $user->ID,
		),
		admin_url( 'admin-post.php' )
	);

	$actions['tailwind-approve-member'] = sprintf(
		'<a href="%s">%s</a>',
		esc_url(
			wp_nonce_url(
				$url,
				'tailwind_approve_member_' . $user->ID
			)
		),
		esc_html__( 'Approve member', 'tailwind-acf' )
	);

	return $actions;
}
add_filter( 'user_row_actions', 'tailwind_member_user_row_actions', 10, 2 );

/**
 * Register admin-post handler for member approval.
 */
add_action( 'admin_post_tailwind_approve_member', 'tailwind_member_handle_approval' );

/**
 * Handle approval requests fired from the Users list table.
 */
function tailwind_member_handle_approval() {
	if ( ! current_user_can( 'promote_users' ) ) {
		wp_die( __( 'You are not allowed to approve members.', 'tailwind-acf' ) );
	}

	$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;

	if ( ! $user_id ) {
		wp_die( __( 'Missing user ID.', 'tailwind-acf' ) );
	}

	check_admin_referer( 'tailwind_approve_member_' . $user_id );

	update_user_meta( $user_id, TAILWIND_MEMBER_STATUS_META, TAILWIND_MEMBER_STATUS_APPROVED );

	/**
	 * Allow follow-up actions (notifications etc.) when a member is approved.
	 *
	 * @param int $user_id Approved user ID.
	 */
	do_action( 'tailwind_member_approved', $user_id );

	$redirect = add_query_arg(
		array(
			'update'        => 'approved',
			'tailwind_user' => $user_id,
		),
		admin_url( 'users.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Get the current member status.
 *
 * @param int $user_id User ID.
 * @return string
 */
function tailwind_member_get_status( $user_id ) {
	$status = get_user_meta( $user_id, TAILWIND_MEMBER_STATUS_META, true );

	if ( ! $status ) {
		return TAILWIND_MEMBER_STATUS_APPROVED;
	}

	return $status;
}

/**
 * Register the access control meta box for pages.
 */
function tailwind_member_add_access_meta_box() {
	add_meta_box(
		'tailwind-member-access',
		__( 'Member Access', 'tailwind-acf' ),
		'tailwind_member_render_access_meta_box',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'tailwind_member_add_access_meta_box' );

/**
 * Render the member access meta box.
 *
 * @param WP_Post $post Current post object.
 */
function tailwind_member_render_access_meta_box( $post ) {
	wp_nonce_field( 'tailwind_member_access_meta_box', 'tailwind_member_access_nonce' );

	$requirement = get_post_meta( $post->ID, TAILWIND_REQUIRED_CAP_META, true );
	$options     = array(
		''                   => __( 'Public (no restriction)', 'tailwind-acf' ),
		'tailwind-member'    => __( 'Approved members', 'tailwind-acf' ),
		'edit_posts'         => __( 'Authors and above', 'tailwind-acf' ),
		'edit_pages'         => __( 'Editors and above', 'tailwind-acf' ),
		'manage_options'     => __( 'Administrators only', 'tailwind-acf' ),
	);
	?>
	<p>
		<label for="tailwind-required-capability" class="screen-reader-text">
			<?php esc_html_e( 'Select the minimum access requirement for this page.', 'tailwind-acf' ); ?>
		</label>
		<select name="tailwind_required_capability" id="tailwind-required-capability" class="widefat">
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $requirement, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="description">
		<?php esc_html_e( 'Choose who can view this page. Members marked as pending will always be blocked.', 'tailwind-acf' ); ?>
	</p>
	<?php
}

/**
 * Persist the selected access requirement.
 *
 * @param int $post_id Post ID.
 */
function tailwind_member_save_access_meta_box( $post_id ) {
	if ( ! isset( $_POST['tailwind_member_access_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['tailwind_member_access_nonce'], 'tailwind_member_access_meta_box' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed = array( '', 'tailwind-member', 'edit_posts', 'edit_pages', 'manage_options' );
	$value   = isset( $_POST['tailwind_required_capability'] ) ? sanitize_text_field( wp_unslash( $_POST['tailwind_required_capability'] ) ) : '';

	if ( ! in_array( $value, $allowed, true ) ) {
		$value = '';
	}

	if ( '' === $value ) {
		delete_post_meta( $post_id, TAILWIND_REQUIRED_CAP_META );
		return;
	}

	update_post_meta( $post_id, TAILWIND_REQUIRED_CAP_META, $value );
}
add_action( 'save_post_page', 'tailwind_member_save_access_meta_box' );

/**
 * Enforce access restrictions on protected pages.
 */
function tailwind_member_enforce_access() {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	$post_id = get_queried_object_id();

	if ( ! $post_id ) {
		return;
	}

	$requirement = get_post_meta( $post_id, TAILWIND_REQUIRED_CAP_META, true );

	if ( ! $requirement && is_page( 'dashboard' ) ) {
		$requirement = 'tailwind-member';
	}

	$requirement = apply_filters( 'tailwind_member_required_capability', $requirement, $post_id );

	if ( ! $requirement ) {
		return;
	}

	if ( 'tailwind-member' === $requirement ) {
		if ( ! is_user_logged_in() ) {
			$login_url = add_query_arg(
				'tailwind-membership',
				'required',
				wp_login_url( get_permalink( $post_id ) )
			);
			wp_safe_redirect( $login_url );
			exit;
		}

		$status = tailwind_member_get_status( get_current_user_id() );

		if ( TAILWIND_MEMBER_STATUS_APPROVED !== $status ) {
			wp_safe_redirect(
				add_query_arg(
					'pending',
					1,
					tailwind_member_get_dashboard_url()
				)
			);
			exit;
		}

		return;
	}

	if ( ! is_user_logged_in() ) {
		$login_url = add_query_arg(
			'tailwind-membership',
			'required',
			wp_login_url( get_permalink( $post_id ) )
		);
		wp_safe_redirect( $login_url );
		exit;
	}

	if ( current_user_can( $requirement ) ) {
		return;
	}

	wp_safe_redirect(
		add_query_arg(
			'denied',
			1,
			tailwind_member_get_dashboard_url()
		)
	);
	exit;
}
add_action( 'template_redirect', 'tailwind_member_enforce_access' );

/**
 * Surface a hint on the login screen when membership authentication is required.
 *
 * @param string $message Existing login message markup.
 * @return string
 */
function tailwind_member_login_info_message( $message ) {
	if ( isset( $_GET['tailwind-membership'] ) && 'required' === $_GET['tailwind-membership'] ) {
		$notice  = '<p class="message">';
		$notice .= esc_html__( 'Please log in with an approved member account to continue.', 'tailwind-acf' );
		$notice .= '</p>';

		$message .= $notice;
	}

	return $message;
}
add_filter( 'login_message', 'tailwind_member_login_info_message' );

/**
 * Swap Login/Register menu links for Dashboard/Logout when appropriate.
 *
 * @param array    $items Menu items.
 * @param stdClass $args  Menu arguments.
 * @return array
 */
function tailwind_member_adjust_auth_menu_links( $items, $args ) {
	if ( ! is_user_logged_in() ) {
		return $items;
	}

	$register_targets = array_filter(
		array(
			wp_registration_url(),
			add_query_arg( 'action', 'register', wp_login_url() ),
		)
	);

	$login_targets = array(
		wp_login_url(),
	);

	foreach ( $items as $item ) {
		$item_url = untrailingslashit( $item->url );
		$title    = sanitize_title( $item->title );

		foreach ( $register_targets as $register_url ) {
			if ( untrailingslashit( $register_url ) === $item_url ) {
				$item->title      = __( 'Dashboard', 'tailwind-acf' );
				$item->post_title = $item->title;
				$item->url        = tailwind_member_get_dashboard_url();
				$item->attr_title = '';
				$item->description = '';
				$item->target     = '';
				$item->xfn        = '';
				$item->classes[]  = 'menu-item-dashboard';
				continue 2;
			}
		}

		if ( in_array( $title, array( 'register', 'sign-up', 'signup' ), true ) ) {
			$item->title      = __( 'Dashboard', 'tailwind-acf' );
			$item->post_title = $item->title;
			$item->url        = tailwind_member_get_dashboard_url();
			$item->attr_title = '';
			$item->description = '';
			$item->target     = '';
			$item->xfn        = '';
			$item->classes[]  = 'menu-item-dashboard';
			continue;
		}

		foreach ( $login_targets as $login_url ) {
			if ( untrailingslashit( $login_url ) === $item_url ) {
				$item->title      = __( 'Log Out', 'tailwind-acf' );
				$item->post_title = $item->title;
				$item->url        = wp_logout_url( home_url() );
				$item->attr_title = '';
				$item->description = '';
				$item->target     = '';
				$item->xfn        = '';
				$item->classes[]  = 'menu-item-logout';
				continue 2;
			}
		}

		if ( in_array( $title, array( 'log-in', 'log_in', 'login' ), true ) ) {
			$item->title      = __( 'Log Out', 'tailwind-acf' );
			$item->post_title = $item->title;
			$item->url        = wp_logout_url( home_url() );
			$item->attr_title = '';
			$item->description = '';
			$item->target     = '';
			$item->xfn        = '';
			$item->classes[]  = 'menu-item-logout';
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'tailwind_member_adjust_auth_menu_links', 10, 2 );
