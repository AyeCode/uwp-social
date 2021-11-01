=== UsersWP - Social Login ===
Contributors: stiofansisland, paoltaia, ayecode
Donate link: http://userswp.io/
Tags: social login, facebook login, google login, twitter login, linkedIn login, vkontakte login, woocommerce login, facebook, twitter, google, social network login, social plugin, userswp
Requires at least: 4.9
Tested up to: 5.8
Stable tag: 1.3.18
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Social Login addon for UsersWP.

== Description ==

Social Login addon for [UsersWP](https://wordpress.org/plugins/userswp/).

This addon lets your user to register and login with popular sites like Facebook, Google, Twitter, LinkedIn, Instagram, Yahoo, WordPress, vkontakte etc.

100% translatable.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option. To do an automatic install of UsersWP - Social Login, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.
Search for "UsersWP Social Login" and click Install.

= Manual installation =

The manual installation method involves downloading UsersWP and uploading it to your webserver via your favourite FTP application. The WordPress codex will tell you more [here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should seamlessly work. We always suggest you backup up your website before performing any automated update to avoid unforeseen problems.

== Frequently Asked Questions ==

No questions so far, but don't hesitate to ask!

== Screenshots ==

1. UWP Login (brand icons removed in screenshots due to WP rules).
2. WP Login (brand icons removed in screenshots due to WP rules).

== Changelog ==

= 1.3.18 =
* Setting for user moderation in social login - ADDED
* Setting for assigning user role after successful social login - ADDED
* Redirection based on form from where the social login initiated - CHANGED
* Error page templates to override - ADDED
* Restrict addon compatibility - ADDED

= 1.3.17 =
* Social login design breaks when no title added from the settings - FIXED
* Form type argument to differentiate where the social login is displaying - ADDED

= 1.3.16 =
* Widgets compatibility changes - CHANGED

= 1.3.15 =
* Social login redirection issue for facebook login - FIXED
* Template for displaying social login which can be override in the theme - CHANGED
* Hybridauth library upgraded to v3.7.1 - CHANGED

= 1.3.14 =
* Update readme file for tested up to version 5.7

= 1.3.13 =
* Social login always redirects to home page when last login redirect is set - FIXED
* Don't allow to access auth URL directly - FIXED
* Allow copy auth URL on click in settings. - CHANGED
* Update avatar from social profile only if not set in UWP profile - CHANGED

= 1.3.12 =
* Update readme file for tested up to version 5.7

= 1.3.11 =
* Setting to disable social login on the WP admin register form - ADDED

= 1.3.10 =
* Remove icon on link user account form - FIXED

= 1.3.9 =
* Update readme file for tested up to version 5.5

= 1.3.8 =
* Send WP default registration email on new user login via social login - ADDED
* Allow to disable social login on wp-admin login form - ADDED
* Hybridauth library upgraded to v3.4.0 - CHANGED

= 1.3.7 =
* Filter 'uwp_social_login_button_html' added to be able to adjust the icon buttons output - ADDED

= 1.3.6 =
* Change the scopes for google social login - FIXED

= 1.3.5 =
* Don't show 'Login via Social' label if no social login configured or active - FIXED
* Change google icon as per their guideline - CHANGED

= 1.3.4 =
* Update readme file for tested up to version 5.4

= 1.3.3 =
* Social login add on runs before UsersWP on some sites causing issue - FIXED

= 1.3.2 =
* First WP.org release - YAY

= 1.3.1 =
* Facebook login not able to get the user's email and showing error - FIXED
* Changes for wp.org repo acceptance - CHANGED

= 1.2.2 =
* Changes to fix unknown column error for avatar usermeta - FIXED

= 1.2.1 =
* Don't create dummy email if email not returned by provider - FIXED

= 1.2.0 =
* Use Hybridauth latest library function for logout - FIXED
* Compatibility with new UWP core style updates - ADDED

= 1.0.10 =
* Session not available when user redirects back from social site - FIXED

= 1.0.9 =
* Upgrade Hybridauth library to V3 to make compatibility with linkedIn API V2 - CHANGED
* Auth URI changed due to change in Hybridauth V3. Auth URIs for all providers need to be changed - BREAKING CHANGE

= 1.0.8 =
* Integrate new settings interface - CHANGED

= 1.0.7 =
* Login with LinkedIn gives error due to destruct method - FIXED

= 1.0.6 =
* Social login is unable to create new user sometimes - FIXED
* GDV2 compatibility - ADDED
* Remove session path check as it shows error on few hostings - FIXED
* Use core WP functions in Uninstall functionality. - CHANGED

= 1.0.5 =
* Implement widget and gutenberg blocks using super duper class - CHANGED

= 1.0.4 =
* Fix login redirect to last page - FIXED
* Plugin uninstall functionality - ADDED

= 1.0.3 =
* Added graceful error message if session problems - ADDED

= 1.0.2 =
* Major code refactoring - CHANGED
* Docblocks added - ADDED
* Linked in sometimes does not connect becasue of trailing slash missing in subdomain - FIXED
* Delete social row when the user get deleted - ADDED

= 1.0.1 =
* Renamed some files - CHANGED
* Textdomain not being loaded correctly - FIXED
* Post method uses incorrect action name - FIXED
* Class names renamed from Users_WP to UsersWP for better naming and consistency - CHANGED

= 1.0.0 =
* First release.

== Upgrade Notice ==

TBA
