<?php
/*
Plugin Name: WordPress-to-Lead for Salesforce CRM
Plugin URI: http://bit.ly/1d56aqB
Description: Easily embed a contactform into your posts, pages or your sidebar, and capture the entries straight into Salesforce CRM!
Author: Nick Ciske, Daddy Analytics, Modern Tribe Inc., Joost de Valk
Version: 2.0.4
Author URI: http://bit.ly/1d56aqB
*/

require_once('lib/ov_plugin_tools.php');

if ( ! class_exists( 'Salesforce_Admin' ) ) {
	require_once('lib/salesforce_admin.class.php');
}

$salesforce = new Salesforce_Admin();

function salesforce_default_settings() {
	$options = array();
	$options['version'] 			= '2.0';
	$options['successmsg'] 			= __('Success!','salesforce');
	$options['errormsg'] 			= __('There was an error, please fill all required fields.','salesforce');
	$options['requiredfieldstext'] 	= __('These fields are required.','salesforce');
	$options['sferrormsg'] 			= __('Failed to connect to Salesforce.com.','salesforce');
	$options['submitbutton']	 	= __('Submit','salesforce');
	$options['subject']	 			= __('Thank you for contacting %BLOG_NAME%','salesforce');
	$options['showccuser'] 			= true;
	$options['ccusermsg']			= __('Send me a copy','salesforce');
	$options['email_sender']		= '';
	$options['ccadmin']				= false;
	$options['captcha']				= false;

	$options['da_token']			= '';
	$options['da_url']				= '';
	$options['da_site']				= '';

	$options['usecss']				= true;
	$options['wpcf7css']			= false;
	//$options['hide_salesforce_link']= true;

	$options['forms'][1] = salesforce_default_form();
	
	update_option('salesforce2', $options);
	
	return $options;
}

function salesforce_default_form() {

	$dform = array();
	
	$dform['form_name'] = 'My Lead Form '.date('Y-m-d h:i:s');
	$dform['source'] = __('Lead form on ','salesforce').get_bloginfo('name');
	$dform['returl'] = '';
	
	$dform['inputs'] = array(
			'first_name' 	=> array('type' => 'text', 'label' => 'First name', 'show' => true, 'required' => true),
			'first_name' 	=> array('type' => 'text', 'label' => 'First name', 'show' => true, 'required' => true),
			'last_name' 	=> array('type' => 'text', 'label' => 'Last name', 'show' => true, 'required' => true),
			'email' 		=> array('type' => 'text', 'label' => 'Email', 'show' => true, 'required' => true),
			'phone' 		=> array('type' => 'text', 'label' => 'Phone', 'show' => true, 'required' => false),
			'description' 	=> array('type' => 'textarea', 'label' => 'Message', 'show' => true, 'required' => true),
			'title' 		=> array('type' => 'text', 'label' => 'Title', 'show' => false, 'required' => false),
			'company' 		=> array('type' => 'text', 'label' => 'Company', 'show' => false, 'required' => false),
			'street' 		=> array('type' => 'text', 'label' => 'Street', 'show' => false, 'required' => false),
			'city'	 		=> array('type' => 'text', 'label' => 'City', 'show' => false, 'required' => false),
			'state'	 		=> array('type' => 'text', 'label' => 'State', 'show' => false, 'required' => false),
			'zip'	 		=> array('type' => 'text', 'label' => 'ZIP', 'show' => false, 'required' => false),
			'country'	 	=> array('type' => 'text', 'label' => 'Country', 'show' => false, 'required' => false),
			'Campaign_ID'	=> array('type' => 'hidden', 'label' => 'Campaign ID', 'show' => false, 'required' => false),
		);
	
	return $dform;

}

function salesforce_back_link($url){

	return '<a href="'.$url.'">&laquo; '.__('Back to configuration page','salesforce').'</a>';

}

/**
 * Sort input array by $subkey
 * Taken from: http://php.net/manual/en/function.ksort.php
 */
function w2l_sksort(&$array, $subkey="id", $sort_ascending=false) {

	if( !is_array( $array ) )
		return $array;
		
	$temp_array = array();

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}

add_action('wp_footer','salesforce_da_js');

