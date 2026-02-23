<?php
/**
 * Cattle CSV Import Admin Page.
 *
 * Provides a wp-admin page for bulk-importing cattle registrations from a CSV
 * file.  The workflow is:
 *   1. Upload a CSV file and preview the first 10 rows.
 *   2. Confirm the import to create cattle_registration posts.
 *   3. Resolve sire/dam lineage links in a second pass.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the "Import Animals" submenu page under the Cattle menu.
 */
function tailwind_cattle_import_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=cattle_registration',
		__( 'Import Animals', 'tailwind-acf' ),
		__( 'Import Animals', 'tailwind-acf' ),
		'manage_options',
		'cattle-import',
		'tailwind_cattle_import_page'
	);
}
add_action( 'admin_menu', 'tailwind_cattle_import_admin_menu' );

/**
 * Render the cattle import admin page.
 *
 * Handles three states:
 *   - Default:  show the upload form.
 *   - Preview:  file uploaded, show a preview table and "Run Import" button.
 *   - Import:   run the two-pass import and display results.
 */
function tailwind_cattle_import_page() {
	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Import Animals from CSV', 'tailwind-acf' ) . '</h1>';

	// Determine which step we are on.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified below per step.
	$step = isset( $_POST['cattle_import_step'] ) ? sanitize_text_field( wp_unslash( $_POST['cattle_import_step'] ) ) : '';

	if ( 'import' === $step ) {
		tailwind_cattle_import_run();
	} elseif ( 'preview' === $step ) {
		tailwind_cattle_import_preview();
	} else {
		tailwind_cattle_import_upload_form();
	}

	echo '</div>';
}

/**
 * Display the CSV upload form.
 */
function tailwind_cattle_import_upload_form() {
	?>
	<p><?php esc_html_e( 'Upload a CSV file containing cattle registration records. The file should include a header row.', 'tailwind-acf' ); ?></p>
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'cattle_import_preview', 'cattle_import_nonce' ); ?>
		<input type="hidden" name="cattle_import_step" value="preview" />
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="cattle_csv_file"><?php esc_html_e( 'CSV File', 'tailwind-acf' ); ?></label>
				</th>
				<td>
					<input type="file" name="cattle_csv_file" id="cattle_csv_file" accept=".csv" required />
					<p class="description">
						<?php esc_html_e( 'Expected columns: Stud, Name, AnmlSex, AnmlDOB, ColourLiteral, Regn, HB, AnmlGrade, BTat, Tat, SHB, SRegn, SGrade, DHB, DRegn, DGrade, AnmlOwnerNo', 'tailwind-acf' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( 'Upload &amp; Preview', 'tailwind-acf' ) ); ?>
	</form>
	<?php
}

/**
 * Handle the preview step: validate and store the uploaded CSV, then show the
 * first 10 rows in a table.
 */
