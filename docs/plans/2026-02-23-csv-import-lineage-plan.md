# CSV Import, Lineage & Animal Assignment — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Import ~1,500 cattle records from CSV, expand the data model with new fields and lineage relationships, add a 3-generation pedigree chart on animal detail pages, enable admin bulk assignment of animals to users, and enhance public animal search.

**Architecture:** Extend the existing `cattle_registration` CPT with 10 new ACF fields (including post-object relationships for sire/dam lineage). Build a wp-admin CSV import page that processes records in a two-pass batch (create posts, then resolve lineage links). Add a CSS-based 3-generation pedigree chart to the single animal template. Add a "Change Owner" bulk action to the cattle admin list.

**Tech Stack:** WordPress, ACF (Advanced Custom Fields), PHP, Tailwind CSS, custom theme (`tailwind-acf`)

**Design doc:** `docs/plans/2026-02-23-csv-import-lineage-design.md`

---

### Task 1: Add new ACF fields to cattle registration

**Files:**
- Modify: `wp-content/themes/tailwind-acf/inc/cattle-registration.php:134-305` (ACF field group)

**Step 1: Add new fields to the ACF field group**

In the `tailwind_register_cattle_acf_fields()` function, add 10 new fields to the `fields` array. Insert them in logical groupings:

After the existing `tattoo_number` field (line 182), add a new "Registration Details" group:

```php
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
    'key'   => 'field_cattle_brand_tattoo',
    'label' => __( 'Brand Tattoo', 'tailwind-acf' ),
    'name'  => 'brand_tattoo',
    'type'  => 'text',
    'instructions' => __( 'Tattoo prefix (e.g., RIB, WIN)', 'tailwind-acf' ),
),
```

After the existing `dam_tattoo` field (line 289), add relationship and extended parentage fields:

```php
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
```

**Step 2: Verify in wp-admin**

Visit wp-admin → Cattle → Add New. Confirm all new fields appear in the editor:
- Registration Number, Stud Name, Herd Book, Brand Tattoo
- Sire (Linked Animal) as a searchable post-object picker
- Dam (Linked Animal) as a searchable post-object picker
- Sire Herd Book, Sire Grade, Dam Herd Book, Dam Grade

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-registration.php
git commit -m "feat: add 10 new ACF fields for registration details and lineage relationships"
```

---

### Task 2: Build the CSV import admin page

**Files:**
- Create: `wp-content/themes/tailwind-acf/inc/cattle-import.php`
- Modify: `wp-content/themes/tailwind-acf/functions.php` (add require_once)

**Step 1: Create the import module file**

Create `inc/cattle-import.php` with the following structure. This file handles:
- Registering the admin submenu page
- Rendering the upload form and preview table
- Processing the CSV import in two passes
- Displaying results

```php
<?php
/**
 * CSV Import for Cattle Registrations.
 *
 * @package Tailwind_ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the import admin page under the Cattle menu.
 */
function tailwind_cattle_import_menu() {
	add_submenu_page(
		'edit.php?post_type=cattle_registration',
		__( 'Import Animals', 'tailwind-acf' ),
		__( 'Import Animals', 'tailwind-acf' ),
		'manage_options',
		'cattle-import',
		'tailwind_cattle_import_page'
	);
}
add_action( 'admin_menu', 'tailwind_cattle_import_menu' );

/**
 * Map CSV sex value to ACF sex code.
 *
 * @param string $csv_sex CSV sex value (1 or 2).
 * @return string ACF sex code (M, F, or empty).
 */
function tailwind_cattle_import_map_sex( $csv_sex ) {
	$map = array(
		'1' => 'M',
		'2' => 'F',
	);
	return $map[ trim( $csv_sex ) ] ?? '';
}

/**
 * Map CSV grade value to ACF grade code.
 *
 * @param string $csv_grade CSV grade value (numeric).
 * @return string ACF grade code (PB, A, B, C, or empty).
 */
function tailwind_cattle_import_map_grade( $csv_grade ) {
	$map = array(
		'1' => 'PB',
		'2' => 'A',
		'3' => 'B',
		'4' => 'C',
		'0' => 'PB',
	);
	return $map[ trim( $csv_grade ) ] ?? 'PB';
}

/**
 * Map CSV colour literal to ACF colour code.
 *
 * @param string $csv_colour CSV colour text (Grey, Silver, etc.).
 * @return string ACF colour code (G, S, B, D, or empty).
 */
function tailwind_cattle_import_map_colour( $csv_colour ) {
	$map = array(
		'grey'   => 'G',
		'silver' => 'S',
		'black'  => 'B',
		'dun'    => 'D',
	);
	return $map[ strtolower( trim( $csv_colour ) ) ] ?? '';
}

/**
 * Parse CSV date (DD-MMM-YY or DD-MMM-YYYY) to Y-m-d format.
 *
 * @param string $csv_date CSV date string.
 * @return string Date in Y-m-d format, or empty string.
 */
