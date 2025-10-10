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
							'secondary_cta_label' => __( 'Learn more', 'tailwind-acf' ),
							'secondary_cta_url'   => '#',
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

		acf_register_block_type(
			array(
				'name'            => 'tailwind-cta-banner',
				'title'           => __( 'Tailwind CTA Banner', 'tailwind-acf' ),
				'description'     => __( 'A prominent call-to-action strip with optional secondary link.', 'tailwind-acf' ),
				'render_template' => get_theme_file_path( 'template-parts/blocks/cta-banner.php' ),
				'category'        => 'layout',
				'icon'            => 'megaphone',
				'keywords'        => array( 'cta', 'call to action', 'tailwind', 'button' ),
				'supports'        => array(
					'align'          => array( 'full', 'wide' ),
					'anchor'         => true,
					'customClassName'=> true,
				),
				'example'         => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'heading'           => __( 'Join the Murray Grey Community', 'tailwind-acf' ),
							'body_text'         => __( 'Connect with fellow breeders and access exclusive resources tailored for members.', 'tailwind-acf' ),
							'primary_label'     => __( 'Become a Member', 'tailwind-acf' ),
							'primary_url'       => '#',
							'secondary_label'   => __( 'Contact Us', 'tailwind-acf' ),
							'secondary_url'     => '#',
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
					array(
						'key'   => 'field_tailwind_hero_secondary_cta_label',
						'label' => __( 'Secondary CTA Label', 'tailwind-acf' ),
						'name'  => 'secondary_cta_label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_tailwind_hero_secondary_cta_url',
						'label' => __( 'Secondary CTA URL', 'tailwind-acf' ),
						'name'  => 'secondary_cta_url',
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
				'key'      => 'group_tailwind_cta_banner',
				'title'    => __( 'Tailwind CTA Banner', 'tailwind-acf' ),
				'fields'   => array(
					array(
						'key'   => 'field_tailwind_cta_heading',
						'label' => __( 'Heading', 'tailwind-acf' ),
						'name'  => 'heading',
						'type'  => 'text',
						'required' => 1,
					),
					array(
						'key'   => 'field_tailwind_cta_body',
						'label' => __( 'Supporting Text', 'tailwind-acf' ),
						'name'  => 'body_text',
						'type'  => 'textarea',
						'rows'  => 3,
					),
					array(
						'key'   => 'field_tailwind_cta_primary_label',
						'label' => __( 'Primary CTA Label', 'tailwind-acf' ),
						'name'  => 'primary_label',
						'type'  => 'text',
						'required' => 1,
					),
					array(
						'key'   => 'field_tailwind_cta_primary_url',
						'label' => __( 'Primary CTA URL', 'tailwind-acf' ),
						'name'  => 'primary_url',
						'type'  => 'url',
						'required' => 1,
					),
					array(
						'key'   => 'field_tailwind_cta_secondary_label',
						'label' => __( 'Secondary CTA Label', 'tailwind-acf' ),
						'name'  => 'secondary_label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_tailwind_cta_secondary_url',
						'label' => __( 'Secondary CTA URL', 'tailwind-acf' ),
						'name'  => 'secondary_url',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_tailwind_cta_background_image',
						'label' => __( 'Background Image', 'tailwind-acf' ),
						'name'  => 'background_image',
						'type'  => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/tailwind-cta-banner',
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

		acf_register_block_type(
			array(
				'name'            => 'tailwind-latest-news',
				'title'           => __( 'Tailwind Latest News', 'tailwind-acf' ),
				'description'     => __( 'Displays the latest three posts in a Tailwind-styled layout.', 'tailwind-acf' ),
				'render_template' => get_theme_file_path( 'template-parts/blocks/latest-news.php' ),
				'category'        => 'widgets',
				'icon'            => 'megaphone',
				'keywords'        => array( 'news', 'posts', 'blog', 'tailwind' ),
				'supports'        => array(
					'align'        => array( 'full', 'wide', 'center' ),
					'anchor'       => true,
					'customClassName' => true,
				),
				'example'         => array(
					'attributes' => array(
						'mode' => 'preview',
						'data' => array(
							'section_heading' => __( 'Latest News', 'tailwind-acf' ),
							'section_intro'   => __( 'Stay up-to-date with what is happening across the association.', 'tailwind-acf' ),
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_tailwind_latest_news',
				'title'    => __( 'Tailwind Latest News', 'tailwind-acf' ),
				'fields'   => array(
					array(
						'key'   => 'field_tailwind_latest_news_heading',
						'label' => __( 'Section Heading', 'tailwind-acf' ),
						'name'  => 'section_heading',
						'type'  => 'text',
						'default_value' => __( 'Latest News', 'tailwind-acf' ),
					),
					array(
						'key'   => 'field_tailwind_latest_news_intro',
						'label' => __( 'Section Intro', 'tailwind-acf' ),
						'name'  => 'section_intro',
						'type'  => 'textarea',
					),
					array(
						'key'   => 'field_tailwind_latest_news_show_date',
						'label' => __( 'Show Published Date', 'tailwind-acf' ),
						'name'  => 'show_date',
						'type'  => 'true_false',
						'ui'    => 1,
						'default_value' => 1,
					),
					array(
						'key'   => 'field_tailwind_latest_news_show_excerpt',
						'label' => __( 'Show Excerpt', 'tailwind-acf' ),
						'name'  => 'show_excerpt',
						'type'  => 'true_false',
						'ui'    => 1,
						'default_value' => 1,
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/tailwind-latest-news',
						),
					),
				),
			)
		);
	}
);
