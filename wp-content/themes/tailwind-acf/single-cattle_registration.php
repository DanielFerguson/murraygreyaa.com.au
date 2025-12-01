<?php

/**
 * Single template for cattle registrations.
 *
 * @package Tailwind_ACF
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $post_id   = get_the_ID();
    $calf_name = get_field('calf_name', $post_id);
    $grade     = get_field('grade', $post_id);
    $year_letter = get_field('year_letter', $post_id);
    $tattoo    = get_field('tattoo_number', $post_id);
    $dob       = get_field('date_of_birth', $post_id);
    $weight    = get_field('birth_weight', $post_id);
    $sex       = get_field('sex', $post_id);
    $colour    = get_field('colour', $post_id);
    $calving   = get_field('calving_ease', $post_id);
    $is_ai     = get_field('is_ai', $post_id);
    $is_et     = get_field('is_et', $post_id);
    $is_twin   = get_field('is_twin', $post_id);
    $sire_name = get_field('sire_name', $post_id);
    $sire_tattoo = get_field('sire_tattoo', $post_id);
    $dam_name  = get_field('dam_name', $post_id);
    $dam_tattoo = get_field('dam_tattoo', $post_id);

    // Labels.
    $grade_labels = array(
        'PB' => __('Pure Breed', 'tailwind-acf'),
        'A'  => __('A Grade', 'tailwind-acf'),
        'B'  => __('B Grade', 'tailwind-acf'),
        'C'  => __('C Grade', 'tailwind-acf'),
    );

    $sex_labels = array(
        'M' => __('Male', 'tailwind-acf'),
        'F' => __('Female', 'tailwind-acf'),
        'S' => __('Steer', 'tailwind-acf'),
    );

    $colour_labels = array(
        'G' => __('Grey', 'tailwind-acf'),
        'S' => __('Silver', 'tailwind-acf'),
        'B' => __('Black', 'tailwind-acf'),
        'D' => __('Dun', 'tailwind-acf'),
    );

    $calving_labels = array(
        '1' => __('Unassisted', 'tailwind-acf'),
        '2' => __('Assisted', 'tailwind-acf'),
        '3' => __('Fully Assisted', 'tailwind-acf'),
        '4' => __('Caesarean', 'tailwind-acf'),
        '5' => __('Breach', 'tailwind-acf'),
    );

    // Format date.
    $dob_formatted = $dob ? date_i18n(get_option('date_format'), strtotime($dob)) : '';
?>

    <main id="primary" class="site-main bg-slate-50">
        <div class="mx-auto max-w-3xl px-6 py-16 sm:px-10 lg:px-12">
            <header class="mb-10">
                <?php if (is_user_logged_in()) : ?>
                    <nav class="mb-4">
                        <a href="<?php echo esc_url(tailwind_member_get_dashboard_url()); ?>" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-brand transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            <?php esc_html_e('Back to Dashboard', 'tailwind-acf'); ?>
                        </a>
                    </nav>
                <?php endif; ?>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                            <?php echo esc_html($calf_name); ?>
                        </h1>
                        <p class="mt-2 text-lg text-slate-600 font-mono">
                            <?php echo esc_html($tattoo); ?>
                        </p>
                    </div>
                    <?php if (function_exists('tailwind_get_cattle_status_badge')) : ?>
                        <div class="mt-1">
                            <?php echo tailwind_get_cattle_status_badge(get_post_status()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Details Grid -->
            <div class="space-y-8">
                <!-- Basic Information -->
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-6 text-lg font-semibold text-slate-900">
                        <?php esc_html_e('Registration Details', 'tailwind-acf'); ?>
                    </h2>

                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Grade', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($grade_labels[$grade] ?? $grade); ?></dd>
                        </div>

                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Year Letter', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($year_letter); ?></dd>
                        </div>

                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Sex', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($sex_labels[$sex] ?? $sex); ?></dd>
                        </div>

                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Colour', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($colour_labels[$colour] ?? $colour); ?></dd>
                        </div>
                    </dl>
                </section>

                <!-- Birth Details -->
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-6 text-lg font-semibold text-slate-900">
                        <?php esc_html_e('Birth Details', 'tailwind-acf'); ?>
                    </h2>

                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Date of Birth', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($dob_formatted); ?></dd>
                        </div>

                        <?php if ($weight) : ?>
                            <div class="border-b border-slate-100 pb-3">
                                <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Birth Weight', 'tailwind-acf'); ?></dt>
                                <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($weight); ?> kg</dd>
                            </div>
                        <?php endif; ?>

                        <div class="border-b border-slate-100 pb-3">
                            <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Calving Ease', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 text-base text-slate-900"><?php echo esc_html($calving_labels[$calving] ?? $calving); ?></dd>
                        </div>

                        <div class="border-b border-slate-100 pb-3 sm:col-span-2">
                            <dt class="text-sm font-medium text-slate-500 mb-2"><?php esc_html_e('Breeding Methods', 'tailwind-acf'); ?></dt>
                            <dd class="mt-1 flex flex-wrap gap-2">
                                <?php if ($is_ai) : ?>
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                        <?php esc_html_e('A.I.', 'tailwind-acf'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($is_et) : ?>
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                                        <?php esc_html_e('E.T.', 'tailwind-acf'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($is_twin) : ?>
                                    <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">
                                        <?php esc_html_e('Twin', 'tailwind-acf'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (! $is_ai && ! $is_et && ! $is_twin) : ?>
                                    <span class="text-slate-500 text-sm"><?php esc_html_e('None specified', 'tailwind-acf'); ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- Parentage -->
                <?php if ($sire_name || $dam_name) : ?>
                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-slate-900">
                            <?php esc_html_e('Parentage', 'tailwind-acf'); ?>
                        </h2>

                        <dl class="grid gap-6 sm:grid-cols-2">
                            <?php if ($sire_name) : ?>
                                <div>
                                    <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Sire', 'tailwind-acf'); ?></dt>
                                    <dd class="mt-1">
                                        <p class="text-base font-medium text-slate-900"><?php echo esc_html($sire_name); ?></p>
                                        <?php if ($sire_tattoo) : ?>
                                            <p class="text-sm text-slate-600 font-mono"><?php echo esc_html($sire_tattoo); ?></p>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                            <?php endif; ?>

                            <?php if ($dam_name) : ?>
                                <div>
                                    <dt class="text-sm font-medium text-slate-500"><?php esc_html_e('Dam', 'tailwind-acf'); ?></dt>
                                    <dd class="mt-1">
                                        <p class="text-base font-medium text-slate-900"><?php echo esc_html($dam_name); ?></p>
                                        <?php if ($dam_tattoo) : ?>
                                            <p class="text-sm text-slate-600 font-mono"><?php echo esc_html($dam_tattoo); ?></p>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </section>
                <?php endif; ?>

                <!-- Meta -->
                <section class="text-sm text-slate-500">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: date */
                            esc_html__('Registered on %s', 'tailwind-acf'),
                            esc_html(get_the_date())
                        );
                        ?>
                    </p>
                </section>
            </div>
        </div>
    </main>

<?php
endwhile;

get_footer();

