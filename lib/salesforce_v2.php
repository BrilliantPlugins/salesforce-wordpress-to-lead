<?php
// Legacy v2 form output & submission
// Uses sequential form id and [salesforce] shortcode

add_shortcode('salesforce', 'salesforce_shortcode');

function salesforce_shortcode( $atts ) {

	extract( shortcode_atts( array(
		'form' => '1',
		'sidebar' => false,
	), $atts ) );

	$emailerror = '';
	$captchaerror = '';
	$content = '';

	$form = absint( $form );
	$sidebar = (bool) $sidebar;

	$options = get_option( salesforce_get_option_name() );

	//load form data via legacy id
	$options['forms'][ $form ] = salesforce_get_meta_data( salesforce_map_legacy_id( $form ) );


	if ( ! is_array( $options ) )
		$options = salesforce_default_settings();

	//don't submit unless we're in the right shortcode
	if( isset( $_POST['form_id'] ) ){
		$submitted_form_id = intval( $_POST['form_id'] );

			if( $form ){
				if( $submitted_form_id != $form ){
					$content = salesforce_form( $options, $sidebar, null, $form );
					return '<div class="salesforce_w2l_lead">'.$content.'</div>';
				}
			}

	}

	//this is the right form, continue
	if ( isset( $_POST['w2lsubmit'] ) ) {
		$error = false;
		$post = array();

		$has_error = false;

		// field validation
		foreach ($options['forms'][$form]['inputs'] as $id => $input) {

			$field_name_attr = 'sf_'.$id;

			if( isset( $_POST[ $field_name_attr ] ) ){

				$val = $_POST[ $field_name_attr ];

				if( is_array($val) ){
					$val = array_map( 'trim', $val );
				}else{
					$val = trim( $val );
				}

			}else{

				$val = '';

			}

			$error = array(
				'valid' => false,
				'message' => $options['errormsg'],
			);

			if ( $input['show'] && $input['required'] && strlen( salesforce_maybe_implode( ';', $val ) ) == 0 ) {
				$error['valid'] = false;
			}else{
				$error['valid'] = true;
			}

			if ( ( ($id == 'email' && $input['required'] ) || ( $input['type'] == 'email' && $val ) )  && !is_email($val) ) {
				$error['valid'] = false;

				if( isset( $options['emailerrormsg'] ) && $options['emailerrormsg'] ){
					$error['message'] = $options['emailerrormsg'];
				}else{
					// backwards compatibility
					$error['message'] = __('The email address you entered is not valid.','salesforce');
				}

			}

			$error = apply_filters('sfwp2l_validate_field', $error, $id, $val, $options['forms'][$form]['inputs'][$id] );

			//$error = apply_filters('sfwp2l_'.$id, $error, $id, $options['forms'][$form]['inputs'][$id] );

			$errors[$id] = $error;

			if ( $input['required'] && strlen( salesforce_maybe_implode( ';', $val ) ) == 0 ) {

			//$options['forms'][$form]['inputs'][$id]['error'] = true;

			//	$error = true;
			//} else if ($id == 'email' && $input['required'] && !is_email($_POST[$field_name_attr]) ) {
			//	$error = true;
			//	$emailerror = true;
			} else {
				if( isset( $_POST[$field_name_attr] ) ){
					if( is_array( $_POST[$field_name_attr] ) ){
						$post[$id] = array_map( 'salesforce_clean_field', $_POST[$field_name_attr] );
					}else{
						$post[$id] = salesforce_clean_field( $_POST[$field_name_attr] );
					}
				}
			}
		}

		//pass daddy analytics fields
		if( isset( $options['da_token'] ) && isset( $options['da_url'] ) ){

			$da_token = $options['da_token'];
			$da_url = $options['da_url'];

			if( isset( $_POST[$da_token] ) )
				$post[$da_token] = $_POST[$da_token];

			if( isset( $_POST[$da_url] ) )
				$post[$da_url] = $_POST[$da_url];

		}

		//check captcha if enabled

		if( salesforce_get_option('captchaform',$form,$options) == 'enabled' || ( salesforce_get_option('captchaform',$form,$options) == '' && $options['captcha']) ){

			if( $_POST['captcha_hash'] != sha1( $_POST['captcha_text'].NONCE_SALT )){
				$has_error = true;

				$errors['captcha']['valid'] = false;

				if( isset( $options['captchaerrormsg'] ) && $options['captchaerrormsg'] ){
					$errors['captcha']['message'] = $options['captchaerrormsg'];
				}else{
					//backwards compatibility
					$errors['captcha']['message'] = __('The text you entered did not match the image.','salesforce');
				}

			}

		}

		foreach( $errors as $error ){
			if(!$error['valid'])
				$has_error = true;
		}

/*
		$org_id = salesforce_get_option('org_id', $form_id, $options);
		echo '$org_id='.$org_id;
*/

		if (!$has_error) {
			$result = submit_salesforce_form( $post, $options, $form );

			//echo 'RESULT='.$result;
			//if($result) echo 'true';
			//if(!$result) echo 'false';

			if (!$result){
				$content = '<strong class="error_message">'.esc_html(stripslashes($options['sferrormsg'])).'</strong>';
			}else{

				// Return / Success URL
				$returl = apply_filters( 'salesforce_w2l_returl', $options['forms'][$form]['returl'], $form );
				$returl = apply_filters( 'salesforce_w2l_returl_'.absint( $form_id ), $returl, $form );
				$returl = esc_url_raw( $returl );

				if( $returl ){
					?>
					<script type="text/javascript">
				   <!--
				      window.location= <?php echo "'" . $returl . "'"; ?>;
				   //-->
				   </script>
					<?php
				}

				// Success message
				$success_message = apply_filters( 'salesforce_w2l_success_message', salesforce_get_option( 'successmsg', $form, $options ), $form );
				$success_message = apply_filters( 'salesforce_w2l_success_message_'.absint( $form_id ), $success_message, $form );

				if( $success_message )
					$content = '<strong class="success_message">'.esc_html( stripslashes( $success_message ) ).'</strong>';

			}

			$sf_form_id = get_salesforce_form_id( $form_id, $sidebar );

			$content = '<div id="'.$sf_form_id.'">'.$content.'</div>';

		} else {
			$errormsg = esc_html( stripslashes($options['errormsg']) ) ;

			$content .= salesforce_form( $options, $sidebar, $errors, $form );
		}
	} else {
		$content = salesforce_form( $options, $sidebar, null, $form );
	}

	return '<div class="salesforce_w2l_lead">'.$content.'</div>';
}

