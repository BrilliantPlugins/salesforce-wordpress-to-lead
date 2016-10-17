<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function salesforce_migrate_option(){
	// get current options
	$options_v2 = get_option( 'salesforce2' );

	$form_mapping = array();

	$post_id = null;

	//if we have options to migrate, do so
	if( is_array( $options_v2 ) ){

		// generate custom post type entries
		foreach( $options_v2['forms'] as $form_id => $form_data ){

			//make sure this form hasn’t already been migrated
			$form = salesforce_get_form_by_legacy_meta_id( $form_id );

			//print_r( $form_id .' => '. $form_data['form_name'] . '<hr>'  );

			if( ! $form ){
				$post_id = salesforce_migrate_form( $form_data, $form_data['form_name'], $form_id );
			}else{
				// already migrated
				//print_r( $form );
			}

			$form_mapping[ $form_id ] = $post_id;

		}

		error_log( 'SALESFORCE $form_mapping = ' . print_r( $form_mapping, 1 ) );

		// copy options
		$options_v3 = $options_v2;

		// update superfluous option version
		$options_v3['version'] = '3.0';

		// create form mapping 'legacy_id' => 'post_id'
		$options_v3['form_mapping'] = $form_mapping;

		//clear out old form data
		unset( $options_v3['forms'] );

		error_log( 'SALESFORCE $options_v3 = ' . print_r( $options_v3, 1 ) );

		// save new option to indicate successful upgrade
		update_option( 'salesforce3', $options_v3 );

	}

}

// Adds a new form (CPT)
function salesforce_migrate_form( $form_data, $form_title = null, $form_id = null ){

	if( ! $form_title ){
		$form_title = salesforce_get_default_form_title();
	}

	if( ! $form_id ){
		$form_id = salesforce_get_next_form_id();
	}

	$form = array(
		'post_type' => salesforce_get_post_type_slug(),
		'post_title' => $form_data['form_name'],
		'post_content' => '',
		'post_status' => 'publish',
		'comment_status' => 'closed',
	);

	//echo '<pre>'.print_r( $form_data , 1).'</pre>';

	$post_id = wp_insert_post( $form );

	if( $post_id ){
		// add metadata

		// form id
		update_post_meta( $post_id, '_salesforce_form_id', $form_id );

		// data
		unset( $form_data['form_name'] ); // name is redundant
		update_post_meta( $post_id, '_salesforce_form_data', $form_data );

		return $post_id;

	}

}