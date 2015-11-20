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

function get_salesforce_form_id( $form_id, $sidebar ){

	return 'sf_form_salesforce_w2l_lead_' . $form_id . str_replace( ' ', '_', $sidebar) ;

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

// Add Daddy Analytics JS tracking to all pages
function salesforce_da_js(  ){

	$options = get_option("salesforce2");

	if( isset( $options['da_token'] ) && $options['da_token'] && isset( $options['da_url'] ) && $options['da_url'] && isset( $options['da_site'] ) && $options['da_site'] ){

		$da_token = $options['da_token'];
		$da_url = $options['da_url'];
		$da_site = $options['da_site'];

		echo "\n\n".'<!-- Begin Daddy Analytics code provided by Salesforce to Lead Plugin-->
		<script src="//cdn.daddyanalytics.com/w2/daddy.js" type="text/javascript"></script>
		<script type="text/javascript">
		var da_data = daddy_init(\'{ "da_token" : "'.esc_attr($da_token).'", "da_url" : "'.esc_attr($da_url).'" }\');
		var clicky_custom = {session: {DaddyAnalytics: da_data}};
		</script>
		<script src="//hello.staticstuff.net/w/__stats.js" type="text/javascript"></script>
		<script type="text/javascript">try{ clicky.init( "'.esc_attr($da_site).'" ); }catch(e){}</script>'."<!-- End Daddy Analytics code provided by Salesforce to Lead Plugin-->\n\n";

	}

}

function salesforce_captcha(){
	include("lib/captcha/captcha.php");
	die();
}

// Widget Class
if ( ! class_exists( 'Salesforce_WordPress_to_Lead_Widgets' ) ) {
	require_once('salesforce_widget.class.php');
	add_action( 'widgets_init', 'salesforce_widget_func' );
}

function salesforce_widget_func() {
	register_widget( 'Salesforce_WordPress_to_Lead_Widgets' );
}

// Procedural Functions
// TODO: wrap in a class

function salesforce_default_settings() {
	$options = array();
	$options['version'] 			= '2.0';
	$options['successmsg'] 			= __('Success!','salesforce');
	$options['errormsg'] 			= __('This field is required.','salesforce');
	$options['emailerrormsg']		= __('The email address you entered is not valid.','salesforce');
	$options['captchaerrormsg']		= __('The text you entered did not match the image.','salesforce');
	$options['requiredfieldstext'] 	= __('These fields are required.','salesforce');
	$options['sferrormsg'] 			= __('Failed to connect to Salesforce.com.','salesforce');
	$options['submitbutton']	 	= __('Submit','salesforce');
	$options['subject']	 			= __('Thank you for contacting %BLOG_NAME%','salesforce');
	$options['showccuser'] 			= true;
	$options['ccusermsg']			= __('Send me a copy','salesforce');
	$options['email_sender']		= '';
	$options['ccadmin']				= false;
	$options['captcha']				= false;

	$options['da_token']			= '';
	$options['da_url']				= '';
	$options['da_site']				= '';

	$options['commentstoleads']    = false;
	$options['commentsnamefields']  = false;

	$options['usecss']				= true;
	$options['wpcf7css']			= false;
	//$options['hide_salesforce_link']= true;

	$options['forms'][1] = Salesforce_Admin::default_form();

	update_option('salesforce2', $options);

	return $options;
}

function salesforce_back_link($url){

	return '<a href="'.$url.'">&laquo; '.__('Back to configuration page','salesforce').'</a>';

}

/**
 * Sort input array by $subkey
 * Taken from: http://php.net/manual/en/function.ksort.php
 */
function salesforce_sksort( &$array, $subkey="id", $sort_ascending=false ) {

	if( !is_array( $array ) )
		return $array;

	$temp_array = array();

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}

function salesforce_get_post_data( $index ){
	if( isset( $_POST[$index] ) ){
		return $_POST[$index];
	}else{
		return false;
	}
}

function salesforce_maybe_implode( $delimiter, $data ){

	if( is_array($data) )
		return trim( implode( $delimiter, $data ) );

	return $data;

}

function salesforce_clean_field( $value ){
	return trim(strip_tags(stripslashes( $value )));
}