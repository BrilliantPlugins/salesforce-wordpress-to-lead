=== WordPress-to-lead for Salesforce CRM ===
Contributors: joostdevalk, nickciske, moderntribe
Tags: crm, contact form, contactform, wordpress to lead, wordpresstolead, salesforce.com, salesforce, salesforce crm, contact form plugin, contact form builder, Wordpress CRM
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 2.0

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your Salesforce.com account!

== Description ==

WordPress-to-Lead for Salesforce CRM creates a solid integration between your WordPress install(s) and your [Salesforce CRM](http://www.salesforce.com) account! People can enter a contact form on your site, and the lead goes straight into Salesforce CRM: no more copy pasting lead info, no more missing leads: each and every one of them is in Salesforce.com for you to follow up.

### Check out the screencast
[youtube http://www.youtube.com/watch?v=hnMzkxPUIyc]

You can fully configure all the different settings for the form, and then use a shortcode to insert the form into your posts or pages, or you can use the widget that comes with the plugin and insert the form into your sidebar!

Please see this [WordPress-to-Lead Demo video](http://www.youtube.com/watch?v=hnMzkxPUIyc) to get a full grasp of the power this plugin holds, and visit the [Salesforce WordPress page]( http://www.salesforce.com/form/signup/wordpress-to-lead.jsp?d=70130000000F4Mw). Check out this page to learn more about [CRM for Small Business](http://www.salesforce.com/smallbusinesscenter/).

== Screenshots ==

1. An example form generated with WordPress-to-Lead for Salesforce CRM (with optional CAPTCHA) -- both post and widget forms are shown in the TwentyEleven theme
2. The backend administration for WordPress-to-Lead for Salesforce CRM
3. The new form editor (multiple forms, hidden fields, add new fields, thanks URL, lead source per form)

== Installation ==

1. Upload the `plugin` folder to the `/wp-content/plugins/` directory or install via the Add New Plugin menu
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Salesforce.com Organisation ID on the WordPress-to-Lead plugin configuration page.

== Frequently Asked Questions ==

= Where do I find my Salesforce organisation ID? =
To find your Organisation ID, do the following steps:
* log in to your SalesForce.com account
* go to Setup &raquo; Company Profile &raquo; Company Information
* you'll find the Organisation ID in the lower right hand corner of your screen

= How do I change the order of input fields? =
Right now, the only way of ordering input fields is by changing the position numbers on the right hand side of the input fields table in the admin settings.

= How do I apply my own styling to the form? =
Disable the "Use Form CSS" checkbox, and copy the form css to your own css file, then start modifying it!

= Is it possible to make multiple forms with this plugin? =
Yes, version 2.0 introduces this feature.

= How do I change the Lead Source that shows up in Salesforce? =
You can easily change this by going into the WordPress-to-Lead admin panel and, under form settings, changing the Lead Source for that form.

= Can I change the submit button? =
Of course you can! Go into the WordPress-to-Lead admin panel and, under Form Settings, change the text from the default "Submit" to whatever you'd like it to be!

= Will I lose data if I upgrade? Do I need to change anything? =
No, the plugin will migrate your existing data to the new format. Your existing form will become Form 1 and be output by the [salesforce] shortcode).

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

== Changelog ==

= 2.0 =
* Improved internationalization
* Multiple forms can be created and inserted via shortcode or widget
* Spam protection (with optional captcha)
* Fixed "Cannot use object of type WP_Error as array" bug
* Fixed bug that showed successful submissions as "Failed to connect to SalesForce.com"
* Hidden fields can now be used
* Campaign_ID can now be set per form
* Widget now hides description text upon submission
* Admins can recieve an email of submissions
* Users can request a copy of their submission (if enabled)
* Custom return/thanks URL can be defined per form
* Country field added

= 1.0.5 =
* Fix in backend security, preventing XSS hack in the backend.

= 1.0.4 =
* CSS fix for when sidebar widget and contactform are on the same page.

= 1.0.3 =
* Fix in email verification.

= 1.0.2 =
* One more escape, plus a check to see whether the email address entered is valid.

= 1.0.1 =
* Added escaping around several fields to prevent XSS vulnerabilities.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.0 =
This version fixes a bug that caused the plugin to appear broken, despite sending leads to SalesForce.com.