function tailwind_cattle_import_preview() {
	check_admin_referer( 'cattle_import_preview', 'cattle_import_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to import animals.', 'tailwind-acf' ) );
	}

	// Validate the uploaded file.
	if ( empty( $_FILES['cattle_csv_file']['tmp_name'] ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'No file was uploaded.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	$uploaded_file = $_FILES['cattle_csv_file']['tmp_name'];
	$file_name     = isset( $_FILES['cattle_csv_file']['name'] ) ? sanitize_file_name( $_FILES['cattle_csv_file']['name'] ) : '';

	// Verify it is a CSV by extension.
	$ext = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
	if ( 'csv' !== $ext ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Please upload a valid CSV file.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	// Copy to a known temp location so it persists between steps.
	$upload_dir = wp_upload_dir();
	$temp_path  = $upload_dir['basedir'] . '/cattle-import-temp.csv';

	if ( ! move_uploaded_file( $uploaded_file, $temp_path ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Failed to store the uploaded file. Check directory permissions.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	// Read the file and parse the first 10 data rows for preview.
	$handle = fopen( $temp_path, 'r' );
	if ( ! $handle ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Could not open the CSV file.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	$header = fgetcsv( $handle );
	if ( ! $header ) {
		fclose( $handle );
		echo '<div class="notice notice-error"><p>' . esc_html__( 'The CSV file appears to be empty.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	// Count total data rows.
	$total_rows   = 0;
	$preview_rows = array();

	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		$total_rows++;
		if ( count( $preview_rows ) < 10 ) {
			$preview_rows[] = $row;
		}
	}

	fclose( $handle );

	if ( 0 === $total_rows ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'The CSV file contains no data rows.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	echo '<div class="notice notice-info"><p>';
	printf(
		/* translators: %d: number of records */
		esc_html__( 'Found %d records in the CSV file. Showing the first 10 rows below.', 'tailwind-acf' ),
		$total_rows
	);
	echo '</p></div>';

	// Preview table.
	echo '<table class="widefat striped">';
	echo '<thead><tr>';
	foreach ( $header as $col ) {
		echo '<th>' . esc_html( $col ) . '</th>';
	}
	echo '</tr></thead>';
	echo '<tbody>';

	foreach ( $preview_rows as $row ) {
		echo '<tr>';
		foreach ( $row as $cell ) {
			echo '<td>' . esc_html( $cell ) . '</td>';
		}
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';

	// Confirmation form.
	?>
	<form method="post" style="margin-top: 1em;">
		<?php wp_nonce_field( 'cattle_import_run', 'cattle_import_nonce' ); ?>
		<input type="hidden" name="cattle_import_step" value="import" />
		<?php submit_button( __( 'Run Import', 'tailwind-acf' ), 'primary', 'submit', true ); ?>
	</form>
	<?php
}

/**
 * Map a CSV row array to cattle registration field values.
 *
 * @param array $row Indexed array matching the CSV column order.
 * @return array Associative array of field values.
 */
function tailwind_cattle_import_map_row( $row ) {
	// CSV column order:
	// 0:Stud, 1:Name, 2:AnmlSex, 3:AnmlDOB, 4:ColourLiteral, 5:Regn,
	// 6:HB, 7:AnmlGrade, 8:BTat, 9:Tat, 10:SHB, 11:SRegn, 12:SGrade,
	// 13:DHB, 14:DRegn, 15:DGrade, 16:AnmlOwnerNo

	// Sex mapping.
	$sex_map = array(
		'1' => 'M',
		'2' => 'F',
	);
	$sex = isset( $sex_map[ trim( $row[2] ) ] ) ? $sex_map[ trim( $row[2] ) ] : '';

	// Date of birth.
	$dob_raw = trim( $row[3] );
	$dob     = '';
	if ( ! empty( $dob_raw ) ) {
		$timestamp = strtotime( $dob_raw );
		if ( false !== $timestamp ) {
			$dob = gmdate( 'Y-m-d', $timestamp );
		}
	}

	// Colour mapping (case-insensitive).
	$colour_map = array(
		'grey'   => 'G',
		'silver' => 'S',
		'black'  => 'B',
		'dun'    => 'D',
	);
	$colour_raw = strtolower( trim( $row[4] ) );
	$colour     = isset( $colour_map[ $colour_raw ] ) ? $colour_map[ $colour_raw ] : '';

	// Grade mapping.
	$grade_map = array(
		'1' => 'PB',
		'2' => 'A',
		'3' => 'B',
		'4' => 'C',
		'0' => 'PB',
	);
	$grade_raw = trim( $row[7] );
	$grade     = isset( $grade_map[ $grade_raw ] ) ? $grade_map[ $grade_raw ] : 'PB';

	$name   = trim( $row[1] );
	$tattoo = trim( $row[9] );

	return array(
		'post_title'          => $name . ( $tattoo ? ' (' . $tattoo . ')' : '' ),
		'calf_name'           => $name,
		'stud_name'           => trim( $row[0] ),
		'sex'                 => $sex,
		'date_of_birth'       => $dob,
		'colour'              => $colour,
		'registration_number' => trim( $row[5] ),
		'herd_book'           => intval( $row[6] ),
		'grade'               => $grade,
		'brand_tattoo'        => trim( $row[8] ),
		'tattoo_number'       => $tattoo,
		'sire_herd_book'      => intval( $row[10] ),
		'sire_tattoo'         => trim( $row[11] ),
		'sire_grade'          => trim( $row[12] ),
		'dam_herd_book'       => intval( $row[13] ),
		'dam_tattoo'          => trim( $row[14] ),
		'dam_grade'           => trim( $row[15] ),
		// Keep raw lineage values for Pass 2.
		'_sregn'              => trim( $row[11] ),
		'_dregn'              => trim( $row[14] ),
	);
}

/**
 * Run the two-pass CSV import.
 *
 * Pass 1: Create cattle_registration posts.
 * Pass 2: Resolve sire/dam lineage by matching registration numbers.
 */
function tailwind_cattle_import_run() {
	check_admin_referer( 'cattle_import_run', 'cattle_import_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to import animals.', 'tailwind-acf' ) );
	}

	// Validate temp file path.
	$upload_dir    = wp_upload_dir();
	$expected_path = $upload_dir['basedir'] . '/cattle-import-temp.csv';

	if ( ! file_exists( $expected_path ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'The temporary CSV file was not found. Please upload the file again.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	// Security: Ensure realpath matches expected location.
	$real_path = realpath( $expected_path );
	if ( false === $real_path || $real_path !== $expected_path ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Invalid file path detected. Please upload the file again.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	$handle = fopen( $expected_path, 'r' );
	if ( ! $handle ) {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Could not open the CSV file for import.', 'tailwind-acf' ) . '</p></div>';
		tailwind_cattle_import_upload_form();
		return;
	}

	// Extend execution time for large imports.
	set_time_limit( 300 );

	// Prevent notification emails for every imported post.
	remove_action( 'wp_insert_post', 'tailwind_cattle_notify_admin_on_submission', 10 );

	// Skip header row.
	fgetcsv( $handle );

	// ----- Pass 1: Create posts -----
	$imported       = 0;
	$skipped        = 0;
	$errors         = array();
	$imported_posts = array(); // registration_number => post_id lookup map.
	$lineage_queue  = array(); // Items that need lineage resolution.

	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		// Skip rows with insufficient columns.
		if ( count( $row ) < 16 ) {
			$errors[] = sprintf(
				/* translators: %d: row number */
				__( 'Row %d: insufficient columns, skipped.', 'tailwind-acf' ),
				$imported + $skipped + count( $errors ) + 1
			);
			continue;
		}

		$fields = tailwind_cattle_import_map_row( $row );

		$reg_number = $fields['registration_number'];

		// Duplicate detection: check if this registration_number already exists.
		if ( ! empty( $reg_number ) ) {
			$existing = tailwind_cattle_import_find_by_regn( $reg_number );
			if ( $existing ) {
				// Add to lookup map so lineage can still resolve, but skip creation.
				$imported_posts[ $reg_number ] = $existing;
				$skipped++;

				// Still queue for lineage if it has sire/dam references.
				if ( ! empty( $fields['_sregn'] ) || ! empty( $fields['_dregn'] ) ) {
					$lineage_queue[] = array(
						'post_id' => $existing,
						'sregn'   => $fields['_sregn'],
						'dregn'   => $fields['_dregn'],
					);
				}

				continue;
			}
		}

		// Create the post.
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'cattle_registration',
				'post_status' => 'publish',
				'post_author' => 0,
				'post_title'  => $fields['post_title'],
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$errors[] = sprintf(
				/* translators: 1: registration number, 2: error message */
				__( 'Failed to create post for %1$s: %2$s', 'tailwind-acf' ),
				$reg_number,
				$post_id->get_error_message()
			);
			continue;
		}

		// Set ACF fields.
		$acf_fields = array(
			'calf_name',
			'stud_name',
			'sex',
			'date_of_birth',
			'colour',
			'registration_number',
			'herd_book',
			'grade',
			'brand_tattoo',
			'tattoo_number',
			'sire_herd_book',
			'sire_tattoo',
			'sire_grade',
			'dam_herd_book',
			'dam_tattoo',
			'dam_grade',
		);

		foreach ( $acf_fields as $field_name ) {
			if ( isset( $fields[ $field_name ] ) && '' !== $fields[ $field_name ] ) {
				update_field( $field_name, $fields[ $field_name ], $post_id );
			}
		}

		// Track for lineage resolution.
		if ( ! empty( $reg_number ) ) {
			$imported_posts[ $reg_number ] = $post_id;
		}

		if ( ! empty( $fields['_sregn'] ) || ! empty( $fields['_dregn'] ) ) {
			$lineage_queue[] = array(
				'post_id' => $post_id,
				'sregn'   => $fields['_sregn'],
				'dregn'   => $fields['_dregn'],
			);
		}

		$imported++;
	}

	fclose( $handle );

	// ----- Pass 2: Resolve lineage -----
	$lineage_resolved   = 0;
	$lineage_unresolved = 0;

	foreach ( $lineage_queue as $item ) {
		$post_id = $item['post_id'];
		$sregn   = $item['sregn'];
		$dregn   = $item['dregn'];
		$linked  = false;

		// Resolve sire.
		if ( ! empty( $sregn ) ) {
			$sire_post_id = isset( $imported_posts[ $sregn ] ) ? $imported_posts[ $sregn ] : tailwind_cattle_import_find_by_regn( $sregn );

			if ( $sire_post_id ) {
				update_field( 'sire_id', $sire_post_id, $post_id );
				$sire_name = get_field( 'calf_name', $sire_post_id );
				if ( $sire_name ) {
					update_field( 'sire_name', $sire_name, $post_id );
				}
				$linked = true;
			} else {
				$lineage_unresolved++;
			}
		}

		// Resolve dam.
		if ( ! empty( $dregn ) ) {
			$dam_post_id = isset( $imported_posts[ $dregn ] ) ? $imported_posts[ $dregn ] : tailwind_cattle_import_find_by_regn( $dregn );

			if ( $dam_post_id ) {
				update_field( 'dam_id', $dam_post_id, $post_id );
				$dam_name = get_field( 'calf_name', $dam_post_id );
				if ( $dam_name ) {
					update_field( 'dam_name', $dam_name, $post_id );
				}
				$linked = true;
			} else {
				$lineage_unresolved++;
			}
		}

		if ( $linked ) {
			$lineage_resolved++;
		}
	}

	// Re-add the notification hook.
	add_action( 'wp_insert_post', 'tailwind_cattle_notify_admin_on_submission', 10, 3 );

	// Clean up temp file.
	if ( file_exists( $expected_path ) ) {
		unlink( $expected_path );
	}

	// ----- Results summary -----
	echo '<div class="notice notice-success"><p>';
	echo '<strong>' . esc_html__( 'Import complete!', 'tailwind-acf' ) . '</strong><br>';
	printf(
		/* translators: %d: number of imported records */
		esc_html__( 'Imported: %d', 'tailwind-acf' ),
		$imported
	);
	echo '<br>';
	printf(
		/* translators: %d: number of skipped (duplicate) records */
		esc_html__( 'Skipped (duplicates): %d', 'tailwind-acf' ),
		$skipped
	);
	echo '<br>';
	printf(
		/* translators: %d: number of lineage links resolved */
		esc_html__( 'Lineage resolved: %d', 'tailwind-acf' ),
		$lineage_resolved
	);
	echo '<br>';
	printf(
		/* translators: %d: number of unresolved lineage references */
		esc_html__( 'Lineage unresolved: %d', 'tailwind-acf' ),
		$lineage_unresolved
	);
	echo '<br>';
	printf(
		/* translators: %d: number of errors */
		esc_html__( 'Errors: %d', 'tailwind-acf' ),
		count( $errors )
	);
	echo '</p></div>';

	if ( ! empty( $errors ) ) {
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Error details:', 'tailwind-acf' ) . '</strong></p><ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	}

	// Link back to the upload form.
	echo '<p><a href="' . esc_url( admin_url( 'edit.php?post_type=cattle_registration&page=cattle-import' ) ) . '" class="button">';
	echo esc_html__( 'Import Another File', 'tailwind-acf' );
	echo '</a></p>';
}

/**
 * Find a cattle_registration post by its registration_number meta value.
 *
 * @param string $registration_number The registration number to search for.
 * @return int|false Post ID if found, false otherwise.
 */
function tailwind_cattle_import_find_by_regn( $registration_number ) {
	if ( empty( $registration_number ) ) {
		return false;
	}

	$posts = get_posts(
		array(
			'post_type'      => 'cattle_registration',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => 'registration_number',
					'value' => $registration_number,
				),
			),
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $posts ) ) {
		return (int) $posts[0];
	}

	return false;
}
