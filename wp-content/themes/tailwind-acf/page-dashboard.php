<?php

/**
 * Template Name: Dashboard
 * Template Post Type: page
 *
 * Template for the member dashboard page.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	$permalink = get_permalink( get_queried_object_id() );
	wp_safe_redirect( wp_login_url( $permalink ) );
	exit;
}

get_header();

$current_user = wp_get_current_user();
$status       = tailwind_member_get_status( $current_user->ID );
$notices      = array();

if ( isset( $_GET['pending'] ) || TAILWIND_MEMBER_STATUS_PENDING === $status ) {
	$notices[] = __( 'Thanks for registering! An administrator will review your account shortly. You will receive an email once you are approved.', 'tailwind-acf' );
}

if ( isset( $_GET['denied'] ) ) {
	$notices[] = __( 'You do not have permission to view that page.', 'tailwind-acf' );
}

?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<main id="primary" class="site-main">
			<div class="mx-auto max-w-5xl px-6 py-16 sm:px-10 lg:px-12">
				<header class="mb-10">
					<h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
						<?php the_title(); ?>
					</h1>
				</header>

				<?php if ( $notices ) : ?>
					<?php foreach ( $notices as $notice ) : ?>
						<div class="mb-6 rounded-md border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
							<?php echo esc_html( $notice ); ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( TAILWIND_MEMBER_STATUS_APPROVED === $status ) : ?>
					<div class="prose prose-slate max-w-none">
						<?php the_content(); ?>
					</div>
				<?php else : ?>
					<p class="text-base text-slate-600">
						<?php esc_html_e( 'You can explore the dashboard as soon as your account is approved.', 'tailwind-acf' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</main>
	<?php endwhile; ?>
<?php endif; ?>

<?php
get_footer();
