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