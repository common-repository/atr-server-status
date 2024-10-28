=== ATR Server Status ===
Contributors: Allan Thue Rehhoff, Nicklas Thomsen
Tags: server status, check server, service status, check service, server, status check, is it down, server check, check server status
Requires at least: 4.0
Tested up to: 5.8.1
Stable tag: 1.5.1
License: GPLv3
Requires PHP: 5.6

== Description ==
**Important notice**
This plugin is no longer in active development, do not use in high-availability environments.

Simple, efficient, ad- and bloatfree plugin for testing whether or not a given server address is up for just you, or everyone else on a given port and protocol.
Servers & services are checked in real-time whenever a user requests to view the page where the shortcode is inserted.

Intuitive interface, makes is really easy to maintain servers & services to check.

You have the ability to filter/hook the message displayed to the user through functions.php in your theme folder.

`
add_filter( "atr_server_success_message", function($message, $server) {
	return $server->humanname." appears to be working alrstight.";
}, 10, 2);

add_filter( "atr_server_error_message", function($message, $server) {
	return $server->humanname." is down.";
}, 10, 2);
`

You can also use the filter "atr_perm_administer_servers" to alter the permission being used to check access rights.

`
add_filter( "atr_perm_administer_servers", function( $permission ) {
	$permission = "editor";
	return $permission;
} );
`

== Shortcode Examples ==
Displays all servers entered in wp-admin
`
[server-status]
`

Display server by certain id
`
[server-status id="X"]
`

Display servers by multiple id's
`
[server-status id="X,X,X"]
`

Excludes certain posts from display, only works if 'id' is not set.
`
[server-status exclude="X"]
`

== Installation ==
1. Install the plugin
2. Configure the servers/services you want to check against in "Server Status" within wp-admin
3. Insert one of the provided shortcodes on the desired page, and or post.

== Screenshots ==
Screenshot 1: Administration screen for servers to be checked against
Screenshot 2: Example result of different servers

== Features ==
* Supports most common protocols (TCP, UDP, HTTP, HTTPS) (FTP is on the todo)
* Define a human friendly readable name for display
* Define hostname
* Define port
* Define timeout in seconds
* Define protocol
* Drag'n'drop ordering
* Edit and delete servers/services
* Shortcodes for checking one or more servers frontend
* Simple, clear and well explained settings page
* Filter available configurations
* Settings page with various configrations to suit your needs
* Possible to disable/enable servers and services
* Includes a widget for displaying servers in sidebars

== Is this plugin for you? ==
If you're looking for a full fledged server monitoring tool, no. Consider using thirdparty software such as [zabbix instead](https://zabbix.org/wiki/Main_Page)

Otherwise, if you just want to provide a service, for your users/visitors to check whether or not one or more of your servers is running healthy then yes, this is for you.

== Feature requests ==
Think this plugin is missing a feature? I'll gladly discuss any feature requests sent to me either through the wordpress support forums, or via my contact formular.

Keep in mind, features must be able to fit seamlessly with the core wordpress UI, and must not be intrusive, or considered adware.

== Got a question? ==
If you have any questions not answered here, do feel free to [send me an email](https://rehhoff.me/contact) and I'll do my best to answer you within 48 hours.

Response time is usually faster if you send me a mail, instead of using the wordpress support forums.

== Frequently Asked Questions ==

= Does this plugin require anything special? =

This plugin depends heavily on the curl_* and fsockopen(); PHP functions, however such functions are enabled by default on the majority of webhosts.
Also check that you have "allow_url_fopen" set to 'On' or '1'

= Server checks are stuck at "Checking server status" =
It is important that you don't have the same server appearing twice on the same page, doing so WILL break the functionality for all server checks.

== Upgrade Notice ==
It is as always, recommended that you take a full backup of both your database and files, prior to updating plugins.
I will do my best to maintain backwards compatibility, but please watch out for breaking changes in the changelog.

== Changelog ==
= 1.5.1 =
* Tested with WordPress 8.1
* Compatibility: Fixed CSS conflict with Advanced Custom Fields: Extended

= 1.5.0 =
* Rewritten the way status messages are handled, now employs WP transients instead of PHP sessions.
* Tested with WordPress v5.5.1 and PHP7.4
* Deleted unused file.

= 1.4.3 =
* Compatiblity: Confirmed working with WordPress 5.3.1
* UI: Changed admin alert message when setting server protocols to HTTP(s) or UDP, to be less intrusive.

= 1.4.2 =
* Security Update: Removing Leftover debug code.

= 1.4.1 =
* Security Update: Removing Leftover debug code.

= 1.4.0 =
* New Feature: Individual servers can now be disabled/enabled
* New Feature: Shortcode Parameter to exclude servers by ID
* Updated readme.txt to include shortcode documentation

= 1.3.4 =
* Compatiblity: Minimum required PHP version is now 5.6
* Compatiblity: Tested with wordpress 5.1
* Coding Standards:  More core coding standards improvements.

= 1.3.2 =
* Fixing an ugly bug that crept in with 1.3.1  

= 1.3.1 =
* Bugfix: Fixed a bug that would prevent 2 shortcodes from being used on the same page.  
* Minor: Behind the scenes improvements.  

= 1.3.0 =
* Compatibility: Tested with wordpress 5.0
* UI: Changed menu icon to a more suitable one.
* UI: Added a promotion to rate this plugin (Don't worry, you can easily disable it under configurations.)
* UI: Added a plugin settings link on plugins overview page.
* Added a server status widget.

= 1.2.4 =
* Security Update: Access checks now uses wordpress current_user_can(); instead of in_array();
* Compatibility: Added Dark Mode plugin compatibility.
* Compatibility: Added some required plugin compatibility checks.
* Coding Standards: Getting up to scratch with wordpress coding standards.
* Coding Standards: Started documenting plugin functions, using docblock syntax
* Errors: Added an error message to the frontend when a non-existant server ID is added to the shortcode. (Only visible to users with sufficient privileges)
* Bugfix: Fixed incorrect "atr_success_message" filter name, renamed properly to "atr_server_success_message".
* Bumped compatible wordpress version to 4.9.4.

= 1.2.3 =
* New setting: attempt to bypass cached results.

= 1.2.2 =
* Critical Hotfix: Fixing javascript ReferenceError breaking site javascript.

= 1.2.1 =
* New setting: SSL Verify Peer.
* New Setting: SSL Verify Host.
* New setting: Request Execution Order.
* Servers boxes now matches elementor page builders alerts (border-left added).

= 1.2.0 =
* Added new configurations page.
* Introduced a settings API, documentation to come at some point.
* Some more translateable strings.
* Bumped WordPress version compatibility.
* Hopefully? Fixed drag-handle sorting, changed icon from ui to dashicons.
* Updated plugin description.

= 1.1.4 =
* More translateable strings
* Implemented a more responsive experience in backend
* Rephrased table heading descriptions to be a bit more descriptive

= 1.1.3 =
* Bugfix & security update.
* Fixed rare scenario where privilege escalation could occur during saving a servers data.
* Fixed CSRF vulnerabilities, when adding/editing/deleting server data.
* Rmove JS console.log breaking <= IE 7
* Some strings are now translateable (More to come in future releases)

= 1.1.2 =
* Added filters for messages displayed to the user.

= 1.1.1 =
* Added sanitization function.
* Now stripping sanitizing any data going into the database.

= 1.1.0 =
* Fixed bug where only 5 server would be displayed in backend
* Code Refactoring for publishing this plugin
* Added assets, and readme files.

= 1.0.0 =
* Initial release