function salesforce_get_form( $form ){

	$options = get_option( salesforce_get_option_name() );

	if( isset( $options['forms'][$form] ) )
		return $options['forms'][$form];

	return false;
}

function salesforce_get_field( $name, $form ){

	$options = get_option( salesforce_get_option_name() );

	if( isset( $options['forms'][$form]['inputs'][$name] ) )
		return $options['forms'][$form]['inputs'][$name];

	return false;
}

function salesforce_get_option( $name, $form, $options = null ){

	if( !$options ){
		$options = get_option( salesforce_get_option_name() );
		if (!is_array($options))
			$options = salesforce_default_settings();
	}

	if( isset( $options['forms'][$form][$name] ) && strlen( trim( $options['forms'][$form][$name] ) ) )
		return $options['forms'][$form][$name];

	if( isset( $options[$name] ) )
		return $options[$name];

	return false;

}

function salesforce_form( $options, $is_sidebar = false, $errors = null, $form_id = 1 ) {

	if( !isset( $options['forms'][$form_id] ) )
		return;

	$content = '';

/*
	if (!empty($content))
		$content = wpautop('<strong>'.$content.'</strong>');
*/

	if ($options['usecss']) {
		wp_enqueue_style( 'sfwp2lcss', plugins_url('../assets/css/sfwp2l.css', __FILE__) );
	}

	$label_location = salesforce_get_option('labellocation', $form_id, $options);

	$sidebar = '';

	if ( $is_sidebar )
		$sidebar = ' sidebar';

	if( !$label_location )
		$label_location = 'top-aligned';

	if( $is_sidebar )
		$label_location = salesforce_get_option('labellocationsidebar', $form_id, $options);

	if( $label_location == 'placeholders' )
		wp_enqueue_script( 'sfwp2ljqph', plugins_url('../assets/js/jquery-placeholder/jquery.placeholder.js', __FILE__)  );

	if( $options['wpcf7css'] && $options['wpcf7jsfix'] )
		wp_dequeue_script( 'contact-form-7');

	$custom_css = '/salesforce-wordpress-to-lead/custom.css';

	if( file_exists( get_stylesheet_directory() . $custom_css ) )
		wp_enqueue_style( 'sfwp2lcsscustom', get_stylesheet_directory_uri() . $custom_css );

	if ( $options['wpcf7css'] ) {
		$content .= '<section class="form-holder clearfix"><div class="wpcf7">';
	}

	$sf_form_id = get_salesforce_form_id( $form_id, $sidebar );

	$action	= '#'.$sf_form_id;
	$action = apply_filters( 'salesforce_w2l_form_action', $action );

	$content .= "\n".'<form id="'.$sf_form_id.'" class="'.($options['wpcf7css'] ? 'wpcf7-form' : 'w2llead'.$sidebar ).' '.$label_location.'" method="post" action="'.$action.'">'."\n";

	$reqtext = stripslashes( salesforce_get_option('requiredfieldstext',$form_id,$options) );

	$date_fields = array();

	if (!empty($reqtext) && salesforce_get_option('requiredfieldstextpos',$form_id,$options) == 'top' )
		$content .= '<p class="sf_required_fields_msg" id="requiredfieldsmsg"><sup><span class="required">*</span></sup> '.esc_html( $reqtext ).'</p>';

	foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
		if (!$input['show'])
			continue;

		$field_name_attr = 'sf_'.$id;

		$val = '';
		if ( isset( $_POST[ $field_name_attr ] ) ){
			$val = $_POST[ $field_name_attr ];

			if( is_array( $val ) ){
				$val = array_map( 'esc_attr', array_map( 'salesforce_clean_field', $val ) );
			}else{
				$val = esc_attr(strip_tags(stripslashes($val)));
			}

		}else{
			if( isset( $input['value'] ) ) $val	= esc_attr( strip_tags( stripslashes( $input['value'] ) ) );
		}

		$val = apply_filters( 'salesforce_w2l_field_value', $val, sanitize_html_class( $id ), $form_id );

		$val = apply_filters( 'salesforce_w2l_field_value_'.absint( $form_id ).'_'. $id, $val );

		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			$content .= '<div class="sf_field sf_field_'.$id.' sf_type_'.$input['type'].'">';
		}

		$error 	= ' ';
		if (isset($input['error']) && $input['error']) {
			$error 	= ' error ';
		}

		if( $input['type'] == 'date' ){
			$date_fields[$id] = $input;
		}

		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			if ($options['wpcf7css']) { $content .= '<p>'; }
			if ($input['type'] == 'checkbox') {

				if( isset( $_POST[$field_name_attr] ) ){
					$post_val = $_POST[$field_name_attr];
				}else{
					$post_val = '';
				}

				$content .= "\t\n\t".'<input type="checkbox" id="sf_'.$id.'" class="w2linput checkbox" name="'.$field_name_attr.'" value="'.$val.'" '.checked( $post_val, $val, false ).' />'."\n\n";
			}

			$placeholder = '';

			if( $label_location == 'placeholders' && $input['type'] != 'checkbox' ){

				$placeholder = stripslashes( strip_tags( $input['label'] ) );

				if ($input['required'] && $input['type'] != 'hidden' && $input['type'] != 'current_date' && $input['type'] != 'select' && $input['type'] != 'multi-select')
					$placeholder .= ' *';

				//$placeholder = ' placeholder="'.$placeholder.'" ';

			}else{

				$required = '';

				if( $input['required'] )
					$required = 'required';

				if (!empty($input['label'])) {

					$opts = null;
					if( isset( $input['opts'] ) )
						$opts = $input['opts'];

					$content .= "\t".'<label class="w2llabel '.$required.' '.$error.$input['type'].($input['type'] == 'checkbox' ? ' w2llabel-checkbox-label' : '').'" for="sf_'.$id.'">'.( $opts == 'html' && $input['type'] == 'checkbox' ? stripslashes($input['label']) : esc_html(stripslashes($input['label'])));
					if ( ! in_array($input['type'], array('checkbox', 'html') ) && ! salesforce_get_option('donotautoaddcolontolabels', $form_id, $options ) ) {
						$content .= ':';
					}
				}
			}
		}

		if( $label_location != 'placeholders' ){

			if ($input['required'] && $input['type'] != 'hidden' && $input['type'] != 'current_date')
				$content .= ' <sup><span class="required">*</span></sup>';

			if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
				$content .= '</label>'."\n";
				if ($options['wpcf7css']) { $content .= '<span class="wpcf7-form-control-wrap">'; }
			}

		}

		if ($input['type'] == 'text') {
			$content .= "\t".'<input type="text" placeholder="'.$placeholder.'" value="'.$val.'" id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-text' : 'w2linput text';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$field_name_attr.'" '.( !empty($input['opts']) ? ' placeholder="'.$input['opts'].'" title="'.$input['opts'].'"' : '' ).' />'."\n\n";

		}else if ($input['type'] == 'email') {
			$content .= "\t".'<input type="email" placeholder="'.$placeholder.'" value="'.$val.'" id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-text' : 'w2linput text';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$field_name_attr.'" '.( !empty($input['opts']) ? ' placeholder="'.$input['opts'].'" title="'.$input['opts'].'"' : '' ).' />'."\n\n";

		}else if ($input['type'] == 'date') {
			$content .= "\t".'<input type="text" placeholder="'.$placeholder.'" value="'.$val.'" id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-text' : 'w2linput text';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$field_name_attr.'" />'."\n\n";

		} else if ($input['type'] == 'textarea') {
			$content .= "\t".( !$options['wpcf7css'] ? "\n\n" : '' )."\n\t".'<textarea id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-textarea' : 'w2linput textarea';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$field_name_attr.'"'.( !empty($input['opts']) ? ' placeholder="'.$input['opts'].'" title="'.$input['opts'].'"' : '' ).' placeholder="'.$placeholder.'">'.$val.'</textarea>'."\n\n";

		} else if ($input['type'] == 'hidden') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$field_name_attr.'" value="'.$val.'" />'."\n\n";

		} else if ($input['type'] == 'current_date') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$field_name_attr.'" value="'.date($input['opts']).'" />'."\n\n";

		} else if ($input['type'] == 'html'){
			$content .= '<br>'.stripslashes($input['opts'])."\n\n";

		} else if ($input['type'] == 'select' || $input['type'] == 'multi-select' ) {
			$content .= "\t\n\t".'<select id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-select style-select' : 'w2linput select';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			if( $input['type'] == 'multi-select' ){
				$content .= '" name="'.$field_name_attr.'[]"';
				$content .= ' multiple="multiple" ';
			}else{
				$content .= '" name="'.$field_name_attr.'"';
			}
			$content .= '>';

			if( $placeholder  ){
				if( $input['required'] ){
					$content .= '<option value="" default disabled selected="selected">'. trim( $placeholder ) . ': *</option>' . "\n";
				}else{
					$content .= '<option value="" default selected="selected">'. trim( $placeholder ) . ':</option>' . "\n";
				}
			}

			if( is_array( $val ) ){
				$values = $val;
			}else{
				$values = array( $val );
			}

			// remove excess whitespace to avoid false positive checks for newlines
			$input['opts'] = trim( $input['opts'] );

			if ( strpos($input['opts'], "\n") !== false && substr_count($input['opts'], "|\n") <= 1 && substr_count($input['opts'], "|\r\n") <= 1) {
				// Newlines and pipes
				$delim1 = "\n";
				$delim2 = "|";
			}else{
				// pipes and colons
				$delim1 = "|";
				$delim2 = ":";
			}

			if (strpos( $input['opts'], $delim1) !== false ) {
				$opts = explode( $delim1, trim( $input['opts'] ) );
				foreach ( $opts AS $opt ) {
					if (strpos( $opt, $delim2 ) !== false) {
						list ($k, $v) = explode($delim2, $opt);
					} else {
						$k = $v = $opt;
					}
					$v = trim(esc_attr(strip_tags(stripslashes($v))));

					if( $placeholder ){
						$content .= '<option value="' . esc_attr($v) . '">' . trim( stripslashes( $k ) ) . '</option>' . "\n";
					}else{
						$content .= '<option value="' . esc_attr($v) . '" '. selected( in_array($v, $values), true, false ).'>' . trim( stripslashes( $k ) ) . '</option>' . "\n";
					}

				}
			}


			$content .= '</select>'."\n\n";
			//$content .= '<pre>'.print_r( $values, 1 ).'</pre>';

		}

		if( $errors && !$errors[$id]['valid'] ){
			$content .=  "\t\n\t<span class=\"error_message\">".  $errors[$id]['message'].'</span>';
		}

		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			if ($options['wpcf7css']) { $content .= '</span></p>'; }
			$content .= '<div class="clearfix"></div></div>';
		}
	}

	//captcha

	if( salesforce_get_option('captchaform',$form_id,$options) == 'enabled' || ( salesforce_get_option('captchaform',$form_id,$options) == '' && $options['captcha']) ){

		// attempt to disable caching
		if ( !defined( 'DONOTCACHEPAGE' ) )
			define( 'DONOTCACHEPAGE', true );
		if ( !defined( 'DONOTCACHEOBJECT' ) )
			define( 'DONOTCACHEOBJECT', true );

		include("lib/captcha/captcha.php");
		$captcha = captcha();

		//$content .=  'CODE='.$captcha['code'].'<hr>';

		$sf_hash = sha1($captcha['code'].NONCE_SALT);

		set_transient( $sf_hash, $captcha['code'], 60*15 );

		$label = __('Type the text shown: *','salesforce');

		$content .= '<div class="sf_field sf_field_captcha sf_type_captcha">';

			$content .=  '<label class="w2llabel">'.$label.'</label>'."\n\n".'
				<img class="w2limg" src="' . $captcha['image_src'] . '&hash=' . $sf_hash . '" alt="CAPTCHA image" />'."\n\n";
				$content .=  '<input type="text" class="w2linput text captcha" name="captcha_text" value="" />';


		if( $errors && !$errors['captcha']['valid'] ){
			$content .=  "<span class=\"error_message\">".$errors['captcha']['message'].'</span>';
		}

		$content .=  '<input type="hidden" class="w2linput hidden" name="captcha_hash" value="'. $sf_hash .'" />';

		$content .= '</div>';

	}

	//send me a copy
	if( $options['showccuser'] ){
		$label = $options['ccusermsg'];
		if( empty($label) ) $label = __('Send me a copy','salesforce');
		$content .= "\t\n\t".'<div class="sf_field sf_field_cb sf_type_checkbox sf_cc_user"><label class="w2llabel checkbox w2llabel-checkbox-label"><input type="checkbox" name="w2lcc" class="w2linput checkbox" value="1" '.checked(1, salesforce_get_post_data('w2lcc') , false).' /> '.esc_html($label)."</label></div>\n";
	}

	//spam honeypot
	$content .= "\t".'<input type="text" name="message" class="w2linput" value="" style="display: none;" />'."\n";

	//form id
	$content .= "\t".'<input type="hidden" name="form_id" class="w2linput" value="'.$form_id.'" />'."\n";

	//daddy analytics
	if( isset( $options['da_token'] ) && $options['da_token'] && isset( $options['da_url'] ) && $options['da_url'] ){

		$da_token = $options['da_token'];
		$da_url = $options['da_url'];

		$content .= "\t".'<input type="hidden" id="Daddy_Analytics_Token" name="'.esc_attr($da_token).'" class="w2linput" value="" style="display: none;" />'."\n";
		$content .= "\t".'<input type="hidden" id="Daddy_Analytics_WebForm_URL" name="'.esc_attr($da_url).'" class="w2linput" value="" style="display: none;" />'."\n";
	}

	$submit = stripslashes( salesforce_get_option( 'submitbutton', $form_id, $options ) );

	if (empty($submit))
		$submit = "Submit";

	$content .= "\t";
	if ($options['wpcf7css']) {
		$content .= '<p class="punt">';
	} else {
		$content .= '<div class="w2lsubmit">';
	}
	$content .= '<input type="submit" name="w2lsubmit" class="';
	if ($options['wpcf7css']) {
		$content .= 'wpcf7-form-control wpcf7-submit btn';
	} else {
		$content .= 'w2linput submit';
	}
	$content .= '" value="'.esc_attr($submit).'" />'."\n";
	if ($options['wpcf7css']) {
		$content .= '</p>';
	} else {
		$content .= '</div>';
	}
	$content .= '</form>'."\n";

	if (!empty($reqtext) && salesforce_get_option('requiredfieldstextpos',$form_id,$options) == '' )
		$content .= '<p class="sf_required_fields_msg" id="requiredfieldsmsg"><sup><span class="required">*</span></sup> '.esc_html( $reqtext ).'</p>';
