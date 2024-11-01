=== Plugin Name ===
Contributors: datadigita
Donate link: http://spamlord.org/donate
Tags: anti-spam, block spam, comments, filter, sarcasm, security, spam, spam counter, spamlord
Requires at least: 3.0.1
Tested up to: 4.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upon activation, SpamLord eliminates the potential for automated spam comments, as well as trackback spam.

== Description ==

### Premier Spam Prevention Plugin for WordPress

#### The QUADRUPLE Crown of Spam protection runs every comment submission through a gauntlet of tests, all invisible and harmless to human visitors, all highly efficient at detecting automated spam:

1. The **clocker** destroys anyone submitting forms too quickly to be human.
2. The **bouncer** eliminates anyone caught trying to sneak in with form fields only spammers would use. 
3. The **planter** silently distributes secret codes to genuine human commenters which are then verified upon submission. 
4. The **surgeon** optionally severs your websites ability to receive trackback spam.

#### Special Bonus!

Not only does SpamLord protect your website, it also enables you to get the last word in. Now you can reply to would-be spammers with options such as:

+ a curated selection of tastefully sarcastic comments (now in 13 languages covering the regions that produce the most spam)
+ a false positive so they think it worked (and won't try harder next time)
+ anything you want using the custom response option

== Installation ==

1. Upload `spamlord` folder to your `/wp-content/plugins/` directory
2. Activate SpamLord through the 'Plugins' menu in WordPress
3. Adjust your SpamLord preferences by visiting the `Settings > SpamLord` screen

== Frequently Asked Questions ==

= I'm worried about false positives, is it possible for SpamLord to flag spam instead of deleting it? =

Yes, visit the `Settings > SpamLord` screen and choose `Moderate` as your `Operating Mode`.

= Will SpamLord work with my Multisite installation? =

Yes, absolutely. Network Activation now allows SpamLord to protect all of your sites at once!

= I have started getting Trackback spam again... Help! =

Nobody is perfect... With our international update we noticed a small flaw in the way we were blocking pingbacks and trackbacks. For the time being, please visit the `Settings > SpamLord` screen, verify that your `Operating Mode` is set to `Total Annihilation`, and save the operating mode (even if it was set correctly).

= Strangely, without the thousands of spam comments I'm feeling a little lonely. How do I uninstall SpamLord? =

Just deactivate it as any other Plugin, and life will be back to normal. I would not recommend this. 

== Screenshots ==

1. The SpamLord settings screen.

== Changelog ==

= 0.667 =
* tested for WordPress 4.6 compatibility (of course it worked absolutely fine as always)
= 0.66 =
* can now detect the spammer's most likely location
* sarcastic responses have been translated into 13 new languages to cover the regions producing the most spam
* optimized pingback/trackback protection
* tweaked to improve performance for bbPress users
= 0.60 =
* small tweak for WordPress 4.0 compatibility
= 0.59 =
* fixed a bug that occurred when replying to comments from the Dashboard
= 0.58 =
* added statistics to configuration page as well as the Dashboard to avoid confusion
= 0.55 =
* Multisite compatible! removed the old automatic update code which was causing the bugs in Network activations
= 0.42 =
* updated icon and branding in honor of WordPress Plugin Directory launch

= 0.40 =
* adds referrer, browser, proxy, IP, HTTP, and DNS information to comment notifications

= 0.32 =
* updated for WordPress 3.8 compatibility

= 0.31 =
* added a handsome icon to the configuration screen
* language settings are now available to customize the `Automated Spam Responder`
* doubled the number of sarcastic responses available to SpamLord
* now possible to define a custom message to spammers
* a bug was eliminated from the update script which had trapped a few victims in an infinite loop of updates

= 0.24 =
* now adds Trackback and Pingback protection to existing posts of all types and status when running in extreme mode
* found and fixed a typo which was the result of trying to pull a small Doritos crumb from the keyboard

= 0.23 =
* added a new operating mode that disables Trackbacks and Pingbacks
* updated verbiage on the configuration page
* added another tastefully sarcastic comment to the `Automated Spam Responder`
* removed one very minor but annoying bug

= 0.14 =
* fixed a typo
= 0.12 =
* testing release

= 0.11 =
* initial release

== Upgrade Notice ==

= 0.667 =
WP 4.6 compatible!
= 0.66 =
international update!
= 0.60 =
WP 4.0 compatible!
= 0.59 =
one less bug!
= 0.58 =
more intuitive statistic display!
= 0.55 =
now multisite compatible!
= 0.42 =
new branding!
= 0.40 =
more informative than ever!
= 0.32 =
updated for WordPress 3.8

= 0.31 =
now customizable!

= 0.24 =
enhanced Trackback protection!

= 0.23 =
now protects against Trackback spam!

= 0.14 =
fixed a typo!
= 0.12 =
testing the updater!

= 0.11 =
initial release!
