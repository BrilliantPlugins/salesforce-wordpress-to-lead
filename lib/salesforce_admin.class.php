<?php
class Salesforce_Admin extends OV_Plugin_Admin {

	var $hook 		= 'salesforce-wordpress-to-lead';
	var $filename	= 'salesforce/salesforce.php';
	var $longname	= 'WordPress-to-Lead for Salesforce CRM Configuration';
	var $shortname	= 'Salesforce';
	var $optionname = 'salesforce2';
	var $homepage	= 'http://bit.ly/1d56aqB';
	var $ozhicon	= 'salesforce-16x16.png';
	
	function Salesforce_Admin() {
		add_action( 'admin_menu', array(&$this, 'register_settings_page') );
		add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
		add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
		
		add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
		add_action('admin_print_styles', array(&$this,'config_page_styles'));	
		add_action('admin_footer', array(&$this,'warning'));
		
		add_action('wp_ajax_sfw2l_get_captcha', 'salesforce_captcha');
		add_action('wp_ajax_nopriv_sfw2l_get_captcha', 'salesforce_captcha');

	}

	function get_ad_link( $content, $term, $medium, $source = 'ThoughtRefinery', $campaign = 'WP-SF-Plugin', $url = 'http://daddyanalytics.com/' ){
	
	
	
		$link = $url . '?utm_source=%s&utm_medium=%s&utm_campaign=%s&utm_term=%s&utm_content=%s';
	
		return sprintf( $link, $source, $medium, $campaign, $term, $content  );
		
	}
	
	function get_ad_code( $type, $id = null ){
		
		$ads = array(
			'banner-side' => array(
				array( 'id' => 'da01', 'url' => '', 'content' => 'assets/ads/side_Analytics-track-form-submission-keyword.png' ),
				array( 'id' => 'da02', 'url' => '', 'content' => 'assets/ads/side_analytics-marketing-roi-offer.png' ),
				array( 'id' => 'da03', 'url' => '', 'content' => 'assets/ads/side_analytics-track-lead-location.png' ),
			),

			'banner-main' => array(
				array( 'id' => 'da04', 'url' => '', 'content' => 'assets/ads/main_analytics-lead-management.png' ),
				array( 'id' => 'da05', 'url' => '', 'content' => 'assets/ads/main_analytics-track-affiliate-ppc.png' ),
				array( 'id' => 'da06', 'url' => '', 'content' => 'assets/ads/main_analytics-track-lead-source-offer.png' ),
			),

			'text' => array(
				array( 'id' => 'da07', 'url' => '', 'content' => 'Daddy Analytics allows you to... TODO1'),
				array( 'id' => 'da08', 'url' => '', 'content' => 'Daddy Analytics allows you to... TODO2'),
				array( 'id' => 'da09', 'url' => '', 'content' => 'Daddy Analytics allows you to... TODO3'),
			),

		);
		
		if( $id ){
		
			foreach( $ads[ $type ] as $ad ){
				if( $ad['id'] == $id )
					return $ad;
			}
			
		}
		
		if( !$num )
			$num = mt_rand( 1, count( $ads[ $type ] ) ) - 1;
		
		//echo $num;
		
		return $ads[ $type ][ $num ];
	}
	
	function warning() {
		$options  = get_option($this->optionname);
		if (!isset($options['org_id']) || empty($options['org_id']))
			echo "<div id='message' class='error'><p><strong>".__('Your WordPress-to-Lead settings are not complete.','salesforce')."</strong> ".__('You must enter your Salesforce.com Organisation ID for it to work.','salesforce')." <a href='".$this->plugin_options_url()."'>".__('Settings','salesforce')."</a></p></div>";
			
			//echo 'ERROR= '.get_option('plugin_error');
			
	}
	
