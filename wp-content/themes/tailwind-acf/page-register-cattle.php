<?php

/**
 * Template Name: Register Cattle
 * Template Post Type: page
 *
 * Front-end cattle registration form for approved members.
 *
 * @package Tailwind_ACF
 */

if (! defined('ABSPATH')) {
    exit;
}

// Restrict to logged-in users.
if (! is_user_logged_in()) {
    wp_safe_redirect(wp_login_url(get_permalink()));
    exit;
}

// Check member status - only approved members can register cattle.
$member_status = tailwind_member_get_status(get_current_user_id());
if (TAILWIND_MEMBER_STATUS_APPROVED !== $member_status) {
    wp_safe_redirect(
        add_query_arg('denied', '1', tailwind_member_get_dashboard_url())
    );
    exit;
}

$errors       = array();
$success      = false;
$form_data    = array();
$field_errors = array();

// Edit mode - check if editing an existing registration.
$edit_post_id = 0;
$is_edit_mode = false;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Initial page load, no state change.
if (isset($_GET['edit'])) {
    $edit_post_id = absint($_GET['edit']);
    $edit_post    = get_post($edit_post_id);

    // Validate: post exists, is cattle_registration, belongs to current user, and is pending.
    if (
        $edit_post
        && 'cattle_registration' === $edit_post->post_type
        && (int) $edit_post->post_author === get_current_user_id()
        && 'pending' === $edit_post->post_status
    ) {
        $is_edit_mode = true;

        // Pre-populate form data from existing post.
        $form_data = array(
            'calf_name'     => get_field('calf_name', $edit_post_id) ?: '',
            'grade'         => get_field('grade', $edit_post_id) ?: '',
            'year_letter'   => get_field('year_letter', $edit_post_id) ?: '',
            'tattoo_number'      => get_field('tattoo_number', $edit_post_id) ?: '',
            'registration_number' => get_field('registration_number', $edit_post_id) ?: '',
            'stud_name'           => get_field('stud_name', $edit_post_id) ?: '',
            'brand_tattoo'        => get_field('brand_tattoo', $edit_post_id) ?: '',
            'date_of_birth' => get_field('date_of_birth', $edit_post_id) ?: '',
            'birth_weight'  => get_field('birth_weight', $edit_post_id) ?: '',
            'sex'           => get_field('sex', $edit_post_id) ?: '',
            'colour'        => get_field('colour', $edit_post_id) ?: '',
            'calving_ease'  => get_field('calving_ease', $edit_post_id) ?: '',
            'is_ai'         => get_field('is_ai', $edit_post_id) ?: false,
            'is_et'         => get_field('is_et', $edit_post_id) ?: false,
            'is_twin'       => get_field('is_twin', $edit_post_id) ?: false,
            'sire_name'     => get_field('sire_name', $edit_post_id) ?: '',
            'sire_tattoo'   => get_field('sire_tattoo', $edit_post_id) ?: '',
            'dam_name'      => get_field('dam_name', $edit_post_id) ?: '',
            'dam_tattoo'    => get_field('dam_tattoo', $edit_post_id) ?: '',
        );
    } else {
        // Invalid edit request - redirect to dashboard.
        wp_safe_redirect(
            add_query_arg('denied', '1', tailwind_member_get_dashboard_url())
        );
        exit;
    }
}

$grade_options        = tailwind_cattle_get_grade_options();
$sex_options          = tailwind_cattle_get_sex_options();
$colour_options       = tailwind_cattle_get_colour_options();
$calving_ease_options = tailwind_cattle_get_calving_ease_options();

