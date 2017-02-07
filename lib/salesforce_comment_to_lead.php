<?php

/**
 * Processes the comment data, and sends the lead if appropriate.
 *
 * @param int $id The ID of the comment
 * @return void
 **/
function salesforce_process_comment( $comment_id ) {
        if ( get_comment_meta( $comment_id, 'salesforce_lead_submitted', true ) )
                return;

        $options = get_option( 'salesforce2' );

        if ( ! $options[ 'commentstoleads' ] )
                return;

        $comment = get_comment( $comment_id );
        $post = get_post( $comment->comment_post_ID );

        // Some plugins use comments on custom post types for all kinds of things
        $allowed_types = apply_filters( 'salesforce_allowed_comment_to_lead_types', array( 'post', 'page' ) );
        if ( ! in_array( $post->post_type, $allowed_types ) )
                return;
                
        $first_name = get_comment_meta( $comment_id, 'author_first_name', true );
        $last_name = get_comment_meta( $comment_id, 'author_last_name', true );

        // Let's get at least some name data in from legacy comments
        if ( ! $first_name && ! $last_name )
                $first_name = $comment->comment_author;

        $lead_data = array( 
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $comment->comment_author_email,
                'lead_source' => 'Web comment, ' . get_site_url(),
                'URL' => $comment->comment_author_url,
                'description' => $comment->comment_content,
        );
        
        if ( submit_salesforce_form( $lead_data, $options ) )
                add_comment_meta( $comment_id, 'salesforce_lead_submitted', 1 );
}

/**
 * Hooks the WP salesforce_comment_post action to save the first and last name data,
 * and send a Salesforce Web-to-Lead API request when an approved comment is added.
 *
 * @param int $id The ID of the comment
 * @param int|string $comment_approved Either 1, 0 or 'spam' as returned from wp_allow_comment
 * @return void
 **/
function salesforce_comment_post( $comment_id, $comment_approved ) {
        $options = get_option( 'salesforce2' );
        if ( ! $options[ 'commentstoleads' ] )
                return;

        add_comment_meta( $comment_id, 'author_first_name', $_POST[ 'author_first_name' ] );
        add_comment_meta( $comment_id, 'author_last_name', $_POST[ 'author_last_name' ] );
        if ( 1 !== $comment_approved )
                return;
        salesforce_process_comment( $comment_id );
}
add_action( 'comment_post', 'salesforce_comment_post', 10, 2 );

/**
 * Hooks the WP wp_set_comment_status action to send the comment to
 * Salesforce, if the comment has just been approved.
 *
 * @param int $id The ID of the comment
 * @param string $comment_status Either 'hold', 'approve', 'spam' or 'delete'
 * @return void
 **/
function salesforce_wp_set_comment_status( $comment_id, $comment_status ) {
        if ( 'approve' != $comment_status )
                return;
        salesforce_process_comment( $comment_id );
}
add_action( 'wp_set_comment_status', 'salesforce_wp_set_comment_status', 10, 2 );

/**
 * Hooks the WP comment_form_defaults filter to swap the name field for a 
 * "First name" and "Last name" field.
 *
 * @param $fields array An array of default settings for the comments form
 * @return array An array of default settings for the comments form
 **/
function salesforce_comment_form_defaults( $defaults ) {
        $options = get_option( 'salesforce2' );
        if ( ! $options[ 'commentsnamefields' ] )
                return $defaults;

        $post = get_post( get_the_ID() );
        $allowed_types = apply_filters( 'salesforce_allowed_comment_to_lead_types', array( 'post', 'page' ) );
        if ( ! in_array( $post->post_type, $allowed_types ) )
                return $defaults;

        unset( $defaults[ 'fields' ][ 'author' ] );
        $req = get_option( 'require_name_email' );
        $aria_req = ( $req ? " aria-required='true'" : '' );
        $commenter = wp_get_current_commenter();
        $additions = array(
                'author_first_name' => '<p class="comment-form-author comment-form-author-first-name">' . '<label for="author_first_name">' . __( 'First Name', 'salesforce' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
                        '<input id="author_first_name" name="author_first_name" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
                'author_last_name' => '<p class="comment-form-author comment-form-author-last-name">' . '<label for="author_last_name">' . __( 'Last Name', 'salesforce' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
                        '<input id="author_last_name" name="author_last_name" type="text" value="" size="30"' . $aria_req . ' /></p>',
        );
        // Make sure the new elements are at the start of the array
        $defaults[ 'fields' ] = $additions + $defaults[ 'fields' ];

        // var_dump( $defaults ); exit;
        return $defaults;
}
add_filter( 'comment_form_defaults', 'salesforce_comment_form_defaults' );

/**
 * Hooks the WP pre_comment_on_post action to cheat in an author field
 * in the global $_POST array if the first and last name fields are 
 * both filled. Hackety McHack Hack.
 *
 * @param int $comment_post_ID The ID of the post being commented on
 * @return void
 **/
function salesforce_pre_comment_on_post( $comment_post_ID ) {
        if ( ! empty( $_POST[ 'author' ] ) )
                return;
        if ( empty( $_POST[ 'author_first_name' ] ) || empty( $_POST[ 'author_last_name' ] ) )
                return;
        if ( is_rtl() )
                $author = $_POST[ 'author_last_name' ] . ' ' . $_POST[ 'author_first_name' ];
        else
                $author = $_POST[ 'author_first_name' ] . ' ' . $_POST[ 'author_last_name' ];
        // There's almost certainly some subtleties in one language or other for the separator character,
        // or the order, etc, so allow devs the opportunity to override the concatenated author string.
        $_POST[ 'author' ] = apply_filters( 'salesforce_comment_author', $author, $_POST[ 'author_first_name' ], $_POST[ 'author_last_name' ] );
}
add_action( 'pre_comment_on_post', 'salesforce_pre_comment_on_post' );