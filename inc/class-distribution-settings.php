<?php

namespace Packaging_Preview;

class Distribution_Settings {

	private static $instance;

	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Distribution_Settings;
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Set up field actions
	 */
	private function setup_actions() {
		fm_register_submenu_page( 'packaging_preview', 'options-general.php',
			esc_html__( 'Packaging Preview Settings', 'fusion' ), esc_html__( 'Packaging', 'fusion' ),
			'manage_options'
		);
		add_action( 'fm_submenu_packaging_preview', array( $this, 'register_packaging_preview_settings' ) );
	}

	/*
	 * Register distribution settings page fields
	 */
	public function register_packaging_preview_settings() {
		$packaging_preview_fields = new \Fieldmanager_Group(
			array(
				'name'     => 'packaging_preview',
				'tabbed'   => true,
				'children' => array(
					'facebook' => new \Fieldmanager_Group( 'Facebook',
						array(
							'name' => 'facebook',
							'children'=> array(
								'profile' => new \Fieldmanager_Textfield(
									esc_html__( 'Facebook publisher profile', 'fusion' ),
									array(
										'description' => __( 'URL to publisher Facebook page', 'fusion' ),
									)
								),
								'app_id' => new \Fieldmanager_Textfield(
									esc_html__( 'Facebook property (app ID)', 'fusion' ),
									array(
									)
								),
								'default_image' => new \Fieldmanager_Media(
									esc_html__( 'Default image for Facebook shares', 'fusion' ),
									array(
									)
								),
							)
						)
					),
					'twitter' => new \Fieldmanager_Group( 'Twitter',
						array(
							'children' => array(
								'profile' => new \Fieldmanager_Textfield(
									esc_html__( 'Twitter publisher account', 'fusion' ),
									array(
										'description' => __( 'Username (without the @) for Twitter via links', 'fusion' ),
									)
								),
								'logo' => new \Fieldmanager_Media(
									esc_html__( 'Twitter profile logo (for preview)', 'fusion' ),
									array()
								),
								'theme_color' => new \Fieldmanager_Textfield(
									esc_html__( 'Twitter theme color', 'fusion' ),
									array(
										'description' => __( 'The accent color specified in the user\'s Twitter profile theme', 'fusion' ),
										'input_type' => 'color',
										'default_value' => '292f33',
									)
								)
							)
						)
					)
				)
			)
		);

		$packaging_preview_fields->activate_submenu_page();
	}
}
