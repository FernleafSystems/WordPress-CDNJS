=== Plugin Name ===
Contributors: paultgoodchild, dlgoodchild
Donate link: https://www.icontrolwp.com/
Tags: CDN, CDNJS, CSS, JS, CloudFlare
Requires at least: 3.5.0
Tested up to: 4.8
Stable tag: 1.3.3

Replace Javascript and CSS libraries on your WordPress site with CloudFlare's FREE CDN

== Description ==

= What is CDNJS? =

This is best described in [this article on Cloudflare.com](http://blog.cloudflare.com/cdnjs-the-fastest-javascript-repo-on-the-web).

CDNJS is a freely available CDN for common Javascript and CSS libraries.

This plugin simply lets you use them more easily on your WordPress site.

This plugin is produced and supported by [iControlWP: Multiple WordPress Management](http://icwp.io/6y).


== Frequently Asked Questions ==

= How can I install the plugin? =

This plugin should install as any other WordPress.org respository plugin.

1.	Browse to Plugins -> Add Plugin
1.	Search: CDNJS
1.	Click Install
1.	Click to Activate.

Alternatively using FTP:

1.	Download the zip file using the download link to the right.
1.	Extract the contents of the file and locate the folder called 'cdnjs' containing the plugin files.
1.	Upload this whole folder to your '/wp-content/plugins/' directory
1.	From the plugins page within WordPress locate the plugin 'CDNJS' and click Activate

A new menu item will appear on the left-hand side called 'CDNJS'.  Click this menu and select 'Includes'.

Select the includes you desire.  Only select what you need.

= How does this affect the JQuery supplied with WordPress itself? =

If you select the option, it will include the currently latest available JQuery and replace the one that comes with your WordPress distribution.

= Why would I use this plugin at all? =

As just another little boost your website page-load performance.  If your visistors are using the CloudFlare/CDNJS files
their pages should load quicker, and as more and more people use the CDN, it'll help everyone load their sites quicker!

= What has iControlWP got to do with Cloudflare and CDNJS? =

Not a lot, but we like website optimizations and making things faster/better. We saw this project on the Cloudflare blog
and instantly wanted to use it on our sites.  What easier way than to make a plugin, and then we can share it too.

= The plugin doesn't list the Javascript/CSS that I want? =

Your webhosting either doesn't support the file_get_contents() for URLs, or doesn't have json_decode function/library for PHP.

= The page for choosing the libraries loads very slow - why? =

It's pulling in the complete list of libraries available each time.  It only does this on this page, so don't worry about
this each time you load your site.

== Changelog ==

= TODO =
* Improve the UI for search and filtering.

= 1.3.3 =

* UPDATE:	Some code improvements, styling etc.
* UPDATE:	Compatibility with WordPress 4.7
* UPDATE:	Compatibility with PHP 7

= 1.3.2 =

* FIX:		Various PHP warnings
* UPDATE:	Compatibility with WordPress 4.5

= 1.3.1 =

* UPDATE:	Compatibility with WordPress 4.0

= 1.3.0 =

* IMPROVED: Query to CDNJS for library now uses their newer API system
* FIX: Compatibility with WordPress 3.9+
* FIX: Compatibility with PHP 5.4.0+

= 1.2.b =

* FIX: foreach() warning when there have not yet anything selected for include

= 1.2.a =
* Now uses the packages.json list for all available packages.
* Fixes a few errors.

= 1.1.a =
* Fixes a few errors.

= 1.0 =
* First public release
* Allows you to select to include a short list of all available CSS and Javascript available from CDNJS.com

== Upgrade Notice ==

= 1.2.b =

* FIX: foreach() warning when there have not yet anything selected for include
