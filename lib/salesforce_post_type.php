<?php

add_action( 'init', 'register_cpt_salesforce_w2l_form' );

function register_cpt_salesforce_w2l_form() {

    $labels = array(
        'name' => _x( 'Salesforce Forms', 'salesforce_w2l_form' ),
        'singular_name' => _x( 'Salesforce Form', 'salesforce_w2l_form' ),
        'add_new' => _x( 'Add New', 'salesforce_w2l_form' ),
        'add_new_item' => _x( 'Add New Form', 'salesforce_w2l_form' ),
        'edit_item' => _x( 'Edit Form', 'salesforce_w2l_form' ),
        'new_item' => _x( 'New Form', 'salesforce_w2l_form' ),
        'view_item' => _x( 'View Form', 'salesforce_w2l_form' ),
        'search_items' => _x( 'Search Salesforce Forms', 'salesforce_w2l_form' ),
        'not_found' => _x( 'No forms found', 'salesforce_w2l_form' ),
        'not_found_in_trash' => _x( 'No forms found in Trash', 'salesforce_w2l_form' ),
        'parent_item_colon' => _x( 'Parent Form:', 'salesforce_w2l_form' ),
        'menu_name' => _x( 'Salesforce Forms', 'salesforce_w2l_form' ),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => false,

        'supports' => array( 'title', 'excerpt', 'author', 'custom-fields' ),

        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-forms',
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'page'
    );

    register_post_type( 'salesforce_w2l_form', $args );
}