=== DisableMU ===
Contributors: dmchale
Tags: admin, mu-plugins, security
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Redefines the location of the /mu-plugins directory to ensure that no code published to the default location will automatically run on your website.

== Description ==

Redefines the location of the /mu-plugins directory, via writing constants to your wp-config.php file, to ensure that no code published to the default location will automatically run on your website.

Since many site administrators are not using /mu-plugins, we want to be sure that no mu-plugins "suddenly"
appear on our website due to their nature: auto-activation is BAD when you didn't intend for code to be there!

DisableMU is not for every website. Many development houses, system admins, and hosting companies have very legitimate uses for /mu-plugins and have built business plans around utilizing this feature of WordPress. By installing and activating DisableMU on your website, you may break things. Please exercise caution and only utilize this plugin if you know what you are doing. As with most plugins, DisableMU is written with the intent of being helpful but you are responsible for its use or actions on your own website.

== Installation ==

1. Upload the `disablemu` directory to the `/wp-content/plugins/` directory via FTP
1. Alternatively, upload the `disablemu_v#.#.zip` file to the 'Plugins->Add New' page in your WordPress admin area
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What other features will you add to this plugin? =

Kicking around ideas, but always open to user input as well. Currently debating...

1. Deletion of /mu-plugins directory, if found
1. Creating cron to cycle the fake directory name (don't want to do this too frequently though since it touches wp-config.php)

If you definitely want to see one of these, or have an idea of your own, let me know!

== Screenshots ==

n/a

== Changelog ==

= 1.1 =
* Moved where definitions live inside wp-config.php, to ensure it is always ABOVE the wp-settings.php include
* Updated CONST definitions to be proper directory/url paths instead of partials
* Updated CONST definitions to remove the "if isDefined()" checks. We want to ensure that WE always take precedence.

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.1 =
Strongly recommended upgrade since many installations were not properly overriding values using version 1.0 of the plugin.

= 1.0 =
You have to start somewhere.