	function config_page() {
		
		$options = get_option($this->optionname);
		
		if ( isset($_POST['submit']) ) {
			
			//die('<pre>'.print_r($_POST,true)); //DEBUG

			if( isset( $_POST['mode'] ) && $_POST['mode'] == 'editform' ){

				$form_id = (int) $_POST['form_id'];
				
				if(!isset($options['forms'][$form_id]))
					$options['forms'][$form_id] = salesforce_default_form();

				//Begin Save Form Data
				$newinputs = array();
				foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
					if (!empty($_POST['inputs'][$id.'_delete'])) {
						continue;
					}

					foreach (array('show','required') as $option_name) {
						if (isset($_POST['inputs'][$id.'_'.$option_name])) {
							$newinputs[$id][$option_name] = true;
							unset($_POST['inputs'][$id.'_'.$option_name]);
						} else {
							$newinputs[$id][$option_name] = false;
						}
					}
					foreach (array('type','label','value','pos','opts') as $option_name) {
						if (isset($_POST['inputs'][$id.'_'.$option_name])) {
							$newinputs[$id][$option_name] = $_POST['inputs'][$id.'_'.$option_name];
							unset($_POST['inputs'][$id.'_'.$option_name]);
						}
					}	
				}
				
				//add any new fields
				
				if( isset($_POST['add_inputs']) ){
				
					foreach ($_POST['add_inputs'] as $key=>$input) {
					
						$id = $input['field_name'];
					
						if( !empty($id) ){
							foreach (array('show','required') as $option_name) {
								if (isset($_POST['add_inputs'][$key][$option_name])) {
									$newinputs[$id][$option_name] = true;
									unset($_POST['add_inputs'][$key][$option_name]);
								} else {
									$newinputs[$id][$option_name] = false;
								}
							}
							
							foreach (array('type','label','value','pos','opts') as $option_name) {
								if (isset($_POST['add_inputs'][$key][$option_name])) {
									$newinputs[$id][$option_name] = $_POST['add_inputs'][$key][$option_name];
									unset($_POST['add_inputs'][$key][$option_name]);
								}
							}
						}
					}
				
				}
				
				w2l_sksort($newinputs,'pos',true);
				$options['forms'][$form_id]['inputs'] = $newinputs; //TODO
				
				foreach (array('form_name','source','returl') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options['forms'][$form_id][$option_name] = $_POST[$option_name];
					}
				}
				
				//End Save Form Data
			
			}elseif( isset( $_POST['mode'] ) && $_POST['mode'] == 'delete'){
			
				if( isset( $_POST['form_id'] ) && $_POST['form_id'] != 1 )
					unset( $options['forms'][$_POST['form_id']] );
			
			}elseif( isset( $_POST['mode'] ) && $_POST['mode'] == 'clone'){
			
				if( isset( $_POST['form_id'] ) && $_POST['form_id'] != 1 ) {
					$new_id = max(array_keys($options['forms'])) + 1;
					$options['forms'][$new_id] = $options['forms'][$_POST['form_id']];
				}
			
			}else{
			
				//Save general settings
								
				$options  = get_option($this->optionname);
				if (!current_user_can('manage_options')) die(__('You cannot edit the WordPress-to-Lead options.', 'salesforce'));
				check_admin_referer('salesforce-udpatesettings');
				
				foreach (array('usecss','showccuser','ccadmin','captcha','wpcf7css','hide_salesforce_link') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}
				
				
		        foreach (array('successmsg','errormsg','sferrormsg','org_id','submitbutton','subject','ccusermsg','requiredfieldstext', 'da_token', 'da_url', 'da_site') as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = $_POST[$option_name];
					}
				}
			}
			
			//save changes to DB
			update_option($this->optionname, $options);
		
		}
		
		//$options = get_option($this->optionname);

		if (empty($options))
			$options = salesforce_default_settings();
		
		?>
		<div class="wrap">
			<a href="http://salesforce.com/"><div id="yoast-icon" style="background: url(<?php echo plugins_url('/salesforce-50x50.png', dirname(__FILE__) ); ?>) no-repeat;" class="icon32"><br /></div></a>
			<h2 style="line-height: 50px;"><?php echo $this->longname; ?></h2>
			<div class="postbox-container" style="width:70%;">
				
				<?php
				
				if( isset($_POST['submit']) && empty($_POST['mode']) ){
					echo '<div id="message" class="updated"><p>' . __('Configuration Saved','salesforce') . '</p></div>';
				}
				
				?>
									
				<div class="metabox-holder col-wrap">	
					<div class="meta-box-sortables">
						<?php if (!isset($_GET['tab']) || $_GET['tab'] == 'home') { ?>
						<form action="" method="post" id="salesforce-conf">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
							<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
							<?php
							
								
								$content = $this->textinput('org_id',__('Your Salesforce.com organisation ID','salesforce'));
								$content .= '<small>'.__('To find your Organisation ID, in your Salesforce.com account, go to Setup &raquo; Company Profile &raquo; Company Information','salesforce').'</small><br/>';
								$this->postbox('sfsettings',__('Salesforce.com Settings', 'salesforce'),$content); 

								$content = __('Daddy Analytics allows you to... TODO','salesforce').'<br/><br/>';

								$content .= $this->textinput('da_token',__('Daddy Analytics Token','salesforce'));
								$content .= $this->textinput('da_url',__('Daddy Analytics Webform URL','salesforce'));
								$content .= $this->textinput('da_site',__('Daddy Analytics Site ID','salesforce'));
								$this->postbox('sfsettings',__('Daddy Analytics Settings', 'salesforce'),$content); 
							
								$content = $this->textinput('successmsg',__('Success message after sending message', 'salesforce') );
								$content .= $this->textinput('errormsg',__('Error message when not all form fields are filled', 'salesforce') );
								$content .= $this->textinput('sferrormsg',__('Error message when Salesforce.com connection fails', 'salesforce') );
								$this->postbox('basicsettings',__('Basic Settings', 'salesforce'),$content); 

								$content = $this->checkbox('showccuser',__('Allow user to request a copy of their submission', 'salesforce') );
								$content .= '<br/>';
								$content .= $this->textinput('ccusermsg',__('Request a copy text', 'salesforce') );
								$content .= $this->textinput('subject',__('Email subject', 'salesforce') );
								$content .= '<small>'.__('Use %BLOG_NAME% to auto-insert the blog title into the subject','salesforce').'</small><br/><br/><br/>';

								$content .= $this->checkbox('ccadmin',__('Send blog admin an email notification', 'salesforce') );
								$content .= $this->checkbox('email_sender',__('Use this sender', 'salesforce') );
								$this->postbox('sfsettings',__('Email Settings', 'salesforce'),$content); 

								$content = $this->textinput('submitbutton',__('Submit button text', 'salesforce') );
								$content .= $this->textinput('requiredfieldstext',__('Required fields text', 'salesforce') );
								$content .= $this->checkbox('usecss',__('Use Form CSS?', 'salesforce') );
								$content .= $this->checkbox('wpcf7css',__('Use WPCF7 CSS integration?', 'salesforce') );
								//$content .= $this->checkbox('hide_salesforce_link',__('Hide "Powered by Salesforce CRM" on form?', 'salesforce') );
								$content .= '<br/><small><a href="'.$this->plugin_options_url().'&amp;tab=css">'.__('Read how to copy the CSS to your own CSS file').'</a></small><br><br>';

								$content .= $this->checkbox('captcha',__('Use CAPTCHA?', 'salesforce') );
								$content .= '<br/><small><a href="http://en.wikipedia.org/wiki/CAPTCHA" target="_blank">'.__('Learn more about CAPTCHAs at Wikipedia').'</a></small>';

								$this->postbox('formsettings',__('Form Settings', 'salesforce'),$content); 
								
								
								?>
								<div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e("Save WordPress-to-Lead Settings", 'salesforce'); ?>" /></div>
								<?php
								
								$content = '<table border="0" cellspacing="0" cellpadding="4">';
								$content .= '<tr><th>ID</th><th>Name</th></tr>';		
								foreach($options['forms'] as $key=>$form){
									
									$content .= '<tr><td>'.$key.'</td><td><a href="'.$this->plugin_options_url().'&tab=form&id='.$key.'">'.$form['form_name'].'</a><td></tr>';
								
								}
								$content .= '</table>';	
								
								$content .= '<p><a class="button-secondary" href="'.$this->plugin_options_url().'&tab=form">'.__('Add a new form','salesforce').' &raquo;</a></p>';			

									$this->postbox('sfforms',__('Forms', 'salesforce'),$content); 

								if( WP_DEBUG )
									$this->postbox('options','DEBUG: Options','<small>This dump of the plugin options is only shown when WP_DEBUG is enabled.</small><br><br>'.'<pre>'.print_r($options,true).'</pre>'); //DEBUG


							?>
							
						</form>
						<?php } else if ($_GET['tab'] == 'css') { ?>
						<?php echo '<p>'.salesforce_back_link($this->plugin_options_url()).'</p>'; ?>
						<p><?php echo __("If you don't want the inline styling this plugins uses, but add the CSS for the form to your own theme's CSS, you can start by just copying the proper CSS below into your CSS file. Just copy the correct text, and then you can usually find &amp; edit your CSS file",'salesforce'); ?> <a href="<?php echo admin_url('theme-editor.php'); ?>?file=<?php echo str_replace(WP_CONTENT_DIR,'',get_stylesheet_directory()); ?>/style.css&amp;theme=<?php echo urlencode(get_current_theme()); ?>&amp;dir=style"><?php echo __('here','salesforce');?></a>.</p>
						<div style="width:260px;margin:0 10px 0 0;float:left;">
							<div id="normalcss" class="postbox">
								<div class="handlediv" title="<?php echo __('Click to toggle','salesforce'); ?>"><br /></div>
								<h3 class="hndle"><span><?php echo __('CSS for the normal form','salesforce'); ?></span></h3>
								<div class="inside">
<pre>form.w2llead {
text-align: left;
clear: both;
}
.w2llabel, .w2linput {
display: block;
width: 120px;
float: left;
}
.w2llabel.error {
color: #f00;
}
.w2llabel {
clear: left;
margin: 4px 0;
}
.w2linput.text {
width: 200px;
height: 18px;
margin: 4px 0;
}
.w2linput.textarea {
clear: both;
width: 320px;
height: 75px;
margin: 10px 0;
}
.w2linput.submit {
float: none;
margin: 10px 0 0 0;
clear: both;
width: 150px;
}
.w2linput.checkbox{
vertical-align: middle;
}
.w2llabel.checkbox{
clear:both;
}
.w2linput.select{
clear:both;
}
.w2limg{ 
display: block; clear: both; 
}
#salesforce {
margin: 3px 0 0 0;
color: #aaa;
}
#salesforce a {
color: #999;
}</pre>
</div>
</div></div>
<div style="width:260px;float:left;">
<div id="widgetcss" class="postbox">
	<div class="handlediv" title="<?php echo __('Click to toggle','salesforce'); ?>"><br /></div>
	<h3 class="hndle"><span><?php echo __('CSS for the sidebar widget form','salesforce'); ?></span></h3>
	<div class="inside">
