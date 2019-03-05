<?php

add_action( 'plugin_loaded', function() {
	gfecsv_add_ui_setting( 'gfecsv_attach_csv'
		, esc_html( __( 'Attach CSV', 'gfecsv' ) )
		, 'checkbox'
		, [
			'tooltip' => __( 'When enabled, a CSV file containing the entry values will be attached to the email.', 'gfecsv' ),
		]
	);

	gfecsv_add_ui_setting( 'gfecsv_csv_filename'
		, esc_html( __( 'CSV Filename', 'gfecsv' ) )
		, 'text'
		, [
			'tooltip' => __( 'The name for CSV file that will be attached to the email.', 'gfecsv' ),
			'show_merge_tags' => true,
		]
	);
} );