// Handle form submission.
if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['tailwind_cattle_nonce'])) {
    // Verify nonce.
    if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tailwind_cattle_nonce'])), 'tailwind_cattle_registration')) {
        $errors[] = __('Security check failed. Please try again.', 'tailwind-acf');
    } else {
        // Check for edit mode from POST data.
        if (isset($_POST['edit_post_id'])) {
            $posted_edit_id = absint($_POST['edit_post_id']);
            $edit_post      = get_post($posted_edit_id);

            // Validate the edit request.
            if (
                $edit_post
                && 'cattle_registration' === $edit_post->post_type
                && (int) $edit_post->post_author === get_current_user_id()
                && 'pending' === $edit_post->post_status
            ) {
                $edit_post_id = $posted_edit_id;
                $is_edit_mode = true;
            }
        }

        // Sanitize and collect form data.
        $form_data = array(
            'calf_name'     => sanitize_text_field(wp_unslash($_POST['calf_name'] ?? '')),
            'grade'         => sanitize_text_field(wp_unslash($_POST['grade'] ?? '')),
            'year_letter'   => strtoupper(sanitize_text_field(wp_unslash($_POST['year_letter'] ?? ''))),
            'tattoo_number'      => sanitize_text_field(wp_unslash($_POST['tattoo_number'] ?? '')),
            'registration_number' => sanitize_text_field(wp_unslash($_POST['registration_number'] ?? '')),
            'stud_name'           => sanitize_text_field(wp_unslash($_POST['stud_name'] ?? '')),
            'brand_tattoo'        => sanitize_text_field(wp_unslash($_POST['brand_tattoo'] ?? '')),
            'date_of_birth' => sanitize_text_field(wp_unslash($_POST['date_of_birth'] ?? '')),
            'birth_weight'  => floatval($_POST['birth_weight'] ?? 0),
            'sex'           => sanitize_text_field(wp_unslash($_POST['sex'] ?? '')),
            'colour'        => sanitize_text_field(wp_unslash($_POST['colour'] ?? '')),
            'calving_ease'  => sanitize_text_field(wp_unslash($_POST['calving_ease'] ?? '')),
            'is_ai'         => ! empty($_POST['is_ai']),
            'is_et'         => ! empty($_POST['is_et']),
            'is_twin'       => ! empty($_POST['is_twin']),
            'sire_name'     => sanitize_text_field(wp_unslash($_POST['sire_name'] ?? '')),
            'sire_tattoo'   => sanitize_text_field(wp_unslash($_POST['sire_tattoo'] ?? '')),
            'dam_name'      => sanitize_text_field(wp_unslash($_POST['dam_name'] ?? '')),
            'dam_tattoo'    => sanitize_text_field(wp_unslash($_POST['dam_tattoo'] ?? '')),
        );

        // Validation.
        if (empty($form_data['calf_name']) || strlen($form_data['calf_name']) < 2) {
            $field_errors['calf_name'] = __('Calf name is required (minimum 2 characters).', 'tailwind-acf');
        }

        if (empty($form_data['grade']) || ! array_key_exists($form_data['grade'], $grade_options)) {
            $field_errors['grade'] = __('Please select a valid grade.', 'tailwind-acf');
        }

        if (empty($form_data['year_letter']) || ! preg_match('/^[A-HJ-NP-Z]$/', $form_data['year_letter'])) {
            $field_errors['year_letter'] = __('Year letter must be a single uppercase letter (A-Z, excluding I and O).', 'tailwind-acf');
        }

        if (empty($form_data['tattoo_number'])) {
            $field_errors['tattoo_number'] = __('Tattoo number is required.', 'tailwind-acf');
        }

        if (empty($form_data['date_of_birth'])) {
            $field_errors['date_of_birth'] = __('Date of birth is required.', 'tailwind-acf');
        } else {
            $dob = strtotime($form_data['date_of_birth']);
            if (! $dob || $dob > time()) {
                $field_errors['date_of_birth'] = __('Date of birth cannot be in the future.', 'tailwind-acf');
            }
        }

        if ($form_data['birth_weight'] && ($form_data['birth_weight'] < 15 || $form_data['birth_weight'] > 80)) {
            $field_errors['birth_weight'] = __('Birth weight must be between 15 and 80 kg.', 'tailwind-acf');
        }

        if (empty($form_data['sex']) || ! array_key_exists($form_data['sex'], $sex_options)) {
            $field_errors['sex'] = __('Please select a valid sex.', 'tailwind-acf');
        }

        if (empty($form_data['colour']) || ! array_key_exists($form_data['colour'], $colour_options)) {
            $field_errors['colour'] = __('Please select a valid colour.', 'tailwind-acf');
        }

        if (empty($form_data['calving_ease']) || ! array_key_exists($form_data['calving_ease'], $calving_ease_options)) {
            $field_errors['calving_ease'] = __('Please select a valid calving ease.', 'tailwind-acf');
        }

        // If no errors, create or update the post.
        if (empty($field_errors)) {
            $post_title = $form_data['calf_name'] . ' (' . $form_data['tattoo_number'] . ')';

            if ($is_edit_mode && $edit_post_id) {
                // Update existing post.
                $post_id = wp_update_post(
                    array(
                        'ID'         => $edit_post_id,
                        'post_title' => $post_title,
                        'post_name'  => sanitize_title($post_title),
                    )
                );
            } else {
                // Create new post.
                $post_id = wp_insert_post(
                    array(
                        'post_type'   => 'cattle_registration',
                        'post_title'  => $post_title,
                        'post_status' => 'pending',
                        'post_author' => get_current_user_id(),
                    )
                );
            }

            if (is_wp_error($post_id)) {
                $errors[] = $post_id->get_error_message();
            } else {
                // Save ACF fields.
                update_field('calf_name', $form_data['calf_name'], $post_id);
                update_field('grade', $form_data['grade'], $post_id);
                update_field('year_letter', $form_data['year_letter'], $post_id);
                update_field('tattoo_number', $form_data['tattoo_number'], $post_id);
                update_field('registration_number', $form_data['registration_number'], $post_id);
                update_field('stud_name', $form_data['stud_name'], $post_id);
                update_field('brand_tattoo', $form_data['brand_tattoo'], $post_id);
                update_field('date_of_birth', $form_data['date_of_birth'], $post_id);
                update_field('birth_weight', $form_data['birth_weight'], $post_id);
                update_field('sex', $form_data['sex'], $post_id);
                update_field('colour', $form_data['colour'], $post_id);
                update_field('calving_ease', $form_data['calving_ease'], $post_id);
                update_field('is_ai', $form_data['is_ai'], $post_id);
                update_field('is_et', $form_data['is_et'], $post_id);
                update_field('is_twin', $form_data['is_twin'], $post_id);
                update_field('sire_name', $form_data['sire_name'], $post_id);
                update_field('sire_tattoo', $form_data['sire_tattoo'], $post_id);
                update_field('dam_name', $form_data['dam_name'], $post_id);
                update_field('dam_tattoo', $form_data['dam_tattoo'], $post_id);

                // Redirect to dashboard with success message.
                wp_safe_redirect(
                    add_query_arg(
                        $is_edit_mode ? 'cattle_updated' : 'cattle_submitted',
                        '1',
                        tailwind_member_get_dashboard_url()
                    )
                );
                exit;
            }
        }
    }
}

