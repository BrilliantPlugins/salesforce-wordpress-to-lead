<?php
/*
Plugin Name: WordPress-to-Lead for Salesforce CRM
Plugin URI: http://wordpress.org/plugins/salesforce-wordpress-to-lead/
Description: Easily embed a contact form into your posts, pages or your sidebar, and capture the entries straight into Salesforce CRM. Also supports Web to Case and Comments to leads.
Author: Daddy Analytics & Cimbura.com
Version: 2.7
Author URI: http://try.daddyanalytics.com/wordpress-to-lead-general?utm_source=ThoughtRefinery&utm_medium=link&utm_campaign=WP2L_Plugin_01&utm_content=da1_author_uri
License: GPL2
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Yoast Plugin Helper Functions
require_once( plugin_dir_path( __FILE__ ) . 'lib/ov_plugin_tools.php' );

// Common functions
require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_functions.php' );

// v2 code (legacy)
require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_v2.php' );

// v3+ code
require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_v3.php' );
require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_post_type.php' );

// Admin Class
if ( ! class_exists( 'Salesforce_Admin' ) ) {
	require_once('lib/salesforce_admin.class.php');
}
$salesforce = new Salesforce_Admin();

// Comment to lead functions
require_once('lib/salesforce_comment_to_lead.php');

// Actions & Filters
add_action('wp_footer','salesforce_da_js');

// Filter Examples - DEV only
if( defined('TR_DEVELOPMENT') && TR_DEVELOPMENT )
	require_once('examples.php');

function salesforce_activate(){

	// v3 no longer requires activation like this
	$options = get_option('salesforce3');

	if( $options['version'] == '3.0' ){
		return;
	}

	// v2 doesn't need this either
	$options = get_option('salesforce2');

	if( $options['version'] == '2.0' ){
		return;
	}

	// migrate to v2 from v1

	$oldoptions = get_option('salesforce');

	if( !empty($oldoptions) && $oldoptions['version'] != '2.0' ){

		$options = salesforce_default_settings();

		//migrate existing data
		$options['successmsg'] 			= $oldoptions['successmsg'];
		$options['errormsg'] 			= $oldoptions['errormsg'];
		$options['requiredfieldstext']	= $oldoptions['requiredfieldstext'];
		$options['sferrormsg'] 			= $oldoptions['sferrormsg'];
		$options['source'] 				= $oldoptions['source'];
		$options['submitbutton'] 		= $oldoptions['submitbutton'];

		$options['usecss'] 				= $oldoptions['usecss'];
		$options['wpcf7css'] 			= $oldoptions['wpcf7css'];
		//$options['hide_salesforce_link'] 		= $oldoptions['hide_salesforce_link'];

		$options['ccusermsg'] 			= false; //default to off for upgrades

		$options['org_id'] 				= $oldoptions['org_id'];

		//copy existing form input data
		if( is_array($oldoptions['inputs']) )
			foreach($oldoptions['inputs'] as $key=>$val){
				$newinputs[$key] = $val;
			}

		//sort merged inputs
		$newinputs = salesforce_sksort( $newinputs, 'pos', true );

		//save merged and sorted inputs
		$options['forms'][1]['inputs'] = $newinputs;

		//source is now saved per form
		$options['forms'][1]['source']	= $oldoptions['source'];

		update_option('salesforce2', $options);
		//$options = get_option('salesforce');

	}

	if( empty($oldoptions) ){
		salesforce_default_settings();
	}

}

/*
//Save Activation Error to DB for review
add_action('activated_plugin','save_error');
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}
*/

// Add settings link to plugins list
function salesforce_add_settings_link( $links ) {
  	array_unshift( $links, '<a href="options-general.php?page=salesforce-wordpress-to-lead">Settings</a>' );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_'.$plugin, 'salesforce_add_settings_link' );

// Admin column

add_filter('manage_'.salesforce_get_post_type_slug().'_posts_columns' , 'add_salesforce_form_columns');

function add_salesforce_form_columns($columns) {
    //unset( $columns['author'] );
    return array_merge(
	    	$columns,
	        array(
	        	'legacy_id' => 'Legacy ID'
	        )
        );
}

add_action('manage_posts_custom_column' , 'salesforce_form_custom_columns', 10, 2 );

function salesforce_form_custom_columns( $column, $post_id ) {
    switch ( $column ) {

    case 'legacy_id' :
        $form_id = salesforce_get_form_id_by_post_id( $post_id );

        if( $form_id ){
        	echo $form_id;
        }else{
	    	echo '&mdash;';
	    }

        break;
    }
}

//Add try DA and support links
function salesforce_add_plugin_meta( $plugin_meta, $plugin_file, $plugin_data, $status ){

	if( $plugin_file == plugin_basename( __FILE__ ) ){
	  	//array_push( $plugin_meta, '<a href="http://try.daddyanalytics.com/wordpress-to-lead-general?utm_source=ThoughtRefinery&utm_medium=link&utm_campaign=WP2L_Plugin_01&utm_content=da1_try_uri" target="_blank">Try Daddy Analytics</a>' );
	  	array_push( $plugin_meta, '<a href="http://wordpress.org/support/plugin/salesforce-wordpress-to-lead" target="_blank">Community Support</a>' );
	  	array_push( $plugin_meta, '<a href="http://thoughtrefinery.com/plugins/support/?plugin=salesforce-wordpress-to-lead" target="_blank">Premium Support</a>' );
	}

	return $plugin_meta;
}

add_filter( 'plugin_row_meta', 'salesforce_add_plugin_meta', 10, 4);

//add_filter('post_row_actions', 'salesforce_add_post_row_actions', 10, 2);

function salesforce_init() {
	load_plugin_textdomain( 'salesforce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Do we need to migrate data from 2.6.x to 3.0+ ?
	// Check for 3.0+ option - named salesforce3
	$option_check_v3 = get_option( 'salesforce3' );

	$option_check_v2 = get_option( 'salesforce2' );

	if( is_array( $option_check_v3 ) ){

		// we’re already upgraded

	}elseif( ! is_array( $option_check_v3 ) ){

		// run upgrade script
		if( is_array( $option_check_v2 ) ){
			error_log( 'SALESFORCE: Starting upgrading from "salesforce2" to "salesforce3"' );
			require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_upgrade_to_v3.php' );
			salesforce_migrate_option();
			error_log( 'SALESFORCE: Finished upgrading from "salesforce2" to "salesforce3"' );
		}else{
			// not sure what happened here?
			error_log( 'SALESFORCE: option "salesforce2" does not exist, cannot migrate to "salesforce3"' );
		}

	}

}

add_action( 'the_content', 'salesforce_single_form' );

function salesforce_single_form( $content ){

	if( get_post_type() == salesforce_get_post_type_slug() ){
		return $content . do_shortcode( '[salesforce_form id="' . get_the_id() . '"]' );
	}else{
		return $content;
	}

}

add_action('plugins_loaded', 'salesforce_init');

register_activation_hook( __FILE__, 'salesforce_activate' );