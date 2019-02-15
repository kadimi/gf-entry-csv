<?php

use League\Csv\Writer;

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
	foreach ( $form[ 'fields' ] as $field ) {
		$data[ $field->label ] = $field->get_value_export( $entry );
	}

	/**
	 * Create CSV.
	 */
	$csv = Writer::createFromString( '' );
	$csv->setNewline("\r\n");
	$csv->insertOne( array_keys( $data ) );
	$csv->insertOne( array_values( $data ) );
	return $csv->getContent();
}
