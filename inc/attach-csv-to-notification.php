<?php

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
	$content = gfecsv_generate_csv( $entry, $notification );
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
