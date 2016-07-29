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
							'children'=> array(
								'profile' => new \Fieldmanager_Textfield(
									esc_html__( 'Facebook publisher profile', 'fusion' ),
										array(
											'description' => __( 'URL to publisher Facebook page', 'fusion' ),
										)
									),
								'property' => new \Fieldmanager_Textfield(
									esc_html__( 'Facebook property ID', 'fusion' ),
										array(
										)
									),
								'app_id' => new \Fieldmanager_Textfield(
									esc_html__( 'Facebook property ID', 'fusion' ),
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
								)
							)
						)
					)
				)
			);

		$packaging_preview_fields->activate_submenu_page();
	}
}
