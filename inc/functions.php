<?php

add_filter( 'gform_tooltips', function( $tooltips ) {
	return $tooltips + [
		'form_field_gfecsv_column_label' => '<h6>' . __( 'Column CSV Label', 'gfecsv' ) . '</h6>' . __( 'Enter a custom label for the column in the CSV file.', 'gfecsv' ),
		'form_field_gfecsv_column_order' => '<h6>' . __( 'Column CSV Order', 'gfecsv' ) . '</h6>' . __( 'Enter a custom order for the column in the CSV file.', 'gfecsv' ),
		'notification_gfecsv_attach_csv' => '<h6>' . __( 'Attach CSV', 'gfecsv' ) . '</h6>' . __( 'When enabled, a CSV file containing the entry values will be attached to the email.', 'gfecsv' ),
		'notification_gfecsv_csv_filename' => '<h6>' . __( 'CSV Filename', 'gfecsv' ) . '</h6>' . __( 'The name for CSV file that will be attached to the email.', 'gfecsv' ),
		'form_field_gfecsv_include_in_csv' => '<h6>' . __( 'Include in CSV', 'gfecsv' ) . '</h6>' . __( 'When enabled, this field will be included in the CSV file.', 'gfecsv' ),
	];
} );

add_action( 'gform_field_advanced_settings', function( $position, $form_id ) {
	if ( $position == -1 ) {
		?>
		<li class="gfecsv_include_in_csv_field_setting field_setting">
			<input
				type="checkbox"
				id="field_gfecsv_include_in_csv"
				class="field_gfecsv_include_in_csv"
				onclick="SetFieldProperty( 'field_gfecsv_include_in_csv', this.checked );"
				onkeypress="SetFieldProperty( 'field_gfecsv_include_in_csv', this.checked );"
			/>
			<label for="field_gfecsv_include_in_csv" class="inline">
				<?php esc_html_e( 'Include in CSV', 'gfecsv' ) ?>
				<?php gform_tooltip( 'form_field_gfecsv_include_in_csv' ) ?>
			</label>
		</li>
		<li class="gfecsv_column_label_field_setting field_setting">
			<label for="field_gfecsv_column_label" class="section_label">
				<?php esc_html_e( 'Custom CSV Label', 'gfecsv' ); ?>
				<?php gform_tooltip("form_field_gfecsv_column_label") ?>
			</label>
			<input
				type="text"
				id="field_gfecsv_column_label"
				class="field_gfecsv_column_label"
				onkeyup="SetFieldProperty( 'field_gfecsv_column_label', this.value );"
			/>
		</li>
		<li class="gfecsv_column_order_field_setting field_setting">
			<label for="field_gfecsv_column_order" class="section_label">
				<?php esc_html_e( 'Custom CSV Order', 'gfecsv' ); ?>
				<?php gform_tooltip("form_field_gfecsv_column_order") ?>
			</label>
			<input
				type="text"
				id="field_gfecsv_column_order"
				class="field_gfecsv_column_order"
				onkeyup="SetFieldProperty( 'field_gfecsv_column_order', this.value );"
			/>
		</li>
		<?php
	}
}, 10, 2 );

add_action( 'gform_editor_js', function() {
	?>
	<script type='text/javascript'>
		jQuery( function( $ ) {

			/**
			 * Add settings to fields.
			 */
			for( field in fieldSettings ) {
				fieldSettings[ field ] += ", .gfecsv_include_in_csv_field_setting";
				fieldSettings[ field ] += ", .gfecsv_column_label_field_setting";
				fieldSettings[ field ] += ", .gfecsv_column_order_field_setting";
			}

			/**
			 * Populate settings with existing values.
			 */
			$( document ).on( 'gform_load_field_settings', function( event, field, form ) {
				$( '#field_gfecsv_column_label' ).val( field[ 'field_gfecsv_column_label' ] );
				$( '#field_gfecsv_column_order' ).val( field[ 'field_gfecsv_column_order' ] );
				$( '#field_gfecsv_include_in_csv' ).attr( 'checked', field[ 'field_gfecsv_include_in_csv' ] == true);

				$( '#field_gfecsv_include_in_csv' ).on( 'change', function() {
					if ( field[ 'field_gfecsv_include_in_csv' ] ) {
						$( '.gfecsv_column_label_field_setting' ).show( 'slow' );
						$( '.gfecsv_column_order_field_setting' ).show( 'slow' );
					} else {
						$( '.gfecsv_column_label_field_setting' ).hide( 'slow' );
						$( '.gfecsv_column_order_field_setting' ).hide( 'slow' );
					}
				} ).change();
			} );


		} );
	</script>
	<?php
} );

