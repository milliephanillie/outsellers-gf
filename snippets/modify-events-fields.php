<?php
/**
 * Modify the end date on the front end (hide it)
 */
global $modify_event_form_id, $modify_event_field_id;
$modify_event_form_id = 5;
$modify_event_field_id = 21;
$modify_event_sub_field_id = 5;

add_filter( 'gk/field-event/inputs', function ( $input ) {
	if ( isset( $input['id'] ) && $input['id'] === 5 ) {
		$input['wrapper_class'][] = 'gf_event_input_hidden';
	}


	return $input;
} );


/**
 * Modify the end date value
 */
add_action( 'gform_pre_submission_filter_5', function ( $form ) {
	$start_date_field = 'input_'.$modify_event_field_id.'_1'; 
	$end_date_field   = 'input_'.$modify_event_field_id.'_2'; 

	if ( ! empty( $_POST[ $start_date_field ] ) ) {
		$start_date = DateTime::createFromFormat( 'm/d/Y', $_POST[ $start_date_field ] ); // Adjust to your format

		if ( $start_date ) {
			// Add 30 minutes (or a day, or whatever you need)
			$end_date = clone $start_date;
			$end_date->modify('+30 minutes');

			// Set back into the POST so GF saves it
			$_POST[ $end_date_field ] = $end_date->format( 'm/d/Y' ); // Format to match your field config
		}
	}
} );