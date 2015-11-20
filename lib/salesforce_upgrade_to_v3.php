<?php

// get current options
$options_v2 = get_option( 'salesforce2' );

//if we have options to migrate, do so
if( is_array( $options_v2 ) ){

	// generate custom post type entries
	foreach( $options_v2['forms'] as $form_id => $form_data ){

		//make sure this form hasn’t already been migrated
		$form = salesforce_get_form_by_id( $form_id );

		print_r( $form_id .' => '. $form_data['form_name'] . '<hr>'  );

		if( ! $form ){
			salesforce_add_form( $form_data, $form_data['form_name'], $form_id );
		}else{
			print_r( $form );
		}

	}

	$options_v3 = $options_v2;

	unset( $options_v3['forms'] );

	// save new option to indicate successful upgrade
	update_option( 'salesforce3', $options_v3 );

}