add_filter( 'gform_notification_ui_settings', function( $ui_settings, $notification, $form ) {
	
	/**
	 * Add "Attach CSV" checkbox.
	 */
	ob_start();
	?>
		<tr valign="top">
			<th scope="row">
				<label for="gform_notification_gfecsv_attach_csv">
					<?php esc_html_e( 'Attach CSV', 'gfecsv' ); ?>
					<?php gform_tooltip( 'notification_gfecsv_attach_csv' ) ?>
				</label>
			</th>
			<td>
				<input type="checkbox" name="gform_notification_gfecsv_attach_csv" id="gform_notification_gfecsv_attach_csv" value="1" <?php echo empty( $notification['gfecsv_attach_csv'] ) ? '' : "checked='checked'" ?>/>
				<label for="gform_notification_gfecsv_attach_csv" class="inline">
					<?php esc_html_e( 'Attach CSV', 'gfecsv' ); ?>
					<?php gform_tooltip( 'notification_gfecsv_attach_csv' ) ?>
				</label>
			</td>
		</tr> <!-- / attach csv -->
	<?php
	$ui_settings['notification_gfecsv_attach_csv'] = ob_get_clean();
	
	/**
	 * Add "CSV filename" text field.
	 */
	ob_start();
	?>
		<tr valign="top">
			<th scope="row">
				<label for="gform_notification_gfecsv_csv_filename">
					<?php esc_html_e( 'CSV Filename', 'gfecsv' ); ?>
					<?php gform_tooltip( 'notification_gfecsv_csv_filename' ) ?>
				</label>
			</th>
			<td>
				<input type="text" name="gform_notification_gfecsv_csv_filename" id="gform_notification_gfecsv_csv_filename" class="merge-tag-support mt-hide_all_fields mt-position-right" value="<?php echo esc_attr( rgar( $notification, 'gfecsv_csv_filename' ) ) ?>" />
			</td>
		</tr> <!-- / csv filename -->
	<?php
	$ui_settings['notification_gfecsv_csv_filename'] = ob_get_clean();

	/**
	 * Done.
	 */
	return $ui_settings;
}, 10, 3 );

add_filter( 'gform_pre_notification_save', function( $notification, $form, $is_new_notification ) {
	return $notification += [
		'gfecsv_attach_csv' => (bool) rgpost( 'gform_notification_gfecsv_attach_csv' ),
		'gfecsv_csv_filename' => rgpost( 'gform_notification_gfecsv_csv_filename' ),
	];
}, 10, 3 );

add_filter( 'gform_notification', function ( $notification, $form, $entry ) {

	/**
	 * Does this form have GF entry CSV enabled? 
	 */
	if ( empty( $notification[ 'gfecsv_attach_csv' ] ) ) {
		return $notification;
	}

	/**
	 * Prepare file content.
	 */
	$content = gfecsv_generate_csv( $entry );
	if ( empty( $content ) ) {
		return $notification;
	}

	/**
	 * Prepare file name.
	 *
	 * Use provided value. Since merge tags are supported, make sure the result is not
	 * and empty string, in which case we fallback to a random 5 charachters name. We
	 * also make sure the extension is added if missing.
	 */
	if ( ! empty( $notification[ 'gfecsv_csv_filename' ] ) ) {
		$filename = GFCommon::replace_variables( $notification[ 'gfecsv_csv_filename' ], $form, $entry, false, false );
		$filename = sanitize_user( $filename, true );
		if ( ! in_array( substr( $filename, -4 ), [ '.csv', '.txt', '.xls' ], true ) ) {
			$filename .= '.csv';
		}
	}
	$filename = ! empty( $filename )
		? $filename
		: wp_generate_password( 5, false ). '.csv'
	;

	/**
	 * Prepare file.
	 */
	$file = gfecsv_temp_file( $filename, $content );

	/**
	 * Add file to notification attachments.
	 */
	$notification[ 'attachments' ] = ( is_array( rgget( 'attachments', $notification ) ) )
		? rgget( 'attachments', $notification )
		: []
	;
	$notification[ 'attachments' ][] = $file;

	/**
	 * Done.
	 */
	return $notification;
}, 10, 3 );

/**
 * Generate entry CSV.
 *
 * @param  int|object  $entry Entry or entry id.
 * @return string             The CSV.
 */
function gfecsv_generate_csv( $entry ) {

	/**
	 * Get entry.
	 */
	if ( ! is_array( $entry ) ) {
		$entry = GFAPI::get_entry( ( int ) $entry );
	}
	if ( ! is_array( $entry ) || ! array_key_exists( 'form_id', $entry ) ) {
		return new WP_Error( 'gfecsv_error_invalid_entry', __( 'Invalid Entry', 'gfecsv' ) );
	}

	/**
	 * Get form.
	 */
	$form = GFAPI::get_form( $entry[ 'form_id' ] );

	/**
	 * Get data.
	 */
	$fields = $form[ 'fields' ];

	/**
	 * Filter out non-included fields only.
	 */
	$fields = array_filter( $fields, function( $field ) {
		return ! empty( $field->field_gfecsv_include_in_csv );
	} );

	usort( $fields , function( $a, $b ) {
		$a = ! empty ( $a->field_gfecsv_column_order )
			? $a->field_gfecsv_column_order
			: 0
		;
		$b = ! empty ( $b->field_gfecsv_column_order )
			? $b->field_gfecsv_column_order
			: 0
		;
		return $a > $b;
	} );
	foreach ( $fields as $field ) {
		$label = ! empty ( $field->field_gfecsv_column_label )
			? $field->field_gfecsv_column_label
			: $field->label
		;
		$data[ $label ] = $field->get_value_export( $entry );
	}
	unset( $fields, $label );

	/**
	 * Create CSV.
	 */
	$csv = \League\Csv\Writer::createFromString( '' );
	$csv->setNewline( "\r\n" );
	$csv->insertOne( array_keys( $data ) );
	$csv->insertOne( array_values( $data ) );
	return $csv->getContent();
}

/**
 * Create temporary file in system temporary dicretory.
 *
 * @author Nabil Kadimi - https://kadimi.com
 * @param  $name  $name    File name.
 * @param  string $content File contents.
 * @return string File path.
 */
function gfecsv_temp_file( $name, $content ) {
	$sep = DIRECTORY_SEPARATOR;
	$file = $sep . trim( sys_get_temp_dir(), $sep ) . $sep . ltrim( $name, $sep );
	file_put_contents( $file, $content );
	register_shutdown_function( function() use( $file ) {
		@unlink( $file );
	} );
	return $file;
}
