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

        'supports' => array( 'title', 'author' ),

        'public' => false,
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

    register_post_type( salesforce_get_post_type_slug(), $args );
}

add_action( 'add_meta_boxes_' . salesforce_get_post_type_slug(), 'salesforce_setup_metaboxes' );

function salesforce_setup_metaboxes(){
	add_meta_box( 'salesforce-form-id', 'Salesforce Web to Lead', 'salesforce_form_id_metabox', salesforce_get_post_type_slug(), 'side', 'high' );

	add_meta_box( 'salesforce-form-editor-ad', 'Plugin Sponsor', 'salesforce_form_editor_ad_metabox', salesforce_get_post_type_slug(), 'normal', 'high' );

	add_meta_box( 'salesforce-form-editor', 'Form Editor', 'salesforce_form_editor_metabox', salesforce_get_post_type_slug(), 'normal', 'high' );

}

function salesforce_form_id_metabox( $post ){

	echo '<p>To embed this form, copy the following shortcode into a post or page:</p><p><code> [salesforce_form id="' . $post->ID . '"] </code></p>';

	echo '<p>You can also use the widget and choose from the dropdown.</p>';

	//echo '<p>Legacy Shortcode<br>[salesforce form="' . salesforce_get_form_id_by_post_id( $post->ID ) . '"]</p>';

}

function salesforce_form_editor_ad_metabox( $post ){

	$salesforce = new Salesforce_Admin();

	$loc = 'banner-main';
	$ad = $salesforce->get_ad_code( $loc );
	if( $ad ){
		$link = $salesforce->get_ad_link( $ad['utm_content'], $ad['utm_medium'], $ad['url'], '',$ad['utm_source'],$ad['utm_campaign']);
		echo '<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>';
	}

}

function salesforce_form_editor_metabox( $post ){

	$salesforce = new Salesforce_Admin();

	salesforce_form_editor_ui( $post );

}