/*
	if (!$options['hide_salesforce_link']) {
		$content .= '<div id="salesforce"><small>'.__('Powered by','salesforce').' <a href="http://www.salesforce.com/">Salesforce CRM</a></small></div>';
	}
*/

	if ( $options['wpcf7css'] ) {
		$content .= '</section>';
	}

	if(  $label_location == 'placeholder' )
		$content .= '<script>jQuery( document ).ready( function($) { $(".salesforce_w2l_lead input, .salesforce_w2l_lead textarea").placeholder(); } );
		</script>';

	if( true )
		$content = str_replace("\n",'', $content);

	if( $date_fields ){
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

		$content .= "<script>jQuery(document).ready(function( $ ) {";

		foreach( $date_fields as $id => $date_field ){

			$options = trim( stripslashes( $date_field['opts'] ) );

			if( !$options ){
				$options = "dateFormat : 'yy-mm-dd',";
			}

			$content .= "
			    jQuery('#sf_".$id."').datepicker({
			        ".$options."
			    });
				";

		}

		$content .= "});</script>";

	}

	$content = apply_filters('salesforce_w2l_form_html', $content);

	return $content;
}

function submit_salesforce_form( $post, $options ) {

	global $wp_version;

	$form_id = absint( $_POST['form_id'] );

	$org_id = salesforce_get_option('org_id', $form_id, $options);
	//echo '$org_id='.$org_id;

	if ( !$org_id )
		$org_id = $options['org_id']; // fallback to global

	if ( !$org_id ) {
		error_log( "Salesforce: No SalesForce Organization ID set." );
		return false;
	}

	//spam honeypot
	if( !empty($_POST['message']) ) {
		error_log( "Salesforce: No message set." );
		return false;
	}

	//print_r($_POST); //DEBUG

	//echo $org_id;

	$post['oid'] 	= $org_id; // web to lead
	$post['orgid'] 	= $org_id; // web to case

	if( !isset( $post['lead_source'] ) ){
		if (!empty($options['forms'][$form_id]['source'])) {
			$post['lead_source'] = str_replace('%URL%','['.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].']',$options['forms'][$form_id]['source']);
		}
	}

	$post['lead_source'] = apply_filters('salesforce_w2l_lead_source', $post['lead_source'], $form_id);

	//$post['debug']	= 0;

