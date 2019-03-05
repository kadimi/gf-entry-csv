<?php

/**
 * Generate entry CSV.
 *
 * @param  int|object  $entry Entry or entry id.
 * @return string             The CSV.
 */
function gfecsv_generate_csv( $entry, $subject = null ) {

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

	/**
	 * Grab info from fields.
	 */
	$_fields = [];
	foreach ( $fields as $field) {
		$label = ! empty ( $field->field_gfecsv_column_label )
			? $field->field_gfecsv_column_label
			: $field->label
		;
		$order = ! empty ( $field->field_gfecsv_column_order )
			? $field->field_gfecsv_column_order
			: 0
		;
		$value = $field->get_value_export( $entry );
		if ( strstr( $label, ',' ) ) {
			/**
			 * Handle Advanced fields.
			 */
			$values = $field->get_value_submission( [] );
			$sub_labels = explode( ',', $label );
			foreach ( $sub_labels as $sub_label ) {
				preg_match( '/^(?<input_id>\d+)(?::(?<order>\d+))?(?::(?<label>.+))?$/', $sub_label, $sub_label_parts );
				if ( $sub_label_parts ) {
					$input = gfecsv_get_advanced_field_input( $field, $sub_label_parts[ 'input_id' ] );
					$_fields[] = [
						'order' => ! empty ( $sub_label_parts[ 'order' ] )
							? $sub_label_parts[ 'order' ]
							: $order
						,
						'label' => ! empty ( $sub_label_parts[ 'label' ] )
							? $sub_label_parts[ 'label' ]
							: (
								! empty( $input[ 'customLabel' ] )
									? $input[ 'customLabel' ]
									: $input[ 'label' ]
							)
						,
						'value' => $values[ "{$field->id}.{$sub_label_parts[input_id]}" ],
					];
				}
			}
		} else {
			/**
			 * Handle standard fields.
			 */
			$_fields[] = compact( 'order', 'label', 'value' );
		}
	}

	/**
	 * Sort fields;
	 */
	sort( $_fields );

	/**
	 * Allow extending
	 */
	$_fields = apply_filters( 'gfecsv_generated_csv_fields', $_fields, $subject );
	/**
	 * Prepare data for CSV.
	 */
	$data = [];
	foreach ( $_fields as $field ) {
		$data[ $field[ 'label' ] ] = $field[ 'value' ];
	}
	$data = apply_filters( 'gfecsv_generated_csv_data', $data, $subject );

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

function gfecsv_add_ui_setting( string $name, string $title, string $type = 'text', array $options = [], string $for = 'notification' ) {

	$defaults = [
		'tooltip' => null,
		'show_merge_tags' => false,
	];
	$options += $defaults;
	extract( $options );

	if ( $tooltip ) {
		add_filter( 'gform_tooltips', function( $tooltips ) use ( $name, $title, $tooltip, $for ) {
			$tooltips[ "{$for}_{$name}" ] = '<h6>' . $title . '</h6>' . $tooltip;
			return $tooltips;
		} );
	}

	do_action( "gfecsv_before_field_setting_$name" );

	add_filter( "gform_{$for}_ui_settings", function( $ui_settings, $subject, $form ) use( $name, $title, $type, $show_merge_tags, $for ) {
		ob_start();
		?>
			<tr valign="top">
				<th scope="row">
					<label for="<?php echo "gform_{$for}_{$name}"; ?>">
						<?php echo esc_html( $title ); ?>
						<?php gform_tooltip( "{$for}_{$name}" ) ?>
					</label>
				</th>
				<td>
				<?php if ( 'checkbox' === $type ): ?>
					<input
						type="checkbox"
						name="<?php echo "gform_{$for}_{$name}"; ?>"
						id="<?php echo "gform_{$for}_{$name}"; ?>"
						value="1"
						<?php echo empty( $subject[ $name ] ) ? '' : "checked='checked'" ?>
					/>
					<label for="<?php echo "gform_{$for}_{$name}"; ?>" class="inline">
						<?php echo esc_html( $title ); ?>
						<?php gform_tooltip( "{$for}_{$name}" ) ?>
					</label>
				<?php else: ?>
					<input
						type="text"
						name="<?php echo "gform_{$for}_{$name}"; ?>"
						id="<?php echo "gform_{$for}_{$name}"; ?>"
						value="<?php echo esc_attr( rgar( $subject, $name ) ) ?>"
						<?php echo $show_merge_tags ? 'class="merge-tag-support mt-hide_all_fields mt-position-right"' : ''; ?>
						
					/>
				<?php endif; ?>
				</td>
			</tr> <!-- / <?php echo esc_html( $title ); ?> -->
		<?php
		$ui_settings["{$for}_{$name}"] = ob_get_clean();
		return $ui_settings;
	}, 10, 3 );

	add_filter( "gform_pre_{$for}_save", function( $subject ) use ( $name, $type, $for ) {
		$subject[ $name ] = ( 'checkbox' === $type ) ? (bool) rgpost( "gform_{$for}_{$name}" ) : rgpost( "gform_{$for}_{$name}" );
		return $subject;
	} );

	do_action( "gfecsv_after_ui_setting_$name" );
}

function gfecsv_add_field_setting( string $name, string $title, string $type = 'text', array $options = [], string $for = 'advanced' ) {

	$defaults = [
		'dependents' => [],
		'tooltip' => null,
		'position' => - 1,
	];
	$options += $defaults;
	extract( $options );

	if ( $tooltip ) {
		add_filter( 'gform_tooltips', function( $tooltips ) use( $name, $title, $tooltip ) {
			$tooltips[ "form_field_{$name}" ] = '<h6>' . $title . '</h6>' . $tooltip;
			return $tooltips;
		} );
	}

	do_action( "gfecsv_before_field_setting_$name" );

	add_action( "gform_field_{$for}_settings", function( $current_position ) use ( $name, $title, $type, $position ) {

		if ( $position !== $current_position ) {
			return;
		}	

		?>
		<li class="<?php echo "{$name}_field_setting"; ?> field_setting">
		<?php if ( 'checkbox' === $type ) : ?> 
			<input
				type="checkbox"
				id="<?php echo "field_{$name}"; ?>"
				class="<?php echo "field_{$name}"; ?>"
				onclick="SetFieldProperty( '<?php echo "field_{$name}"; ?>', this.checked );"
				onkeypress="SetFieldProperty( '<?php echo "field_{$name}"; ?>', this.checked );"
			/>
			<label for="<?php echo "field_{$name}"; ?>" class="inline">
				<?php echo esc_html( $title ); ?>
				<?php gform_tooltip( "form_field_{$name}" ) ?>
			</label>
		<?php else : ?> 
			<label for="<?php echo "field_{$name}"; ?>" class="section_label">
				<?php echo esc_html( $title ); ?>
				<?php gform_tooltip( "form_field_{$name}" ) ?>
			</label>
			<input
				type="text"
				id="<?php echo "field_{$name}"; ?>"
				class="<?php echo "field_{$name}"; ?>"
				onkeyup="SetFieldProperty( '<?php echo "field_{$name}"; ?>', this.value );"
			/>
		<?php endif; ?> 
		<?php
	} );

	add_action( 'gform_editor_js', function() use( $name, $type, $dependents ) {
		?>
		<script type='text/javascript'>
			jQuery( function( $ ) {

				/**
				 * Add setting to fields.
				 */
				for( field in fieldSettings ) {
					fieldSettings[ field ] += ", .<?php echo "{$name}_field_setting"; ?>";
				}

				/**
				 * Populate settings with existing values.
				 */
				$( document ).on( 'gform_load_field_settings', function( event, field, form ) {
					<?php if ( 'checkbox' === $type ) : ?>
						$( '#<?php echo "field_{$name}"; ?>' ).attr( 'checked', field[ '<?php echo "field_{$name}"; ?>' ] == true );
					<?php else : ?>
						$( '#<?php echo "field_{$name}"; ?>' ).val( field[ '<?php echo "field_{$name}"; ?>' ] );
					<?php endif; ?>

					<?php if ( is_array( $dependents ) && $dependents ) : ?>
						$( '#<?php echo "field_{$name}"; ?>' ).on( 'change', function() {
							<?php foreach ( $dependents as $dependent ) : ?>
								if ( field[ '<?php echo "field_{$name}"; ?>' ] ) {
									$( '.<?php echo $dependent; ?>' ).show( 'slow' );
								} else {
									$( '.<?php echo $dependent; ?>' ).hide( 'slow' );
								}
							<?php endforeach; ?>
						} ).change();
					<?php endif; ?>
				} );
			} );
		</script>
		<?php
	}, 10, 3 );

	do_action( "gfecsv_after_field_setting_$name" );
}

function gfecsv_get_advanced_field_input( $field, $input_id ) {
	foreach ( $field->inputs as $input ) {
		if ( "{$field->id}.{$input_id}" === $input[ 'id' ] ) {
			return $input;
		}
	}
	return false;
}