function salesforce_form_editor_ui( $post ){

	$salesforce = new Salesforce_Admin();

	$form_id = salesforce_get_form_id_by_post_id( $post->ID );

	$options = get_option( salesforce_get_option_name() );

	// load form data into option for display
	// TODO refactor this!
	$options['forms'][$form_id] = get_post_meta( $post->ID , salesforce_get_meta_key(), true );

	$content = '<table id="salesforce_form_editor" class="wp-list-table widefat fixed">';
	$content .= '<tr>'
	.'<th width="10%">'.__('Field','salesforce').'</th>'
	.'<th width="15%">'.__('Operations','salesforce').'</th>'
	.'<th width="12%">'.__('Type','salesforce').'</th>'
	.'<th width="13%">'.__('Label/Value','salesforce').'</th>'
	//.'<th width="15%">'.__('Value','salesforce').'</th>'
	.'<th width="20%">'.__('Options','salesforce').'</th>'
	.'<th width="8%">'.__('Order','salesforce').'</th>'
	.'</tr>';
	$i = 1;

	foreach ( $options['forms'][$form_id]['inputs'] as $field => $input ) {

	$trclass= 'disabled';
	if( $input['show'] )
		$trclass= 'enabled';

		if (empty($input['pos']))
			$input['pos'] = $i;
		$content .= '<tr class="' .$trclass.' '. (($i % 2) ? 'alternate' : '') . '">';
		$content .= '<th>'.$field.'</th>';
		$content .= '<td>';
		$content .= '<table>';
		$content .= '<tr>';
		$content .= '<td><label for="salesforce_inputs['.$field.'_show]">Enabled</label></td>';
		$content .= '<td><input type="checkbox" name="salesforce_inputs['.$field.'_show]" id="salesforce_inputs['.$field.'_show]" '.checked($input['show'],true,false).'/></td>';
		$content .= '</tr><tr>';
		$content .= '<td><label for="salesforce_inputs['.$field.'_required]">Required</label></td>';
		$content .= '<td><input type="checkbox" name="salesforce_inputs['.$field.'_required]" id="salesforce_inputs['.$field.'_required]" '.checked($input['required'],true,false).'/></td>';
		$content .= '</tr><tr>';
		$content .= '<td><label for="salesforce_inputs['.$field.'_delete]">Delete</label></td>';
		$content .= '<td><input type="checkbox" name="salesforce_inputs['.$field.'_delete]" id="salesforce_inputs['.$field.'_delete]" /></td>';
		$content .= '</tr>';
		$content .= '</table>';
		$content .= '</td>';
		$content .= '<td><select name="salesforce_inputs['.$field.'_type]">';
		$content .= '<option value="text" '.selected($input['type'],'text',false).'>Text</option>';
		$content .= '<option value="email" '.selected($input['type'],'email',false).'>Email</option>';
		$content .= '<option value="textarea" '.selected($input['type'],'textarea',false).'>Textarea</option>';
		$content .= '<option value="hidden" '.selected($input['type'],'hidden',false).'>Hidden</option>';
		$content .= '<option value="select" '.selected($input['type'],'select',false).'>Select (picklist)</option>';
		$content .= '<option value="multi-select" '.selected($input['type'],'multi-select',false).'>Multi-Select (picklist)</option>';
		$content .= '<option value="checkbox" '.selected($input['type'],'checkbox',false).'>Checkbox</option>';
		//$content .= '<option '.selected($input['type'],'current_date',false).'>current_date</option>';
		$content .= '<option value="date" '.selected($input['type'],'date',false).'>Date</option>';
		$content .= '<option value="html" '.selected($input['type'],'html',false).'>HTML</option>';
		$content .= '</select></td>';
		$content .= '<td><small>Label:</small> <input size="10" name="salesforce_inputs['.$field.'_label]" type="text" value="'.esc_html(stripslashes($input['label'])).'"/>'; //</td>'.'<td>';

		$content .= '<br><small>Value:</small> <input size="10" name="salesforce_inputs['.$field.'_value]" type="text" value="';
		if( isset($input['value']) ) $content .= esc_html(stripslashes($input['value']));
		$content .= '"/></td>';

		$opts = '';
		if( isset( $input['opts'] ) )
			$opts = $input['opts'];

		$content .= '<td><textarea rows="4" name="salesforce_inputs['.$field.'_opts]"  >'.esc_textarea( stripslashes( $opts ) ).'</textarea></td>';
		$content .= '<td><input size="2" name="salesforce_inputs['.$field.'_pos]" type="text" value="'.esc_html( $input['pos'] ).'"/></td>';
		$content .= '</tr>';
		$i++;
	}

	$content .= '</table>';

?>
	<script>

	var pos = <?php echo $i; ?>;
	var i = 1;
	function salesforce_add_field(){
	pos++;

	var row = '<tr>';
	row += '<td><input type="text" size="10" name="salesforce_add_inputs['+i+'][field_name]"></td>';
	row += '<td><table>'
	row += '<tr><td><label for="salesforce_add_inputs['+i+'][show]">Enabled</label></td><td><input type="checkbox" name="salesforce_add_inputs['+i+'][show]"></td></tr>';
	row += '<tr><td><label for="salesforce_add_inputs['+i+'][required]">Required</label></td><td><input type="checkbox" name="salesforce_add_inputs['+i+'][required]"></td></tr>';
	row += '</table></td>';
	row += '<td><select name="salesforce_add_inputs['+i+'][type]">'
		+ '<option value="text">Text</option>'
		+ '<option value="email">Email</option>'
		+ '<option value="textarea">Textarea</option>'
		+ '<option value="hidden">Hidden</option>'
		+ '<option value="select">Select (picklist)</option>'
		+ '<option value="multi-select">Multi-Select (picklist)</option>'
		+ '<option value="checkbox">Checkbox</option>'
		//+ '<option value="current_date">current_date</option>'
		+ '<option value="date">Date</option>'
		+ '<option value="html">HTML</option>'
		+ '</select></td>';
	row += '<td><small>Label:</small><input size="10" type="text" name="salesforce_add_inputs['+i+'][label]">';
	row += '<small>Value:</small><input size="14" type="text" name="salesforce_add_inputs['+i+'][value]"></td>';
	row += '<td><textarea rows="4" name="salesforce_add_inputs['+i+'][opts]"></textarea></td>';
	row += '<td><input type="text" size="2" name="salesforce_add_inputs['+i+'][pos]" value="'+pos+'"></td>';
	row += '</tr>';

	jQuery('#salesforce_form_editor > tbody').append(row);

	i++;

}

</script>
	<?php

	$content .= '<p><a class="button-secondary" href="javascript:salesforce_add_field();">Add a field</a></p>';

	// $this->postbox('sffields',__('Form Fields', 'salesforce'),$content);
	echo $content;

	$content = '';

	if( $options['forms'][$form_id]['type'] == '' )
		$options['forms'][$form_id]['type'] = 'lead';

	$content .= '<p>';
	$content .= '<label>'.__('Form Type:','salesforce').'</label><br/>';
	$content .= '<input type="radio" name="salesforce_type" value="lead" '.checked($options['forms'][$form_id]['type'],'lead',false).'> Web to Lead <br>';
	$content .= '<input type="radio" name="salesforce_type" value="case"'.checked($options['forms'][$form_id]['type'],'case',false).'> Web to Case';
	$content .= '<br/><small>'.__('<b>Note:</b> Daddy Analytics does not support cases at this time.').'</small>';
	$content .= '</p>';


	$content .= '<p>';
	$content .= '<label>'.__('Lead Source:','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_source" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['source']).'">';

	$content .= '<br/><small>'.__('Lead Source (up to 40 characters) to display in Salesforce.com, use %URL% to include the URL of the page containing the form (need more characters? See the <a href="https://wordpress.org/plugins/salesforce-wordpress-to-lead/faq/" target="_blank">FAQ</a>). You can also use a field above to set the lead source (this value will not be used if a field named lead_source exists).').'</small>';

	if( !defined('SFWP2L_HIDE_ADS') )
		$content .= '<br/><br/><small>'.__('<b>Daddy Analytics</b> will populate the Lead Source field with the web source of the Lead (such as Organic - Google, Paid - Bing, or Google Adwords). Daddy Analytics will also populate the Salesforce Address field with the estimated GeoLocation of your Leads. <br><i>Leave the Lead Source field blank if you have a subscription to <a href="'.$salesforce->get_ad_link( 'da_ls', 'text' ).'">Daddy Analytics.</a></i>').'</small>';

	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Return/Thanks URL:','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_returl" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['returl']).'">';
	$content .= '<br/><small>'.__('e.g.http://yoursite.com/thanks/').'</small>';
	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Success Message:','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_successmsg" style="width:50%;" value="'.esc_html(salesforce_get_option('successmsg',$form_id,$options)).'">';
	$content .= '<br/><small>'.__('Overrides the default message for this form.(leave blank to use the global setting)').'</small>';
	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Submit button text (override):','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_submitbutton" style="width:50%;" value="'.esc_html(stripslashes( salesforce_get_option('submitbutton',$form_id,$options) )).'">';
	$content .= '<br/><small>'.__('Overrides the default message for this form.(leave blank to use the global setting)').'</small>';
	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Required fields text (override):','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_requiredfieldstext" style="width:50%;" value="'.esc_html(stripslashes( salesforce_get_option('requiredfieldstext',$form_id,$options) )).'">';
	$content .= '<br/><small>'.__('Overrides the default message for this form (leave blank to use the global setting).').'</small>';
	$content .= '</p>';

	/* Add a Subject line to the User email on a per-form basis */
	$content .= '<p>';
	$content .= '<label>' . __( 'User CC email subject:', 'salesforce' ) . '</label><br/>';
	$content .= '<input type="text" name="salesforce_cc_email_subject" style="width:50%;" value="' . esc_html( salesforce_get_option( 'cc_email_subject', $form_id, $options ) ) . '">';
	$content .= '<br/><small>' . __( 'Subject of the email when sending out a copy to the user.(leave blank to use the global setting)' ) . '</small>';
	$content .= '</p>';


	$content .= '<p>';
	$content .= '<label>'.__('Captcha:','salesforce').'</label><br/>';

	$content .= '<input type="radio" name="salesforce_captchaform" value=""'.checked(salesforce_get_option('captchaform',$form_id,$options),'',false).'> Use global setting <br>';
	$content .= '<input type="radio" name="salesforce_captchaform" value="enabled" '.checked(salesforce_get_option('captchaform',$form_id,$options),'enabled',false).'> Enabled for this form<br>';
	$content .= '<input type="radio" name="salesforce_captchaform" value="disabled"'.checked(salesforce_get_option('captchaform',$form_id,$options),'disabled',false).'> Disabled for this form';


	$content .= '<br/><small>'.__('Overrides the default captcha settings for this form.').'</small>';
	$content .= '</p>';


	$content .= '<p>';
	$content .= '<label>'.__('Required Fields Text Location:','salesforce').'</label><br/>';
	$content .= '<input type="radio" name="salesforce_requiredfieldstextpos" value=""'.checked( salesforce_get_option('requiredfieldstextpos',$form_id,$options),'',false).'> Below Form <br>';
	$content .= '<input type="radio" name="salesforce_requiredfieldstextpos" value="top" '.checked(salesforce_get_option('requiredfieldstextpos',$form_id,$options),'top',false).'> Above Form <br>';
	$content .= '<input type="radio" name="salesforce_requiredfieldstextpos" value="hidden"'.checked(salesforce_get_option('requiredfieldstextpos',$form_id,$options),'hidden',false).'> None';
	$content .= '</p>';


	$content .= '<p>';
	$content .= '<label>'.__('Label Location (Content):','salesforce').'</label><br/>';

	$label_location = trim( $options['forms'][$form_id]['labellocation'] );

	if( !$label_location )
		$label_location = 'top-aligned';

	$content .= '<input type="radio" name="salesforce_labellocation" value="top-aligned" '.checked( $label_location, 'top-aligned', false ).'> Top Aligned <br>';
	$content .= '<input type="radio" name="salesforce_labellocation" value="left-aligned"'.checked( $label_location, 'left-aligned', false ).'> Left Aligned <br>';
	$content .= '<input type="radio" name="salesforce_labellocation" value="placeholders"'.checked( $label_location, 'placeholders', false ).'> Placeholders';
	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Label Location (Sidebar):','salesforce').'</label><br/>';

	$label_location_sidebar = trim( $options['forms'][$form_id]['labellocationsidebar'] );
	if( !$label_location_sidebar )
		$label_location_sidebar = 'top-aligned';

	$content .= '<input type="radio" name="salesforce_labellocationsidebar" value="top-aligned" '.checked( $label_location_sidebar, 'top-aligned', false ).'> Top Aligned <br>';
	$content .= '<input type="radio" name="salesforce_labellocationsidebar" value="left-aligned"'.checked( $label_location_sidebar, 'left-aligned', false ).'> Left Aligned <br>';
	$content .= '<input type="radio" name="salesforce_labellocationsidebar" value="placeholders"'.checked( $label_location_sidebar, 'placeholders', false ).'> Placeholders';
	$content .= '</p>';

	$content .= '<p>';
	$content .= '<label>'.__('Auto Formatting:','salesforce').'</label><br/>';

	$content .= '<input type="checkbox" name="salesforce_donotautoaddcolontolabels" value="1" '.checked( $options['forms'][$form_id]['donotautoaddcolontolabels'], '1', false ).'> Do not automatically add a colon to labels <br>';
	$content .= '</p>';

	$content .= '<input type="hidden" name="salesforce_form_id" id="form_id" value="'.$form_id.'">';

	$content .= '<p>';
	$content .= '<label>'.__('Salesforce.com Organization ID (override):','salesforce').'</label><br/>';
	$content .= '<input type="text" name="salesforce_org_id" style="width:50%;" value="'. esc_html( stripslashes( salesforce_get_option( 'org_id', $form_id, $options ) ) ).'">';
	$content .= '<br/><small>'.__('Overrides the default org_id for this form (leave blank to use the global setting).').'</small>';
	$content .= '</p>';

	echo $content;

	echo '<hr><p><b>Legacy Form ID:</b> ' . salesforce_get_form_id_by_post_id( $post->ID ) . '</p>';

}

//save options

add_action( 'save_post_'.salesforce_get_post_type_slug() , 'salesforce_form_editor_save', 10, 3 );

function salesforce_form_editor_save( $post_id, $post, $update ){

	remove_action( 'save_post_'.salesforce_get_post_type_slug() , 'salesforce_form_editor_save', 10, 3 );

	if ( wp_is_post_revision( $post_id ) )
		return;

	$salesforce = new Salesforce_Admin();

	//$post = get_post( $post_id );

		$options = get_post_meta( $post_id, salesforce_get_meta_key(), true );

		//echo '<pre>'. print_r( $_POST, 1 ).'</pre>';

		//echo '<pre>'. print_r( $options, 1 ).'</pre>';

		// START FORM EDITOR SAVE OPTIONS

			if( empty( $options ) ){
				$options = $salesforce->default_form();
			}

			//echo '<pre>'. print_r( $options, 1 ).'</pre>';

			//Begin Save Form Data
			$newinputs = array();

			foreach ( $options['inputs'] as $id => $input ) {

				if ( ! empty($_POST['salesforce_inputs'][$id.'_delete'] ) ) {
					continue;
				}

				foreach (array('show','required') as $option_name) {
					if (isset($_POST['salesforce_inputs'][$id.'_'.$option_name])) {
						$newinputs[$id][$option_name] = true;
						unset($_POST['salesforce_inputs'][$id.'_'.$option_name]);
					} else {
						$newinputs[$id][$option_name] = false;
					}
				}

				foreach ( array( 'type', 'label', 'value', 'pos', 'opts' ) as $option_name ) {

					if ( isset( $_POST['salesforce_inputs'][$id.'_'.$option_name] ) ) {
						$newinputs[$id][$option_name] = $_POST['salesforce_inputs'][$id.'_'.$option_name];
						unset($_POST['salesforce_inputs'][$id.'_'.$option_name]);
					}
				}
			}

			//add any new fields

			if( isset( $_POST['salesforce_add_inputs'] ) ){

				foreach ( $_POST['salesforce_add_inputs'] as $key => $input ) {

					//force valid field names
					$id = salesforce_sanitize_key( $input['field_name'] );

					// can't use 'name' in wp, doesn't exist at Salesforce, must be a custom field
					// avoids support requests when using 'name' breaks the page
					if( $id == 'name' )
						$id = 'name__c';

					if( ! empty( $id ) ){
						foreach (array('show','required') as $option_name) {
							if (isset($_POST['salesforce_add_inputs'][$key][$option_name])) {
								$newinputs[$id][$option_name] = true;
								unset($_POST['salesforce_add_inputs'][$key][$option_name]);
							} else {
								$newinputs[$id][$option_name] = false;
							}
						}

						foreach ( array('type','label','value','pos','opts' ) as $option_name) {
							if ( isset( $_POST['salesforce_add_inputs'][$key][$option_name] ) ) {
								echo '$option_name = '.$option_name.' = '.$_POST['salesforce_add_inputs'][$key][$option_name].'<hr>';
								$newinputs[$id][$option_name] = $_POST['salesforce_add_inputs'][$key][$option_name];
								unset( $_POST['salesforce_add_inputs'][$key][$option_name] );
							}
						}
					}
				}

			}

			//echo '<pre>NEWINPUTS = '. print_r( $newinputs, 1 ).'</pre>';

			// normal options
			salesforce_sksort ($newinputs, 'pos', true );
			$options['inputs'] = $newinputs; //TODO ?

			foreach ( array( 'source','returl','successmsg','captchaform','labellocation','labellocationsidebar','submitbutton','requiredfieldstext','requiredfieldstextpos','type','org_id', 'cc_email_subject','donotautoaddcolontolabels') as $option_name ) {
				if ( isset( $_POST['salesforce_'.$option_name] ) ) {
					$options[$option_name] = $_POST[ 'salesforce_' . $option_name ];
				}else{
					$options[$option_name] = '';
				}
			}

		//echo '<pre>'. print_r( $options, 1 ).'</pre>';

		// SAVE OPTION(S) TO DB
		update_post_meta( $post_id, salesforce_get_meta_key(), $options );

		//END FORM EDITOR SAVE OPTIONS

	add_action( 'save_post_'.salesforce_get_post_type_slug() , 'salesforce_form_editor_save', 10, 3 );

}