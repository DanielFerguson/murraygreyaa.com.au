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
    $sire_id   = get_field('sire_id', $post_id);
    $dam_id    = get_field('dam_id', $post_id);
    $registration_number = get_field('registration_number', $post_id);
    $stud_name = get_field('stud_name', $post_id);
    $brand_tattoo = get_field('brand_tattoo', $post_id);

    // Build pedigree data.
    $pedigree = array(
        'sire' => null,
        'dam'  => null,
        'sire_sire' => null,
        'sire_dam'  => null,
        'dam_sire'  => null,
        'dam_dam'   => null,
    );

    // Sire
    if ($sire_id) {
        $pedigree['sire'] = array(
            'id'   => $sire_id,
            'name' => get_field('calf_name', $sire_id) ?: get_the_title($sire_id),
            'regn' => get_field('registration_number', $sire_id) ?: get_field('tattoo_number', $sire_id),
            'url'  => get_permalink($sire_id),
        );
        $ss = get_field('sire_id', $sire_id);
        $sd = get_field('dam_id', $sire_id);
        if ($ss) {
            $pedigree['sire_sire'] = array(
                'id' => $ss, 'name' => get_field('calf_name', $ss) ?: get_the_title($ss),
                'regn' => get_field('registration_number', $ss) ?: get_field('tattoo_number', $ss),
                'url' => get_permalink($ss),
            );
        }
        if ($sd) {
            $pedigree['sire_dam'] = array(
                'id' => $sd, 'name' => get_field('calf_name', $sd) ?: get_the_title($sd),
                'regn' => get_field('registration_number', $sd) ?: get_field('tattoo_number', $sd),
                'url' => get_permalink($sd),
            );
        }
    } elseif ($sire_name || $sire_tattoo) {
        $pedigree['sire'] = array('id' => null, 'name' => $sire_name, 'regn' => $sire_tattoo, 'url' => null);
    }

    // Dam
    if ($dam_id) {
        $pedigree['dam'] = array(
            'id'   => $dam_id,
            'name' => get_field('calf_name', $dam_id) ?: get_the_title($dam_id),
            'regn' => get_field('registration_number', $dam_id) ?: get_field('tattoo_number', $dam_id),
            'url'  => get_permalink($dam_id),
        );
        $ds = get_field('sire_id', $dam_id);
        $dd = get_field('dam_id', $dam_id);
        if ($ds) {
            $pedigree['dam_sire'] = array(
                'id' => $ds, 'name' => get_field('calf_name', $ds) ?: get_the_title($ds),
                'regn' => get_field('registration_number', $ds) ?: get_field('tattoo_number', $ds),
                'url' => get_permalink($ds),
            );
        }
        if ($dd) {
            $pedigree['dam_dam'] = array(
                'id' => $dd, 'name' => get_field('calf_name', $dd) ?: get_the_title($dd),
                'regn' => get_field('registration_number', $dd) ?: get_field('tattoo_number', $dd),
                'url' => get_permalink($dd),
            );
        }
    } elseif ($dam_name || $dam_tattoo) {
        $pedigree['dam'] = array('id' => null, 'name' => $dam_name, 'regn' => $dam_tattoo, 'url' => null);
    }

    $has_any_lineage = $pedigree['sire'] || $pedigree['dam'];

    // Labels.
    $grade_labels   = tailwind_cattle_get_grade_labels();
    $sex_labels     = tailwind_cattle_get_sex_labels();
    $colour_labels  = tailwind_cattle_get_colour_labels();
    $calving_labels = tailwind_cattle_get_calving_ease_labels();

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

                <!-- Pedigree -->
                <?php if ($has_any_lineage) : ?>
                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-slate-900">
                            <?php esc_html_e('Pedigree', 'tailwind-acf'); ?>
                        </h2>

                        <!-- Desktop: horizontal pedigree -->
                        <div class="hidden md:block">
                            <div class="flex items-stretch gap-0">
                                <!-- Generation 1: Parents -->
                                <div class="flex flex-col justify-center gap-4 min-w-[200px]">
                                    <?php tailwind_render_pedigree_box($pedigree['sire'], __('Sire', 'tailwind-acf')); ?>
                                    <?php tailwind_render_pedigree_box($pedigree['dam'], __('Dam', 'tailwind-acf')); ?>
                                </div>

                                <!-- Connector -->
                                <div class="flex flex-col justify-center w-8">
                                    <div class="flex-1 border-b-2 border-l-2 border-slate-200 rounded-bl-lg"></div>
                                    <div class="flex-1 border-t-2 border-l-2 border-slate-200 rounded-tl-lg"></div>
                                </div>

                                <!-- Generation 2: Grandparents -->
                                <div class="flex flex-col justify-between gap-2 min-w-[200px]">
                                    <?php tailwind_render_pedigree_box($pedigree['sire_sire'], __("Sire's Sire", 'tailwind-acf')); ?>
                                    <?php tailwind_render_pedigree_box($pedigree['sire_dam'], __("Sire's Dam", 'tailwind-acf')); ?>
                                    <?php tailwind_render_pedigree_box($pedigree['dam_sire'], __("Dam's Sire", 'tailwind-acf')); ?>
                                    <?php tailwind_render_pedigree_box($pedigree['dam_dam'], __("Dam's Dam", 'tailwind-acf')); ?>
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
                                        <?php tailwind_render_pedigree_box($pedigree['sire_sire'], __("Sire's Sire", 'tailwind-acf')); ?>
                                        <?php tailwind_render_pedigree_box($pedigree['sire_dam'], __("Sire's Dam", 'tailwind-acf')); ?>
                                        <?php tailwind_render_pedigree_box($pedigree['dam_sire'], __("Dam's Sire", 'tailwind-acf')); ?>
                                        <?php tailwind_render_pedigree_box($pedigree['dam_dam'], __("Dam's Dam", 'tailwind-acf')); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
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







