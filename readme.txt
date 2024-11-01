=== YD Profile Visitor Tracker ===
Contributors: ydubois
Donate link: http://www.yann.com/
Tags: visit, visitor, who, profile, tracking, tracker, statistics, login, user, social, social network, friend, friends, buddypress, bbpress, member, members, log
Requires at least: 2.9
Tested up to: 3.1
Stable tag: trunk

Description: Social oriented plugin to track who has been visiting your user profile or other page in a BuddyPress or community-oriented WordPress / bbPress setup.

== Description ==

= Who has visited your profile? =

This plugin tracks and displays who has been visiting your profile or other personal pages in a WordPress / BuddyPress / WP+bbPress installation. An important “social” add-on to community-oriented sites.

Use one template tag for the tracking, and another template tag (or shortcode) for the display.

When displaying the visitor's list you can display any field from the default WordPress user data, all the standard user meta data (from usermeta table), 
all the bbPress-used metadata (if bbPress is installed), and all BuddyPress extended profile (xprofile) data. 
This give you incredible customization power for this plugin.

You can track visits on any page, not just profile, author or member's pages.
(automatic tracking of BuddyPress member's profile is built-in, you can track visits on other pages with the provided template tag)

You can track the value of specific visitor cookies.

You can filter or sort the displayed data using anu of the above-mentioned fields, plus the optional cookie value.

Compatible with PHP5 and above only.

Compatible with WP + bbPress standalone (latest versions): you can now track and display vitors on member's profile pages (you don't need BuddyPress at all to add some social bells and whistle to your vanilla WP or bbPress install).

Compatible with BuddyPress and/or BuddyPress + bbPress (you will get advanced features such as "add friend", and "send private mail" buttons and all expected BuddyPress social-oriented features).

Compatible with WordPress standalone : you can track registered visitors on author pages or any other kind of page (ie. once again BuddyPress and bbPress are not needed).

Only existing WP data structure (tables) is used.

Works with WP3.1 mono or multi-site.

= Active support =

Drop me a line on my [YD Profile Visitor Tracker plugin support site](http://www.yann.com/en/wp-plugins/yd-profile-visitor-tracker "Yann Dubois' Profile Visitor Tracker plugin") to report bugs, ask for specific feature or improvement, or just tell me how you're using it.

= Funding Credits =

Original development of this plugin has been paid for by [Selliance](http://www.selliance.com "Selliance"). Please visit their site!

Le développement d'origine de cette extension a été financé par [Selliance](http://www.selliance.com "Selliance"). Allez visiter leur site !

== Installation ==

Wordpress automatic installation is fully supported and recommended.

The shortcode for displaying a visitors list in a page is as follows:
`[yd_visitor_profiles profile_id="1"]` 
(you can specify any profile ID to get that user's latest visitors list)

The template tag for use in a BuddyPress profile page can be used as follows:
`<?php 
if( is_callable( array( 'pvtPlugin', 'display_visitors' ) ) ) {
	global $pvt_o, $bp;
	echo $pvt_o->display_visitors( 
		array( 
			'profile_id'	=> $bp->displayed_user->id
		 )
	); 
}
?>`

The template for displaying visitors list in a bbPress standalone (without BuddyPress) profile page is as follows:
`<?php 
if( is_callable( array( 'pvtPlugin', 'display_visitors' ) ) ) {
    global $pvt_o, $bp;
    echo $pvt_o->display_visitors( 
        array( 
    		'profile_id'  => $user->ID
         )
    ); 
}
?>`

If `$user->ID` does not work on the page you want to track, try `$user_id` instead. 
There are numerous ways of fetching both the IDs of the visitor and of the visited page owner depending on the setup and where you are in the page. 

There are MANY customization parameters available for both the shortcode and template function.
Some of them are documented on [the plugin's official site](http://www.yann.com/en/wp-plugins/yd-profile-visitor-tracker)... 
Others will be documented later on as requests come in ;-).

The tracking template tag (if you do not use the auto-tracking feature available in the plugin's setting page) is as follows:

`<?php if( is_callable( array( 'pvtPlugin', 'track' ) ) ) pvtPlugin::track(); ?>`

If you are in bbPress standalone, here is the tracking tag for a profile page:

`<?php if( is_callable( array( 'pvtPlugin', 'track' ) ) ) pvtPlugin::track( array( 'profile_id' => $user_id ) ); ?>`

(for any kind of WP or other page you can pass the profile_id the same way, using the `profile_id` parameter)

There are also some tracking options that will be documented later on.

See the screenshots for advanced integration layouts.

== Frequently Asked Questions ==

= Where should I ask questions? =

http://www.yann.com/en/wp-plugins/yd-profile-visitor-tracker

Use comments.

I will answer only on that page so that all users can benefit from the answer. 
So please come back to see the answer or subscribe to that page's post comments.

= Puis-je poser des questions et avoir des docs en français ? =

Oui, l'auteur est français.
("but alors... you are French?")

= What is your e-mail address? =

It is mentioned in the comments at the top of the main plugin file. However, please prefer comments on the plugin page (as indicated above) for all non-private matters.

== Screenshots ==

1. A personal profile visitor page on BuddyPress (using the shortcode)
2. Other ways of displaying visitors lists on a page in BuddyPress (using shortcode)
3. Displaying visitors in your BuddyPress member profile tab (using the template function)
4. Displaying visitors in a bbPress profile page (using template functions, no BuddyPress required)

== Revisions ==

* 0.1.9. New Improvements of 2011/04/01
* 0.1.8. Framework Upgrade + bugfixes + new features of 2011/04/01
* 0.1.7. Bugfix release of 2011/03/28
* 0.1.6. Official first release of 2011/03/28
* 0.1.5. Beta [RC2] release of 2011/03/25
* 0.1.4. Initial beta release of 2011/03/24

== Changelog ==

= 0.1.9 =
* Some more fixes and improvements for bbPress / WP-alone setups [2011/01/01]
* Now gets and can display all regular WP user fields (use wp_ prefix fo keys)
* Now gets and can display all main bbPress and WP usermeta fields (use um_ prefix)
* Automatic add link to bbPress profiles or WP user link
* BR clear to resolve some CSS/display issues
* Added some scrrenshots to the doc
= 0.1.8 =
* Framework update to VERSION 20110328-01 [2011/03/28]
* Very small debug statement bugfix
* Bugfix: profile_id can now be passed as parameter to tracking template function
* Standalone bbPress user profile integration checked and effective (without BuddyPress)
* Added "before_text" parameter
* Updated doc for uses without BuddyPress
= 0.1.7 =
* bugfix: removed visitors tab [2011/03/28]
= 0.1.6 =
* Official first release
= 0.1.5 =
* Beta [RC2]
= 0.1.4 =
* Initial beta release

== Upgrade Notice ==

= 0.1.9 =
* No specifics. Automatic upgrade works fine.
= 0.1.8 =
* No specifics. Automatic upgrade works fine.
= 0.1.7 =
* No specifics. Automatic upgrade works fine.
= 0.1.6 =
* No specifics. Automatic upgrade works fine.
= 0.1.5 =
* No specifics. Automatic upgrade works fine.
= 0.1.4 =
* No specifics. Automatic upgrade works fine.

== Did you like it? ==

Drop me a line on http://www.yann.com/en/wp-plugins/yd-profile-visitor-tracker

And... *please* rate this plugin --&gt;