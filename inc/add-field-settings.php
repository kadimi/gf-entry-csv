<?php

add_action( 'plugin_loaded', function() {
	gfecsv_add_field_setting( 'gfecsv_include_in_csv'
		, esc_html( 'Include in CSV', 'gfecsv' )
		, 'checkbox'
		, [
			'tooltip' => __( 'When enabled, this field will be included in the CSV file.', 'gfecsv' ),
			'dependents' => [
				'gfecsv_column_label_field_setting',
				'gfecsv_column_order_field_setting',
			],
		]
	);

	gfecsv_add_field_setting( 'gfecsv_column_label'
		, esc_html( 'Column CSV Label', 'gfecsv' )
		, 'text'
		, [
			'tooltip' => __( 'Enter a custom label for the column in the CSV file.', 'gfecsv' ),
		]
	);

	gfecsv_add_field_setting( 'gfecsv_column_order'
		, esc_html( 'Column CSV Order', 'gfecsv' )
		, 'text'
		, [
			'tooltip' => __( 'Enter a custom order for the column in the CSV file.', 'gfecsv' ),
		]
	);
} );