// Add Daddy Analytics JS tracking to all pages
function salesforce_da_js(  ){
	
	$options = get_option("salesforce2");
	
	if( isset( $options['da_token'] ) && isset( $options['da_url'] ) && isset( $options['da_site'] ) ){
	
		$da_token = $options['da_token'];
		$da_url = $options['da_url'];
		$da_site = $options['da_site'];
		
		echo '<script src="//cdn.daddyanalytics.com/w2/daddy.js" type="text/javascript"></script>
		<script type="text/javascript">
		var da_data =daddy_init(\'{ "da_token" : '.$da_token.', "da_url" : '.$da_url.' }\');
		var clicky_custom = {session: {DaddyAnalytics: da_data}};
		</script>
		<script src="//hello.staticstuff.net/w/__stats.js" type="text/javascript"></script>
		<script type="text/javascript">try{ clicky.init( '.$da_site.' ); }catch(e){}</script>';
	
	}
	
}

function salesforce_captcha(){
	include("lib/captcha/captcha.php");
	die();
}

function salesforce_form($options, $is_sidebar = false, $content = '', $form_id = 1) {
	
	if( !isset($options['forms'][$form_id]) )
		return;
	
	if (!empty($content))
		$content = wpautop('<strong>'.$content.'</strong>');
		
	if ($options['usecss'] && !$is_sidebar) {
		$content .= '<style type="text/css">
		form.w2llead{ text-align:left; clear:both;}
		.w2llabel, .w2linput { display:block; float:left; }
		.w2llabel.error { color:#f00; }
		.w2llabel { clear:left; margin:4px 0; width:50%; }
		.w2linput.text{ width:50%; height:18px; margin:4px 0; }
		.w2linput.textarea { clear:both; width:100%; height:75px; margin:10px 0;}
		.w2lsubmit{ float:none; clear:both; }
		.w2linput.submit { float:none; margin: 10px 0 0 0; clear:both;}
		.w2linput.checkbox{ vertical-align: middle;}
		.w2llabel.checkbox{ clear:both; }
		.w2limg{ display: block; clear: both; }
		#salesforce{ margin:3px 0 0 0; color:#aaa; }
		#salesforce a{ color:#999; }
		SPAN.required { font-weight: bold; }
		</style>';
	} elseif ($is_sidebar && $options['usecss']) {
		$content .= '<style type="text/css">
		.sidebar form.w2llead{ clear:none; text-align:left; }
		.sidebar .w2linput, #sidebar .w2llabel{ float:none; display:inline; }
		.sidebar .w2llabel.error { color:#f00; }
		.sidebar .w2llabel { margin:4px 0; float:none; display:inline; }
		.sidebar .w2linput.text{ width:95%; height:18px; margin:4px 0;}
		.sidebar .w2linput.textarea {width:95%; height:50px; margin:10px 0;}
		.sidebar .w2lsubmit{ float:none; clear:both; }
		.sidebar .w2linput.submit { margin:10px 0 0 0; }
		#salesforce{ margin:3px 0 0 0; color:#aaa; }
		#salesforce a{ color:#999; }
		SPAN.required { font-weight: bold; }
		</style>';
	}
	$sidebar = '';
	
	if ( $is_sidebar )
		$sidebar = ' sidebar';
	
	if ( $options['wpcf7css'] ) {
		$content .= '<section class="form-holder clearfix"><div class="wpcf7">';
	}	
	$content .= "\n".'<form id="salesforce_w2l_lead_'.$form_id.str_replace(' ','_',$sidebar).'" class="'.($options['wpcf7css'] ? 'wpcf7-form' : 'w2llead'.$sidebar ).'" method="post">'."\n";

	foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
		if (!$input['show'])
			continue;
		$val 	= '';
		if (isset($_POST[$id])){
			$val	= esc_attr(strip_tags(stripslashes($_POST[$id])));
		}else{
			if( isset($input['value']) ) $val	= esc_attr(strip_tags(stripslashes($input['value'])));
		}

		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			$content .= '<div class="sf_field sf_field_'.$id.' sf_type_'.$input['type'].'">';
		}

		$error 	= ' ';
		if (isset($input['error']) && $input['error']) {
			$error 	= ' error ';
		}
			
		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			if ($options['wpcf7css']) { $content .= '<p>'; }
			if ($input['type'] == 'checkbox') {
				$content .= "\t\n\t".'<input type="checkbox" id="sf_'.$id.'" class="w2linput checkbox" name="'.$id.'" value="'.$val.'" />'."\n\n";
			}
			if (!empty($input['label'])) {
				$content .= "\t".'<label class="w2llabel'.$error.$input['type'].($input['type'] == 'checkbox' ? ' w2llabel-checkbox-label' : '').'" for="sf_'.$id.'">'.( $input['opts'] == 'html' && $input['type'] == 'checkbox' ? stripslashes($input['label']) : esc_html(stripslashes($input['label'])));
				if (!in_array($input['type'], array('checkbox', 'html'))) {
					$content .= ':';
				}
			}
		}
		
		if ($input['required'] && $input['type'] != 'hidden' && $input['type'] != 'current_date')
			$content .= ' <sup><span class="required">*</span></sup>';
		
		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			$content .= '</label>'."\n";
			if ($options['wpcf7css']) { $content .= '<span class="wpcf7-form-control-wrap">'; }
		}
		
		if ($input['type'] == 'text') {			
			$content .= "\t".'<input value="'.$val.'" id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-text' : 'w2linput text';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$id.'" type="text"'.( !empty($input['opts']) ? ' placeholder="'.$input['opts'].'" title="'.$input['opts'].'"' : '' ).'/>'."\n\n";
		} else if ($input['type'] == 'textarea') {
			$content .= "\t".( !$options['wpcf7css'] ? '<br/>' : '' )."\n\t".'<textarea id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-textarea' : 'w2linput textarea';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$id.'"'.( !empty($input['opts']) ? ' placeholder="'.$input['opts'].'" title="'.$input['opts'].'"' : '' ).'>'.$val.'</textarea>'."\n\n";
		} else if ($input['type'] == 'hidden') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$id.'" value="'.$val.'">'."\n\n";
		} else if ($input['type'] == 'current_date') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$id.'" value="'.date($input['opts']).'">'."\n\n";
		} else if ($input['type'] == 'html'){
			$content .= stripslashes($input['opts'])."\n\n";
		} else if ($input['type'] == 'select') {
			$content .= "\t\n\t".'<select id="sf_'.$id.'" class="';
			$content .= $options['wpcf7css'] ? 'wpcf7-form-control wpcf7-select style-select' : 'w2linput select';
			$content .= $options['wpcf7css'] && $input['required'] ? ' wpcf7-validates-as-required required' : '';
			$content .= '" name="'.$id.'">';
			if (strpos($input['opts'], '|') !== false) {
				$opts = explode('|', $input['opts']);
				foreach ($opts AS $opt) {
					if (strpos($opt,':') !== false) {
						list ($k, $v) = explode(':', $opt);
					} else {
						$k = $v = $opt;
					}
					$content .= '<option value="' . $v . '">' . $k . '</option>' . "\n";
				}
			}
			$content .= '</select>'."\n\n";
		}
		if($input['type'] != 'hidden' && $input['type'] != 'current_date') {
			if ($options['wpcf7css']) { $content .= '</span></p>'; }
			$content .= '</div>';
		}
	}

	//captcha
	
	if($options['captcha']){
	
		include("lib/captcha/captcha.php");
		$captcha = captcha();
		
		//$content .=  'CODE='.$captcha['code'].'<hr>';
	
		$sf_hash = sha1($captcha['code'].NONCE_SALT);
	
		set_transient( $sf_hash, $captcha['code'], 60*15 );
	
		$content .=  '<label class="w2llabel">'.__('Type the text shown: *','salesforce').'</label><br>
			<img class="w2limg" src="' . $captcha['image_src'] . '&hash=' . $sf_hash . '" alt="CAPTCHA image" /><br>';

		$content .=  '<input type="text" class="w2linput text" name="captcha_text" value=""><br>';
		$content .=  '<input type="hidden" class="w2linput hidden" name="captcha_hash" value="'. $sf_hash .'">';
	
	}
	
	//send me a copy
	if( $options['showccuser'] ){
		$label = $options['ccusermsg'];
		if( empty($label) ) $label = __('Send me a copy','salesforce');
		$content .= "\t\n\t".'<p><label class="w2llabel checkbox w2llabel-checkbox-label"><input type="checkbox" name="w2lcc" class="w2linput checkbox" value="1"/> '.esc_html($label)."</label><p>\n";
	}
	
	//spam honeypot
	$content .= "\t".'<input type="text" name="message" class="w2linput" value="" style="display: none;"/>'."\n";

	//form id
	$content .= "\t".'<input type="hidden" name="form_id" class="w2linput" value="'.$form_id.'" />'."\n";

	$submit = stripslashes($options['submitbutton']);
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
	$content .= '" value="'.esc_attr($submit).'"/>'."\n";
	if ($options['wpcf7css']) {
		$content .= '</p>';
	} else {
		$content .= '</div>';
	}
	$content .= '</form>'."\n";

	$reqtext = stripslashes($options['requiredfieldstext']);
	if (!empty($reqtext))
		$content .= '<p class="sf_required_fields_msg" id="requiredfieldsmsg"><sup><span class="required">*</span></sup> '.esc_html( $reqtext ).'</p>';
