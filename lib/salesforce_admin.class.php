<?php
class Salesforce_Admin extends OV_Plugin_Admin {

	public $optionname;

	public $hook;
	public $filename;
	public $longname;
	public $shortname;
	public $homepage;
	public $ozhicon;

	function Salesforce_Admin() {

		$this->optionname = 'salesforce2';

		$this->hook 		= 'salesforce-wordpress-to-lead';
		$this->filename		= 'salesforce/salesforce.php';
		$this->longname		= 'WordPress-to-Lead for Salesforce CRM Configuration';
		$this->shortname	= 'Salesforce';
		$this->homepage		= 'https://brilliantplugins.com/downloads/salesforce/';
		$this->ozhicon		= 'salesforce-16x16.png';

		add_action( 'admin_menu', array(&$this, 'register_settings_page') );
		add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
		add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );

		add_action('admin_print_scripts', array(&$this,'config_page_scripts'));
		add_action('admin_print_styles', array(&$this,'config_page_styles'));
		add_action('admin_footer', array(&$this,'warning'));

		add_action('wp_ajax_sfw2l_get_captcha', 'salesforce_captcha');
		add_action('wp_ajax_nopriv_sfw2l_get_captcha', 'salesforce_captcha');

	}

	public static function default_form() {

		$dform = array();

		$dform['form_name'] = 'My New Lead Form '.date('Y-m-d h:i:s');

		if( self::using_da() ){
			$dform['source'] = '';
		}else{
			$dform['source'] = __('Lead form on ','salesforce').get_bloginfo('name');
		}

		$dform['labellocation'] = 'top-aligned';
		$dform['labellocationsidebar'] = 'top-aligned';

		$dform['returl'] = '';

		$dform['type'] = 'lead';

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

	static function using_da(){

		$options = get_option( 'salesforce2' );

		if( isset( $options['da_token'] ) && isset( $options['da_url'] ) && isset( $options['da_site'] ) && $options['da_token'] && $options['da_url'] && $options['da_site'] )
			return true;

		return false;
	}

	function get_ad_term(){

		if( ( isset( $_GET['id'] ) && $_GET['id'] ) || ( isset( $_GET['tab'] ) && $_GET['tab'] == 'form' ) ){
			$term = 'form';
		}else{
			$term = 'settings';
		}

		return $term;

	}

	function get_ad_link( $content, $medium, $url = 'http://brilliantplugins.com/', $term='', $source = 'wp-admin', $campaign = 'bwtlfs' ){

		if( !$term )
			$term = $this->get_ad_term();

		$link = $url . '?utm_source=%s&utm_medium=%s&utm_campaign=%s&utm_term=%s&utm_content=%s';
		return sprintf( $link, $source, $medium, $campaign, $term, $content  );

	}

	function get_ad_code( $type, $id = null, $num = null ){

		$options  = get_option($this->optionname);

		// disable ads for now
		return;

		if( defined( 'SFWP2L_HIDE_ADS' ) && SFWP2L_HIDE_ADS == true ){
			return; // hide ads due to constant
		}elseif( defined( 'SFWP2L_HIDE_ADS' ) && SFWP2L_HIDE_ADS == false ){
			// show ads anyways
		}else{
			if( $this->using_da() )
				return; // hide ads as they've signed up
		}

		$ads = array(
			'banner-side' => array(
				array( 'id' => 'da1_1', 'url' => 'http://daddyanalytics.com', 'content' => 'assets/ads/side-analytics-track-form-submission-keyword.png' ),
				array( 'id' => 'da1_2', 'url' => 'http://daddyanalytics.com', 'content' => 'assets/ads/side-analytics-marketing-roi-offer.png' ),
				array( 'id' => 'da1_3', 'url' => 'http://daddyanalytics.com', 'content' => 'assets/ads/side-analytics-track-lead-location.png' ),
			),

			'banner-main' => array(
				array( 'id' => 'da1_4', 'url' => 'https://breadwinnerhq.com/quickbooks-online-salesforce/', 'content' => 'assets/ads/QBO_SF_Banner_Images/Breadwinner-SF-Q-v1-connect-salesforce-QB.png' , "utm_source" => "wp_plugin", "utm_medium" => "banner" , "utm_content" => "connect", "utm_campaign" => "BW%20QBO%20Wordpress%20Banner"),
				array( 'id' => 'da1_5', 'url' => 'https://breadwinnerhq.com/quickbooks-online-salesforce/', 'content' => 'assets/ads/QBO_SF_Banner_Images/Breadwinner-SF-Q-v1-create-invoices-in-QB.png', "utm_source" => "wp_plugin", "utm_medium" => "banner" , "utm_content" => "create", "utm_campaign" => "BW%20QBO%20Wordpress%20Banner" ),
				array( 'id' => 'da1_7', 'url' => 'https://breadwinnerhq.com/xero-salesforce/', 'content' => 'assets/ads/Xero_SF_Banner_Images/Breadwinner-SF-X-v1-connect-salesforce-xero.png', "utm_source" => "wp_plugin", "utm_medium" => "banner" , "utm_content" => "connect", "utm_campaign" => "BW%20Xero%20Wordpress%20Banner"),
				array( 'id' => 'da1_8', 'url' => 'https://breadwinnerhq.com/xero-salesforce/', 'content' => 'assets/ads/Xero_SF_Banner_Images/Breadwinner-SF-X-v1-create-invoices-in-xero.png',"utm_source" => "wp_plugin", "utm_medium" => "banner" , "utm_content" => "create", "utm_campaign" => "BW%20Xero%20Wordpress%20Banner" ),
			),

			'text' => array(
				array( 'id' => 'da1_7', 'content' => 'Daddy Analytics allows you to track your leads from their original source, such as Adwords, Google Organic, Social Media, or other blogs. With that information you can get your true marketing ROI, as each Opportunity is attributed to the marketing activity that brought in the Lead. <a class="button-secondary" href="%link1%" target="_blank">Watch a video of Daddy Analytics</a>'),
				array( 'id' => 'da1_8', 'cta' => 'Sign up Now', 'content' => 'Daddy Analytics allows you to track your leads from their original source, such as Adwords, Google Organic, Social Media, or other blogs. With that information you can get your true marketing ROI, as each Opportunity is attributed to the marketing activity that brought in the Lead. <a  class="button-secondary" href="%link2%" target="_blank">Sign up for a free trial of Daddy Analytics</a>'),
				//array( 'id' => 'da09', 'cta' => 'Sign up Soon!', 'content' => 'Daddy Analytics allows you to... TODO3'),
			),

		);

		if( $id ){

			foreach( $ads[ $type ] as $ad ){
				if( $ad['id'] == $id )
					return $ad;
			}

		}

		$num = mt_rand( 1, count( $ads[ $type ] ) ) - 1;
		//echo $num;

		return $ads[ $type ][ $num ];
	}

	function warning() {
		$options = get_option($this->optionname);

		$show_admin_nag_message = apply_filters( 'salesforce_w2l_show_admin_nag_message', true );

		if ( !isset( $options['org_id'] ) || empty( $options['org_id'] ) && $show_admin_nag_message )
			echo "<div id='message' class='error'><p><strong>".__('Your WordPress-to-Lead settings are not complete.','salesforce')."</strong> ".__('You must enter your Salesforce.com Organization ID for it to work.','salesforce')." <a href='".$this->plugin_options_url()."&tab=settings'>".__('Settings','salesforce')."</a></p></div>";

			//echo 'ERROR= '.get_option('plugin_error');

	}

	function admin_tabs( $current = 'forms' ) {
		if( isset( $_GET['tab'] ) )
			$current = $_GET['tab'];

		if( !$current )
			$current = 'forms';

	    $tabs = array( 'forms' => 'Forms', 'settings' => 'Settings', 'css' => 'Styling', 'form' => 'Form Editor', 'import' => 'Import' );
	    //echo '<div id="icon-themes" class="icon32"><br></div>';
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab$class' href='?page=salesforce-wordpress-to-lead&tab=$tab'>$name</a>";

	    }
	    echo '</h2>';
	}

	function config_page() {

		wp_enqueue_style( 'sfwp2lcssadmin', plugins_url('assets/css/sfwp2l-admin.css', dirname(__FILE__) ) );

		$options = get_option($this->optionname);

		if ( isset($_POST['submit']) ) {

			//die('<pre>'.print_r($_POST,true)); //DEBUG

			if( isset( $_POST['mode'] ) && $_POST['mode'] == 'editform' ){

				$form_id = (int) $_POST['form_id'];

				if(!isset($options['forms'][$form_id]))
					$options['forms'][$form_id] = $this->default_form();

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

				// normal options
				w2l_sksort($newinputs,'pos',true);
				$options['forms'][$form_id]['inputs'] = $newinputs; //TODO

				foreach (array('form_name','source','returl','successmsg','captchaform','labellocation','labellocationsidebar','submitbutton','requiredfieldstext','requiredfieldstextpos','type','org_id', 'cc_email_subject','donotautoaddcolontolabels' ) as $option_name) {
					if (isset($_POST[$option_name])) {
						$options['forms'][$form_id][$option_name] = $_POST[$option_name];
					}else{
						$options['forms'][$form_id][$option_name] = '';
					}
				}

				//End Save Form Data

			}elseif( isset( $_POST['mode'] ) && $_POST['mode'] == 'delete'){

				if( isset( $_POST['form_id'] ) && $_POST['form_id'] != 1 )
					unset( $options['forms'][$_POST['form_id']] );

			}elseif( isset( $_POST['mode'] ) && $_POST['mode'] == 'clone'){

				if( isset( $_POST['form_id'] ) ) {
					$new_id = max(array_keys($options['forms'])) + 1;
					$options['forms'][$new_id] = $options['forms'][$_POST['form_id']];
					$options['forms'][$new_id]['form_name'] .= ' (copy)';
				}

			}else{

				//Save general settings

				$options  = get_option($this->optionname);
				if (!current_user_can('manage_options')) die(__('You cannot edit the WordPress-to-Lead options.', 'salesforce'));
				check_admin_referer('salesforce-udpatesettings');

				foreach( array(

					'usecss',
					'showccuser',
					'ccadmin',
					'captcha',
					'wpcf7css',
					'wpcf7jsfix',
					'sslverify',
					'hide_salesforce_link',
					'commentstoleads',
					'commentsnamefields'

					) as $option_name) {
					if (isset($_POST[$option_name])) {
						$options[$option_name] = true;
					} else {
						$options[$option_name] = false;
					}
				}

		        foreach ( array(

		        	'successmsg',
		        	'errormsg',
		        	'emailerrormsg',
		        	'captchaerrormsg',
		        	'sferrormsg',
		        	'org_id',
		        	'submitbutton',
		        	'subject',
		        	'ccusermsg',
		        	'requiredfieldstext',
		        	'ccothers',
		        	'emailfromname',
		        	'emailfromaddress',

					'captcha_type',
					'recaptcha_site_key',
					'recaptcha_secret_key',

		        	'da_token',
		        	'da_url',
		        	'da_site'

		        	) as $option_name) {
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

		<?php $this->admin_tabs(); ?>

			<div class="postbox-container" style="width:70%;">

				<?php

				if( isset($_POST['submit']) && empty($_POST['mode']) ){
					echo '<div id="message" class="updated"><p>' . __('Configuration Saved','salesforce') . '</p></div>';
				}

				?>

				<div class="metabox-holder col-wrap">
					<div class="meta-box-sortables">

						<?php if (!isset($_GET['tab']) || $_GET['tab'] == 'forms') {

								$content = '<table border="0" cellspacing="0" cellpadding="4">';
								$content .= '<tr><th>ID</th><th>Name</th></tr>';
								foreach($options['forms'] as $key=>$form){

									$name = $form['form_name'];

									if( !$name )
										$name = '(Blank)';

									$content .= '<tr><td>'.$key.'</td><td><a href="'.$this->plugin_options_url().'&tab=form&id='.$key.'">'.$name.'</a><td></tr>';

								}
								$content .= '</table>';

								$content .= '<p><a class="button-secondary" href="'.$this->plugin_options_url().'&tab=form">'.__('Add a new form','salesforce').' &raquo;</a></p>';

								$this->postbox('sfforms',__('Forms', 'salesforce'),$content);

								$loc = 'banner-main';
								$ad = $this->get_ad_code( $loc );
								if( $ad ){
									$link = $this->get_ad_link( $ad['utm_content'], $ad['utm_medium'], $ad['url'], '',$ad['utm_source'],$ad['utm_campaign']);
									echo '<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>';
								}


						}

						 if (isset($_GET['tab']) && $_GET['tab'] == 'settings') { ?>
						<form action="" method="post" id="salesforce-conf">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
							<input type="hidden" value="<?php echo $options['version']; ?>" name="version"/>
							<?php

								//if( $options['org_id'] )
									//$class='closed';

								$content = $this->textinput('org_id',__('Your Salesforce.com Organization ID','salesforce'), __('To find your Salesforce.com Organization ID, in your Salesforce.com account, go to Setup &raquo; Company Profile &raquo; Company Information','salesforce'));
								$this->postbox('sfsettings',__('Salesforce.com Settings', 'salesforce'), $content);


							$loc = 'banner-main';
							$ad = $this->get_ad_code( $loc );
							if( $ad ){
//								$link = $this->get_ad_link( $ad['id'], $loc, $ad['url'] );
                                                                $link = $this->get_ad_link( $ad['utm_content'], $ad['utm_medium'], $ad['url'], '',$ad['utm_source'],$ad['utm_campaign']);
								echo '<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>';
							}
							$loc = 'text';
							$ad = $this->get_ad_code( $loc );
							if( $ad ){
								$link1 = $this->get_ad_link( $ad['id'], $loc, 'http://try.daddyanalytics.com/watch-a-video-wp2l/?utm_source=ThoughtRefinery&utm_medium=text&utm_campaign=WP2L_Plugin_01&utm_content=da1_7' );
								$link2 = $this->get_ad_link( $ad['id'], $loc, 'http://try.daddyanalytics.com/start-free-trial-wp2l/?utm_source=ThoughtRefinery&utm_medium=text&utm_campaign=WP2L_Plugin_01&utm_content=da1_8' );

								$ad['content'] = str_replace( array('%link1%','%link2%'), array($link1,$link2), $ad['content'] );

								$content = $ad['content'].'<br/><br/>';
								$class = '';
							}else{
								//$class = 'closed';
								$content = '';
							}

								$content .= $this->textinput('da_token',__('Daddy Analytics Token','salesforce'));
								$content .= $this->textinput('da_url',__('Daddy Analytics Web to Lead URL ID','salesforce'));
								$content .= $this->textinput('da_site',__('Daddy Analytics Site ID','salesforce'));
								$this->postbox('sfsettings',__('Daddy Analytics Settings', 'salesforce'), $content);

								$content = $this->textinput('successmsg',__('Success message after sending lead to SalesForce', 'salesforce') );
								$content .= $this->textinput('errormsg',__('Error message shown when required fields are not filled out', 'salesforce') );
								$content .= $this->textinput('emailerrormsg',__('Error message shown when email field is invalid', 'salesforce'), 'Default: The email address you entered is not valid.' );

								$content .= $this->textinput('captchaerrormsg',__('Error message shown when captcha is invalid', 'salesforce'), 'Default: The text you entered did not match the image.' );

								$content .= $this->textinput('sferrormsg',__('Error message when Salesforce.com connection fails', 'salesforce') );
								$this->postbox('basicsettings',__('Basic Settings', 'salesforce'),$content);

								$content = $this->checkbox('showccuser',__('Allow user to request a copy of their submission', 'salesforce') );
								$content .= '<br/>';
								$content .= $this->textinput('ccusermsg',__('Request a copy text', 'salesforce') );
								$content .= $this->textinput('subject',__('Email subject', 'salesforce'), __('Use %BLOG_NAME% to auto-insert the blog title into the subject','salesforce') );

								//$content .= $this->checkbox('email_sender',__('Use this sender', 'salesforce') );
								$content .= $this->textinput('ccothers',__('Email new submissions to', 'salesforce'), __('Separate multiple addresses with commas.','salesforce') );

								$content .= $this->textinput('emailfromname',__('Email from name', 'salesforce') );
								$content .= $this->textinput('emailfromaddress',__('Email from address', 'salesforce') );

								$content .= $this->checkbox('ccadmin',__('Send blog admin an email notification', 'salesforce') );

								$this->postbox('sfsettings',__('Email Settings', 'salesforce'), $content);

								$content = $this->textinput('submitbutton',__('Submit button text', 'salesforce') );
								$content .= $this->textinput('requiredfieldstext',__('Required fields text', 'salesforce') );

								$this->postbox('formsettings',__('Form Settings', 'salesforce'), $content);

								$content = $this->checkbox('captcha',__('Enable CAPTCHA', 'salesforce') );

								$items = array(
									array( 'value' => '', 'name' => __('Built-In', 'salesforce') ),
									array( 'value' => 'recaptcha', 'name' => __('Google ReCaptcha', 'salesforce') ),
								);

								$content .= '<br/>';

								$content .= $this->radiogroup('captcha_type', 'Captcha Type:', $items);

								$content .= '<br/>';

								$content .= $this->textinput('recaptcha_site_key',__('Google ReCaptcha Site Key', 'salesforce') );
								$content .= $this->textinput('recaptcha_secret_key',__('Google ReCaptcha Secret Key', 'salesforce') );

								$content .= '<small><a href="https://www.google.com/recaptcha/admin" target="_blank">'.__('Get ReCaptcha Keys').'</a></small>';

								$this->postbox('formsettings',__('Captcha Settings', 'salesforce'), $content);

								$content = $this->checkbox('usecss',__('Use Default CSS?', 'salesforce') );
								$content .= $this->checkbox('wpcf7css',__('Use WP CF7 CSS integration?', 'salesforce') );
								$content .= $this->checkbox('wpcf7jsfix',__('Remove WP CF7 Javascript on SFWP2L pages? <i>(fixes CF7 hijacking form submits, may break CF7 forms on the same page)</i>', 'salesforce') );

								$content .= '<br/><small><a href="'.$this->plugin_options_url().'&amp;tab=css">'.__('Read how to override the default CSS with your own CSS file').'</a></small><br><br>';

								$this->postbox('csssettings',__('Style Settings', 'salesforce'), $content);

								$content = $this->checkbox('sslverify',__('Enable verification of SalesForce SSL certificate when connecting <i>(fixes connection issues on some sites behind F7 firewalls and BigIP load balancers)</i>', 'salesforce') );

								$this->postbox('sslsettings',__('SSL Settings', 'salesforce'), $content);

								$content = $this->checkbox('commentstoleads',__('Create a lead when an approved comment is published', 'salesforce') );
								$content .= $this->checkbox('commentsnamefields',__('Replace the "Name" field on the comment form with "First Name" and "Last Name"', 'salesforce') );
								$content .= sprintf( '<p class="description">%s</p>', __( '<small>Using first and last name fields allows for cleaner Salesforce leads, otherwise the "first name" on the lead will contain the full name, but it may create issues with some WordPress themes.</small>', 'salesforce' ) );

								$this->postbox('commentsettings',__('Comment Settings', 'salesforce'), $content);

								?>
								<div class="submit"><input type="submit" class="button-primary" name="submit" value="<?php _e("Save WordPress-to-Lead Settings", 'salesforce'); ?>" /></div>
								<?php


								if( WP_DEBUG )
									$this->postbox('options','DEBUG: Options','<small>This dump of the plugin options is only shown when WP_DEBUG is enabled.</small><br><br>'.'<pre>'.print_r($options,true).'</pre>', 'closed'); //DEBUG


							?>

						</form>
						<?php }

						if (isset($_GET['tab']) && $_GET['tab'] == 'css') {

						wp_enqueue_style( 'prismcss', plugins_url('assets/css/prism.css', dirname(__FILE__) ) );
						wp_enqueue_script( 'prismjs', plugins_url('assets/js/prism/prism.min.js', dirname(__FILE__) ) );


						//echo '<p>'.salesforce_back_link($this->plugin_options_url()).'</p>'; ?>
						<p>
						<?php echo __("<p>If you don't want the default styling this plugin uses, you can add the CSS for the form to your own theme by creating a folder named <i>salesforce-wordpress-to-lead</i> in your theme folder, then creating a file called <i>custom.css</i> within that with your custom CSS.</p>

							<p>".get_stylesheet_directory()."/<b>salesforce-wordpress-to-lead</b>/<b>custom.css</b></p>

							<p>If found, that file will be enqueued after the default CSS, or by itself if the default CSS is disabled on the main options screen.</p>
							");

						echo '<pre><code class="language-css">'.file_get_contents( dirname(plugin_dir_path(__FILE__)) . '/assets/css/sfwp2l.css' ).'</code><pre>';

						?>

<?php } else if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'import') {

	require_once 'salesforce_importer.php';
	sfwtli_importer_ui();

} else if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'form') {

					if( (!isset($form_id) || !$form_id ) && isset( $_GET['id'] ) ){
						$form_id = absint( $_GET['id'] );
					}else{
						$form_id = 0;
					}

					if(isset($_POST['mode']) && $_POST['mode'] == 'delete' && $form_id != 1 ){

						echo '<div id="message" class="updated"><p>' . __('Deleted Form #','salesforce') . $form_id . '</p></div>';

					} else if(isset($_POST['mode']) && $_POST['mode'] == 'clone'  ) {

						echo '<div id="message" class="updated"><p>' . __('Duplicated Form #','salesforce') . $form_id . '</p></div>';

					}else{

	if(!isset($form_id) && isset($_GET['id']))
		$form_id = (int) $_GET['id'];

	if( isset($_POST['form_id']) )
		$form_id = (int) $_POST['form_id'];

	if( !isset($form_id) || $form_id == 0 ){
		//generate a new default form
		end( $options['forms'] );
		$form_id = key( $options['forms'] ) + 1;
		$options['forms'][$form_id] = $this->default_form();
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

										$loc = 'banner-main';
										$ad = $this->get_ad_code( $loc );
										if( $ad ){
//											$link = $this->get_ad_link( $ad['id'], $loc, $ad['url'] );
                                                                                        $link = $this->get_ad_link( $ad['utm_content'], $ad['utm_medium'], $ad['url'], '',$ad['utm_source'],$ad['utm_campaign']);
											echo '<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>';
										}

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


										foreach ($options['forms'][$form_id]['inputs'] as $field => $input) {

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
											$content .= '<td><small>Label:</small> <input size="10" name="inputs['.$field.'_label]" type="text" value="'.esc_html(stripslashes($input['label'])).'"/>'; //</td>'.'<td>';

											$content .= '<br><small>Value:</small> <input size="10" name="inputs['.$field.'_value]" type="text" value="';
											if( isset($input['value']) ) $content .= esc_html(stripslashes($input['value']));
											$content .= '"/></td>';

											$opts = '';
											if( isset( $input['opts'] ) )
												$opts = $input['opts'];

											$content .= '<td><textarea rows="4" name="inputs['.$field.'_opts]"  >'.esc_textarea( stripslashes( $opts ) ).'</textarea></td>';
											$content .= '<td><input size="2" name="inputs['.$field.'_pos]" type="text" value="'.esc_html( $input['pos'] ).'"/></td>';
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
	row += '<td><input type="text" size="10" name="add_inputs['+i+'][field_name]"></td>';
	row += '<td><table>'
	row += '<tr><td><label for="add_inputs['+i+'][show]">Enabled</label></td><td><input type="checkbox" name="add_inputs['+i+'][show]"></td></tr>';
	row += '<tr><td><label for="add_inputs['+i+'][required]">Required</label></td><td><input type="checkbox" name="add_inputs['+i+'][required]"></td></tr>';
	row += '</table></td>';
	row += '<td><select name="add_inputs['+i+'][type]">'
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
	row += '<td><small>Label:</small><input size="10" type="text" name="add_inputs['+i+'][label]">';
	row += '<small>Value:</small><input size="14" type="text" name="add_inputs['+i+'][value]"></td>';
	row += '<td><textarea rows="4" name="add_inputs['+i+'][opts]"></textarea></td>';
	row += '<td><input type="text" size="2" name="add_inputs['+i+'][pos]" value="'+pos+'"></td>';
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
									$content .= '<input type="radio" name="type" value="lead" '.checked($options['forms'][$form_id]['type'],'lead',false).'> Web to Lead <br>';
									$content .= '<input type="radio" name="type" value="case"'.checked($options['forms'][$form_id]['type'],'case',false).'> Web to Case';
									$content .= '<br/><small>'.__('<b>Note:</b> Daddy Analytics does not support cases at this time.').'</small>';
									$content .= '</p>';


									$content .= '<p>';
									$content .= '<label>'.__('Lead Source:','salesforce').'</label><br/>';
									$content .= '<input type="text" name="source" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['source']).'">';

									$content .= '<br/><small>'.__('Lead Source (up to 40 characters) to display in Salesforce.com, use %URL% to include the URL of the page containing the form (need more characters? See the <a href="https://wordpress.org/plugins/salesforce-wordpress-to-lead/faq/" target="_blank">FAQ</a>). You can also use a field above to set the lead source (this value will not be used if a field named lead_source exists).').'</small>';

									if( !defined('SFWP2L_HIDE_ADS') )
										$content .= '<br/><br/><small>'.__('<b>Daddy Analytics</b> will populate the Lead Source field with the web source of the Lead (such as Organic - Google, Paid - Bing, or Google Adwords). Daddy Analytics will also populate the Salesforce Address field with the estimated GeoLocation of your Leads. <br><i>Leave the Lead Source field blank if you have a subscription to <a href="'.$this->get_ad_link( 'da_ls', 'text' ).'">Daddy Analytics.</a></i>').'</small>';

									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Return/Thanks URL:','salesforce').'</label><br/>';
									$content .= '<input type="text" name="returl" style="width:50%;" value="'.esc_html($options['forms'][$form_id]['returl']).'">';
									$content .= '<br/><small>'.__('e.g.http://yoursite.com/thanks/').'</small>';
									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Success Message:','salesforce').'</label><br/>';
									$content .= '<input type="text" name="successmsg" style="width:50%;" value="'.esc_html(salesforce_get_option('successmsg',$form_id,$options)).'">';
									$content .= '<br/><small>'.__('Overrides the default message for this form.(leave blank to use the global setting)').'</small>';
									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Submit button text (override):','salesforce').'</label><br/>';
									$content .= '<input type="text" name="submitbutton" style="width:50%;" value="'.esc_html(stripslashes( salesforce_get_option('submitbutton',$form_id,$options) )).'">';
									$content .= '<br/><small>'.__('Overrides the default message for this form.(leave blank to use the global setting)').'</small>';
									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Required fields text (override):','salesforce').'</label><br/>';
									$content .= '<input type="text" name="requiredfieldstext" style="width:50%;" value="'.esc_html(stripslashes( salesforce_get_option('requiredfieldstext',$form_id,$options) )).'">';
									$content .= '<br/><small>'.__('Overrides the default message for this form (leave blank to use the global setting).').'</small>';
									$content .= '</p>';

									/* Add a Subject line to the User email on a per-form basis */
									$content .= '<p>';
									$content .= '<label>' . __( 'User CC email subject:', 'salesforce' ) . '</label><br/>';
									$content .= '<input type="text" name="cc_email_subject" style="width:50%;" value="' . esc_html( salesforce_get_option( 'cc_email_subject', $form_id, $options ) ) . '">';
									$content .= '<br/><small>' . __( 'Subject of the email when sending out a copy to the user.(leave blank to use the global setting)' ) . '</small>';
									$content .= '</p>';


									$content .= '<p>';
									$content .= '<label>'.__('Captcha:','salesforce').'</label><br/>';

									$content .= '<input type="radio" name="captchaform" value=""'.checked(salesforce_get_option('captchaform',$form_id,$options),'',false).'> Use global setting <br>';
									$content .= '<input type="radio" name="captchaform" value="enabled" '.checked(salesforce_get_option('captchaform',$form_id,$options),'enabled',false).'> Enabled for this form<br>';
									$content .= '<input type="radio" name="captchaform" value="disabled"'.checked(salesforce_get_option('captchaform',$form_id,$options),'disabled',false).'> Disabled for this form';


									$content .= '<br/><small>'.__('Overrides the default captcha settings for this form.').'</small>';
									$content .= '</p>';


									$content .= '<p>';
									$content .= '<label>'.__('Required Fields Text Location:','salesforce').'</label><br/>';
									$content .= '<input type="radio" name="requiredfieldstextpos" value=""'.checked( salesforce_get_option('requiredfieldstextpos',$form_id,$options),'',false).'> Below Form <br>';
									$content .= '<input type="radio" name="requiredfieldstextpos" value="top" '.checked(salesforce_get_option('requiredfieldstextpos',$form_id,$options),'top',false).'> Above Form <br>';
									$content .= '<input type="radio" name="requiredfieldstextpos" value="hidden"'.checked(salesforce_get_option('requiredfieldstextpos',$form_id,$options),'hidden',false).'> None';
									$content .= '</p>';


									$content .= '<p>';
									$content .= '<label>'.__('Label Location (Content):','salesforce').'</label><br/>';

									$label_location = trim( $options['forms'][$form_id]['labellocation'] );

									if( !$label_location )
										$label_location = 'top-aligned';

									$content .= '<input type="radio" name="labellocation" value="top-aligned" '.checked( $label_location, 'top-aligned', false ).'> Top Aligned <br>';
									$content .= '<input type="radio" name="labellocation" value="left-aligned"'.checked( $label_location, 'left-aligned', false ).'> Left Aligned <br>';
									$content .= '<input type="radio" name="labellocation" value="placeholders"'.checked( $label_location, 'placeholders', false ).'> Placeholders';
									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Label Location (Sidebar):','salesforce').'</label><br/>';

									$label_location_sidebar = trim( $options['forms'][$form_id]['labellocationsidebar'] );
									if( !$label_location_sidebar )
										$label_location_sidebar = 'top-aligned';

									$content .= '<input type="radio" name="labellocationsidebar" value="top-aligned" '.checked( $label_location_sidebar, 'top-aligned', false ).'> Top Aligned <br>';
									$content .= '<input type="radio" name="labellocationsidebar" value="left-aligned"'.checked( $label_location_sidebar, 'left-aligned', false ).'> Left Aligned <br>';
									$content .= '<input type="radio" name="labellocationsidebar" value="placeholders"'.checked( $label_location_sidebar, 'placeholders', false ).'> Placeholders';
									$content .= '</p>';

									$content .= '<p>';
									$content .= '<label>'.__('Auto Formatting:','salesforce').'</label><br/>';

									$content .= '<input type="checkbox" name="donotautoaddcolontolabels" value="1" '.checked( $options['forms'][$form_id]['donotautoaddcolontolabels'], '1', false ).'> Do not automatically add a colon to labels <br>';
									$content .= '</p>';

									$content .= '<input type="hidden" name="form_id" id="form_id" value="'.$form_id.'">';

									$content .= '<p>';
									$content .= '<label>'.__('Salesforce.com Organization ID (override):','salesforce').'</label><br/>';
									$content .= '<input type="text" name="org_id" style="width:50%;" value="'.esc_html(stripslashes(salesforce_get_option('org_id',$form_id,$options))).'">';
									$content .= '<br/><small>'.__('Overrides the default org_id for this form (leave blank to use the global setting).').'</small>';
									$content .= '</p>';


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

							<?php if( !empty($_GET['id']) ){ ?>
							<form action="" method="post" id="salesforce-clone">
							<?php if (function_exists('wp_nonce_field')) { wp_nonce_field('salesforce-udpatesettings'); } ?>
								<input type="hidden" value="clone" name="mode"/>
								<input type="hidden" value="<?php echo $form_id; ?>" name="form_id"/>
								<input type="submit" name="submit" class="button-secondary" value="Duplicate this form">
							</form>
							<?php } ?>
<?php } ?>
				<?php } ?>

				<?php //echo '<p>'.salesforce_back_link($this->plugin_options_url()).'</p>'; ?>

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

								$this->postbox('usesalesforce',__('How to Use This Form','salesforce'),__('<p>To embed this form, copy the following shortcode into a post or page:</p><p> [salesforce form="'.$fid.'"] </p>','salesforce'));

							}else{

								$this->postbox('usesalesforce',__('How to Use This Plugin','salesforce'),__('<p>To embed a form, copy the following shortcode into a post or page:</p><p> [salesforce form="X"] </p><p>Replace X with the form number for the form you want to show.</p><p><i>Make sure you have entered all the correct settings on the left, including your Organization ID.</i></p>','salesforce'));

							}

							$this->plugin_like(false);


							$content = '<p>'.__('<b>Community</b><br>If you have any problems with this plugin, ideas for improvements, or  feature requests, please talk about them in the community support forum.<p><i>Be sure to read the <a target="_blank" href="http://wordpress.org/support/topic/support-guidelines/">support guidelines</a> before posting.</i></p>','ystplugin').'</p><p><a target="_blank" class="button-secondary" href="http://wordpress.org/support/plugin/'.$this->hook.'">'.__("Get Community Support",'ystplugin').'</a></p>';

							$content .= '<p>'.__('<b>Premium</b><br>Need guaranteed support, customization help, or want to sponsor a feature addition?','ystplugin').'</p><p> <a target="_blank" class="button-secondary" href="http://thoughtrefinery.com/plugins/support/?plugin='.$this->hook.'">'.__("Request Premium Support",'ystplugin').'</a></p>';


							$this->postbox($this->hook.'support', 'Need support?', $content);

							$loc = 'banner-side';

							$ad = $this->get_ad_code( $loc );

							if( $ad ){

								$link =$this->get_ad_link( $ad['id'], $loc, $ad['url'] );

								$this->postbox('usesalesforce',__('Plugin Sponsor: Daddy Analytics','salesforce'),__('<p style="text-align: center;"><a href="'.$link.'" target="_blank"><img src="'.plugins_url( $ad['content'], dirname(__FILE__)).'"></a></p>','salesforce'));
							}

							$this->postbox('usesalesforce',__('Want to contribute?','salesforce'),__('<p class="aligncenter">Pull requests welcome!<br><br>

							<a class="button-secondary" href="https://github.com/nciske/salesforce-wordpress-to-lead" target="_blank">Fork me on GitHub</a><br><br>

							<a class="button-secondary" href="https://github.com/nciske/salesforce-wordpress-to-lead/issues" target="_blank">Submit an issue</a>




							</p>','salesforce'));


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