/*
	$body = '';

	foreach( $post as $k => $v ){
		if( is_array( $v ) ){
			foreach( $v as $i ){
				$body .= '&'.urlencode($k).'='.urlencode($i);
			}
		}else{
			$body .= '&'.urlencode($k).'='. urlencode($v);

		}
	}

	$body = substr( $body, 1 );
*/

	$form_type = $options['forms'][$form_id]['type'];

	// Filter arguments before generating POST to SF
	$post = apply_filters( 'salesforce_w2l_post_data', $post, $form_id, $form_type );

	$body = preg_replace('/%5B[0-9]+%5D/simU', '', http_build_query($post) ); // remove php style arrays for array values [1]
	//echo $body .'<hr>';

	$sslverify = false;

	// setting to override
	if( !empty( $options['sslverify'] ) )
		$sslverify = (bool) $options['sslverify'];

	// Set SSL verify to false because of server issues, unless setting is set... a filter can also be used to override arguments
	$args = array(
		'body' 		=> $body,
		'headers' 	=> array(
			'Content-Type' => 'application/x-www-form-urlencoded',
			'user-agent' => 'WordPress-to-Lead for Salesforce plugin - WordPress/'.$wp_version.'; '.get_bloginfo('url'),
		),
		'sslverify'	=> $sslverify,
	);

	$args = apply_filters( 'salesforce_w2l_post_args', $args );

	if( $form_type == 'case' ){
		$url = 'https://login.salesforce.com/servlet/servlet.WebToCase?encoding=UTF-8';
	}else{
		$url = 'https://login.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8';
	}

	// Do we need to change the URL we're submitting to?
	$url = apply_filters( 'salesforce_w2l_api_url', $url, $form_type, $post );

	// Pre submit actions
	do_action( 'salesforce_w2l_before_submit', $post, $form_id, $form_type );

	//die( print_r( $args, 1 ) );

	$result = wp_remote_post( $url, $args );

	// Test broken submit
	//$result = new WP_Error( 'broke', __( "I've fallen and can't get up", "my_textdomain" ) );

	if( is_wp_error($result) ) {
		error_log( "Salesforce HTTP error: " . print_r( $result, true ) );

		do_action( 'salesforce_w2l_error_submit', $result, $post, $form_id, $form_type );

		$subject = __( 'Salesforce Web to %%type%% Error', 'salesforce' );
		$append = print_r( $result, 1 );
		salesforce_cc_admin( $post, $options, $form_id, $subject, $append );

		return false;
	}

	if ($result['response']['code'] == 200){

		// Post submit actions
		do_action( 'salesforce_w2l_after_submit', $post, $form_id, $form_type );

		unset( $_POST['oid'] );
		unset( $_POST['org_id'] );

		if( isset( $_POST['w2lcc'] ) && $_POST['w2lcc'] == 1 )
			salesforce_cc_user($post, $options, $form_id);

		salesforce_cc_admin($post, $options, $form_id);

		// Prevent multiple form submissions by clearing key data
		unset( $_POST['form_id'] );
		unset( $_POST['w2lsubmit'] );

		return true;
	}else{
		error_log( "Salesforce response error: " . print_r( $result, true ) );
		return false;
	}
}

