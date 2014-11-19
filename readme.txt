=== EASE Framework ===
Contributors: loomtronic, cloudward, jstrid
Tags: Amazon, AWS, EASE, Google, Google Docs, Google Drive, Google Sheets, S3, Forms, Membership, Database, eCommerce, Storage, Google Cloud, spreadsheets, fields
Requires at least: 3.5
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Customizable integration with Google Sheets, Google Cloud, MySQL and Amazon S3 without APIs

== Description ==

The Cloudward EASE Framework for WordPress is an HTML-like, markup language that makes it easy to:

* Access Google Drive, Amazon S3, and your WordPress database
* Build custom forms and lists in WordPress Pages and Posts
* Build protected membership content and charge for access
* Build eCommerce listings using Google Sheets and accept payments
* Use Google Sheets as your site database, easy to update and manage
* Read and write to Google Sheets, Cloud SQL and MySQL
* Upload and download files to S3 or Google Drive
* Create custom fields driven by Google Sheets


EASE is easy to install and customize. It's also open-source, which provides a strong, flexible foundation to build your business on.

Easily integrate your existing WordPress app:

* Google Cloud SQL or your WordPress MySQL database
* Google Cloud Storage
* Google Sheets
* Amazon S3


See what you can do with EASE:

* At our demo site http://www.momsbakeryonline.com
* Watch this video to see how easy it is to get started: https://www.youtube.com/watch?v=FhJv-GYYJPM
* How to Build a Memberships Site: http://youtu.be/raLWG86tYEo


How EASE Works:

* **TAGS:** You just put EASE tags into your forms and the EASE framework will set up your database for you
* **FORMS:** EASE forms look like regular HTML forms, but the EASE framework tells your database what to do or Google Sheets where to save the data
* **LISTS:** EASE lists get the data out of your database or a Google Sheet and print it on the page. Great for directory and membership listings
* **FUNCTIONS:** EASE functions manage if-then logic, system utilities, cookies, authorization, file management, and more complicated functions when you need them. Plus, the EASE library of functions continues to grow

For more info on the EASE syntax, check out our reference guide: http://goo.gl/UTA1JE


== Installation ==
1. Download the plugin from the WordPress Plugin directory
2. Activate the Plugin through the Plugins menu in WordPress
3. Configure the services you want to use by going to the `/wp-admin/admin.php?page=ease_plugin_settings` and following the instructions there

== Frequently Asked Questions ==

= Where do I get support for this plugin? =

You can get support at http://support.cloudward.com/

= What are the Limitations? =
The EASE Framework connects to your local database. If your hosting provider locks down your local database, then some functions may not operate (you can turn off database access in plugin settings), Also, the EASE Framework connects to Google Drive via Oauth. While we strive to make this work with as many hosting providers as possible, If your hosting provider locks down some settings access to Google Drive may be limited. 

== Screenshots ==

1. A store created from a Google Sheet
2. Beautiful forms can be created that save to Google Sheets or MySQL
3. Build password-protected members pages
4. Logon on screens and privilege management are easily managed
5. Our Example Helper Script Library has lots of examples
6. Our Helper script library has applications like a membership site
7. EASE is Powerful - we even built a CRM from it
8. EASE is easy to learn and use
9. Installation Wizard helps get you started fast

== Changelog ==
= 0.1.0 =
* Initial Release

= 0.1.1 =
* Upgraded EASE Framework to 2.3.7
* Added ability to install script helper collections

= 0.1.2 =
* Add EASE Framework fixes
* Added additional helper scripts for surveys, contacts, membership site, store and more
* Certified with WP 4.0
* Upgraded EASE Framework to 2.6

= 0.1.3 =
* Updated membership helper script

= 0.1.4 =
* Fixed an issue where content was echoing instead of returning when EASE was not being run

= 0.1.5 =
* Updates for welcome and settings screens to improve usability

= 0.1.6 =
* Upgraded EASE Framework to 2.9.17 
* Fixed jQuery Validation plugin vulnerability issue

== Upgrade Notice ==

= 0.1.5 =
Added new Setup wizard. The new version contains bug fixes and new helper scripts. Helper Scripts are example application pages written in
EASE. Scripts include: surveys, contacts, Membership site, PayPal Store, File Uploads and more.