function tailwind_cattle_import_parse_date( $csv_date ) {
	$csv_date = trim( $csv_date );
	if ( empty( $csv_date ) ) {
		return '';
	}

	$timestamp = strtotime( $csv_date );
	if ( false === $timestamp ) {
		return '';
	}

	return date( 'Y-m-d', $timestamp );
}

/**
 * Check if a cattle registration with this registration number already exists.
 *
 * @param string $registration_number The registration number to check.
 * @return int|false Post ID if exists, false otherwise.
 */
function tailwind_cattle_import_find_by_regn( $registration_number ) {
	$registration_number = trim( $registration_number );
	if ( empty( $registration_number ) ) {
		return false;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'cattle_registration',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => 'registration_number',
					'value'   => $registration_number,
					'compare' => '=',
				),
			),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	return ! empty( $existing ) ? $existing[0] : false;
}

/**
 * Handle the CSV import processing.
 *
 * @param string $file_path Path to the uploaded CSV file.
 * @return array Results with counts: imported, skipped, lineage_resolved, lineage_unresolved.
 */
function tailwind_cattle_import_process( $file_path ) {
	$results = array(
		'imported'            => 0,
		'skipped'             => 0,
		'errors'              => 0,
		'lineage_resolved'    => 0,
		'lineage_unresolved'  => 0,
		'error_messages'      => array(),
	);

	// Read CSV.
	$handle = fopen( $file_path, 'r' );
	if ( false === $handle ) {
		$results['error_messages'][] = __( 'Could not open CSV file.', 'tailwind-acf' );
		return $results;
	}

	// Read header row.
	$header = fgetcsv( $handle );
	if ( false === $header ) {
		fclose( $handle );
		$results['error_messages'][] = __( 'CSV file is empty or invalid.', 'tailwind-acf' );
		return $results;
	}

	// Map header columns to indices.
	$header = array_map( 'trim', $header );
	$col_map = array_flip( $header );

	$required_cols = array( 'Stud', 'Name', 'AnmlSex', 'Regn', 'Tat' );
	foreach ( $required_cols as $col ) {
		if ( ! isset( $col_map[ $col ] ) ) {
			fclose( $handle );
			$results['error_messages'][] = sprintf(
				/* translators: %s: column name */
				__( 'Required column "%s" not found in CSV header.', 'tailwind-acf' ),
				$col
			);
			return $results;
		}
	}

	// Increase time limit for large imports.
	set_time_limit( 300 );

	// Temporarily unhook the admin notification to avoid sending 1500 emails.
	remove_action( 'wp_insert_post', 'tailwind_cattle_notify_admin_on_submission', 10 );

	// Pass 1: Create posts.
	$imported_posts = array(); // registration_number => post_id
	$lineage_queue  = array(); // post_id => array( 'sire_regn' => ..., 'dam_regn' => ... )
	$row_number     = 1;

	while ( false !== ( $row = fgetcsv( $handle ) ) ) {
		$row_number++;

		// Skip empty rows.
		if ( empty( array_filter( $row ) ) ) {
			continue;
		}

		$regn = isset( $col_map['Regn'] ) ? trim( $row[ $col_map['Regn'] ] ?? '' ) : '';

		// Skip if no registration number.
		if ( empty( $regn ) ) {
			$results['skipped']++;
			continue;
		}

		// Skip duplicates.
		$existing_id = tailwind_cattle_import_find_by_regn( $regn );
		if ( $existing_id ) {
			$imported_posts[ $regn ] = $existing_id;
			$results['skipped']++;
			continue;
		}

		// Extract fields.
		$stud_name  = isset( $col_map['Stud'] ) ? trim( $row[ $col_map['Stud'] ] ?? '' ) : '';
		$name       = isset( $col_map['Name'] ) ? trim( $row[ $col_map['Name'] ] ?? '' ) : '';
		$sex        = isset( $col_map['AnmlSex'] ) ? tailwind_cattle_import_map_sex( $row[ $col_map['AnmlSex'] ] ?? '' ) : '';
		$dob        = isset( $col_map['AnmlDOB'] ) ? tailwind_cattle_import_parse_date( $row[ $col_map['AnmlDOB'] ] ?? '' ) : '';
		$colour     = isset( $col_map['ColourLiteral'] ) ? tailwind_cattle_import_map_colour( $row[ $col_map['ColourLiteral'] ] ?? '' ) : '';
		$hb         = isset( $col_map['HB'] ) ? intval( $row[ $col_map['HB'] ] ?? 0 ) : 0;
		$grade      = isset( $col_map['AnmlGrade'] ) ? tailwind_cattle_import_map_grade( $row[ $col_map['AnmlGrade'] ] ?? '' ) : 'PB';
		$btat       = isset( $col_map['BTat'] ) ? trim( $row[ $col_map['BTat'] ] ?? '' ) : '';
		$tat        = isset( $col_map['Tat'] ) ? trim( $row[ $col_map['Tat'] ] ?? '' ) : '';
		$shb        = isset( $col_map['SHB'] ) ? intval( $row[ $col_map['SHB'] ] ?? 0 ) : 0;
		$sregn      = isset( $col_map['SRegn'] ) ? trim( $row[ $col_map['SRegn'] ] ?? '' ) : '';
		$sgrade     = isset( $col_map['SGrade'] ) ? trim( $row[ $col_map['SGrade'] ] ?? '' ) : '';
		$dhb        = isset( $col_map['DHB'] ) ? intval( $row[ $col_map['DHB'] ] ?? 0 ) : 0;
		$dregn      = isset( $col_map['DRegn'] ) ? trim( $row[ $col_map['DRegn'] ] ?? '' ) : '';
		$dgrade     = isset( $col_map['DGrade'] ) ? trim( $row[ $col_map['DGrade'] ] ?? '' ) : '';

		// Build post title.
		$post_title = $name;
		if ( $tat ) {
			$post_title .= ' (' . $tat . ')';
		}

		// Create the post.
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'cattle_registration',
				'post_title'  => $post_title,
				'post_status' => 'publish',
				'post_author' => 0,
			)
		);

		if ( is_wp_error( $post_id ) ) {
			$results['errors']++;
			$results['error_messages'][] = sprintf(
				/* translators: 1: row number, 2: error message */
				__( 'Row %1$d: %2$s', 'tailwind-acf' ),
				$row_number,
				$post_id->get_error_message()
			);
			continue;
		}

		// Save ACF fields.
		update_field( 'calf_name', $name, $post_id );
		update_field( 'registration_number', $regn, $post_id );
		update_field( 'stud_name', $stud_name, $post_id );
		update_field( 'herd_book', $hb, $post_id );
		update_field( 'brand_tattoo', $btat, $post_id );
		update_field( 'tattoo_number', $tat, $post_id );
		update_field( 'grade', $grade, $post_id );
		update_field( 'sex', $sex, $post_id );
		update_field( 'colour', $colour, $post_id );

		if ( $dob ) {
			update_field( 'date_of_birth', $dob, $post_id );
		}

		// Save sire/dam text fields.
		if ( $sregn ) {
			update_field( 'sire_tattoo', $sregn, $post_id );
		}
		if ( $dregn ) {
			update_field( 'dam_tattoo', $dregn, $post_id );
		}

		// Save extended parentage fields.
		update_field( 'sire_herd_book', $shb, $post_id );
		update_field( 'sire_grade', $sgrade, $post_id );
		update_field( 'dam_herd_book', $dhb, $post_id );
		update_field( 'dam_grade', $dgrade, $post_id );

		$imported_posts[ $regn ] = $post_id;
		$results['imported']++;

		// Queue lineage resolution.
		if ( $sregn || $dregn ) {
			$lineage_queue[ $post_id ] = array(
				'sire_regn' => $sregn,
				'dam_regn'  => $dregn,
			);
		}
	}

	fclose( $handle );

	// Pass 2: Resolve lineage links.
	foreach ( $lineage_queue as $post_id => $parents ) {
		$resolved = false;

		if ( ! empty( $parents['sire_regn'] ) ) {
			$sire_post_id = $imported_posts[ $parents['sire_regn'] ] ?? tailwind_cattle_import_find_by_regn( $parents['sire_regn'] );
			if ( $sire_post_id ) {
				update_field( 'sire_id', $sire_post_id, $post_id );
				// Also set sire_name from the linked post.
				$sire_name = get_field( 'calf_name', $sire_post_id );
				if ( $sire_name ) {
					update_field( 'sire_name', $sire_name, $post_id );
				}
				$resolved = true;
			}
		}

		if ( ! empty( $parents['dam_regn'] ) ) {
			$dam_post_id = $imported_posts[ $parents['dam_regn'] ] ?? tailwind_cattle_import_find_by_regn( $parents['dam_regn'] );
			if ( $dam_post_id ) {
				update_field( 'dam_id', $dam_post_id, $post_id );
				// Also set dam_name from the linked post.
				$dam_name = get_field( 'calf_name', $dam_post_id );
				if ( $dam_name ) {
					update_field( 'dam_name', $dam_name, $post_id );
				}
				$resolved = true;
			}
		}

		if ( $resolved ) {
			$results['lineage_resolved']++;
		} else {
			$results['lineage_unresolved']++;
		}
	}

	// Re-hook the notification.
	add_action( 'wp_insert_post', 'tailwind_cattle_notify_admin_on_submission', 10, 3 );

	return $results;
}

