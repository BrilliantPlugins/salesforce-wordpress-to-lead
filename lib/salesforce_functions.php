<?php

// Adds a new form (CPT)
function salesforce_add_form( $form_data, $form_title = null, $form_id = null ){

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

	}

}

function salesforce_get_next_form_id(){

	global $wpdb;

	$sql = 'SELECT MAX( CAST( meta_value AS UNSIGNED ) ) FROM '.$wpdb->postmeta.' WHERE meta_key = "_salesforce_form_id" AND post_id IN ( SELECT ID from '.$wpdb->posts.' WHERE post_type="salesforce_w2l_form" );';

	echo  $sql;

	$form_id = $wpdb->get_var( $sql );

	if( $form_id < 1 )
		$form_id = 0;

	return $form_id + 1;

}

function salesforce_get_default_form_title(){

	return 'Salesforce Web to Lead Form created on '.date('Y-m-d h:i:s');

}

function salesforce_get_form_by_id( $form_id ){

	$args = array(

		'post_type' => salesforce_get_post_type_slug(),
		'meta_query' => array(
			array(
				'key' => '_salesforce_form_id',
				'value' => absint( $form_id ),
			),
		),

	);

	$forms = get_posts( $args );

	if( is_array( $forms ) )
		return current( $forms );

}

function salesforce_get_form_id_by_post_id( $post_id ){

	return absint( get_post_meta( $post_id, '_salesforce_form_id', true  ) );

}

function salesforce_get_form_id( $form_id, $sidebar ){

	return 'salesforce_w2l_lead_'.$form_id.str_replace(' ','_',$sidebar);

}

function salesforce_get_post_type_slug(){
	return 'salesforce_w2l_form';
}

function salesforce_get_option_name(){
	return 'salesforce3';
}

function salesforce_get_meta_key(){
	return '_salesforce_form_data';
}