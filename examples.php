<?php

// Remove Form Action
add_filter( 'salesforce_w2l_form_action', 'salesforce_w2l_form_action_example', 10, 1 );
function salesforce_w2l_form_action_example(  $action ){

	return '';

}

// Lead Source
add_filter( 'salesforce_w2l_lead_source', 'salesforce_w2l_lead_source_example', 10, 2 );
function salesforce_w2l_lead_source_example(  $lead_source, $form_id ){

	if( $form_id == 1 )
		return 'Example Lead Source for Form #1 on page id #'.get_the_id();

	return $lead_source;

}

add_filter( 'salesforce_w2l_post_args', 'salesforce_w2l_post_args_example' );

function salesforce_w2l_post_args_example( $args ){

	$args['timeout'] = 10; // http timeout in seconds
	return $args;

}

add_filter( 'salesforce_w2l_post_data', 'salesforce_w2l_post_data_example', 10, 3 );

function salesforce_w2l_post_data_example( $post, $form_id, $form_type ){
	error_log( 'POST ARGS '.print_r( $post, 1 ) );
	$post['test'] = 'test';
	return $post;
}


add_filter( 'salesforce_w2l_post_data', 'salesforce_w2l_post_data_date_example', 10, 3 );

function salesforce_w2l_post_data_date_example( $post, $form_id, $form_type ){

	$date_fields = array( 'your_field_name', 'your_other_field_name' );

	foreach( $post as $key=>$val ){
		if( in_array( $key, $date_fields )  )
			$post[$key] = date( 'm/d/Y', strtotime( $val ) );
	}

	return $post;
}


add_action('salesforce_w2l_before_submit', 'salesforce_w2l_before_submit_example', 10, 3 );

function salesforce_w2l_before_submit_example( $post, $form_id, $form_type ){
	error_log( 'BEFORE SUBMIT '.print_r($post,1) );
}

add_action('salesforce_w2l_error_submit', 'salesforce_w2l_error_submit_example', 10, 4 );

function salesforce_w2l_error_submit_example( $result, $post, $form_id, $form_type ){
	error_log( 'ERROR SUBMIT ' . print_r($result,1) );
}

add_action('salesforce_w2l_after_submit', 'salesforce_w2l_after_submit_example', 10, 3 );

function salesforce_w2l_after_submit_example( $post, $form_id, $form_type ){
	error_log( 'AFTER SUBMIT '.print_r($post,1) );
}


add_filter( 'salesforce_w2l_post_args', 'salesforce_w2l_post_args_timeout_example', 10, 1 );

function salesforce_w2l_post_args_timeout_example( $args ){
	$args['timeout'] = 10;
	return $args;
}

//add_filter( 'salesforce_w2l_show_admin_nag_message', '__return_false', 10, 1 );


