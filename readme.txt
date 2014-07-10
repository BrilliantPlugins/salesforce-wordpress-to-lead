=== WordPress-to-lead for Salesforce CRM ===
Contributors: stonydaddydonkeylabscom, nickciske
Tags: crm, contact form, contactform, wordpress to lead, wordpresstolead, salesforce.com, salesforce, salesforce crm, contact form plugin, contact form builder, Wordpress CRM
Requires at least: 3.5.2
Tested up to: 3.9.1
Stable tag: 2.3.8
License: GPLv2
Donate link: http://daddyanalytics.com/donate-wordpress-lead-salesforce-plugin/

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your Salesforce.com account!

== Description ==

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your [Salesforce CRM](http://www.salesforce.com) account! People can enter a contact form on your site, and the lead (or case) goes straight into Salesforce CRM: no more copy pasting lead info, no more missing leads: each and every one of them is in Salesforce.com for you to follow up.

### Check out the screencast
[youtube http://www.youtube.com/watch?v=hnMzkxPUIyc]

You can fully configure all the different settings for the form, and then use a shortcode to insert the form into your posts or pages, or you can use the widget that comes with the plugin and insert the form into your sidebar!

Please see this [WordPress-to-Lead Demo video](http://www.youtube.com/watch?v=hnMzkxPUIyc) to get a full grasp of the power this plugin holds, and visit the [Salesforce WordPress page]( http://www.salesforce.com/form/signup/wordpress-to-lead.jsp?d=70130000000F4Mw). Check out this page to learn more about [CRM for Small Business](http://www.salesforce.com/smallbusinesscenter/).

#### Previous contributors:
* Joost de Valk (http://profiles.wordpress.org/joostdevalk/)
* ModernTribe (http://profiles.wordpress.org/moderntribe/)

== Screenshots ==

1. An example form generated with WordPress-to-Lead for Salesforce CRM (with optional CAPTCHA) -- both post and widget forms are shown in the TwentyEleven theme
2. The backend administration for WordPress-to-Lead for Salesforce CRM
3. The new form editor (multiple forms, hidden fields, add new fields, thanks URL, lead source per form)

== Installation ==

1. Upload the `plugin` folder to the `/wp-content/plugins/` directory or install via the Add New Plugin menu
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Salesforce.com Organization ID on the WordPress-to-Lead plugin configuration page.

== Frequently Asked Questions ==

= How do I setup Web to Lead/Case for my SalesForce Account? =

[Setting Up Web-to-Lead](http://login.salesforce.com/help/doc/en/setting_up_web-to-lead.htm)

[Setting Up Web-to-Case](http://login.salesforce.com/help/doc/en/setting_up_web-to-case.htm)

= How do I setup a Web to Case form? =
Choose _Web to Case_ in the **Form Settings** (bottom of the form editor page).

= Where do I find my Salesforce organization ID? =
To find your Organization ID, do the following steps:

1. Log in to your SalesForce.com account
2. Go to Setup &raquo; Company Profile &raquo; Company Information
3. You'll find the Organization ID in the lower right hand corner of your screen

= How do I use a SalesForce custom field? =

1. Go to Setup &raquo; Customize &raquo; Leads &raquo; Fields
1. If your custom field does not exist yet, create it now.
1. Find the API Name for your field (e.g. Custom_Field_Example__c). If it doesn't end in "__c" it's not the API name and will not work.
1. Add a new field to your form using the form editor on the plugin admin screen
1. Enter the API Name as the field name (left most box), then fill out the other fields normally (make sure to enable the field!).
1. Save your changes -- new submissions will now post that custom field to SalesForce.

= How do I use the checkbox field? =
Like any other field. Note that it is a single checkbox, not a checkbox list.

_Checkbox lists and radio buttons will be in a future update._

= How do I use the select (picklist) field? =
Use it like any other field -- however you'll need to specify the options (and optional values) for each field using the options box (far right).

The value box for a select list is the default value (the one selected on a fresh form).

`
//Use same data for display and value passed to SF
one|two|three

//Use different data for display and value passed to SF, require user to select something (assuming field is required)
Select One: | name1:value1 | name2:value2

//Use different data for display and value passed to SF
name1:value1 | name2:value2
`

_Note: Leading & trailing whitespace is trimmed when names and values are displayed, so feel free to use spaces to make things more readable._

= How do I use the HTML field? =
1. Optionally enter a label (field will display full width if a label is not entered.
2. Enter HTML code in the options box.

_Note: You cannot use the HTML box to enter a custom field, as only "known" fields are submitted to salesforce and HTML fields are not submitted (just displayed). Be careful to avoid the `<form>` or `</form>` tags in an HTML field as they will likely break your form._

= How do I change the order of input fields? =
Right now, the only way of ordering input fields is by changing the position numbers on the right hand side of the input fields table in the admin settings. Drag and drop re-ordering is on the roadmap.

= How do I apply my own styling to the form? =
Instructions for disabling or overriding the CSS are included on the plugin settings screen (see Style Settings).

= What does "Use WPCF7 CSS integration" do? =
This option adds the WPCF7 classes to the form fields so you get the WPCF7 CSS styles applied (if that plugin is also activated).

= Is it possible to make multiple forms with this plugin? =
Yes, version 2.0 introduces this feature. Version 2.1 allows you to duplicate forms to reduce re-entering data.

= How do I change the Lead Source that shows up in Salesforce? =
You can easily change this by going into the WordPress-to-Lead admin panel and, under form settings, changing the Lead Source for that form. Daddy Analytics uers can set this to blank to have it automatically filled.

= Can I change the submit button? =
Of course you can! Go into the WordPress-to-Lead admin panel and, under Form Settings, change the text from the default "Submit" to whatever you'd like it to be!

= Will I lose data if I upgrade to 2.0? Do I need to change anything? =
Nope! The plugin will migrate your existing data to the new format. Your existing form will become Form 1 and be output by the [salesforce] shortcode).

= How do I show my other forms? =
Just use `[salesforce form="X"]` (X is the form number).
Or select a form number in the widget.

= I put my campaign name in the Campaign_ID field but it's not working =
The Campaign_ID field requires the Campaign ID -- the name will not work. To find the Campaign_ID, go your the campaign page and look in the URL bar for the ID:

e.g. https://salesforce.com/621U000000IJat

In this example, 621U000000IJat is the Campaign_ID -- make sure you use the ID from the campaign you want the lead attached to and not the example ID shown here.

= Does the return/thanks URL have to be on my site? =
No, as long as it's a valid URL it will work. However it should be an absolute URL regardless of where it is located.
e.g. http://yoursite.com/thanks/ not just /thanks/

= Is there a limit to how many leads can be captured? =
While the plugin has no limits, SalesForce does limit API calls per day:

_The daily limit for Web-to-Lead requests is 500. If your organization exceeds its daily Web-to-Lead limit, the Default Lead Creator (specified in the Web-to-Lead setup page) receives an email containing the additional lead information._

See also: [How many leads can we capture from our website?](https://help.salesforce.com/apex/HTViewHelpDoc?id=faq_leads_how_many_leads.htm&language=en_US#faq_leads_how_many_leads)



== Filters and Hooks ==

**Note:**
* These should be placed in your active theme functions.php or a functionality plugin.
* Never edit a plugin directly (unless you understand the implications of doing so).
* You can use Pluginception to create a custom plugin for these to make them independent of your theme: https://wordpress.org/plugins/pluginception/

= Filters =

**salesforce_w2l_api_url**

Change the API url the plugin posts data to. Passes the form type (lead or case)

`
add_filter( 'salesforce_w2l_api_url', 'my_w2l_api_url', 10, 2 );

function my_w2l_api_url( $url, $form_type ){
	return 'https://my.custom-api-url.com/something/';
}
`

**sfwp2l_validate_field**

Provide your own validation logic for each field.

_An error array is passed in, along with the field name, submitted value, and field configuration (type, default value, required, etc)._

Here's an example of blocking common free email providers:

`
add_filter('sfwp2l_validate_field','block_non_biz_emails', 10, 4);

function block_non_biz_emails( $error, $name, $val, $field ){

	if( $name == 'email' ){

		$non_biz_domains = array( 'gmail.com', 'yahoo.com', 'hotmail.com', 'aol.com' );

		$domain = array_pop(explode('@', $val));

		if( in_array( $domain, $non_biz_domains ) ){
			$error['valid'] = false;
			$error['message'] = 'Please enter a business email addresss.';
		}

	}

	return $error;
}
`

You can add to the $non_biz_domains to block other providers as well.

**salesforce_w2l_form_html**

HTML of the form before it's returned to WordPress for display

**salesforce_w2l_cc_user_from_name**

Change from name (user confirmation)

**salesforce_w2l_cc_user_from_email**

Change from email (user confirmation)

**salesforce_w2l_cc_admin_from_name**

Change from name (admin notification)

**salesforce_w2l_cc_admin_from_email**

Change from email (admin notification)

**salesforce_w2l_cc_admin_email_list**

Adding this code to your functions.php file will add 3 emails to the list. You can add as many as you want and each will get an admin notification email.

`
add_filter('salesforce_w2l_cc_admin_email_list','salesforce_add_emails');

function salesforce_add_emails( $emails ){

//uncomment line below to remove site admin
//unset($emails[0]);

$emails[]='email@domain.com';
$emails[]='email2@domain.com';
$emails[]='email3@domain.com';

return $emails;
}
`

**salesforce_w2l_cc_user_email_content**

**salesforce_w2l_cc_admin_email_content**

Allows you to filter (append, prepend, modify) the email message content sent to the user or admin(s).

`
add_filter('salesforce_w2l_cc_user_email_content','salesforce_filter_user_message', 10, 1);

function salesforce_filter_user_message( $message ){

	$message = 'Before the user message' . "\r\n\r\n" . $message . "\r\n\r\n" . 'After the user message';

	return $message;

}

add_filter('salesforce_w2l_cc_admin_email_content','salesforce_filter_admin_message', 10, 1);

function salesforce_filter_admin_message( $message ){

	$message = 'Before the admin message' . "\r\n\r\n" . $message . "\r\n\r\n" . 'After the admin message';

	return $message;

}
`

**salesforce_w2l_field_value**

**salesforce_w2l_field_value_{Form ID}_{Field Name}**

Allows you to filter the value of a field before it is output to dynamically populate it with a value, auto set it based on another value, etc.

Note that the second filter requires you to replace {Form ID} and {Field Name} to be replaced with the relevant form id and field name.

If you need access to the field or form settings in your filter you can use:

`$field = salesforce_get_field( $field_name, $form_id );`

`$form = salesforce_get_form( $form_id );`

Examples:

`
// Filter all field of all forms
add_filter( 'salesforce_w2l_field_value', 'salesforce_w2l_field_value_example', 10, 3 );
function salesforce_w2l_field_value_example( $val, $field, $form ){

	// Target a specific form
	if( $form == 1 )
		$val = 'TEST';

	// Target a specific field on all forms
	if( $field == 'test_field' )
		$val = 'TEST3';

	// Target a specific field on a form
	if( $form == 1 && $field == 'test_field' )
		$val = 'TEST2';

	return $val;

}

// Filter a specific field on a specific form
// salesforce_w2l_field_value_{Form ID}_{Field Name}
add_filter( 'salesforce_w2l_field_value_1_tester', 'salesforce_w2l_field_value_1_tester_example', 10, 1 );
function salesforce_w2l_field_value_1_tester_example(  $val ){

	return '123';

}
`

**salesforce_w2l_form_action**

Allows you to remove the form action.

`
// Remove Form Action
add_filter( 'salesforce_w2l_form_action', 'salesforce_w2l_form_action_example', 10, 1 );
function salesforce_w2l_form_action_example(  $action ){

	return '';

}
`

**salesforce_w2l_lead_source**

Allows you to remove the form action.

`
// Alter Lead Source
add_filter( 'salesforce_w2l_lead_source', 'salesforce_w2l_lead_source_example', 10, 2 );
function salesforce_w2l_lead_source_example(  $lead_source, $form_id ){

	if( $form_id == 1 )
		return 'Example Lead Source for Form #1 on page id #'.get_the_id();

	return $lead_source;

}
`

== Changelog ==

= 2.3.8 =
* Add lead source back into admin email
* Add filter for lead source

= 2.3.7 =
* Fix issue where deleting form title made the edit link disappear
* Fix settings url in alert to go to settings tab
* Add filter to allow form action to be removed

= 2.3.6 =
* Fix issue with OID and other fields being appended to the user confirmation email
* Add `salesforce_w2l_cc_user_suppress_fields` filter to allow supression of fields in the user confirmation email

= 2.3.5 =
* Readme improvements

= 2.3.4 =
* Fix bug in load_plugin_textdomain call
* Readme improvements
* Change value input size to match label field

= 2.3.3 =
* Fix confusing wrapping on form editor on very wide screens (reported by cindybou)
* Change name of and add note to filters and hooks section

= 2.3.2 =
* Add filter for field values
* Add salesforce_get_form and salesforce_get_field helper functions

= 2.3.1 =
* Version number bumps

= 2.3 =
* Allow some settings to be overridden per form (Success Message, Captcha, OrgId, etc)
* Support for option to use placeholders instead of labels (per form)
* Remove newlines in form HTML that were being converted to <br> upon output
* Add Settings link to plugin list screen
* Grey out non enabled fields in admin
* Set a default checkbox width as some themes think input{ width: 100% } is a good idea...
* All default CSS now uses relative sizes
* Prevent multiple submission of form data (even if it's the same form id on the page multiple times)
* Remember "send me a copy" between submits
* Defined DONOTCACHEPAGE if captcha is enabled
* Fix PHP notices when form is submitted
* Update ads and landing page URLs
* Add support links to plugin list page, restore WP.org plugin link
* Load text domain on plugin init, add .pot file

= 2.2.5 =
* Fix PHP warnings and notices

= 2.2.4 =
* Add email and captcha error to settings page.

= 2.2.3 =
* Added filter to user and admin email content

= 2.2.2 =
* Fix deprecated (in PHP 5.3) ereg_replace functon in captcha lib

= 2.2.1 =
* Fix untranslatable string (invalid email)
* Set input height to auto (18px causing issues with themes containing plentiful padding)
* Change "Daddy Analytics Webform URL" to "Daddy Analytics Web to Lead URL ID"
* Fixed slashes issues in field labels, select options and field labels in emails
* Test using new deploy script: https://github.com/eyesofjeremy/Github-to-WordPress-Plugin-Directory-Deployment-Script

= 2.2 =
* Bug: Fixed checkboxes not retaining checked state after submit
* Bug: Only output DA JS when token has been entered
* Wrapped all output in a div tag to allow styling of success and error messages
* Added #anchor to action to keep form on screen after submit when not the first item in a page (may not work in older versions of IE)
* Add per field validation filter and error output (thanks to http://HomeStretchMktg.com for sponsoring this feature)
* Added tabs to plugin settings page
* Moved form list to its own tab (vs the bottom of the settings screen)
* Added syntax highlighting to defaut CSS example on new Styling tab
* Tested and working in WordPress 3.8

= 2.1.1 =
* Fixes a bug that caused the organization id field to be hidden on new installs

= 2.1 =
* Add drop down field type (thanks jbuchbinder)
* Improve form HTML (thanks jbuchbinder)
* Add from and reply to options for emails (thanks jbuchbinder)
* Add delete checkbox to form editor (thanks jbuchbinder)
* Add HTML field type (thanks jbuchbinder)
* Add simple checkbox field (thanks jbuchbinder)
* Add ability to duplicate forms (thanks jbuchbinder)
* Add WPCF7 CSS integration option (thanks jbuchbinder)
* Add wrapper divs with class names to visible fields
* Remove Powered by SF for all forms
* Make required field indicator consistent with message
* Comments to lead option (thanks simonwheatley)
* Global DaddyAnalytics settings added to make integration easier
* Added filters to aid in extending the plugin
* Hide fields with no label in admin and user email
* Required fields now trim whitespace from the value before validation (e.g. a space or tab is no longer a valid value)
* Fixed checkbox/label alignment
* Readme improvements
* Added daily limit info to FAQ
* Removed previous contributors no longer involved in plugin development, added credit to readme
* Refactored and cleaned up codebase
* Added filters to allow code based overrides of select features (see Other Notes for details)
* Added Web to Case option (per form setting)
* Fixed first field being added having a duplicate position to last field
* Select fields can have a default value set

= 2.0.3 =
* Captcha image now works on subfolder installs (e.g. /wordpress/)
* Removed captcha dependence on including wp-load.php

= 2.0.2 =
* Small formatting fixes (checkbox spacing and submit button CSS)

= 2.0.1 =
* Fixed issue with captcha URL being broken on some installs
* Added several filters, including one to allow editing of the distribution list for new lead notifications and one to allow filtering of the form HTML before output
* Fixed bug where captcha would wrap outside form on some themes
* Fixed bug where forms other than id 1 did not show field labels in emails
* Fixed bug causing unexpected output upon activation
* Fixed bug that caused form to always be in 'sidebar' mode
* Now supports more than 1 form per page
* Forms now have a unique ID for use with CSS and jQuery: salesforce_w2l_lead_[ID] and salesforce_w2l_lead_[ID]_sidebar
* Fixed a bunch of notices and warnings

= 2.0 =
* Improved internationalization
* Multiple forms can be created and inserted via shortcode or widget
* Spam protection (with optional captcha)
* Fixed "Cannot use object of type WP_Error as array" bug
* Fixed bug that showed successful submissions as "Failed to connect to SalesForce.com"
* Hidden fields can now be used
* Campaign_ID can now be set per form
* Widget now hides description text upon submission
* Admins can receive an email of submissions
* Users can request a copy of their submission (if enabled)
* Custom return/thanks URL can be defined per form
* Country field added

= 1.0.5 =
* Fix in backend security, preventing XSS hack in the backend.

= 1.0.4 =
* CSS fix for when sidebar widget and contact form are on the same page.

= 1.0.3 =
* Fix in email verification.

= 1.0.2 =
* One more escape, plus a check to see whether the email address entered is valid.

= 1.0.1 =
* Added escaping around several fields to prevent XSS vulnerabilities.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.2 =
Includes new CSS rules: make sure to update any custom CSS files with the new *.sf_field span.error_message* rule.
Changes how error messages are output. Please review your *error message* on the settings screen to make sure it still makes sense in the new context.

= 2.1 =
This version includes most of the functionality in the "jbuchbinder" GitHub fork many users installed. Most users should not experience any issues upgrading. However, the "current date" field is not included in this release.

= 2.0 =
This version fixes a bug that caused the plugin to appear broken, despite sending leads to SalesForce.com.
