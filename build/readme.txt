=== OpenEMM ===

Contributors:
Donate link:
Tags: newsletter, mailing, OpenEMM
Requires at least:
Tested up to:
Stable tag:
License: MIT
License URI: https://github.com/artcomventure/wordpress-plugin-OpenEMM/blob/master/LICENSE

Spread your content over social networks and more (Facebook, Twitter, Google+, Pinterest, Tumblr, Whatsapp, SMS, Email).

== Description ==

== Installation ==

1. Upload files to the `/wp-content/plugins/` directory of your WordPress installation.
  * Either [download the latest files](https://github.com/artcomventure/wordpress-plugin-OpenEMM/archive/master.zip) and extract zip (optionally rename folder)
  * ... or clone repository:
  ```
  $ cd /PATH/TO/WORDPRESS/wp-content/plugins/
  $ git clone https://github.com/artcomventure/wordpress-plugin-OpenEMM.git
  ```
  If you want a different folder name than `wordpress-plugin-OpenEMM` extend clone command by ` 'FOLDERNAME'` (replace the word `'FOLDERNAME'` by your chosen one):
  ```
  $ git clone https://github.com/artcomventure/wordpress-plugin-OpenEMM.git 'FOLDERNAME'
  ```
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. **Enjoy**

== Usage ==

Once activated you'll find the 'OPENEMM' settings page listed in the submenu of 'Settings'.

1. Enter required values for mailing list ID and path to webservice's file and its credentials.
2. Choose optional/required fields for your subscription form.
3. Customize the notifications.
4. Customize the douple opt in (optional) email.

To display the subscription form insert the shortcode `[openemm] into the editor``

== Plugin Updates ==

Although the plugin is not _yet_ listed on https://wordpress.org/plugins/, you can use WordPress' update functionality to keep it in sync with the files from [GitHub](https://github.com/artcomventure/wordpress-plugin-slider).

**Please use for this our [WordPress Repository Updater](https://github.com/artcomventure/wordpress-plugin-repoUpdater)** with the settings:

* Repository URL: https://github.com/artcomventure/wordpress-plugin-OpenEMM/
* Subfolder (optionally, if you don't want/need the development files in your environment): build

_We test our plugin through its paces, but we advise you to take all safety precautions before the update. Just in case of the unexpected._

== Questions, concerns, needs, suggestions? ==

Don't hesitate! [Issues](https://github.com/artcomventure/wordpress-plugin-OpenEMM/issues) welcome.
== Changelog ==

= 1.3.1 - 2019-07-11 =
**Fixed**

* Typo/t9n

= 1.3.0 - 2019-07-04 =
**Added**

* Email notification.

= 1.2.5 - 2019-06-07 =
**Fixed**

* t9n

= 1.2.4 - 2019-06-06 =
**Changed**

* $_SESSION vs WP's transient

= 1.2.3 - 2019-05-24 =
**Fixed**

* Email 'From:' header. :/

= 1.2.2 - 2019-05-10 =
**Fixed**

* Maybe use template from sub-theme.

= 1.2.1 - 2019-05-09 =
**Fixed**

* Subscriber data filter (missing gender).

= 1.2.0 - 2019-05-09 =
**Added**

* Filters for custom form fields and labels.

= 1.1.6 - 2019-04-24 =
**Fixed**

* Add missing sidebar.

= 1.1.5 - 2019-04-23 =
**Fixed**

* Reset styles.

= 1.1.4 - 2019-04-12 =
**Fixed**

* Cache busting.

= 1.1.3 - 2019-04-12 =
**Fixed**

* Add missing submit button :/

= 1.1.2 - 2019-04-12 =
**Changed**

* Messages styles.

= 1.1.1 - 2019-04-12 =
**Added**

* Editable button (text).

= 1.1.0 - 2019-04-11 =
**Updated**

* New db column (data).
* Option structure.

= 1.0.0 - 2019-04-11 =
**Added**

* Initial file commit
