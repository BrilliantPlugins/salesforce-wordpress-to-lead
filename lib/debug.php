<?php

add_filter( 'salesforce_w2l_api_url', 'salesforce_w2l_api_url_debug', 10, 2 );

function salesforce_w2l_api_url_debug( $url, $form_type ){

	//$newurl = str_replace( 'www.salesforce.com', 'login.salesforce.com', $url );
	error_log( '$url=' . $url );
	error_log( '$form_type=' . $form_type );
    return $url;
}

add_action( 'salesforce_w2l_before_submit', 'salesforce_w2l_before_submit_debug', 10, 3 );

function salesforce_w2l_before_submit_debug( $post, $form_id, $form_type ){

	error_log( 'salesforce_w2l_before_submit_debug' );
	error_log( '$post=' . print_r( $post, 1 ) );
	error_log( '$form_id=' . $form_id );
	error_log( '$form_type=' . $form_type );

}

add_action( 'salesforce_w2l_error_submit', 'salesforce_w2l_error_submit_debug', 10, 4  );

function salesforce_w2l_error_submit_debug( $result, $post, $form_id, $form_type) {

	error_log( 'salesforce_w2l_error_submit_debug' );
	error_log( '$result=' . print_r( $result, 1 ) );
	error_log( '$post=' . print_r( $post, 1 ) );
	error_log( '$form_id=' . $form_id );
	error_log( '$form_type=' . $form_type );

}

add_action( 'salesforce_w2l_after_submit', 'salesforce_w2l_after_submit_debug', 10, 3 );

function salesforce_w2l_after_submit_debug( $post, $form_id, $form_type) {

	error_log( 'salesforce_w2l_after_submit_debug' );
	error_log( '$post=' . print_r( $post, 1 ) );
	error_log( '$form_id=' . $form_id );
	error_log( '$form_type=' . $form_type );

}

add_filter( 'salesforce_w2l_cc_admin_email_list', 'salesforce_w2l_cc_admin_email_list_debug', 10, 1 );

function salesforce_w2l_cc_admin_email_list_debug( $emails ){

	error_log( 'salesforce_w2l_cc_admin_email_list_debug' );
	error_log( '$emails=' . print_r( $emails, 1 ) );

}

add_filter('salesforce_w2l_cc_admin_email_content', 'salesforce_w2l_cc_admin_email_content_debug', 10, 1 );

function salesforce_w2l_cc_admin_email_content_debug( $message ){

	error_log( 'salesforce_w2l_cc_admin_email_content_debug' );
	error_log( '$message=' . print_r( $message, 1 ) );

}

add_filter('salesforce_w2l_cc_admin_email_subject', 'salesforce_w2l_cc_admin_email_subject_debug', 10, 3 );

function salesforce_w2l_cc_admin_email_subject_debug( $subject, $form_type, $post ){

	error_log( 'salesforce_w2l_cc_admin_email_subject_debug' );
	error_log( '$subject=' . print_r( $subject, 1 ) );
	error_log( '$form_type=' . print_r( $form_type, 1 ) );
	error_log( '$post=' . print_r( $post, 1 ) );

}