function salesforce_cc_admin( $post, $options, $form_id = 1, $subject = '', $append = '' ){

	if( !$subject )
		$subject = '[' . __( 'Salesforce Web to %%type%% Submission', 'salesforce' ) . ']';

	$subject = str_replace( '%%type%%', $form_type,  $subject );

	$subject .= ' ' . $options['forms'][$form_id]['form_name'];

	$from_name = salesforce_get_option( 'emailfromname', $form_id, $options );
	if( !$from_name )
		$from_name = get_bloginfo('name');

	$from_email = salesforce_get_option( 'emailfromaddress', $form_id, $options );
	if( !$from_email )
		$from_email = get_option('admin_email');

	$from_name = apply_filters('salesforce_w2l_cc_admin_from_name', $from_name);
	$from_email = apply_filters('salesforce_w2l_cc_admin_from_email', $from_email);

	$headers = 'From: '.$from_name.' <' . $from_email . ">\r\n";
	if (get_option('email_sender') != '') {
		$headers .= 'Sender: '.get_option('email_sender')."\r\n";
	}
	$headers .= 'Reply-to: '.$from_name.' <' . $from_email . ">\r\n";

	if( $options['forms'][$form_id]['type'] == 'case' ){
		$form_type = __( 'Case', 'salesforce' );
	}else{
		$form_type = __( 'Lead', 'salesforce' );
	}

	$message = '';

	//unset($post['debug']);
	//unset($post['debugEmail']);

	//format message
	foreach($post as $name=>$value){

		if( isset( $options['forms'][$form_id]['inputs'][$name]['label'] ) ){
			$label = trim( $options['forms'][$form_id]['inputs'][$name]['label'] );
		}else{
			$label = '';
		}

		if( !empty($value) && ! empty( $label ) ){

			if( $label != '' && $name != 'lead_source' )
				$message .= stripslashes($label).': '. salesforce_maybe_implode( ';', $value ) . "\r\n";
		}
	}

	if ( $post['lead_source'] ) {
		$message .= "\r\n".'Lead Source: '.salesforce_maybe_implode( ';', $post['lead_source'] )."\r\n";
	}

	// add form info
	$message .= "\r\n".'Form ID: '. $form_id . "\r\n".'Form Editor: ' . add_query_arg( array( 'page' => 'salesforce-wordpress-to-lead', 'tab' => 'form', 'id' => $form_id ), admin_url( 'options-general.php' ) ) ."\r\n";

	if( $append ){
		$message .= "\r\n".'= Addditional Information ='."\r\n\r\n".$append."\r\n";
	}

	$emails = array();

	// cc admin?
	if( isset( $options['ccadmin'] ) && $options['ccadmin'] )
		$emails[] = get_option('admin_email');

	// cc others?
	if( isset( $options['ccothers'] ) && $options['ccothers'] ){
		$others = explode( ',', $options['ccothers'] );

		if( count( $others ) ){
			foreach( $others as $other ){
				$emails[] = trim( $other );
			}
		}

	}

	$emails = apply_filters( 'salesforce_w2l_cc_admin_email_list', $emails );

	//print_r( $emails );

	$message = apply_filters('salesforce_w2l_cc_admin_email_content', $message );
	$subject = apply_filters('salesforce_w2l_cc_admin_email_subject', $subject, $form_type, $post );

	if( WP_DEBUG )
		error_log( 'salesforce_cc_admin:'.print_r( array($emails,$message,$subject),1 ) );

	if( $message ){
		foreach( $emails as $email ){
			wp_mail( $email, $subject, $message, $headers );
		}
	}
}