/**
 * Render the import admin page.
 */
function tailwind_cattle_import_page() {
	// Check permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'tailwind-acf' ) );
	}

	$results  = null;
	$preview  = null;
	$csv_path = null;

	// Handle preview upload.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked below.
	if ( isset( $_POST['cattle_import_preview'] ) ) {
		check_admin_referer( 'cattle_import_preview', 'cattle_import_nonce' );

		if ( ! empty( $_FILES['csv_file']['tmp_name'] ) ) {
			$csv_path = sanitize_text_field( $_FILES['csv_file']['tmp_name'] );

			// Read first 11 rows (header + 10 data rows) for preview.
			$handle = fopen( $csv_path, 'r' );
			if ( $handle ) {
				$preview = array();
				$row_count = 0;
				while ( false !== ( $row = fgetcsv( $handle ) ) && $row_count < 11 ) {
					$preview[] = $row;
					$row_count++;
				}

				// Count total rows.
				$total_rows = $row_count - 1; // Subtract header.
				while ( false !== fgetcsv( $handle ) ) {
					$total_rows++;
				}
				fclose( $handle );

				// Move uploaded file to a temporary location for the import step.
				$upload_dir = wp_upload_dir();
				$temp_path  = $upload_dir['basedir'] . '/cattle-import-temp.csv';
				move_uploaded_file( $_FILES['csv_file']['tmp_name'], $temp_path );
				$csv_path = $temp_path;
			}
		}
	}

	// Handle actual import.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked below.
	if ( isset( $_POST['cattle_import_run'] ) ) {
		check_admin_referer( 'cattle_import_run', 'cattle_import_run_nonce' );

		$csv_path = isset( $_POST['csv_path'] ) ? sanitize_text_field( wp_unslash( $_POST['csv_path'] ) ) : '';
		$upload_dir = wp_upload_dir();
		$expected_path = $upload_dir['basedir'] . '/cattle-import-temp.csv';

		// Security: only allow importing the expected temp file.
		if ( $csv_path === $expected_path && file_exists( $csv_path ) ) {
			$results = tailwind_cattle_import_process( $csv_path );
			// Clean up temp file.
			wp_delete_file( $csv_path );
		} else {
			$results = array(
				'imported'           => 0,
				'skipped'            => 0,
				'errors'             => 0,
				'lineage_resolved'   => 0,
				'lineage_unresolved' => 0,
				'error_messages'     => array( __( 'CSV file not found. Please re-upload.', 'tailwind-acf' ) ),
			);
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Animals', 'tailwind-acf' ); ?></h1>

		<?php if ( $results ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: imported count, 2: skipped count, 3: lineage resolved, 4: lineage unresolved */
						esc_html__( 'Import complete. Imported: %1$d | Skipped (duplicate): %2$d | Lineage links resolved: %3$d | Unresolved parents: %4$d', 'tailwind-acf' ),
						$results['imported'],
						$results['skipped'],
						$results['lineage_resolved'],
						$results['lineage_unresolved']
					);
					?>
				</p>
			</div>

			<?php if ( $results['errors'] > 0 ) : ?>
				<div class="notice notice-error">
					<p><?php printf( esc_html__( 'Errors: %d', 'tailwind-acf' ), $results['errors'] ); ?></p>
					<?php if ( ! empty( $results['error_messages'] ) ) : ?>
						<ul style="list-style: disc; padding-left: 20px;">
							<?php foreach ( array_slice( $results['error_messages'], 0, 20 ) as $msg ) : ?>
								<li><?php echo esc_html( $msg ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $preview && $csv_path ) : ?>
			<!-- Preview Table -->
			<h2><?php esc_html_e( 'CSV Preview', 'tailwind-acf' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %d: total row count */
					esc_html__( 'Showing first 10 of %d rows. Review the data below, then click "Run Import" to proceed.', 'tailwind-acf' ),
					$total_rows
				);
				?>
			</p>

			<div style="overflow-x: auto; margin-bottom: 20px;">
				<table class="widefat striped">
					<thead>
						<tr>
							<?php foreach ( $preview[0] as $col_header ) : ?>
								<th><?php echo esc_html( $col_header ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php for ( $i = 1; $i < count( $preview ); $i++ ) : ?>
							<tr>
								<?php foreach ( $preview[ $i ] as $cell ) : ?>
									<td><?php echo esc_html( $cell ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endfor; ?>
					</tbody>
				</table>
			</div>

			<form method="post">
				<?php wp_nonce_field( 'cattle_import_run', 'cattle_import_run_nonce' ); ?>
				<input type="hidden" name="csv_path" value="<?php echo esc_attr( $csv_path ); ?>">
				<p class="submit">
					<input type="submit" name="cattle_import_run" class="button button-primary button-hero" value="<?php esc_attr_e( 'Run Import', 'tailwind-acf' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cattle_registration&page=cattle-import' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Cancel', 'tailwind-acf' ); ?></a>
				</p>
			</form>

		<?php else : ?>
			<!-- Upload Form -->
			<p><?php esc_html_e( 'Upload a CSV file to import cattle registrations. The CSV must have the following columns: Stud, Name, AnmlSex, AnmlDOB, ColourLiteral, Regn, HB, AnmlGrade, BTat, Tat, SHB, SRegn, SGrade, DHB, DRegn, DGrade, AnmlOwnerNo.', 'tailwind-acf' ); ?></p>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'cattle_import_preview', 'cattle_import_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="csv_file"><?php esc_html_e( 'CSV File', 'tailwind-acf' ); ?></label>
						</th>
						<td>
							<input type="file" name="csv_file" id="csv_file" accept=".csv" required>
							<p class="description"><?php esc_html_e( 'Select a .csv file to upload.', 'tailwind-acf' ); ?></p>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="cattle_import_preview" class="button button-primary" value="<?php esc_attr_e( 'Upload & Preview', 'tailwind-acf' ); ?>">
				</p>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
```

**Step 2: Include the import module in functions.php**

In `functions.php`, add the require_once near the existing cattle-registration include:

```php
require_once get_template_directory() . '/inc/cattle-import.php';
```

**Step 3: Verify in wp-admin**

- Visit wp-admin → Cattle → Import Animals
- Confirm the upload form appears
- Upload the CSV file from `data/database-export-12012026.csv`
- Confirm the preview table shows 10 rows with correct columns
- Click "Run Import"
- Confirm success notice with counts

**Step 4: Commit**

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-import.php wp-content/themes/tailwind-acf/functions.php
git commit -m "feat: add CSV import admin page for bulk cattle registration import"
```

---

### Task 3: Add "Registration Number" and "Stud Name" columns to admin list

**Files:**
- Modify: `wp-content/themes/tailwind-acf/inc/cattle-registration.php:357-419` (admin columns)

**Step 1: Add columns to the admin list**

In `tailwind_cattle_admin_columns()`, add `registration_number` and `stud_name` columns after `title`:

```php
// Inside the if ( 'title' === $key ) block, add:
$new_columns['registration_number'] = __( 'Registration', 'tailwind-acf' );
$new_columns['stud_name'] = __( 'Stud', 'tailwind-acf' );
```

In `tailwind_cattle_admin_column_content()`, add cases:

```php
case 'registration_number':
    $regn = get_field( 'registration_number', $post_id );
    echo esc_html( $regn ?: '—' );
    break;

case 'stud_name':
    $stud = get_field( 'stud_name', $post_id );
    echo esc_html( $stud ?: '—' );
    break;
```

**Step 2: Verify in wp-admin**

Visit wp-admin → Cattle → All Registrations. Confirm "Registration" and "Stud" columns appear with data from imported animals.

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-registration.php
git commit -m "feat: add registration number and stud name columns to cattle admin list"
```

---

### Task 4: Add bulk "Change Owner" action to cattle admin list

**Files:**
- Modify: `wp-content/themes/tailwind-acf/inc/cattle-registration.php` (add new functions at end of file)

**Step 1: Register the bulk action and add a user dropdown**

Add these functions at the end of `cattle-registration.php`:

```php
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
function tailwind_cattle_owner_dropdown( $which ) {
	global $typenow;

	if ( 'cattle_registration' !== $typenow || 'top' !== $which ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$users = get_users(
		array(
			'meta_key'   => 'tailwind_member_status',
			'meta_value' => 'approved',
			'orderby'    => 'display_name',
			'order'      => 'ASC',
		)
	);

	?>
	<label for="cattle_new_owner" class="screen-reader-text"><?php esc_html_e( 'New Owner', 'tailwind-acf' ); ?></label>
	<select name="cattle_new_owner" id="cattle_new_owner" style="float:none; margin-left: 6px;">
		<option value=""><?php esc_html_e( '— Select Owner —', 'tailwind-acf' ); ?></option>
		<option value="0"><?php esc_html_e( 'No Owner (Unassigned)', 'tailwind-acf' ); ?></option>
		<?php foreach ( $users as $user ) : ?>
			<option value="<?php echo esc_attr( $user->ID ); ?>">
				<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'tailwind_cattle_owner_dropdown' );

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
```

**Step 2: Verify in wp-admin**

- Visit wp-admin → Cattle → All Registrations
- Confirm the "Change Owner" option appears in the Bulk Actions dropdown
- Confirm the user dropdown appears next to the filters
- Select a few cattle, choose "Change Owner", select a user, click Apply
- Confirm the posts now show that user as the "Submitter" column
- Login as that user → Dashboard should show the assigned animals

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/inc/cattle-registration.php
git commit -m "feat: add bulk 'Change Owner' action for cattle registrations"
```

---

### Task 5: Add 3-generation pedigree chart to animal detail page

**Files:**
- Modify: `wp-content/themes/tailwind-acf/single-cattle_registration.php:182-215` (parentage section)

**Step 1: Replace the parentage section with pedigree chart**

Replace the existing parentage section (lines 182-215) with a pedigree chart. Add new field fetches at the top of the template (after line 34) and replace the parentage section.

Add after line 34 (the existing field fetches):
```php
$sire_id   = get_field('sire_id', $post_id);
$dam_id    = get_field('dam_id', $post_id);
$registration_number = get_field('registration_number', $post_id);
$stud_name = get_field('stud_name', $post_id);
$brand_tattoo = get_field('brand_tattoo', $post_id);

// Build pedigree data (3 generations).
$pedigree = array(
    'animal' => array(
        'id'   => $post_id,
        'name' => $calf_name,
        'regn' => $registration_number ?: $tattoo,
        'url'  => get_permalink($post_id),
    ),
    'sire' => null,
    'dam'  => null,
    'sire_sire' => null,
    'sire_dam'  => null,
    'dam_sire'  => null,
    'dam_dam'   => null,
);

// Sire (parent).
if ($sire_id) {
    $pedigree['sire'] = array(
        'id'   => $sire_id,
        'name' => get_field('calf_name', $sire_id) ?: get_the_title($sire_id),
        'regn' => get_field('registration_number', $sire_id) ?: get_field('tattoo_number', $sire_id),
        'url'  => get_permalink($sire_id),
    );
    // Grandparents via sire.
    $sire_sire_id = get_field('sire_id', $sire_id);
    $sire_dam_id  = get_field('dam_id', $sire_id);
    if ($sire_sire_id) {
        $pedigree['sire_sire'] = array(
            'id'   => $sire_sire_id,
            'name' => get_field('calf_name', $sire_sire_id) ?: get_the_title($sire_sire_id),
            'regn' => get_field('registration_number', $sire_sire_id) ?: get_field('tattoo_number', $sire_sire_id),
            'url'  => get_permalink($sire_sire_id),
        );
    }
    if ($sire_dam_id) {
        $pedigree['sire_dam'] = array(
            'id'   => $sire_dam_id,
            'name' => get_field('calf_name', $sire_dam_id) ?: get_the_title($sire_dam_id),
            'regn' => get_field('registration_number', $sire_dam_id) ?: get_field('tattoo_number', $sire_dam_id),
            'url'  => get_permalink($sire_dam_id),
        );
    }
} elseif ($sire_name || $sire_tattoo) {
    $pedigree['sire'] = array(
        'id'   => null,
        'name' => $sire_name,
        'regn' => $sire_tattoo,
        'url'  => null,
    );
}

// Dam (parent).
if ($dam_id) {
    $pedigree['dam'] = array(
        'id'   => $dam_id,
        'name' => get_field('calf_name', $dam_id) ?: get_the_title($dam_id),
        'regn' => get_field('registration_number', $dam_id) ?: get_field('tattoo_number', $dam_id),
        'url'  => get_permalink($dam_id),
    );
    // Grandparents via dam.
    $dam_sire_id = get_field('sire_id', $dam_id);
    $dam_dam_id  = get_field('dam_id', $dam_id);
    if ($dam_sire_id) {
        $pedigree['dam_sire'] = array(
            'id'   => $dam_sire_id,
            'name' => get_field('calf_name', $dam_sire_id) ?: get_the_title($dam_sire_id),
            'regn' => get_field('registration_number', $dam_sire_id) ?: get_field('tattoo_number', $dam_sire_id),
            'url'  => get_permalink($dam_sire_id),
        );
    }
    if ($dam_dam_id) {
        $pedigree['dam_dam'] = array(
            'id'   => $dam_dam_id,
            'name' => get_field('calf_name', $dam_dam_id) ?: get_the_title($dam_dam_id),
            'regn' => get_field('registration_number', $dam_dam_id) ?: get_field('tattoo_number', $dam_dam_id),
            'url'  => get_permalink($dam_dam_id),
        );
    }
} elseif ($dam_name || $dam_tattoo) {
    $pedigree['dam'] = array(
        'id'   => null,
        'name' => $dam_name,
        'regn' => $dam_tattoo,
        'url'  => null,
    );
}

$has_any_lineage = $pedigree['sire'] || $pedigree['dam'];
```

Replace the parentage section (lines 182-215 in the original) with this pedigree chart. Use a PHP helper for rendering each ancestor box to keep the template DRY:

```php
<?php if ($has_any_lineage) : ?>
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-6 text-lg font-semibold text-slate-900">
            <?php esc_html_e('Pedigree', 'tailwind-acf'); ?>
        </h2>

        <!-- Desktop: horizontal pedigree (hidden on mobile) -->
        <div class="hidden md:block">
            <div class="flex items-stretch gap-0">
                <!-- Generation 1: Parents -->
                <div class="flex flex-col justify-center gap-4 min-w-[200px]">
                    <?php tailwind_render_pedigree_box($pedigree['sire'], __('Sire', 'tailwind-acf')); ?>
                    <?php tailwind_render_pedigree_box($pedigree['dam'], __('Dam', 'tailwind-acf')); ?>
                </div>

                <!-- Connector lines -->
                <div class="flex flex-col justify-center w-8">
                    <div class="flex-1 border-b-2 border-l-2 border-slate-200 rounded-bl-lg"></div>
                    <div class="flex-1 border-t-2 border-l-2 border-slate-200 rounded-tl-lg"></div>
                </div>

                <!-- Generation 2: Grandparents -->
                <div class="flex flex-col justify-between gap-2 min-w-[200px]">
                    <?php tailwind_render_pedigree_box($pedigree['sire_sire'], __('Sire\'s Sire', 'tailwind-acf')); ?>
                    <?php tailwind_render_pedigree_box($pedigree['sire_dam'], __('Sire\'s Dam', 'tailwind-acf')); ?>
                    <?php tailwind_render_pedigree_box($pedigree['dam_sire'], __('Dam\'s Sire', 'tailwind-acf')); ?>
                    <?php tailwind_render_pedigree_box($pedigree['dam_dam'], __('Dam\'s Dam', 'tailwind-acf')); ?>
                </div>
            </div>
        </div>

        <!-- Mobile: stacked pedigree -->
        <div class="md:hidden space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 mb-2"><?php esc_html_e('Parents', 'tailwind-acf'); ?></h3>
                <div class="space-y-3">
                    <?php tailwind_render_pedigree_box($pedigree['sire'], __('Sire', 'tailwind-acf')); ?>
                    <?php tailwind_render_pedigree_box($pedigree['dam'], __('Dam', 'tailwind-acf')); ?>
                </div>
            </div>
            <?php if ($pedigree['sire_sire'] || $pedigree['sire_dam'] || $pedigree['dam_sire'] || $pedigree['dam_dam']) : ?>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500 mb-2"><?php esc_html_e('Grandparents', 'tailwind-acf'); ?></h3>
                    <div class="space-y-3 pl-4 border-l-2 border-slate-200">
                        <?php tailwind_render_pedigree_box($pedigree['sire_sire'], __('Sire\'s Sire', 'tailwind-acf')); ?>
                        <?php tailwind_render_pedigree_box($pedigree['sire_dam'], __('Sire\'s Dam', 'tailwind-acf')); ?>
                        <?php tailwind_render_pedigree_box($pedigree['dam_sire'], __('Dam\'s Sire', 'tailwind-acf')); ?>
                        <?php tailwind_render_pedigree_box($pedigree['dam_dam'], __('Dam\'s Dam', 'tailwind-acf')); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
```

**Step 2: Add the pedigree box render helper**

Add this function to `inc/cattle-registration.php` (at the end, before the closing):

```php
/**
 * Render a pedigree box for an ancestor.
 *
 * @param array|null $ancestor Ancestor data (id, name, regn, url) or null.
 * @param string     $label    Relationship label (e.g., "Sire", "Dam").
 */
function tailwind_render_pedigree_box( $ancestor, $label ) {
	if ( ! $ancestor ) {
		// Empty placeholder.
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
		<p class="text-sm font-semibold text-slate-900 <?php echo $ancestor['url'] ? 'text-green-700' : ''; ?>">
			<?php echo esc_html( $ancestor['name'] ?: '—' ); ?>
		</p>
		<?php if ( $ancestor['regn'] ) : ?>
			<p class="text-xs font-mono text-slate-500"><?php echo esc_html( $ancestor['regn'] ); ?></p>
		<?php endif; ?>
	</<?php echo $tag; ?>>
	<?php
}
```

**Step 3: Verify on the front end**

- Visit an imported animal's detail page (one with sire/dam links)
- Confirm the pedigree chart shows with clickable ancestor links
- Click through to parent pages and confirm their pedigrees also display
- Test on mobile viewport to confirm vertical stack layout

**Step 4: Commit**

```bash
git add wp-content/themes/tailwind-acf/single-cattle_registration.php wp-content/themes/tailwind-acf/inc/cattle-registration.php
git commit -m "feat: add 3-generation pedigree chart to animal detail page"
```

---

### Task 6: Update single animal template with new fields

**Files:**
- Modify: `wp-content/themes/tailwind-acf/single-cattle_registration.php` (registration details section)

**Step 1: Display registration number, stud name, and brand tattoo**

In the "Registration Details" section (lines 104-130), add the new fields to the grid. After the existing fields, add:

```php
<?php if ($registration_number) : ?>
    <div class="border-b border-slate-100 pb-3">
        <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Registration', 'tailwind-acf'); ?></dt>
        <dd class="mt-1 text-base font-mono text-slate-900"><?php echo esc_html($registration_number); ?></dd>
    </div>
<?php endif; ?>

<?php if ($stud_name) : ?>
    <div class="border-b border-slate-100 pb-3">
        <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Stud', 'tailwind-acf'); ?></dt>
        <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($stud_name); ?></dd>
    </div>
<?php endif; ?>

<?php if ($brand_tattoo) : ?>
    <div class="border-b border-slate-100 pb-3">
        <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Brand Tattoo', 'tailwind-acf'); ?></dt>
        <dd class="mt-1 text-base font-mono text-slate-900"><?php echo esc_html($brand_tattoo); ?></dd>
    </div>
<?php endif; ?>
```

**Step 2: Verify**

Visit an imported animal's detail page. Confirm Registration Number, Stud Name, and Brand Tattoo display correctly.

**Step 3: Commit**

```bash
git add wp-content/themes/tailwind-acf/single-cattle_registration.php
git commit -m "feat: display registration number, stud name, and brand tattoo on detail page"
```

---

### Task 7: Enhance animal search with registration number and stud name

**Files:**
- Modify: `wp-content/themes/tailwind-acf/page-animal-search.php:106-141` (search meta query)

**Step 1: Add new fields to the search query**

In the full-text search meta query (lines 106-141), add two more LIKE comparisons inside the `'relation' => 'OR'` array:

```php
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
```

**Step 2: Display registration number and stud on search result cards**

In the search result card (lines 378-437), add the registration number and stud to the card display. After the tattoo display (line 400), add:

```php
<?php
$regn = get_field('registration_number', $post_id);
$stud = get_field('stud_name', $post_id);
?>
```

And in the `<dl>` grid, add a stud field:

```php
<?php if ($stud) : ?>
    <div>
        <dt class="text-slate-500"><?php esc_html_e('Stud', 'tailwind-acf'); ?></dt>
        <dd class="font-medium text-slate-900 truncate"><?php echo esc_html($stud); ?></dd>
    </div>
<?php endif; ?>
```

Also update the tattoo display line to prefer registration number when available:

```php
<p class="mt-1 text-sm font-mono text-slate-500">
    <?php echo esc_html($regn ?: $tattoo); ?>
</p>
```

**Step 3: Verify**

- Visit the animal search page
- Search for a stud name (e.g., "Arki") — confirm results appear
- Search for a registration number (e.g., "RIB G70") — confirm results appear
- Confirm the search result cards show stud name and registration number

**Step 4: Commit**

```bash
git add wp-content/themes/tailwind-acf/page-animal-search.php
git commit -m "feat: add registration number and stud name to animal search"
```

---

### Task 8: Add new fields to the front-end registration form

**Files:**
- Modify: `wp-content/themes/tailwind-acf/page-register-cattle.php`

**Step 1: Add registration_number, stud_name, and brand_tattoo to the form**

In the "Calf Information" section (after the tattoo_number field around line 416), add:

```html
<!-- Registration Number -->
<div>
    <label for="registration_number" class="block text-sm font-medium text-slate-700 mb-1">
        <?php esc_html_e('Registration Number', 'tailwind-acf'); ?>
    </label>
    <input
        type="text"
        id="registration_number"
        name="registration_number"
        value="<?php echo tailwind_cattle_old_value('registration_number', $form_data); ?>"
        class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand"
        placeholder="e.g. RIB G70">
    <p class="mt-1 text-xs text-slate-500"><?php esc_html_e('Optional. Full registration number.', 'tailwind-acf'); ?></p>
</div>

<!-- Stud Name -->
<div>
    <label for="stud_name" class="block text-sm font-medium text-slate-700 mb-1">
        <?php esc_html_e('Stud Name', 'tailwind-acf'); ?>
    </label>
    <input
        type="text"
        id="stud_name"
        name="stud_name"
        value="<?php echo tailwind_cattle_old_value('stud_name', $form_data); ?>"
        class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand">
</div>

<!-- Brand Tattoo -->
<div>
    <label for="brand_tattoo" class="block text-sm font-medium text-slate-700 mb-1">
        <?php esc_html_e('Brand Tattoo', 'tailwind-acf'); ?>
    </label>
    <input
        type="text"
        id="brand_tattoo"
        name="brand_tattoo"
        value="<?php echo tailwind_cattle_old_value('brand_tattoo', $form_data); ?>"
        class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand"
        placeholder="e.g. RIB">
    <p class="mt-1 text-xs text-slate-500"><?php esc_html_e('Optional. Tattoo prefix.', 'tailwind-acf'); ?></p>
</div>
```

**Step 2: Update form data collection, edit pre-population, and ACF save**

In the `$form_data` sanitization block (around line 138), add:

```php
'registration_number' => sanitize_text_field(wp_unslash($_POST['registration_number'] ?? '')),
'stud_name'           => sanitize_text_field(wp_unslash($_POST['stud_name'] ?? '')),
'brand_tattoo'        => sanitize_text_field(wp_unslash($_POST['brand_tattoo'] ?? '')),
```

In the edit mode pre-population (around line 55), add:

```php
'registration_number' => get_field('registration_number', $edit_post_id) ?: '',
'stud_name'           => get_field('stud_name', $edit_post_id) ?: '',
'brand_tattoo'        => get_field('brand_tattoo', $edit_post_id) ?: '',
```

In the ACF field save block (after line 243), add:

```php
update_field('registration_number', $form_data['registration_number'], $post_id);
update_field('stud_name', $form_data['stud_name'], $post_id);
update_field('brand_tattoo', $form_data['brand_tattoo'], $post_id);
```

**Step 3: Verify**

- Visit the Register Cattle page as an approved member
- Confirm new fields appear in the form
- Submit a test registration with the new fields
- Verify the data saves correctly

**Step 4: Commit**

```bash
git add wp-content/themes/tailwind-acf/page-register-cattle.php
git commit -m "feat: add registration number, stud name, and brand tattoo to registration form"
```

---

### Task 9: Final verification and cleanup

**Step 1: End-to-end verification**

Run through the complete flow:
1. Visit wp-admin → Cattle → Import Animals
2. Upload `data/database-export-12012026.csv`
3. Preview → Run Import
4. Confirm ~1,500 animals imported with success counts
5. Visit wp-admin → Cattle → All Registrations → verify data in admin columns
6. Use bulk "Change Owner" to assign a few animals to a test user
7. Log in as that user → Dashboard shows the assigned animals
8. Visit Animal Search → search by stud name, registration number
9. Click an animal with lineage → verify 3-gen pedigree chart
10. Click through ancestor links in the pedigree

**Step 2: Commit any final fixes**

```bash
git add -A
git commit -m "fix: final adjustments from end-to-end verification"
```
