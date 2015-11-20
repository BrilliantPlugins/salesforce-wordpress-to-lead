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

	$options = get_option('salesforce2');

	if( $options['version'] == '2.0' )
		return;

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
		salesforce_sksort($newinputs,'pos',true);

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

function salesforce_init() {
	load_plugin_textdomain( 'salesforce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Do we need to migrate data from 2.6.x to 2.7+ ?
	// Check for 2.7+ option - named salesforce3
	$option_check = get_option( 'salesforce3' );

	if( ! is_array( $option_check ) ){

		// run upgrade script
		require_once( plugin_dir_path( __FILE__ ) . 'lib/salesforce_upgrade_to_v3.php' );

	}

}
add_action('plugins_loaded', 'salesforce_init');

register_activation_hook( __FILE__, 'salesforce_activate' );