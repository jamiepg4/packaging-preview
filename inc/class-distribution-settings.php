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
		fm_register_submenu_page( 'packaging-preview', 'options-general.php',
			esc_html__( 'Packaging Preview Settings', 'fusion' ), esc_html__( 'Packaging', 'fusion' ),
			'manage_options'
		);
		add_action( 'fm_submenu_packaging-preview', array( $this, 'register_packaging_preview_settings' ) );
	}

	/*
	 * Register distribution settings page fields
	 */
	public function register_packaging_preview_settings() {
		$distribution_settings_fields = new \Fieldmanager_Group( esc_html__( 'Packaging Preview Settings', 'fusion' ),
			array(
				'name'     => 'fusion_distribution_settings',
				'tabbed'   => false,
				'children' => array(
					'fb_publisher' => new \Fieldmanager_Textfield(
						esc_html__( 'Facebook publisher profile', 'fusion' ),
							array(
								'description' => __( 'URL to publisher Facebook page', 'fusion' ),
							)
						),
					'fb_property' => new \Fieldmanager_Textfield(
						esc_html__( 'Facebook property ID', 'fusion' ),
							array(
								'input_type' => 'number'
							)
						),
					'tw_profile' => new \Fieldmanager_Textfield(
						esc_html__( 'Twitter publisher account', 'fusion' ),
							array(
								'description' => __( 'Username (without the @) for Twitter via links', 'fusion' ),
							)
						),
				)
			)
		);

		$distribution_settings_fields->activate_submenu_page();
	}
}
