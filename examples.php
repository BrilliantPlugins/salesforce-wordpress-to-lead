<?php

// Remove Form Action
add_filter( 'salesforce_w2l_form_action', 'salesforce_w2l_form_action_example', 10, 1 );
function salesforce_w2l_form_action_example(  $action ){

	return '';

}