get_header();

// Define helper functions only if they don't exist (prevents redefinition errors).
if (! function_exists('tailwind_cattle_field_error_class')) {
    /**
     * Helper to get field error class for cattle registration form.
     *
     * @param string $field        Field name.
     * @param array  $field_errors Array of field errors.
     * @return string
     */
    function tailwind_cattle_field_error_class($field, $field_errors)
    {
        return isset($field_errors[$field]) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 focus:border-brand focus:ring-brand';
    }
}

if (! function_exists('tailwind_cattle_old_value')) {
    /**
     * Helper to get old form value for cattle registration form.
     *
     * @param string $field     Field name.
     * @param array  $form_data Form data array.
     * @param string $default   Default value.
     * @return string
     */
    function tailwind_cattle_old_value($field, $form_data, $default = '')
    {
        return isset($form_data[$field]) ? esc_attr($form_data[$field]) : $default;
    }
}
?>

<main id="primary" class="site-main bg-slate-50">
    <div class="mx-auto max-w-3xl px-6 py-16 sm:px-10 lg:px-12">
        <header class="mb-10">
            <nav class="mb-4">
                <a href="<?php echo esc_url(tailwind_member_get_dashboard_url()); ?>" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-brand transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <?php esc_html_e('Back to Dashboard', 'tailwind-acf'); ?>
                </a>
            </nav>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                <?php echo $is_edit_mode ? esc_html__('Edit Cattle Registration', 'tailwind-acf') : esc_html__('Register New Cattle', 'tailwind-acf'); ?>
            </h1>
            <p class="mt-2 text-base text-slate-600">
                <?php
                if ($is_edit_mode) {
                    esc_html_e('Update the details below. Your changes will be saved but the registration will remain pending until approved.', 'tailwind-acf');
                } else {
                    esc_html_e('Complete the form below to submit a new cattle registration. An administrator will review your submission.', 'tailwind-acf');
                }
                ?>
            </p>
        </header>

        <?php if (! empty($errors)) : ?>
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-8">
            <?php wp_nonce_field('tailwind_cattle_registration', 'tailwind_cattle_nonce'); ?>
            <?php if ($is_edit_mode) : ?>
                <input type="hidden" name="edit_post_id" value="<?php echo esc_attr($edit_post_id); ?>">
            <?php endif; ?>

            <!-- Calf Information -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-6 text-lg font-semibold text-slate-900">
                    <?php esc_html_e('Calf Information', 'tailwind-acf'); ?>
                </h2>

                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- Calf Name -->
                    <div>
                        <label for="calf_name" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Calf Name', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="calf_name"
                            name="calf_name"
                            value="<?php echo tailwind_cattle_old_value('calf_name', $form_data); ?>"
                            required
                            maxlength="100"
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('calf_name', $field_errors); ?>">
                        <?php if (isset($field_errors['calf_name'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['calf_name']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Grade -->
                    <div>
                        <label for="grade" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Grade', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="grade"
                            name="grade"
                            required
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('grade', $field_errors); ?>">
                            <option value=""><?php esc_html_e('Select grade...', 'tailwind-acf'); ?></option>
                            <?php foreach ($grade_options as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(tailwind_cattle_old_value('grade', $form_data), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($field_errors['grade'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['grade']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Year Letter -->
                    <div>
                        <label for="year_letter" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Year Letter', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="year_letter"
                            name="year_letter"
                            value="<?php echo tailwind_cattle_old_value('year_letter', $form_data); ?>"
                            required
                            maxlength="1"
                            pattern="[A-HJ-NP-Za-hj-np-z]"
                            class="block w-full rounded-lg shadow-sm text-sm uppercase <?php echo tailwind_cattle_field_error_class('year_letter', $field_errors); ?>"
                            placeholder="A">
                        <p class="mt-1 text-xs text-slate-500"><?php esc_html_e('Single letter (A-Z, excluding I and O)', 'tailwind-acf'); ?></p>
                        <?php if (isset($field_errors['year_letter'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['year_letter']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tattoo Number -->
                    <div>
                        <label for="tattoo_number" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Tattoo Number', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="tattoo_number"
                            name="tattoo_number"
                            value="<?php echo tailwind_cattle_old_value('tattoo_number', $form_data); ?>"
                            required
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('tattoo_number', $field_errors); ?>">
                        <?php if (isset($field_errors['tattoo_number'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['tattoo_number']); ?></p>
                        <?php endif; ?>
                    </div>

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
                </div>
            </div>

            <!-- Birth Details -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-6 text-lg font-semibold text-slate-900">
                    <?php esc_html_e('Birth Details', 'tailwind-acf'); ?>
                </h2>

                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Date of Birth', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="<?php echo tailwind_cattle_old_value('date_of_birth', $form_data); ?>"
                            required
                            max="<?php echo esc_attr(date('Y-m-d')); ?>"
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('date_of_birth', $field_errors); ?>">
                        <?php if (isset($field_errors['date_of_birth'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['date_of_birth']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Birth Weight -->
                    <div>
                        <label for="birth_weight" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Birth Weight (kg)', 'tailwind-acf'); ?>
                        </label>
                        <input
                            type="number"
                            id="birth_weight"
                            name="birth_weight"
                            value="<?php echo tailwind_cattle_old_value('birth_weight', $form_data); ?>"
                            min="15"
                            max="80"
                            step="0.1"
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('birth_weight', $field_errors); ?>"
                            placeholder="35.0">
                        <p class="mt-1 text-xs text-slate-500"><?php esc_html_e('Optional, 15-80 kg', 'tailwind-acf'); ?></p>
                        <?php if (isset($field_errors['birth_weight'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['birth_weight']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Sex -->
                    <div>
                        <label for="sex" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Sex', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="sex"
                            name="sex"
                            required
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('sex', $field_errors); ?>">
                            <option value=""><?php esc_html_e('Select sex...', 'tailwind-acf'); ?></option>
                            <?php foreach ($sex_options as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(tailwind_cattle_old_value('sex', $form_data), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($field_errors['sex'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['sex']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Colour -->
                    <div>
                        <label for="colour" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Colour', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="colour"
                            name="colour"
                            required
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('colour', $field_errors); ?>">
                            <option value=""><?php esc_html_e('Select colour...', 'tailwind-acf'); ?></option>
                            <?php foreach ($colour_options as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(tailwind_cattle_old_value('colour', $form_data), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($field_errors['colour'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['colour']); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Calving Ease -->
                    <div class="sm:col-span-2">
                        <label for="calving_ease" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e('Calving Ease', 'tailwind-acf'); ?> <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="calving_ease"
                            name="calving_ease"
                            required
                            class="block w-full rounded-lg shadow-sm text-sm <?php echo tailwind_cattle_field_error_class('calving_ease', $field_errors); ?>">
                            <option value=""><?php esc_html_e('Select calving ease...', 'tailwind-acf'); ?></option>
                            <?php foreach ($calving_ease_options as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(tailwind_cattle_old_value('calving_ease', $form_data), $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($field_errors['calving_ease'])) : ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo esc_html($field_errors['calving_ease']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Boolean Flags -->
                <div class="mt-6 flex flex-wrap gap-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_ai"
                            value="1"
                            <?php checked(tailwind_cattle_old_value('is_ai', $form_data)); ?>
                            class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                        <span class="text-sm text-slate-700"><?php esc_html_e('A.I. (Artificial Insemination)', 'tailwind-acf'); ?></span>
                    </label>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_et"
                            value="1"
                            <?php checked(tailwind_cattle_old_value('is_et', $form_data)); ?>
                            class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                        <span class="text-sm text-slate-700"><?php esc_html_e('E.T. (Embryo Transfer)', 'tailwind-acf'); ?></span>
                    </label>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_twin"
                            value="1"
                            <?php checked(tailwind_cattle_old_value('is_twin', $form_data)); ?>
                            class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
                        <span class="text-sm text-slate-700"><?php esc_html_e('Twin', 'tailwind-acf'); ?></span>
                    </label>
                </div>
            </div>

            <!-- Parentage -->
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-6 text-lg font-semibold text-slate-900">
                    <?php esc_html_e('Parentage', 'tailwind-acf'); ?>
                </h2>

                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- Sire's Name -->
                    <div>
                        <label for="sire_name" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e("Sire's Name", 'tailwind-acf'); ?>
                        </label>
                        <input
                            type="text"
                            id="sire_name"
                            name="sire_name"
                            value="<?php echo tailwind_cattle_old_value('sire_name', $form_data); ?>"
                            class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand">
                    </div>

                    <!-- Sire's Tattoo -->
                    <div>
                        <label for="sire_tattoo" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e("Sire's Registration Number/Tattoo", 'tailwind-acf'); ?>
                        </label>
                        <input
                            type="text"
                            id="sire_tattoo"
                            name="sire_tattoo"
                            value="<?php echo tailwind_cattle_old_value('sire_tattoo', $form_data); ?>"
                            class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand">
                    </div>

                    <!-- Dam's Name -->
                    <div>
                        <label for="dam_name" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e("Dam's Name", 'tailwind-acf'); ?>
                        </label>
                        <input
                            type="text"
                            id="dam_name"
                            name="dam_name"
                            value="<?php echo tailwind_cattle_old_value('dam_name', $form_data); ?>"
                            class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand">
                    </div>

                    <!-- Dam's Tattoo -->
                    <div>
                        <label for="dam_tattoo" class="block text-sm font-medium text-slate-700 mb-1">
                            <?php esc_html_e("Dam's Registration Number/Tattoo", 'tailwind-acf'); ?>
                        </label>
                        <input
                            type="text"
                            id="dam_tattoo"
                            name="dam_tattoo"
                            value="<?php echo tailwind_cattle_old_value('dam_tattoo', $form_data); ?>"
                            class="block w-full rounded-lg border-slate-300 shadow-sm text-sm focus:border-brand focus:ring-brand">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4">
                <a
                    href="<?php echo esc_url(tailwind_member_get_dashboard_url()); ?>"
                    class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    <?php esc_html_e('Cancel', 'tailwind-acf'); ?>
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                    <?php echo $is_edit_mode ? esc_html__('Save Changes', 'tailwind-acf') : esc_html__('Submit Registration', 'tailwind-acf'); ?>
                </button>
            </div>
        </form>
    </div>
</main>

<?php
get_footer();