<pre>.sidebar form.w2llead {
clear: none;
text-align: left;
}
.sidebar .w2linput, 
.sidebar .w2llabel {
float: none;
display: inline;
}
.sidebar .w2llabel.error {
color: #f00;
}
.sidebar .w2llabel {
margin: 4px 0;
}
.sidebar .w2linput.text {
width: 160px;
height: 18px;
margin: 4px 0;
}
.sidebar .w2linput.textarea {
width: 160px;
height: 50px;
margin: 10px 0;
}
.sidebar .w2linput.submit {
margin: 10px 0 0 0;
}
#salesforce {
margin: 3px 0 0 0;
color: #aaa;
}
#salesforce a {
color: #999;
}</pre>
</div></div></div>

						<?php } else if ($_GET['tab'] == 'form') { ?>


<?php
if(isset($_POST['mode']) && $_POST['mode'] == 'delete' && $form_id != 1 ){

echo '<div id="message" class="updated"><p>' . __('Deleted Form #','salesforce') . $form_id . '</p></div>';

} else if(isset($_POST['mode']) && $_POST['mode'] == 'clone' && $form_id != 1 ) {

echo '<div id="message" class="updated"><p>' . __('Cloned Form #','salesforce') . $form_id . '</p></div>';

}else{

if(!isset($form_id) && isset($_GET['id']))
	$form_id = (int) $_GET['id'];

if( isset($_POST['form_id']) )
	$form_id = (int) $_POST['form_id'];

if( !isset($form_id) || $form_id == 0 ){
	//generate a new default form
	end( $options['forms'] );
	$form_id = key( $options['forms'] ) + 1;
	$options['forms'][$form_id] = salesforce_default_form();
}

//check for deleted forms
if( $form_id && !isset($options['forms'][$form_id]) ){
	echo '<div id="message" class="error"><p>' . __('This form could not be found.','salesforce') . '</p></div>';
}else{

	if(isset($_POST['submit']) && $_POST['submit'])
		echo '<div id="message" class="updated"><p>' . __('Form settings updated.','salesforce') . '</p></div>';
?>

						<form action="" method="post" id="salesforce-conf">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
							<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
							<input type="hidden" value="editform" name="mode"/>								
							<?php
							
								//$this->postbox('options','Options','<pre>'.print_r($options,true).'</pre>'); //DEBUG

									$content = '<p>';
									$content .= '<input type="text" name="form_name" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['form_name']).'">';
									//$content .= '<br/><small>'.__('').'</small>';
									$content .= '</p>';
									
									$this->postbox('sfformtitle',__('Form Name', 'salesforce'),$content);

									$content = '<style type="text/css">th{text-align:left;}</style>';
									$content .= '<table id="salesforce_form_editor" class="wp-list-table widefat fixed">';
									$content .= '<tr>'
									.'<th width="10%">'.__('Field','salesforce').'</th>'
									.'<th width="15%">'.__('Operations','salesforce').'</th>'
									.'<th width="12%">'.__('Type','salesforce').'</th>'
									.'<th width="13%">'.__('Label','salesforce').'</th>'
									.'<th width="15%">'.__('Value','salesforce').'</th>'
									.'<th width="20%">'.__('Options','salesforce').'</th>'
									.'<th width="8%">'.__('Position','salesforce').'</th>'
									.'</tr>';
									$i = 1;
									foreach ($options['forms'][$form_id]['inputs'] as $field => $input) {
										if (empty($input['pos']))
											$input['pos'] = $i;
										$content .= '<tr class="' . (($i % 2) ? 'alternate' : '') . '">';
										$content .= '<th>'.$field.'</th>';
										$content .= '<td>';
										$content .= '<table>';
										$content .= '<tr>';
										$content .= '<td><label for="inputs['.$field.'_show]">Enabled</label></td>';
										$content .= '<td><input type="checkbox" name="inputs['.$field.'_show]" id="inputs['.$field.'_show]" '.checked($input['show'],true,false).'/></td>';
										$content .= '</tr><tr>';
										$content .= '<td><label for="inputs['.$field.'_required]">Required</label></td>';
										$content .= '<td><input type="checkbox" name="inputs['.$field.'_required]" id="inputs['.$field.'_required]" '.checked($input['required'],true,false).'/></td>';
										$content .= '</tr><tr>';
										$content .= '<td><label for="inputs['.$field.'_delete]">Delete</label></td>';
										$content .= '<td><input type="checkbox" name="inputs['.$field.'_delete]" id="inputs['.$field.'_delete]" /></td>';
										$content .= '</tr>';
										$content .= '</table>';
										$content .= '</td>';
										$content .= '<td><select name="inputs['.$field.'_type]">';
										$content .= '<option '.selected($input['type'],'text',false).'>text</option>';
										$content .= '<option '.selected($input['type'],'textarea',false).'>textarea</option>';
										$content .= '<option '.selected($input['type'],'hidden',false).'>hidden</option>';
										$content .= '<option '.selected($input['type'],'select',false).'>select (picklist)</option>';
										$content .= '<option '.selected($input['type'],'checkbox',false).'>checkbox</option>';
										//$content .= '<option '.selected($input['type'],'current_date',false).'>current_date</option>';
										$content .= '<option '.selected($input['type'],'html',false).'>html</option>';
										$content .= '</select></td>';
										$content .= '<td><input size="10" name="inputs['.$field.'_label]" type="text" value="'.esc_html(stripslashes($input['label'])).'"/></td>';
										
										$content .= '<td><input size="14" name="inputs['.$field.'_value]" type="text" value="';
										if( isset($input['value']) ) $content .= esc_html(stripslashes($input['value']));
										$content .= '"/></td>';
										$content .= '<td><input name="inputs['.$field.'_opts]" type="text" value="'.esc_html(stripslashes($input['opts'])).'"/></td>';
										$content .= '<td><input size="2" name="inputs['.$field.'_pos]" type="text" value="'.esc_html($input['pos']).'"/></td>';
										$content .= '</tr>';
										$i++;
									}
									
									$content .= '</table>';
									
									?>
<script>

var pos = <?php echo $i; ?>;
var i = 1;
function salesforce_add_field(){

var row = '<tr>';
row += '<td><input type="text" size="10" name="add_inputs['+i+'][field_name]"></td>';
row += '<td><table>'
row += '<tr><td><label for="add_inputs['+i+'][show]">Enabled</label></td><td><input type="checkbox" name="add_inputs['+i+'][show]"></td></tr>';
row += '<tr><td><label for="add_inputs['+i+'][required]">Required</label></td><td><input type="checkbox" name="add_inputs['+i+'][required]"></td></tr>';
row += '</table></td>';
row += '<td><select name="add_inputs['+i+'][type]">'
	+ '<option>text</option>'
	+ '<option>textarea</option>'
	+ '<option>hidden</option>'
	+ '<option>select</option>'
	+ '<option>checkbox</option>'
	//+ '<option>current_date</option>'
	+ '<option>html</option>'
	+ '</select></td>';
row += '<td><input size="10" type="text" name="add_inputs['+i+'][label]"></td>';
row += '<td><input size="14" type="text" name="add_inputs['+i+'][value]"></td>';
row += '<td><input type="text" name="add_inputs['+i+'][opts]"></td>';
row += '<td><input type="text" size="2" name="add_inputs['+i+'][pos]" value="'+pos+'"></td>';
row += '</tr>';

jQuery('#salesforce_form_editor > tbody').append(row);

pos++;
i++;

}

</script>
									<?php
									
									$content .= '<p><a class="button-secondary" href="javascript:salesforce_add_field();">Add a field</a></p>';
									
									// $this->postbox('sffields',__('Form Fields', 'salesforce'),$content);
									echo $content;
									
									$content = '<p>';
									$content .= '<label>'.__('Lead Source:','salesforce').'</label><br/>';
									$content .= '<input type="text" name="source" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['source']).'">';
									$content .= '<br/><small>'.__('Lead Source to display in Salesforce.com, use %URL% to include the URL of the page containing the form').'</small>';
									$content .= '</p>';
									
									$content .= '<p>';
									$content .= '<label>'.__('Return/Thanks URL:','salesforce').'</label><br/>';
									$content .= '<input type="text" name="returl" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['returl']).'">';
									$content .= '<br/><small>'.__('e.g.http://yoursite.com/thanks/').'</small>';
									$content .= '</p>';

									$content .= '<input type="hidden" name="form_id" id="form_id" value="'.$form_id.'">';
									
									$this->postbox('sfformmeta',__('Form Settings', 'salesforce'),$content); 
								
							?>
							
							<div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e("Save Form", 'salesforce'); ?>" /></div>
						</form>
						
							<?php if( !empty($_GET['id']) && $_GET['id'] != 1 ){ ?>
							<form action="" method="post" id="salesforce-delete">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
								<input type="hidden" value="delete" name="mode"/>
								<input type="hidden" value="<?php echo $form_id; ?>" name="form_id"/>
								<input type="submit" name="submit" class="button-secondary" value="Delete this form">
							</form>
							<form action="" method="post" id="salesforce-clone">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
								<input type="hidden" value="clone" name="mode"/>
								<input type="hidden" value="<?php echo $form_id; ?>" name="form_id"/>
								<input type="submit" name="submit" class="button-secondary" value="Clone this form">
							</form>
							<?php } ?>
<?php } ?>
				<?php } ?>
				
				<?php echo '<p>'.salesforce_back_link($this->plugin_options_url()).'</p>'; ?>
				
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="postbox-container" style="width:20%;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables">
						<?php
						
							if( isset( $_GET['id'] ) && $_GET['id'] ){
							
								$fid = absint( $_GET['id'] );
								
								$term = 'form';
							
								$this->postbox('usesalesforce',__('How to Use This Form','salesforce'),__('<p>To embed this form, copy the following shortcode into a post or page:</p><p> [salesforce form="'.$fid.'"] </p>','salesforce'));

							}else{
								$term = 'settings';
								
								$this->postbox('usesalesforce',__('How to Use This Plugin','salesforce'),__('<p>To embed a form, copy the following shortcode into a post or page:</p><p> [salesforce form="X"] </p><p>Replace X with the form number for the form you want to show.</p><p>Make sure you have entered all the correct settings on the left, including your Organisation ID.</p>','salesforce'));
								
							}

							$this->plugin_like(false);
							$this->plugin_support();

							$loc = 'banner-side';

							$ad = $this->get_ad_code( $loc );		

							$link =$this->get_ad_link( $ad['id'], $term, $loc );
							
							$this->postbox('usesalesforce',__('Plugin Sponsor: Daddy Analytics','salesforce'),__('<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>','salesforce'));


							// $this->news(); 
						?>
					</div>
					<br/><br/><br/>
				</div>
			</div>
		</div>
		<?php
	}
} // end class SalesForce_Admin