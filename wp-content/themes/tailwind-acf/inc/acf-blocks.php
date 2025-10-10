<?php
/**
 * ACF block registration and field definitions.
 *
 * @package Tailwind_ACF
 */

add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}

		acf_register_block_type(
			array(
				'name'            => 'tailwind-hero',
				'title'           => __( 'Tailwind Hero', 'tailwind-acf' ),
				'description'     => __( 'A full-width hero with background image and call-to-action.', 'tailwind-acf' ),
				'render_template' => get_theme_file_path( 'template-parts/blocks/hero.php' ),
				'category'        => 'layout',
				'icon'            => 'align-full-width',
				'keywords'        => array( 'hero', 'banner', 'tailwind' ),
				'supports'        => array(
					'align'        => array( 'full', 'wide' ),
					'anchor'       => true,
					'customClassName' => true,
				),
				'example'         => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'headline'      => __( 'Create something great', 'tailwind-acf' ),
							'content'       => __( 'Use Tailwind utility classes right inside the block editor.', 'tailwind-acf' ),
							'cta_label'     => __( 'Get started', 'tailwind-acf' ),
							'cta_url'       => '#',
							'eyebrow'       => __( 'Announcement', 'tailwind-acf' ),
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'tailwind-feature-grid',
				'title'           => __( 'Tailwind Feature Grid', 'tailwind-acf' ),
				'description'     => __( 'A responsive grid to highlight product features.', 'tailwind-acf' ),
				'render_template' => get_theme_file_path( 'template-parts/blocks/feature-grid.php' ),
				'category'        => 'layout',
				'icon'            => 'grid-view',
				'keywords'        => array( 'features', 'grid', 'tailwind' ),
				'supports'        => array(
					'align'        => array( 'full', 'wide' ),
					'anchor'       => true,
					'customClassName' => true,
				),
				'example'         => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'heading' => __( 'Why teams choose us', 'tailwind-acf' ),
							'intro'   => __( 'Utility-first styling with a customisable grid layout.', 'tailwind-acf' ),
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_tailwind_hero',
				'title'    => __( 'Tailwind Hero', 'tailwind-acf' ),
				'fields'   => array(
					array(
						'key'   => 'field_tailwind_hero_eyebrow',
						'label' => __( 'Eyebrow', 'tailwind-acf' ),
						'name'  => 'eyebrow',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_tailwind_hero_headline',
						'label' => __( 'Headline', 'tailwind-acf' ),
						'name'  => 'headline',
						'type'  => 'textarea',
					),
					array(
						'key'   => 'field_tailwind_hero_content',
						'label' => __( 'Supporting text', 'tailwind-acf' ),
						'name'  => 'content',
						'type'  => 'textarea',
					),
					array(
						'key'   => 'field_tailwind_hero_background',
						'label' => __( 'Background Image', 'tailwind-acf' ),
						'name'  => 'background_image',
						'type'  => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
					array(
						'key'   => 'field_tailwind_hero_cta_label',
						'label' => __( 'CTA Label', 'tailwind-acf' ),
						'name'  => 'cta_label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_tailwind_hero_cta_url',
						'label' => __( 'CTA URL', 'tailwind-acf' ),
						'name'  => 'cta_url',
						'type'  => 'url',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/tailwind-hero',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_tailwind_feature_grid',
				'title'    => __( 'Tailwind Feature Grid', 'tailwind-acf' ),
				'fields'   => array(
					array(
						'key'   => 'field_tailwind_feature_grid_heading',
						'label' => __( 'Heading', 'tailwind-acf' ),
						'name'  => 'heading',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_tailwind_feature_grid_intro',
						'label' => __( 'Intro', 'tailwind-acf' ),
						'name'  => 'intro',
						'type'  => 'textarea',
					),
					array(
						'key'          => 'field_tailwind_feature_grid_items',
						'label'        => __( 'Features', 'tailwind-acf' ),
						'name'         => 'features',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add feature', 'tailwind-acf' ),
						'sub_fields'   => array(
							array(
								'key'   => 'field_tailwind_feature_grid_item_title',
								'label' => __( 'Title', 'tailwind-acf' ),
								'name'  => 'title',
								'type'  => 'text',
							),
							array(
								'key'   => 'field_tailwind_feature_grid_item_body',
								'label' => __( 'Description', 'tailwind-acf' ),
								'name'  => 'description',
								'type'  => 'textarea',
							),
							array(
								'key'     => 'field_tailwind_feature_grid_item_style',
								'label'   => __( 'Accent Style', 'tailwind-acf' ),
								'name'    => 'accent',
								'type'    => 'select',
								'choices' => array(
									'brand'     => __( 'Brand (blue)', 'tailwind-acf' ),
									'slate'     => __( 'Slate', 'tailwind-acf' ),
									'emerald'   => __( 'Emerald', 'tailwind-acf' ),
									'orange'    => __( 'Orange', 'tailwind-acf' ),
								),
								'allow_null' => 1,
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/tailwind-feature-grid',
						),
					),
				),
			)
		);
	}
);
