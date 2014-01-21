=== SS Downloads ===
Contributors: strangerstudios
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZMZ467TYFFDEQ
Tags: file, download, files, secure files, downloads, email capture 
Requires at least: 3.8
Tested up to: 3.8
Stable tag: 1.5

Embed forms in your pages and posts that accept an email address in exchange for a file download.

== Description ==

Adds a short code like [download file="path_to_file"] that embeds a form in the post asking for an email address before showing a link to a file for download. Great for promoting white papers and other digital assets on your site.

Live demo: http://www.strangerstudios.com/blog/2010/07/ss-downloads-wordpress-plugin/

The plugin works in 3 parts.

1. The short code to add the form to your pages.

2. The logic to check (using session variables) if the user has provided an email address before showing either the email capture form or the download link.

3. A script to serve files securely. It checks for the same session variable before delivering the file. Files can be located outside the web directory or servered from the uploads folder, etc, with an obfuscated URL.

The look of the email and download forms can be changed by copying files from the /css/ and /templates/ folder of the plugin into your active theme folder. Rename the files ssd-original_file_name.php/css (e.g. ssd-download.php or ssd-ss-downloads.css) and edit as needed.

== Installation ==

1. Upload the `ss-downloads` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add shortcodes like [download file="http://path_to_file" title="optional title"] or [download file="wp-content/uploads/2010/7/filename.ext"] to your blog posts and pages.
1. Navigate to Tools --> SS Downloads to view a list of collected emails and stats on file downloads.
1. You can also change the settings on that page to either require signups or to send the file (or a link to the file) by email.

== Frequently Asked Questions ==

= How can I better protect the location of my source files? =

For compatibility reasons, the plugin now redirects file requests to the source file instead of serving the file via script. For most people, the defaults settings are good enough for protecting your downloads. However, l33t haXors may be able to note the true location of a download by spying in on this redirect process. For added security, you can use our "ssdownloads_getfile_redirect" hook to tell SS Downloads to serve files through a PHP script which will better mask the location of the source file, and even allow you to keep the source files outside of your sites root directory (so the URL of the file is not navigable at all). Note that there is no 100% solution to stop all pirating. (Every little bit helps though.) 

To tell SS Downloads to serve files via PHP script, add code like the following to your functions.php file: (this code will not work on all servers, please test your setup after applying this code)

function my_ssdownloads_getfile_redirect($s)
{
    return false;	//tells SS Downloads to use the PHP script to serve the file instead of redirecting to the source file
}
add_action("ssdownloads_getfile_redirect", "my_ssdownloads_getfile_redirect");

= I get odd errors when browsing to a page with a shortcode on it. =

You might be able to solve this by specifying "file_get_contents" or "cURL" as "Template Method" in the SS Downloads settings, although the "Let WordPress Choose" options should work best on most setups.

= How can I change the look of the email or download forms? =

To modify the templates:

1. Copy the .php file of the template you would like to customize to your active theme folder. 
1. Name the file ssd-{template name}.php, e.g. ssd-download.php
1. Change the file as needed.

You can also modify the CSS file:

1. Copy the ss-downloads.css from the css folder of the plugin to your active theme folder. 
1. Name the file ssd-ss-downloads.css
1. Change the file as needed.

= I get "file not found" or other include/require errors when using the plugin. =

The plugin is probably having trouble finding your plugins directory or other files. This happens sometimes if your WordPress install is in a sub folder, or your plugins directory is in a different spot.

First, let me know at jason@strangerstudios.com or on the WP forums. Others may be having the same problem, and I may be able to tweak the plugin to support your case.

Second, try overriding the SSD_PLUGIN_URL constant using our "ssdownloads_plugins_url" hook. Add this code to your functions.php:

function my_ssdownloads_plugins_url($url)
{
    return "http://www.yoursite.com/wp-content/plugins/ss-downloads";	//change this to be the URL path to your ss-downloads plugin folder, no trailing slash
}
add_action("ssdownloads_plugins_url", "my_ssdownloads_plugins_url");

= Email me at jason@strangerstudios.com to ask a question =

I will answer your question and post it here.

== Screenshots ==

1. Email capture form.
1. Download link.
1. Addresses and stats in admin.

== Changelog ==
= 1.5 =
* Important update that fixes some cross site scripting vulnerabilities.

= 1.4.4.1 =
* Forgot to include the exportemails.php file in the repo. So update to 
get that file to be able to export emails.

= 1.4.4 =
* Added a "name and email" option to the "required" dropdown and a matching template file to collect name and email address.
* Added a "name" column to the justemails (ironic, I know) and ss_downloads tables.
* Added name column to download export.
* Added names when available to the "Collected Emails Addresses" list.
* Added an email export and clear table option.
* Using plugin_url to get URLs for the exports now instead of get_bloginfo('url')

= 1.4.3 =
* Removed short tags (<?=) for greater compatibility across servers.

= 1.4.2 =
* Renamed the mimetype class being used to avoid conflicts.

= 1.4.1 =
* Fixed default values on plugin activation.

= 1.4 =
* Now using the WordPress HTTP API to load the templates, which should work out of the box on all systems.
* Updated template system to look for files in the current theme folder
* Added hooks to set values in setup.php so changes aren't overwritten in an upgrade
* Added a way to show messages in the ss-downloads admin page (e.g. upgrade notes, or paidmembershipspro.com ads)
* Added a link to clear download records on admin page.

= 1.3.3 =
* Fixed changing the download shortcode.
* IMPORTANT! Changed the GETFILE_REDIRECT constant to default to true. This means that there will be slightly less security when showing downloads, however the plugin will work across more systems out of the box. If you want that extra level of security (and your server can support) it, add - define("GETFILE_REDIRECT", true); - to your functions.php or somewhere else in your code to run downloads through the getfile.php script.

= 1.3.2 =
* Fixed mimetype class conflict.

= 1.3.1 =
* Fixed the GETFILE_REDIRECT use in getfile.php

= 1.3 =
* Added cURL support and option
* Added ability to change the shortcode
* New "Email Sent" template
* Added "reset" service
* Added documentation on customizing setup.php

= 1.2.2 =
* Fixed definitions in setup.php to work with more hosts

= 1.2.1 =
* Fixed bug with echo statements in addemail.php
* Fixed bug with links in email when sending link by email

= 1.2 =
* Can change settings to require account creation (instead of just an email address).
* Can change settings to email the file as attachment instead of showing a link.
* Can also send a link to the file by email (instead of showing it on the site).

= 1.0 =
* This is the launch version. No changes yet.

== Upgrade Notice ==
= 1.4.2 =
This update only renamed the mimetype class used to ssd_mimetype to avoid conflicts. Not necessary unless other plugins aren't checking for that class first and causing conflicts.

= 1.4.1 =
This is a large update with many improvements, including use of the WordPress HTTP API for better compatibility and a better templating system.

Please note however that some changes may impact how the plugin works or whether is works at all on your system. Customizations you have maid to setup.php or the template files will be overridden by an upgrade. There are new systems in place to tweak the setup.php values via custom hooks and to change templates by copying versions of those files into your theme folder. If you have made customizations to your version of the plugin, you should backup your version of the ss-downloads folder and reproduce those edits via the new systems. Notes on how to tweak setup.php and the templates can be found in those files and the FAQ here.