/*
	if (!$options['hide_salesforce_link']) {
		$content .= '<div id="salesforce"><small>'.__('Powered by','salesforce').' <a href="http://www.salesforce.com/">Salesforce CRM</a></small></div>';
	}
*/
	
	if ( $options['wpcf7css'] ) {
		$content .= '</section>';
	}	

	$content = apply_filters('salesforce_w2l_form_html', $content);
	
	return $content;
}

function submit_salesforce_form($post, $options) {
	
	global $wp_version;
	if (!isset($options['org_id']) || empty($options['org_id'])) {
		error_log( "Salesforce: No organisation ID set." );
		return false;
	}

	//spam honeypot
	if( !empty($_POST['message']) ) {
		error_log( "Salesforce: No message set." );
		return false;
	}

	//print_r($_POST); //DEBUG
	
	$form_id = intval( $_POST['form_id'] );

	$post['oid'] 			= $options['org_id'];
	if (!empty($options['forms'][$form_id]['source'])) {
		$post['lead_source']	= str_replace('%URL%','['.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].']',$options['forms'][$form_id]['source']);
	}
	$post['debug']			= 0;

	// Set SSL verify to false because of server issues.
	$args = array( 	
		'body' 		=> $post,
		'headers' 	=> array(
			'user-agent' => 'WordPress-to-Lead for Salesforce plugin - WordPress/'.$wp_version.'; '.get_bloginfo('url'),
		),
		'sslverify'	=> false,  
	);
	
	$result = wp_remote_post('https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8', $args);

	if( is_wp_error($result) ) {
		error_log( "Salesforce HTTP error: " . print_r( $result, true ) );
		return false;
	}
	
	if ($result['response']['code'] == 200){

		if( isset( $_POST['w2lcc'] ) && $_POST['w2lcc'] == 1 )
			salesforce_cc_user($post, $options, $form_id);

		if( isset( $options['ccadmin'] ) && $options['ccadmin'] )
			salesforce_cc_admin($post, $options, $form_id);
		
		return true;
	}else{
		error_log( "Salesforce response error: " . print_r( $result, true ) );
		return false;
	}
}