function salesforce_cc_user( $post, $options, $form_id = 1 ){

	$from_name = salesforce_get_option( 'emailfromname', $form_id, $options );
	if( !$from_name )
		$from_name = get_bloginfo('name');

	$from_email = salesforce_get_option( 'emailfromaddress', $form_id, $options );
	if( !$from_email )
		$from_email = get_option('admin_email');

	$from_name = apply_filters('salesforce_w2l_cc_user_from_name', $from_name );
	$from_email = apply_filters('salesforce_w2l_cc_user_from_email', $from_email );

	$headers = 'From: '.$from_name.' <' . $from_email . ">\r\n";

	if (!empty($options['forms'][$form_id]['cc_email_subject'])) {
		$subject = str_replace('%BLOG_NAME%', get_bloginfo('name'), $options['forms'][$form_id]['cc_email_subject']);
	} else {
		$subject = str_replace('%BLOG_NAME%', get_bloginfo('name'), $options['subject']);
	}
	if( empty($subject) ) $subject = __('Thank you for contacting','salesforce').' '.get_bloginfo('name');

	//remove hidden fields
	foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
		if( $input['type'] == 'hidden' )
			unset( $post[$id] );
	}

	if (!empty($options['forms'][$form_id]['source'])) {
		unset($post['lead_source']);
	}

	$remove_keys = apply_filters( 'salesforce_w2l_cc_user_suppress_fields', array('debug','debugEmail','oid','orgid',$options['da_token'],$options['da_url']) );

	foreach( $remove_keys as $key ){
		unset($post[$key]);
	}

	$message = '';

	//format message
	foreach($post as $name => $value){

		if( isset( $options['forms'][$form_id]['inputs'][$name]['label'] ) ){
			$label = trim( $options['forms'][$form_id]['inputs'][$name]['label'] );
		}else{
			$label = '';
		}

		if( !empty( $name ) && !empty( $value ) && !empty( $label ) ){
			$message .= stripslashes($label).': '.salesforce_maybe_implode(',', $value)."\r\n";
		}

	}

	$message = apply_filters('salesforce_w2l_cc_user_email_content', $message );

	if( defined( WP_DEBUG )  && WP_DEBUG )
		error_log( 'salesforce_cc_user:'.print_r( array($message),1 ) );

	if( $message )
		wp_mail( $_POST['email'], $subject, $message, $headers );

}