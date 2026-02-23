<?php
/**
 * Template Name: Animal Search
 * Template Post Type: page
 *
 * Public search page for approved cattle registrations.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Filter options.
$grade_options  = array( '' => __( 'All Grades', 'tailwind-acf' ) ) + tailwind_cattle_get_grade_options();
$sex_options    = array( '' => __( 'All', 'tailwind-acf' ) ) + tailwind_cattle_get_sex_options();
$colour_options = array( '' => __( 'All Colours', 'tailwind-acf' ) ) + tailwind_cattle_get_colour_options();

// Year letters A-Z.
$year_options = array( '' => __( 'All Years', 'tailwind-acf' ) );
foreach ( range( 'A', 'Z' ) as $letter ) {
	$year_options[ $letter ] = $letter;
}

// Get search parameters from URL (sanitized).
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public search, no state change.
$search_query = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_grade = isset( $_GET['grade'] ) ? sanitize_text_field( wp_unslash( $_GET['grade'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_sex = isset( $_GET['sex'] ) ? sanitize_text_field( wp_unslash( $_GET['sex'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_colour = isset( $_GET['colour'] ) ? sanitize_text_field( wp_unslash( $_GET['colour'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$filter_year = isset( $_GET['year'] ) ? sanitize_text_field( wp_unslash( $_GET['year'] ) ) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;

$per_page = 12;

// Build the query.
$query_args = array(
	'post_type'      => 'cattle_registration',
	'post_status'    => 'publish',
	'posts_per_page' => $per_page,
	'paged'          => $current_page,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

// Meta query for filters.
$meta_query = array( 'relation' => 'AND' );

if ( $filter_grade && array_key_exists( $filter_grade, $grade_options ) ) {
	$meta_query[] = array(
		'key'     => 'grade',
		'value'   => $filter_grade,
		'compare' => '=',
	);
}

if ( $filter_sex && array_key_exists( $filter_sex, $sex_options ) ) {
	$meta_query[] = array(
		'key'     => 'sex',
		'value'   => $filter_sex,
		'compare' => '=',
	);
}

if ( $filter_colour && array_key_exists( $filter_colour, $colour_options ) ) {
	$meta_query[] = array(
		'key'     => 'colour',
		'value'   => $filter_colour,
		'compare' => '=',
	);
}

if ( $filter_year && array_key_exists( $filter_year, $year_options ) ) {
	$meta_query[] = array(
		'key'     => 'year_letter',
		'value'   => $filter_year,
		'compare' => '=',
	);
}

// Full-text search across multiple fields.
if ( $search_query ) {
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => 'calf_name',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'tattoo_number',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'sire_name',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'sire_tattoo',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'dam_name',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'dam_tattoo',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'registration_number',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
		array(
			'key'     => 'stud_name',
			'value'   => $search_query,
			'compare' => 'LIKE',
		),
	);
}

if ( count( $meta_query ) > 1 ) {
	$query_args['meta_query'] = $meta_query;
}

// Execute query.
$cattle_query = new WP_Query( $query_args );

// If searching by breeder name (author), we need a separate approach.
// For simplicity, if no meta results and search query exists, try author search.
$author_results = array();
if ( $search_query && ! $cattle_query->have_posts() ) {
	// Search by author display name.
	$user_query = new WP_User_Query(
		array(
			'search'         => '*' . $search_query . '*',
			'search_columns' => array( 'display_name', 'user_login' ),
			'fields'         => 'ID',
		)
	);

	$author_ids = $user_query->get_results();

	if ( ! empty( $author_ids ) ) {
		$query_args['author__in'] = $author_ids;
		unset( $query_args['meta_query'] );

		// Re-add non-search meta queries.
		$filter_meta = array( 'relation' => 'AND' );
		if ( $filter_grade ) {
			$filter_meta[] = array(
				'key'   => 'grade',
				'value' => $filter_grade,
			);
		}
		if ( $filter_sex ) {
			$filter_meta[] = array(
				'key'   => 'sex',
				'value' => $filter_sex,
			);
		}
		if ( $filter_colour ) {
			$filter_meta[] = array(
				'key'   => 'colour',
				'value' => $filter_colour,
			);
		}
		if ( $filter_year ) {
			$filter_meta[] = array(
				'key'   => 'year_letter',
				'value' => $filter_year,
			);
		}
		if ( count( $filter_meta ) > 1 ) {
			$query_args['meta_query'] = $filter_meta;
		}

		$cattle_query = new WP_Query( $query_args );
	}
}

// Labels for display.
$sex_labels    = tailwind_cattle_get_sex_labels();
$colour_labels = tailwind_cattle_get_colour_labels();

// Check if any filters are active.
$has_filters = $search_query || $filter_grade || $filter_sex || $filter_colour || $filter_year;

get_header();
?>

<main id="primary" class="site-main bg-white min-h-screen">
	<!-- Header Section -->
	<div class="bg-green-950 py-16 sm:py-20">
		<div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-12">
			<h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
				<?php esc_html_e( 'Animal Search', 'tailwind-acf' ); ?>
			</h1>
			<p class="mt-3 text-lg text-green-100/80">
				<?php esc_html_e( 'Search our registry of approved Murray Grey cattle.', 'tailwind-acf' ); ?>
			</p>
		</div>
	</div>

	<!-- Search Form -->
	<div class="border-b border-slate-200 bg-slate-50">
		<div class="mx-auto max-w-7xl px-6 py-8 sm:px-8 lg:px-12">
			<form method="get" action="<?php echo esc_url( get_permalink() ); ?>" class="space-y-6">
				<!-- Search Input -->
				<div>
					<label for="search" class="sr-only"><?php esc_html_e( 'Search', 'tailwind-acf' ); ?></label>
					<div class="relative">
						<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
							<svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
							</svg>
						</div>
						<input
							type="text"
							name="s"
							id="search"
							value="<?php echo esc_attr( $search_query ); ?>"
							placeholder="<?php esc_attr_e( 'Search by name, tattoo, breeder, sire, dam...', 'tailwind-acf' ); ?>"
							class="block w-full rounded-xl border-slate-300 bg-white py-3.5 pl-12 pr-4 text-base shadow-sm placeholder:text-slate-400 focus:border-green-600 focus:ring-green-600"
						>
					</div>
				</div>

				<!-- Filters -->
				<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
					<!-- Grade -->
					<div>
						<label for="grade" class="block text-sm font-medium text-slate-700 mb-1">
							<?php esc_html_e( 'Grade', 'tailwind-acf' ); ?>
						</label>
						<select
							name="grade"
							id="grade"
							class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
						>
							<?php foreach ( $grade_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filter_grade, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Sex -->
					<div>
						<label for="sex" class="block text-sm font-medium text-slate-700 mb-1">
							<?php esc_html_e( 'Sex', 'tailwind-acf' ); ?>
						</label>
						<select
							name="sex"
							id="sex"
							class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
						>
							<?php foreach ( $sex_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filter_sex, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Colour -->
					<div>
						<label for="colour" class="block text-sm font-medium text-slate-700 mb-1">
							<?php esc_html_e( 'Colour', 'tailwind-acf' ); ?>
						</label>
						<select
							name="colour"
							id="colour"
							class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
						>
							<?php foreach ( $colour_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filter_colour, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Year Letter -->
					<div>
						<label for="year" class="block text-sm font-medium text-slate-700 mb-1">
							<?php esc_html_e( 'Year Letter', 'tailwind-acf' ); ?>
						</label>
						<select
							name="year"
							id="year"
							class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-600 focus:ring-green-600"
						>
							<?php foreach ( $year_options as $value => $label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filter_year, $value ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- Buttons -->
					<div class="flex items-end gap-3">
						<button
							type="submit"
							class="flex-1 inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2"
						>
							<svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
							</svg>
							<?php esc_html_e( 'Search', 'tailwind-acf' ); ?>
						</button>
						<?php if ( $has_filters ) : ?>
							<a
								href="<?php echo esc_url( get_permalink() ); ?>"
								class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
							>
								<?php esc_html_e( 'Clear', 'tailwind-acf' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Results Section -->
	<div class="mx-auto max-w-7xl px-6 py-12 sm:px-8 lg:px-12">
		<div class="mb-6 flex items-center justify-between">
			<p class="text-sm text-slate-600">
				<?php if ( $has_filters ) : ?>
					<?php
					printf(
						/* translators: %d: number of results */
						esc_html( _n( '%d result found', '%d results found', $cattle_query->found_posts, 'tailwind-acf' ) ),
						esc_html( $cattle_query->found_posts )
					);
					?>
				<?php else : ?>
					<?php
					printf(
						/* translators: %d: number of animals */
						esc_html( _n( '%d animal registered', '%d animals registered', $cattle_query->found_posts, 'tailwind-acf' ) ),
						esc_html( $cattle_query->found_posts )
					);
					?>
				<?php endif; ?>
			</p>
		</div>

		<?php if ( $cattle_query->have_posts() ) : ?>
			<!-- Results Grid -->
			<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<?php while ( $cattle_query->have_posts() ) : $cattle_query->the_post(); ?>
					<?php
					$post_id   = get_the_ID();
					$calf_name = get_field( 'calf_name', $post_id );
					$tattoo    = get_field( 'tattoo_number', $post_id );
					$grade     = get_field( 'grade', $post_id );
					$sex       = get_field( 'sex', $post_id );
					$colour    = get_field( 'colour', $post_id );
					$dob       = get_field( 'date_of_birth', $post_id );
					$author    = get_userdata( get_post_field( 'post_author', $post_id ) );
					$regn      = get_field( 'registration_number', $post_id );
					$stud      = get_field( 'stud_name', $post_id );
					?>
					<a
						href="<?php the_permalink(); ?>"
						class="group block rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-green-300 hover:shadow-md"
					>
						<div class="flex items-start justify-between gap-4">
							<div class="min-w-0 flex-1">
								<h3 class="text-lg font-semibold text-slate-900 group-hover:text-green-700 transition truncate">
									<?php echo esc_html( $calf_name ); ?>
								</h3>
								<p class="mt-1 text-sm font-mono text-slate-500">
									<?php echo esc_html( $regn ?: $tattoo ); ?>
								</p>
							</div>
							<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
								<?php echo esc_html( $grade ); ?>
							</span>
						</div>

						<dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
							<div>
								<dt class="text-slate-500"><?php esc_html_e( 'Sex', 'tailwind-acf' ); ?></dt>
								<dd class="font-medium text-slate-900"><?php echo esc_html( $sex_labels[ $sex ] ?? $sex ); ?></dd>
							</div>
							<div>
								<dt class="text-slate-500"><?php esc_html_e( 'Colour', 'tailwind-acf' ); ?></dt>
								<dd class="font-medium text-slate-900"><?php echo esc_html( $colour_labels[ $colour ] ?? $colour ); ?></dd>
							</div>
							<?php if ( $dob ) : ?>
								<div>
									<dt class="text-slate-500"><?php esc_html_e( 'Born', 'tailwind-acf' ); ?></dt>
									<dd class="font-medium text-slate-900"><?php echo esc_html( date_i18n( 'M Y', strtotime( $dob ) ) ); ?></dd>
								</div>
							<?php endif; ?>
							<?php if ( $author ) : ?>
								<div>
									<dt class="text-slate-500"><?php esc_html_e( 'Breeder', 'tailwind-acf' ); ?></dt>
									<dd class="font-medium text-slate-900 truncate"><?php echo esc_html( $author->display_name ); ?></dd>
								</div>
							<?php endif; ?>
							<?php if ( $stud ) : ?>
								<div>
									<dt class="text-slate-500"><?php esc_html_e( 'Stud', 'tailwind-acf' ); ?></dt>
									<dd class="font-medium text-slate-900 truncate"><?php echo esc_html( $stud ); ?></dd>
								</div>
							<?php endif; ?>
						</dl>

						<div class="mt-4 flex items-center text-sm font-medium text-green-700 group-hover:text-green-800">
							<?php esc_html_e( 'View details', 'tailwind-acf' ); ?>
							<svg class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
							</svg>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<!-- Pagination -->
			<?php if ( $cattle_query->max_num_pages > 1 ) : ?>
				<nav class="mt-12 flex items-center justify-center gap-2" aria-label="<?php esc_attr_e( 'Pagination', 'tailwind-acf' ); ?>">
					<?php
					$pagination_args = array(
						'total'     => $cattle_query->max_num_pages,
						'current'   => $current_page,
						'format'    => '?paged=%#%',
						'prev_text' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>',
						'next_text' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>',
						'type'      => 'array',
						'mid_size'  => 2,
					);

					// Preserve filter parameters in pagination.
					$base_url = get_permalink();
					$url_params = array();
					if ( $search_query ) {
						$url_params['s'] = $search_query;
					}
					if ( $filter_grade ) {
						$url_params['grade'] = $filter_grade;
					}
					if ( $filter_sex ) {
						$url_params['sex'] = $filter_sex;
					}
					if ( $filter_colour ) {
						$url_params['colour'] = $filter_colour;
					}
					if ( $filter_year ) {
						$url_params['year'] = $filter_year;
					}

					if ( ! empty( $url_params ) ) {
						$base_url = add_query_arg( $url_params, $base_url );
					}

					$pagination_args['base'] = $base_url . '%_%';

					$links = paginate_links( $pagination_args );

					if ( $links ) :
						foreach ( $links as $link ) :
							// Add Tailwind classes to pagination links.
							$link = str_replace(
								'page-numbers',
								'page-numbers inline-flex h-10 min-w-[2.5rem] items-center justify-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50',
								$link
							);
							$link = str_replace(
								'current',
								'current bg-green-700 border-green-700 text-white hover:bg-green-800',
								$link
							);
							echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						endforeach;
					endif;
					?>
				</nav>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

		<?php else : ?>
			<!-- No Results -->
			<div class="rounded-xl border border-slate-200 bg-slate-50 px-6 py-16 text-center">
				<svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
				</svg>
				<h3 class="mt-4 text-lg font-semibold text-slate-900">
					<?php esc_html_e( 'No cattle found', 'tailwind-acf' ); ?>
				</h3>
				<p class="mt-2 text-sm text-slate-600">
					<?php if ( $has_filters ) : ?>
						<?php esc_html_e( 'Try adjusting your search or filters to find what you\'re looking for.', 'tailwind-acf' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'There are no approved cattle registrations in the database yet.', 'tailwind-acf' ); ?>
					<?php endif; ?>
				</p>
				<?php if ( $has_filters ) : ?>
					<a
						href="<?php echo esc_url( get_permalink() ); ?>"
						class="mt-6 inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800"
					>
						<?php esc_html_e( 'Clear all filters', 'tailwind-acf' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();