function salesforce_cc_user($post, $options, $form_id = 1){
	
	$from_name = apply_filters('salesforce_w2l_cc_user_from_name', get_bloginfo('name'));
	$from_email = apply_filters('salesforce_w2l_cc_user_from_email', get_option('admin_email'));
	
	$headers = 'From: '.$from_name.' <' . $from_email . ">\r\n";

	$subject = str_replace('%BLOG_NAME%', get_bloginfo('name'), $options['subject']);
	if( empty($subject) ) $subject = __('Thank you for contacting','salesforce').' '.get_bloginfo('name');

	//remove hidden fields
	foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
		if( $input['type'] == 'hidden' )
			unset( $post[$id] );
	}
	
	unset($post['oid']);
	if (!empty($options['forms'][$form_id]['source'])) {
		unset($post['lead_source']);
	}
	unset($post['debug']);
	
	$message = '';

	//$message .= print_r( $post , true);
	//$message .= print_r( $options['forms'][$form_id]['inputs'] , true);

	
	//format message
	foreach($post as $name=>$value){
		if( !empty($value) )
			$message .= $options['forms'][$form_id]['inputs'][$name]['label'].': '.$value."\r\n";
	}

	wp_mail( $_POST['email'], $subject, $message, $headers );

}

function salesforce_cc_admin($post, $options, $form_id = 1){

	$from_name = apply_filters('salesforce_w2l_cc_admin_from_name', get_bloginfo('name'));
	$from_email = apply_filters('salesforce_w2l_cc_admin_from_email', get_option('admin_email'));
	
	$headers = 'From: '.$from_name.' <' . $from_email . ">\r\n";
	if (get_option('email_sender') != '') {
		$headers .= 'Sender: '.get_option('email_sender')."\r\n";
	}
	$headers .= 'Reply-to: '.$from_name.' <' . $from_email . ">\r\n";

	$subject = __('Salesforce WP to Lead Submission','salesforce');

	$message = '';

	unset($post['oid']);
	if (!empty($options['forms'][$form_id]['source'])) {
		unset($post['lead_source']);
	}
	unset($post['debug']);
	
	//format message
	foreach($post as $name=>$value){
		if( !empty($value) )
			$message .= $options['forms'][$form_id]['inputs'][$name]['label'].': '.$value."\r\n";
	}

	$emails = array( get_option('admin_email') );

	$emails = apply_filters( 'salesforce_w2l_cc_admin_email_list', $emails );
	
	//print_r( $emails );
	
	foreach( $emails as $email ){
		wp_mail( $email, $subject, $message, $headers );
	}

}

