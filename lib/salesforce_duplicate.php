<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter('post_row_actions', 'salesforce_add_post_row_actions', 10, 2);

function salesforce_add_post_row_actions( $actions, $post ){

	if( $post->post_type != salesforce_get_post_type_slug() )
		return $actions;

    $actions['salesforce_duplicate'] = '<a href="'. add_query_arg( array( 'post_type' => salesforce_get_post_type_slug(), 'sf_action' => 'duplicate', 'post_id' => $post->ID ), admin_url( 'edit.php' ) ).'" class="salesforce_duplicate_link">' . __('Duplicate') . '</a>';

   return $actions;

}

//add_query_arg( array( 'post_type' => salesforce_get_post_type_slug() ), admin_url( 'edit.php' ) )
