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
$user_registrations = array();
if (TAILWIND_MEMBER_STATUS_APPROVED === $status && post_type_exists('cattle_registration')) {
	$user_registrations = get_posts(
		array(
			'post_type'      => 'cattle_registration',
			'author'         => $current_user->ID,
			'post_status'    => array('pending', 'publish'),
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
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
								/* translators: %s: user display name */
								esc_html__('Welcome back, %s', 'tailwind-acf'),
								esc_html($current_user->display_name)
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

				<?php if ($success) : ?>
					<?php foreach ($success as $message) : ?>
						<div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 flex items-start gap-3">
							<svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<span><?php echo esc_html($message); ?></span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ($notices) : ?>
					<?php foreach ($notices as $notice) : ?>
						<div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 flex items-start gap-3">
							<svg class="h-5 w-5 flex-shrink-0 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
							</svg>
							<span><?php echo esc_html($notice); ?></span>
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
					foreach ($user_registrations as $reg) {
						if ('pending' === $reg->post_status) {
							$pending_count++;
						}
					}
					?>

					<!-- Cattle Registrations Section -->
					<section class="mt-8">
						<div class="flex items-center justify-between mb-6">
							<h2 class="text-xl font-semibold text-slate-900">
								<?php esc_html_e('My Cattle Registrations', 'tailwind-acf'); ?>
							</h2>
							<?php if (count($user_registrations) > 0) : ?>
								<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
									<?php
									printf(
										/* translators: %d: number of registrations */
										esc_html(_n('%d registration', '%d registrations', count($user_registrations), 'tailwind-acf')),
										count($user_registrations)
									);
									?>
								</span>
							<?php endif; ?>
						</div>

						<?php if (empty($user_registrations)) : ?>
							<div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
								<svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
								</svg>
								<h3 class="mt-4 text-base font-medium text-slate-900">
									<?php esc_html_e('No registrations yet', 'tailwind-acf'); ?>
								</h3>
								<p class="mt-1 text-sm text-slate-600">
									<?php esc_html_e('Get started by registering your first cattle.', 'tailwind-acf'); ?>
								</p>
								<?php if ($register_cattle_url) : ?>
									<a
										href="<?php echo esc_url($register_cattle_url); ?>"
										class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
										<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
										</svg>
										<?php esc_html_e('Register Cattle', 'tailwind-acf'); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
								<div class="overflow-x-auto">
									<table class="min-w-full divide-y divide-slate-200">
										<thead class="bg-slate-50">
											<tr>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Calf', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Tattoo', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Sex', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Grade', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Status', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
													<?php esc_html_e('Submitted', 'tailwind-acf'); ?>
												</th>
												<th scope="col" class="relative px-6 py-3">
													<span class="sr-only"><?php esc_html_e('Actions', 'tailwind-acf'); ?></span>
												</th>
											</tr>
										</thead>
										<tbody class="divide-y divide-slate-200 bg-white">
											<?php foreach ($user_registrations as $registration) : ?>
												<?php
												$calf_name = get_field('calf_name', $registration->ID);
												$tattoo    = get_field('tattoo_number', $registration->ID);
												$sex       = get_field('sex', $registration->ID);
												$grade     = get_field('grade', $registration->ID);
												$post_date = get_the_date('', $registration);

												$sex_labels = array(
													'M' => __('Male', 'tailwind-acf'),
													'F' => __('Female', 'tailwind-acf'),
													'S' => __('Steer', 'tailwind-acf'),
												);
												?>
												<tr class="hover:bg-slate-50 transition-colors">
													<td class="px-6 py-4 whitespace-nowrap">
														<div class="text-sm font-medium text-slate-900">
															<?php echo esc_html($calf_name); ?>
														</div>
													</td>
													<td class="px-6 py-4 whitespace-nowrap">
														<div class="text-sm text-slate-600 font-mono">
															<?php echo esc_html($tattoo); ?>
														</div>
													</td>
													<td class="px-6 py-4 whitespace-nowrap">
														<div class="text-sm text-slate-600">
															<?php echo esc_html($sex_labels[$sex] ?? $sex); ?>
														</div>
													</td>
													<td class="px-6 py-4 whitespace-nowrap">
														<div class="text-sm text-slate-600">
															<?php echo esc_html($grade); ?>
														</div>
													</td>
													<td class="px-6 py-4 whitespace-nowrap">
														<?php
														if (function_exists('tailwind_get_cattle_status_badge')) {
															echo tailwind_get_cattle_status_badge($registration->post_status);
														} else {
															echo esc_html($registration->post_status);
														}
														?>
													</td>
													<td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
														<?php echo esc_html($post_date); ?>
													</td>
													<td class="px-6 py-4 whitespace-nowrap text-right text-sm">
														<?php if ('publish' === $registration->post_status) : ?>
															<a
																href="<?php echo esc_url(get_permalink($registration)); ?>"
																class="text-brand hover:text-brand-dark font-medium transition">
																<?php esc_html_e('View', 'tailwind-acf'); ?>
															</a>
														<?php elseif ('pending' === $registration->post_status && $register_cattle_url) : ?>
															<a
																href="<?php echo esc_url(add_query_arg('edit', $registration->ID, $register_cattle_url)); ?>"
																class="text-brand hover:text-brand-dark font-medium transition">
																<?php esc_html_e('Edit', 'tailwind-acf'); ?>
															</a>
														<?php else : ?>
															<span class="text-slate-400">
																<?php esc_html_e('Awaiting review', 'tailwind-acf'); ?>
															</span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
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
