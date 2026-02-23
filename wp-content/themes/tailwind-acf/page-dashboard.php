<?php

/**
 * Template Name: Dashboard
 * Template Post Type: page
 *
 * Template for the member dashboard page.
 *
 * @package Tailwind_ACF
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! is_user_logged_in()) {
	$permalink = get_permalink(get_queried_object_id());
	wp_safe_redirect(wp_login_url($permalink));
	exit;
}

get_header();

$current_user = wp_get_current_user();
$status       = tailwind_member_get_status($current_user->ID);
$notices      = array();
$success      = array();

// Pending account notice.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only, no state change.
$is_pending_param = isset($_GET['pending']) && sanitize_text_field(wp_unslash($_GET['pending']));
if ($is_pending_param || TAILWIND_MEMBER_STATUS_PENDING === $status) {
	$notices[] = __('Thanks for registering! An administrator will review your account shortly. You will receive an email once you are approved.', 'tailwind-acf');
}

// Access denied notice.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only, no state change.
if (isset($_GET['denied']) && '1' === sanitize_text_field(wp_unslash($_GET['denied']))) {
	$notices[] = __('You do not have permission to view that page.', 'tailwind-acf');
}

// Cattle submission success notice.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only, no state change.
if (isset($_GET['cattle_submitted']) && '1' === sanitize_text_field(wp_unslash($_GET['cattle_submitted']))) {
	$success[] = __('Your cattle registration has been submitted successfully! An administrator will review it shortly.', 'tailwind-acf');
}

// Cattle update success notice.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only, no state change.
if (isset($_GET['cattle_updated']) && '1' === sanitize_text_field(wp_unslash($_GET['cattle_updated']))) {
	$success[] = __('Your cattle registration has been updated successfully.', 'tailwind-acf');
}

// Get registration form page URL.
$register_cattle_page = get_page_by_path('register-cattle');
$register_cattle_url  = $register_cattle_page ? get_permalink($register_cattle_page) : '';

// Get user's cattle registrations.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cattle_page = isset( $_GET['cattle_page'] ) ? max( 1, absint( $_GET['cattle_page'] ) ) : 1;
$per_page    = 12;

$user_registrations  = array();
$total_registrations = 0;
$total_pages         = 0;
if ( TAILWIND_MEMBER_STATUS_APPROVED === $status && post_type_exists( 'cattle_registration' ) ) {
	$user_query = new WP_Query(
		array(
			'post_type'      => 'cattle_registration',
			'author'         => $current_user->ID,
			'post_status'    => array( 'pending', 'publish' ),
			'posts_per_page' => $per_page,
			'paged'          => $cattle_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$user_registrations  = $user_query->posts;
	$total_registrations = $user_query->found_posts;
	$total_pages         = $user_query->max_num_pages;
}
?>

<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
		<main id="primary" class="site-main bg-slate-50">
			<div class="mx-auto max-w-5xl px-6 py-16 sm:px-10 lg:px-12">
				<header class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
					<div>
					<h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
						<?php the_title(); ?>
					</h1>
						<p class="mt-1 text-base text-slate-600">
							<?php
							printf(
								/* translators: 1: display name, 2: registration date */
								esc_html__( 'Welcome back, %1$s. Member since %2$s.', 'tailwind-acf' ),
								esc_html( $current_user->display_name ),
								esc_html( date_i18n( 'F Y', strtotime( $current_user->user_registered ) ) )
							);
							?>
						</p>
					</div>
					<?php if (TAILWIND_MEMBER_STATUS_APPROVED === $status && $register_cattle_url) : ?>
						<a
							href="<?php echo esc_url($register_cattle_url); ?>"
							class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
							<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
							</svg>
							<?php esc_html_e('Register Cattle', 'tailwind-acf'); ?>
						</a>
					<?php endif; ?>
				</header>

				<?php if ( $success ) : ?>
					<?php foreach ( $success as $message ) : ?>
						<div class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-start gap-3">
							<svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span><?php echo esc_html( $message ); ?></span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( $notices ) : ?>
					<?php foreach ( $notices as $notice ) : ?>
						<div class="mb-6 rounded-lg border-l-4 border-yellow-500 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 flex items-start gap-3">
							<svg class="h-5 w-5 flex-shrink-0 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
							</svg>
							<span><?php echo esc_html( $notice ); ?></span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if (TAILWIND_MEMBER_STATUS_APPROVED === $status) : ?>
					<?php
					$content = get_the_content();
					if (trim($content)) :
					?>
						<div class="prose prose-slate max-w-none mb-12">
						<?php the_content(); ?>
					</div>
					<?php endif; ?>

					<?php
					// Count pending registrations.
					$pending_count = 0;
					foreach ( $user_registrations as $reg ) {
						if ( 'pending' === $reg->post_status ) {
							$pending_count++;
						}
					}
					?>

					<!-- Cattle Registrations Section -->
					<section class="mt-8">
						<div class="flex items-center justify-between mb-6">
							<h2 class="text-xl font-semibold text-slate-900">
								<?php esc_html_e( 'My Cattle Registrations', 'tailwind-acf' ); ?>
							</h2>
							<?php if ( $total_registrations > 0 ) : ?>
								<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
									<?php
									printf(
										/* translators: %d: number of registrations */
										esc_html( _n( '%d registration', '%d registrations', $total_registrations, 'tailwind-acf' ) ),
										$total_registrations
									);
									?>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( empty( $user_registrations ) ) : ?>
							<div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
								<svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
								</svg>
								<h3 class="mt-4 text-base font-medium text-slate-900">
									<?php esc_html_e( 'No registrations yet', 'tailwind-acf' ); ?>
								</h3>
								<p class="mt-1 text-sm text-slate-600">
									<?php esc_html_e( 'Get started by registering your first cattle.', 'tailwind-acf' ); ?>
								</p>
								<?php if ( $register_cattle_url ) : ?>
									<a
										href="<?php echo esc_url( $register_cattle_url ); ?>"
										class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
										<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
										</svg>
										<?php esc_html_e( 'Register Cattle', 'tailwind-acf' ); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<?php
							$sex_labels    = tailwind_cattle_get_sex_labels();
							$colour_labels = tailwind_cattle_get_colour_labels();
							?>
							<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
								<?php foreach ( $user_registrations as $registration ) : ?>
									<?php
									$calf_name = get_field( 'calf_name', $registration->ID );
									$tattoo    = get_field( 'tattoo_number', $registration->ID );
									$sex       = get_field( 'sex', $registration->ID );
									$grade     = get_field( 'grade', $registration->ID );
									$colour    = get_field( 'colour', $registration->ID );
									$dob       = get_field( 'date_of_birth', $registration->ID );
									?>
									<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
										<div class="flex items-start justify-between gap-3 mb-3">
											<div class="min-w-0">
												<h3 class="text-base font-semibold text-slate-900 truncate">
													<?php echo esc_html( $calf_name ); ?>
												</h3>
												<p class="text-sm font-mono text-slate-500">
													<?php echo esc_html( $tattoo ); ?>
												</p>
											</div>
											<?php
											if ( function_exists( 'tailwind_get_cattle_status_badge' ) ) {
												echo tailwind_get_cattle_status_badge( $registration->post_status );
											}
											?>
										</div>
										<dl class="grid grid-cols-2 gap-2 text-sm mb-4">
											<div>
												<dt class="text-slate-500"><?php esc_html_e( 'Grade', 'tailwind-acf' ); ?></dt>
												<dd class="font-medium text-slate-900"><?php echo esc_html( $grade ); ?></dd>
											</div>
											<div>
												<dt class="text-slate-500"><?php esc_html_e( 'Sex', 'tailwind-acf' ); ?></dt>
												<dd class="font-medium text-slate-900"><?php echo esc_html( $sex_labels[ $sex ] ?? $sex ); ?></dd>
											</div>
											<?php if ( $colour ) : ?>
											<div>
												<dt class="text-slate-500"><?php esc_html_e( 'Colour', 'tailwind-acf' ); ?></dt>
												<dd class="font-medium text-slate-900"><?php echo esc_html( $colour_labels[ $colour ] ?? $colour ); ?></dd>
											</div>
											<?php endif; ?>
											<?php if ( $dob ) : ?>
											<div>
												<dt class="text-slate-500"><?php esc_html_e( 'Born', 'tailwind-acf' ); ?></dt>
												<dd class="font-medium text-slate-900"><?php echo esc_html( date_i18n( 'M Y', strtotime( $dob ) ) ); ?></dd>
											</div>
											<?php endif; ?>
										</dl>
										<div class="flex gap-3 text-sm font-medium">
											<?php if ( 'publish' === $registration->post_status ) : ?>
												<a href="<?php echo esc_url( get_permalink( $registration ) ); ?>" class="text-brand hover:text-brand-dark transition">
													<?php esc_html_e( 'View', 'tailwind-acf' ); ?>
												</a>
											<?php elseif ( 'pending' === $registration->post_status && $register_cattle_url ) : ?>
												<a href="<?php echo esc_url( add_query_arg( 'edit', $registration->ID, $register_cattle_url ) ); ?>" class="text-brand hover:text-brand-dark transition">
													<?php esc_html_e( 'Edit', 'tailwind-acf' ); ?>
												</a>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>

							<?php if ( $total_pages > 1 ) : ?>
								<nav class="mt-8 flex justify-center gap-2" aria-label="<?php esc_attr_e( 'Pagination', 'tailwind-acf' ); ?>">
									<?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
										<?php
										$page_url   = add_query_arg( 'cattle_page', $i, get_permalink() );
										$is_current = ( $i === $cattle_page );
										?>
										<a
											href="<?php echo esc_url( $page_url ); ?>"
											class="inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-lg border px-3 text-sm font-medium transition <?php echo $is_current ? 'bg-green-700 border-green-700 text-white' : 'border-slate-200 text-slate-700 hover:bg-slate-50'; ?>"
											<?php if ( $is_current ) : ?>aria-current="page"<?php endif; ?>
										>
											<?php echo esc_html( $i ); ?>
										</a>
									<?php endfor; ?>
								</nav>
							<?php endif; ?>
						<?php endif; ?>

						<?php if ($pending_count > 0) : ?>
							<!-- Pending Registrations Payment Info -->
							<div class="mt-8 rounded-xl border border-blue-200 bg-blue-50 p-6">
								<div class="flex gap-4">
									<div class="flex-shrink-0">
										<svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
										</svg>
									</div>
									<div class="flex-1">
										<h3 class="text-base font-semibold text-blue-900 mb-2">
											<?php
											printf(
												/* translators: %d: number of pending registrations */
												esc_html(_n(
													'You have %d registration pending approval',
													'You have %d registrations pending approval',
													$pending_count,
													'tailwind-acf'
												)),
												$pending_count
											);
											?>
										</h3>
										<div class="text-sm text-blue-800 space-y-3">
											<p>
												<?php esc_html_e('Mail fees ($40/registration) to MGAA Registrar PO Box 1053 Bendigo 3552 or email', 'tailwind-acf'); ?>
												<a href="mailto:rod@rgbadmin.com.au" class="font-medium underline hover:text-blue-900">rod@rgbadmin.com.au</a>.
											</p>
											<div class="bg-white/60 rounded-lg p-4 font-mono text-sm">
												<p class="font-semibold text-blue-900 mb-1"><?php esc_html_e('Bank Details:', 'tailwind-acf'); ?></p>
												<p><?php esc_html_e('MURRAY GREY ASSOCIATION AUSTRALIA', 'tailwind-acf'); ?></p>
												<p><?php esc_html_e('BSB: 633 000', 'tailwind-acf'); ?></p>
												<p><?php esc_html_e('ACC: 177 280 963', 'tailwind-acf'); ?></p>
												<p class="mt-2 text-blue-700"><?php esc_html_e('Reference: Member Number or Surname/Stud Name', 'tailwind-acf'); ?></p>
											</div>
											<p class="text-blue-700">
												<?php esc_html_e('Once fees are paid and confirmed, your listing will be made public and searchable. Until then, the status will remain "Pending Approval".', 'tailwind-acf'); ?>
											</p>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</section>
				<?php else : ?>
					<div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
						<svg class="mx-auto h-12 w-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<h3 class="mt-4 text-base font-medium text-slate-900">
							<?php esc_html_e('Account Pending Approval', 'tailwind-acf'); ?>
						</h3>
						<p class="mt-2 text-sm text-slate-600 max-w-md mx-auto">
							<?php esc_html_e('Your account is being reviewed by an administrator. You will be able to access the dashboard and register cattle once your account is approved.', 'tailwind-acf'); ?>
					</p>
					</div>
				<?php endif; ?>
			</div>
		</main>
	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