function salesforce_form_shortcode($atts) {

	extract( shortcode_atts( array(
		'form' => '1',
		'sidebar' => false,
	), $atts ) );
	
	$emailerror = '';
	$captchaerror = '';
	$content = '';
	
	$form = (int) $form;
	$sidebar = (bool) $sidebar;
	
	$options = get_option("salesforce2");
	if (!is_array($options))
		$options = salesforce_default_settings();

	//don't submit unless we're in the right shortcode
	if( isset( $_POST['form_id'] ) ){
		$form_id = intval( $_POST['form_id'] );
		
		if( $form_id != $form ){
			$content = salesforce_form($options, $sidebar, null, $form);
			return $content;
			
		}
	}

	//this is the right form, continue
	if (isset($_POST['w2lsubmit'])) {
		$error = false;
		$post = array();
		
		foreach ($options['forms'][$form]['inputs'] as $id => $input) {
			if ($input['required'] && empty($_POST[$id])) {
				$options['forms'][$form]['inputs'][$id]['error'] = true;
				$error = true;
			} else if ($id == 'email' && $input['required'] && !is_email($_POST[$id]) ) {
				$error = true;
				$emailerror = true;
			} else {
				if( isset($_POST[$id]) ) $post[$id] = trim(strip_tags(stripslashes($_POST[$id])));
			}
		}
		
		//check captcha if enabled
		if( $options['captcha'] ){
			
			if( $_POST['captcha_hash'] != sha1( $_POST['captcha_text'].NONCE_SALT )){
				$error = true;
				$captchaerror = true;
			}
			
		}
		
		if (!$error) {
			$result = submit_salesforce_form($post, $options, $form);
			
			//echo 'RESULT='.$result;
			//if($result) echo 'true';
			//if(!$result) echo 'false';
						
			if (!$result){
				
				$content = '<strong>'.esc_html(stripslashes($options['sferrormsg'])).'</strong>';			
			}else{
			
				if( !empty($options['forms'][$form]['returl']) ){
					//wp_redirect( $options['forms'][$form]['returl'] );
					//exit;
					
					?>
					<script type="text/javascript">
				   <!--
				      window.location= <?php echo "'" . $options['forms'][$form]['returl'] . "'"; ?>;
				   //-->
				   </script>
					<?php
				}
			
				$content = '<strong>'.esc_html(stripslashes($options['successmsg'])).'</strong>';
			}
		} else {
			$errormsg = esc_html( stripslashes($options['errormsg']) ) ;
			if ($emailerror)
				$errormsg .= '<br/>'.__('The email address you entered is not a valid email address.','salesforce');
			
			if ($captchaerror)
				$errormsg .= '<br/>'.__('The text you entered did not match the image.','salesforce');
			
			$content .= salesforce_form($options, $sidebar, $errormsg, $form);
		}
	} else {
		$content = salesforce_form($options, $sidebar, null, $form);
	}
	
	return $content;
}

add_shortcode('salesforce', 'salesforce_form_shortcode');	

class Salesforce_WordPress_to_Lead_Widgets extends WP_Widget {

	function Salesforce_WordPress_to_Lead_Widgets() {
		$widget_ops = array( 'classname' => 'salesforce', 'description' => __('Displays a WordPress-to-Lead for Salesforce Form','salesforce') );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'salesforce' );
		$this->WP_Widget( 'salesforce', 'Salesforce', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		if ( !empty($instance['desc']) && empty($_POST) ) {
			echo '<p>' . $instance['desc'] . '</p>';
		}
		$is_sidebar = true;
		echo do_shortcode('[salesforce form="'.$instance['form'].'" sidebar="true"]');
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'desc', 'form') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title' => 'Contact Us', 
			'desc' 	=> 'Contact us using the form below', 
			'form' 	=> 1, 
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e("Introduction"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" value="<?php echo $instance['desc']; ?>" style="width:90%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e("Form"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'form' ); ?>" name="<?php echo $this->get_field_name( 'form' ); ?>">
				<?php
				$sfoptions = get_option('salesforce2');
				
				foreach($sfoptions['forms'] as $key=>$value){
					
					echo '<option value="'.$key.'"';
					if( $instance['form'] == $key)
						echo ' selected="selected"';
					echo '>'.$value['form_name'].'</option>';
						 				
				
				}
				?>
			</select>
		</p>


	<?php 
	}
}

function salesforce_widget_func() {
	register_widget( 'Salesforce_WordPress_to_Lead_Widgets' );
}
add_action( 'widgets_init', 'salesforce_widget_func' );

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
		w2l_sksort($newinputs,'pos',true);
		
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

register_activation_hook( __FILE__, 'salesforce_activate' );
