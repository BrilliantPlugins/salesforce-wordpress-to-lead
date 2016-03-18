<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function salesforce_get_duplicate_url( $post_id ){

	return add_query_arg( array( 'post_type' => salesforce_get_post_type_slug(), 'sf_action' => 'duplicate', 'post_id' => $post_id, 'sf_nonce' => wp_create_nonce( 'sf_duplicate_form_' . $post_id ) ), admin_url( 'edit.php' ) );

}


add_filter('post_row_actions', 'salesforce_add_post_row_actions', 10, 2);

function salesforce_add_post_row_actions( $actions, $post ){

	if( $post->post_type != salesforce_get_post_type_slug() )
		return $actions;

    $actions['salesforce_duplicate'] = '<a href="'. salesforce_get_duplicate_url( $post->ID ) .'" class="salesforce_duplicate_link">' . __('Duplicate') . '</a>';

   return $actions;

}

add_action( 'admin_init', 'salesforce_duplicate_form' );

function salesforce_duplicate_form(){

	if( isset( $_GET['post_type'], $_GET['sf_action'], $_GET['post_id'], $_GET['sf_nonce'] ) && $_GET['sf_action'] == 'duplicate' &&  wp_verify_nonce( $_GET['sf_nonce'], 'sf_duplicate_form_' . absint( $_GET['post_id'] ) ) && $_GET['post_type'] == salesforce_get_post_type_slug() && get_post_type( absint( $_GET['post_id'] ) ) == salesforce_get_post_type_slug() ){
		// we are trying to duplicate a salesforce form, not something else!

		$post_id = absint( $_GET['post_id'] );

		$post = get_post( $post_id );

		$form = array(
			'post_type' => salesforce_get_post_type_slug(),
			'post_title' => 'Copy of ' . $post->post_title,
			'post_content' => $post->post_content,
			'post_status' => 'pending',
			'comment_status' => 'closed',
		);

		$new_post_id = wp_insert_post( $form );

		if( $new_post_id ){
			// add metadata

			// data
			update_post_meta( $new_post_id, '_salesforce_form_data', get_post_meta( $post_id, '_salesforce_form_data', true ) );

			// redirect to remove query vars
			wp_redirect( get_edit_post_link( $new_post_id, 'url' ), 302 );
			exit;

		}

	}

}
