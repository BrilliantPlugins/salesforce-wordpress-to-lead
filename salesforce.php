<?php
/*
Plugin Name: WordPress-to-Lead for Salesforce CRM
Plugin URI: http://www.salesforce.com/form/signup/wordpress-to-lead.jsp?d=70130000000F4Mw
Description: Easily embed a contactform into your posts, pages or your sidebar, and capture the entries straight into Salesforce CRM!
Author: Joost de Valk, Nick Ciske, Modern Tribe Inc.
Version: 2.0.3
Author URI: http://tri.be/
*/

if ( ! class_exists( 'Salesforce_Admin' ) ) {

	require_once('ov_plugin_tools.php');
	
	class Salesforce_Admin extends OV_Plugin_Admin {

		var $hook 		= 'salesforce-wordpress-to-lead';
		var $filename	= 'salesforce/salesforce.php';
		var $longname	= 'WordPress-to-Lead for Salesforce CRM Configuration';
		var $shortname	= 'Salesforce.com';
		var $optionname = 'salesforce2';
		var $homepage	= 'http://www.salesforce.com/wordpress/';
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
						foreach (array('show','required') as $option_name) {
							if (isset($_POST['inputs'][$id.'_'.$option_name])) {
								$newinputs[$id][$option_name] = true;
								unset($_POST['inputs'][$id.'_'.$option_name]);
							} else {
								$newinputs[$id][$option_name] = false;
							}
						}
						foreach (array('type','label','value','pos') as $option_name) {
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
								
								foreach (array('type','label','value','pos') as $option_name) {
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
				
				}else{
				
					//Save general settings
									
					$options  = get_option($this->optionname);
					if (!current_user_can('manage_options')) die(__('You cannot edit the WordPress-to-Lead options.', 'salesforce'));
					check_admin_referer('salesforce-udpatesettings');
					
					foreach (array('usecss','showccuser','ccadmin','captcha') as $option_name) {
						if (isset($_POST[$option_name])) {
							$options[$option_name] = true;
						} else {
							$options[$option_name] = false;
						}
					}
					
					
			        foreach (array('successmsg','errormsg','sferrormsg','org_id','submitbutton','subject','ccusermsg','requiredfieldstext') as $option_name) {
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
				<a href="http://salesforce.com/"><div id="yoast-icon" style="background: url(<?php echo plugins_url('',__FILE__); ?>/salesforce-50x50.png) no-repeat;" class="icon32"><br /></div></a>
				<h2 style="line-height: 50px;"><?php echo $this->longname; ?></h2>
				<div class="postbox-container" style="width:70%;">
					
					<?php
					
					if( isset($_POST['submit']) && empty($_POST['mode']) ){
						echo '<div id="message" class="updated"><p>' . __('Configuration Saved','salesforce') . '</p></div>';
					}
					
					?>
										
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php if (!isset($_GET['tab']) || $_GET['tab'] == 'home') { ?>
							<form action="" method="post" id="salesforce-conf">
								<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
								<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
								<?php
								
									//$this->postbox('options','Options','<pre>'.print_r($options,true).'</pre>'); //DEBUG
								
									$content = $this->textinput('successmsg',__('Success message after sending message', 'salesforce') );
									$content .= $this->textinput('errormsg',__('Error message when not all form fields are filled', 'salesforce') );
									$content .= $this->textinput('sferrormsg',__('Error message when Salesforce.com connection fails', 'salesforce') );
									$this->postbox('basicsettings',__('Basic Settings', 'salesforce'),$content); 
									
									$content = $this->textinput('org_id',__('Your Salesforce.com organisation ID','salesforce'));
									$content .= '<small>'.__('To find your Organisation ID, in your Salesforce.com account, go to Setup &raquo; Company Profile &raquo; Company Information','salesforce').'</small><br/><br/><br/>';
									$this->postbox('sfsettings',__('Salesforce.com Settings', 'salesforce'),$content); 

									$content = $this->checkbox('showccuser',__('Allow user to request a copy of their submission', 'salesforce') );
									$content .= '<br/>';
									$content .= $this->textinput('ccusermsg',__('Request a copy text', 'salesforce') );
									$content .= $this->textinput('subject',__('Email subject', 'salesforce') );
									$content .= '<small>'.__('Use %BLOG_NAME% to auto-insert the blog title into the subject','salesforce').'</small><br/><br/><br/>';

									$content .= $this->checkbox('ccadmin',__('Send blog admin an email notification', 'salesforce') );
									$this->postbox('sfsettings',__('Email Settings', 'salesforce'),$content); 

									$content = $this->textinput('submitbutton',__('Submit button text', 'salesforce') );
									$content .= $this->textinput('requiredfieldstext',__('Required fields text', 'salesforce') );
									$content .= $this->checkbox('usecss',__('Use Form CSS?', 'salesforce') );
									$content .= '<br/><small><a href="'.$this->plugin_options_url().'&amp;tab=css">'.__('Read how to copy the CSS to your own CSS file').'</a></small><br><br>';

									$content .= $this->checkbox('captcha',__('Use CAPTCHA?', 'salesforce') );
									$content .= '<br/><small><a href="http://en.wikipedia.org/wiki/CAPTCHA" target="_blank">'.__('Learn more about CAPTCHAs at Wikipedia').'</a></small>';

									$this->postbox('formsettings',__('Form Settings', 'salesforce'),$content); 
									
									
									?>
									<div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e("Save WordPress-to-Lead Settings", 'salesforce'); ?>" /></div>
									<?php
									
									$content = '<table border="1">';
									$content .= '<tr><th>ID</th><th>Name</th></tr>';		
									foreach($options['forms'] as $key=>$form){
										
										$content .= '<tr><td>'.$key.'</td><td><a href="'.$this->plugin_options_url().'&tab=form&id='.$key.'">'.$form['form_name'].'</a><td></tr>';
									
									}
									$content .= '</table>';	
									
									$content .= '<p><a class="button-secondary" href="'.$this->plugin_options_url().'&tab=form">'.__('Add a new form','salesforce').' &raquo;</a></p>';			

										$this->postbox('sfforms',__('Forms', 'salesforce'),$content); 


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

										$content = '<style type="text/css">th{text-align:left;}</style><table id="salesforce_form_editor">';
										$content .= '<tr>'
										.'<th width="15%">'.__('Field','salesforce').'</th>'
										.'<th width="10%">'.__('Enable','salesforce').'</th>'
										.'<th width="10%">'.__('Required','salesforce').'</th>'
										.'<th width="10%">'.__('Type','salesforce').'</th>'
										.'<th width="20%">'.__('Label','salesforce').'</th>'
										.'<th width="20%">'.__('Value','salesforce').'</th>'
										.'<th width="10%">'.__('Position','salesforce').'</th>'
										.'</tr>';
										$i = 1;
										foreach ($options['forms'][$form_id]['inputs'] as $field => $input) {
											if (empty($input['pos']))
												$input['pos'] = $i;
											$content .= '<tr>';
											$content .= '<th>'.$field.'</th>';
											$content .= '<td><input type="checkbox" name="inputs['.$field.'_show]" '.checked($input['show'],true,false).'/></td>';
											$content .= '<td><input type="checkbox" name="inputs['.$field.'_required]" '.checked($input['required'],true,false).'/></td>';
											$content .= '<td><select name="inputs['.$field.'_type]">';
											$content .= '<option '.selected($input['type'],'text',false).'>text</option>';
											$content .= '<option '.selected($input['type'],'textarea',false).'>textarea</option>';
											$content .= '<option '.selected($input['type'],'hidden',false).'>hidden</option>';
											$content .= '</select></td>';
											$content .= '<td><input size="20" name="inputs['.$field.'_label]" type="text" value="'.esc_html($input['label']).'"/></td>';
											
											$content .= '<td><input size="20" name="inputs['.$field.'_value]" type="text" value="';
											if( isset($input['value']) ) $content .= esc_html($input['value']);
											$content .= '"/></td>';
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
	row += '<td><input type="checkbox" name="add_inputs['+i+'][show]"></td>';
	row += '<td><input type="checkbox" name="add_inputs['+i+'][required]"></td>';
	row += '<td><select name="add_inputs['+i+'][type]"><option>text</option><option>textarea</option><option>hidden</option></select></td>';
	row += '<td><input type="text" name="add_inputs['+i+'][label]"></td>';
	row += '<td><input type="text" name="add_inputs['+i+'][value]"></td>';
	row += '<td><input type="text" size="2" name="add_inputs['+i+'][pos]" value="'+pos+'"></td>';
	row += '</tr>';
	
	jQuery('#salesforce_form_editor tbody').append(row);
	
	pos++;
	i++;

}

</script>
										<?php
										
										$content .= '<p><a class="button-secondary" href="javascript:salesforce_add_field();">Add a field</a></p>';
										
										$this->postbox('sffields',__('Form Fields', 'salesforce'),$content);
										
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
								$this->postbox('usesalesforce',__('How to Use This Plugin','salesforce'),__('<p>To use this form, copy the following shortcode into a post or page:</p><pre style="padding:5px 10px;margin:10px 0;background-color:lightyellow;">[salesforce form="X"]</pre><p>Replace X with the form number for the form you want to show.</p><p>Make sure you have entered all the correct settings on the left, including your Organisation ID.</p>','salesforce'));
								$this->plugin_like(false);
								$this->plugin_support();
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
	$salesforce = new Salesforce_Admin();
}

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
	$options['ccadmin']				= false;
	$options['captcha']				= false;

	$options['usecss']				= true;

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
		</style>';
	}
	$sidebar = '';
	
	if ( $is_sidebar )
		$sidebar = ' sidebar';
		
	$content .= "\n".'<form id="salesforce_w2l_lead_'.$form_id.str_replace(' ','_',$sidebar).'" class="w2llead'.$sidebar.'" method="post">'."\n";

	foreach ($options['forms'][$form_id]['inputs'] as $id => $input) {
		if (!$input['show'])
			continue;
		$val 	= '';
		if (isset($_POST[$id])){
			$val	= esc_attr(strip_tags(stripslashes($_POST[$id])));
		}else{
			if( isset($input['value']) ) $val	= esc_attr(strip_tags(stripslashes($input['value'])));
		}

		$error 	= ' ';
		if (isset($input['error']) && $input['error']) 
			$error 	= ' error ';
			
		if($input['type'] != 'hidden')
			$content .= "\t".'<label class="w2llabel'.$error.$input['type'].'" for="sf_'.$id.'">'.esc_html(stripslashes($input['label'])).':';
		
		if ($input['required'] && $input['type'] != 'hidden')
			$content .= ' *';
		
		if($input['type'] != 'hidden')
			$content .= '</label>'."\n";
		
		if ($input['type'] == 'text') {			
			$content .= "\t".'<input value="'.$val.'" id="sf_'.$id.'" class="w2linput text" name="'.$id.'" type="text"/><br/>'."\n\n";
		} else if ($input['type'] == 'textarea') {
			$content .= "\t".'<br/>'."\n\t".'<textarea id="sf_'.$id.'" class="w2linput textarea" name="'.$id.'">'.$val.'</textarea><br/>'."\n\n";
		} else if ($input['type'] == 'hidden') {
			$content .= "\t\n\t".'<input type="hidden" id="sf_'.$id.'" class="w2linput hidden" name="'.$id.'" value="'.$val.'">'."\n\n";
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
		$content .= "\t\n\t".'<p><label class="w2llabel checkbox"><input type="checkbox" name="w2lcc" class="w2linput checkbox" value="1"/> '.esc_html($label)."</label><p>\n";
	}
	
	//spam honeypot
	$content .= "\t".'<input type="text" name="message" class="w2linput" value="" style="display: none;"/>'."\n";

	//form id
	$content .= "\t".'<input type="hidden" name="form_id" class="w2linput" value="'.$form_id.'" />'."\n";

	$submit = stripslashes($options['submitbutton']);
	if (empty($submit))
		$submit = "Submit";
	$content .= "\t".'<div class="w2lsubmit"><input type="submit" name="w2lsubmit" class="w2linput submit" value="'.esc_attr($submit).'"/></div>'."\n";
	$content .= '</form>'."\n";

	$reqtext = stripslashes($options['requiredfieldstext']);
	if (!empty($reqtext))
		$content .= '<p id="requiredfieldsmsg"><sup>*</sup>'.esc_html($reqtext).'</p>';
	$content .= '<div id="salesforce"><small>'.__('Powered by','salesforce').' <a href="http://www.salesforce.com/">Salesforce CRM</a></small></div>';
	
	$content = apply_filters('salesforce_w2l_form_html', $content);
	
	return $content;
}

function submit_salesforce_form($post, $options) {
	
	global $wp_version;
	if (!isset($options['org_id']) || empty($options['org_id']))
		return false;

	//spam honeypot
	if( !empty($_POST['message']) )
		return false;

	//print_r($_POST); //DEBUG
	
	$form_id = intval( $_POST['form_id'] );

	$post['oid'] 			= $options['org_id'];
	$post['lead_source']	= str_replace('%URL%','['.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].']',$options['forms'][$form_id]['source']);
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
	
	if( is_wp_error($result) )
		return false;
	
	if ($result['response']['code'] == 200){

		if( isset( $_POST['w2lcc'] ) && $_POST['w2lcc'] == 1 )
			salesforce_cc_user($post, $options, $form_id);

		if( isset( $options['ccadmin'] ) && $options['ccadmin'] )
			salesforce_cc_admin($post, $options, $form_id);
		
		return true;
	}else{

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
	unset($post['lead_source']);
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

	$subject = __('Salesforce WP to Lead Submission','salesforce');

	$message = '';

	unset($post['oid']);
	unset($post['lead_source']);
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