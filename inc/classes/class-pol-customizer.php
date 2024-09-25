<?php
/**
 * Customizer settings.
 *
 * @package GOAT PoL
 */

class POL_Customizer {

	/**
	 * Customizer options.
	 */
	public static function pol_register( $wp_customize ) {

		/* ------------------------------------------------------------------------------ /*
		/*  THEME OPTIONS
		/* ------------------------------------------------------------------------------ */

		$wp_customize->add_panel( 'pol_theme_options', array(
			'priority'       => 30,
			'capability'     => 'edit_theme_options',
			'theme_supports' => '',
			'title'          => esc_html__( 'Theme Options', 'pol' ),
			'description'    => esc_html__( 'Options included in the GOAT PoL theme.', 'pol' ),
		) );


		/* ------------------------------------------------------------------------------ /*
		/*  SITE IDENTITY
		/* ------------------------------------------------------------------------------ */

		/* Map Logo ---------------- */

		$wp_customize->add_setting( 'pol_map_logo', array(
			'capability' 				=> 'edit_theme_options',
			'sanitize_callback' => 'absint'
		) );

		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'pol_map_logo', array(
			'label'				=> esc_html__( 'Map Logo', 'pol' ),
			'mime_type'		=> 'image',
			'priority'		=> 9,
			'section' 		=> 'title_tagline',
		) ) );

		/* Logotype ---------------- */

		$wp_customize->add_setting( 'pol_logotype', array(
			'capability' 				=> 'edit_theme_options',
			'sanitize_callback' => 'absint'
		) );

		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'pol_logotype', array(
			'label'				=> esc_html__( 'Logotype', 'pol' ),
			'mime_type'		=> 'image',
			'priority'		=> 9,
			'section' 		=> 'title_tagline',
		) ) );

		/* Site Logo --------------------- */

		// Make the core custom_logo setting use refresh transport, so we update the markup around the site logo element as well.
		$wp_customize->get_setting( 'custom_logo' )->transport = 'refresh';

		// Remove the ability to display site name and description in header.
		$wp_customize->remove_control( 'header_text' );		

	}


	/**
	 * Returns the available post meta options.
	 */
	public static function get_post_meta_options( $post_type ) {

		$post_meta_options = array(
			'post'	=> array(
				'author'			=> esc_html__( 'Author', 'pol' ),
				'categories'	=> esc_html__( 'Categories', 'pol' ),
				'tags'				=> esc_html__( 'Tags', 'pol' ),
				'comments'		=> esc_html__( 'Comments', 'pol' ),
				'date'				=> esc_html__( 'Date', 'pol' ),
				'edit-link'		=> esc_html__( 'Edit link (for logged in users)', 'pol' ),
			)
		);

		return isset( $post_meta_options[$post_type] ) ? $post_meta_options[$post_type] : array();
		
	}


	/**
	 * Returns an array of post types with post meta options and their default values.
	 */
	public static function get_post_types_with_post_meta() {

		return array( 
			'post' => array(
				'default' => array(
					'archive'	=> array( 'date', 'author' ),
					'single'	=> array( 'categories', 'date', 'tags', 'edit-link' ),
				),
			),
		);
		
	}


	/**
	 * Returns the global color options.
	 */
	public static function get_color_options() {

		return array(
			// Note: The body background color uses the built-in WordPress theme mod, which is why it isn't included in this array.
			'pol_light_background_color' => array(
				'default'	=> '#F0F0F0',
				'label'		=> esc_html__( 'Light Background Color', 'pol' ),
				'slug'		=> 'light-background',
				'palette'	=> true,
			),
			'pol_primary_color' => array(
				'default'	=> '#39414d',
				'label'		=> esc_html__( 'Primary Text Color', 'pol' ),
				'slug'		=> 'primary',
				'palette'	=> true,
			),
			'pol_secondary_color' => array(
				'default'	=> '#78808F',
				'label'		=> esc_html__( 'Secondary Text Color', 'pol' ),
				'slug'		=> 'secondary',
				'palette'	=> true,
			),
			'pol_tertiary_color' => array(
				'default'	=> '#A0A0A0',
				'label'		=> esc_html__( 'Tertiary Text Color', 'pol' ),
				'slug'		=> 'tertiary',
				'palette'	=> true,
			),
			'pol_border_color' => array(
				'default'	=> '#E0E0E0',
				'label'		=> esc_html__( 'Border Color', 'pol' ),
				'slug'		=> 'border',
				'palette'	=> true,
			),
			'pol_accent_color' => array(
				'default'	=> '#587291',
				'label'		=> esc_html__( 'Accent Color', 'pol' ),
				'slug'		=> 'accent',
				'palette'	=> true,
			),
			'pol_accent_dark_color' => array(
				'default'	=> '#28303d',
				'label'		=> esc_html__( 'Accent Dark Color', 'pol' ),
				'slug'		=> 'accent-dark',
				'palette'	=> true,
			),
			'pol_accent_light_color' => array(
				'default'	=> '#D0D1D6',
				'label'		=> esc_html__( 'Accent Light Color', 'pol' ),
				'slug'		=> 'accent-light',
				'palette'	=> true,
			),
		);
		
	}


	/**
	 * Returns the post archive column options.
	 */
	public static function get_archive_columns_options() {
		
		return array(
			'pol_post_grid_columns_mobile' => array(
				'label'				=> esc_html__( 'Columns on Mobile', 'pol' ),
				'default'			=> '1',
				'description'	=> esc_html__( 'Screen width: 0px - 700px', 'pol' ),
			),
			'pol_post_grid_columns_tablet' => array(
				'label'				=> esc_html__( 'Columns on Tablet Portrait', 'pol' ),
				'default'			=> '2',
				'description'	=> esc_html__( 'Screen width: 700px - 1000px', 'pol' ),
			),
			'pol_post_grid_columns_laptop' => array(
				'label'				=> esc_html__( 'Columns on Tablet Landscape', 'pol' ),
				'default'			=> '2',
				'description'	=> esc_html__( 'Screen width: 1000px - 1200px', 'pol' ),
			),
			'pol_post_grid_columns_desktop' => array(
				'label'				=> esc_html__( 'Columns on Desktop', 'pol' ),
				'default'			=> '3',
				'description'	=> esc_html__( 'Screen width: 1200px - 1600px', 'pol' ),
			),
			'pol_post_grid_columns_desktop_xl'	=> array(
				'label'				=> esc_html__( 'Columns on Large Desktop', 'pol' ),
				'default'			=> '4',
				'description'	=> esc_html__( 'Screen width: > 1600px', 'pol' ),
			),
		);

	}


	/**
	 * Enqueue the Customizer JavaScript.
	 */
	public static function enqueue_customizer_javascript() {
		wp_enqueue_script( 'pol-customizer-javascript', get_template_directory_uri() . '/assets/js/customizer.js', array( 'jquery', 'customize-controls' ), '', true );
	}

}
add_action( 'customize_register', array( 'POL_Customizer', 'pol_register' ) );
add_action( 'customize_controls_enqueue_scripts', array( 'POL_Customizer', 'enqueue_customizer_javascript' ) );