=== Mailing Group Module ===
Contributors: Marcus Sorensen & netforcelabs.com
Donate link: http://www.wpmailinggroup.com
Tags: mailing group, user subscription, cross mailing, listserv
Requires at least: 3.0.1
Tested up to: 3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Mailing group plugin is used for creating one mailing group in which users can be subscribed, once done all the user from a particular group receive every email send from one of the user to the group.

== Description ==

The WP MailingGroup plugin allows you to run a Mailing Group, also known as a Listserv in web geek speak, right from your WordPress website. This means you can sign up your users, friends, neighbours, family and whoever else you want, directly from your WordPress administration area, and they can then all exchange emails via their favourite email software

This is a true Mailing Group, just like on Yahoo Groups or Google Groups, where there is an email address to send messages to, and everyone who is subscribed to the mailing group gets the message. They can then click Reply, and the whole list will receive their response.

This is NOT a one-way Announcement list where only YOU can email everyone else. This plugin is to help you and your groups stay connected!

See WPMailingGroup.com for more.

== Installation ==

1. Unzip / Unrar the plugin folder and copy all the files to YOUR_SITE/wp-content/plugins/ folder.
2. You can also use the WordPress plugin uploader to do so via Plugins > Add new
3. Activate the plugin through the Plugins page in WordPress
4. Insert the registration form shortcode on a page or widget on your website:

[mailing_group_form]


5. Install the cron scripts on your server
Note: There are 3 cron scripts that needs to be set up in order to send and receive email correctly.


The preferred settings for the cron scripts are as follows:

a. PHP_PATH INSTALLATION_PATH/mailing-group-module/crons/mg_cron_parse_email.php (every 5 mins)
b. PHP_PATH INSTALLATION_PATH/mailing-group-module/crons/mg_cron_send_email.php (every 2 mins)
c. PHP_PATH INSTALLATION_PATH/mailing-group-module/crons/mg_cron_bounced_email.php (every 15 mins)

Where PHP_PATH = The path for the interpreter. For example, /usr/bin/perl (Perl), /usr/bin/php (PHP 4) or /usr/bin/php5 (PHP 5) according to your server.

(NB: INSTALLATION_PATH refers to the folder that the Mailing Group plugin has been installed in within WordPress, eg. /home/sites/yourwebsite.com/public_html/wp-content/plugins/)

There are various methods to set the crons on your server. Here are a few for your reference:

From cPanel:
http://docs.cpanel.net/twiki/bin/view/AllDocumentation/CpanelDocs/CronJobs

From Plesk:
http://www.hosting.com/support/plesk/crontab/

From Telnet / Putty / Command line (for Advanced Users):
http://www.web-site-scripts.com/knowledge-base/article/AA-00484/0/Setup-Cron-job-on-Linux-UNIX-via-command-line.html


== Frequently Asked Questions ==

= How many subscribers can I have in the mailing group? =

You can add up to 20 subscribers in ONE mailing group using the free plugin.

= Can I create my own customised messages to send to prospective subscribers? =
 
Yes, you can: go to Mailing Group Manager > General Settings, and select the Custom Messages tab. There you can input your custom message (using the listed variables, if you are technical!). 
You can also go to Mailing Group Manager > Subscription Requests, and click the message icon next to a subscription request. This opens up a popup window where you can type in a custom message, and check the box at the bottom that allows you to save it for repeated use.

= I don�t have access to cron scripts on my shared server. Can I still use the plugin? =

If you do not have cron access, then you can only use the plugin to collect a list of subscribers. You would not be able to use it as a mailing group manager. The crons are responsible for checking mail that arrives in the designated email box for your group, and for sending out messages to group members. Without the cron scripts in place, none of that would happen.

  
== Screenshots ==

1. Screenshot-1.png - Your Mailing Group can be added and configured and only one Mailing Group is available in this Free plugin.
2. Screenshot-2.png - Add subscribers to the mailing group.
3. Screenshot-3.png - Shows the list of subscribers in the mailing group and buttons to activate their membership.
4. Screenshot-4.png - Import subscribers to the mailing group from Excel (VCF import is available in the Premium plugin).


== Changelog ==

=1.0.1=
* Added support for Selecting POP3 SSL as optional.

= 1.0 =
* First version of the plugin released.