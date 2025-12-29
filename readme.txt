=== Campaign Office Core ===
Contributors: mrwalker511
Tags: campaign, political, volunteer, events, crm
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Core functionality for political campaigns including custom post types, volunteer management, and event RSVP system.

== Description ==

Campaign Office Core provides essential campaign management functionality for WordPress. Built for political campaigns, advocacy groups, and grassroots organizations.

= Key Features =

**Custom Post Types**
* Issues & Policy Positions
* Campaign Events
* Endorsements
* Team Members
* Volunteer Opportunities
* Press Releases

**Volunteer Management**
* Volunteer signup forms
* Skills and availability tracking
* Contact management
* Admin dashboard
* CSV export

**Event Management**
* RSVP system with capacity limits
* Recurring events support
* Dietary restrictions tracking
* Guest count management
* CSV export for attendees

= Perfect For =

* Political campaigns (local, state, federal)
* Non-profit advocacy organizations
* Grassroots movements
* Community organizing efforts
* Issue-based campaigns

= Data That Persists =

Unlike theme-based solutions, all your campaign data persists when you change themes. This includes:
* All custom post types content
* Volunteer signups and contact information
* Event RSVPs and attendee data
* Custom taxonomies and relationships

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/campaign-office-core/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your campaign settings
4. Start adding issues, events, and team members!

= Automatic Installation =

1. Log into your WordPress admin
2. Go to Plugins → Add New
3. Search for "Campaign Office Core"
4. Click Install Now
5. Activate the plugin

== Frequently Asked Questions ==

= Do I need the Campaign Office theme to use this plugin? =

No! While this plugin is optimized for the Campaign Office theme, it works with any WordPress theme. You'll get the full functionality with default WordPress styling.

= Will my data be lost if I change themes? =

No! All data created by this plugin (volunteers, events, endorsements, etc.) persists regardless of which theme you use. This is why it's better than theme-based functionality.

= Can I export my volunteer and RSVP data? =

Yes! Both the volunteer management and event RSVP systems include CSV export functionality for use with external tools and mailing services.

= How do I add a volunteer signup form? =

Use the shortcode `[cp_volunteer_form]` on any page or post. You can customize it with parameters like title and opportunity_id.

= Does this work with Gutenberg? =

Yes! All custom post types are fully compatible with the WordPress block editor (Gutenberg).

= Can I create recurring events? =

Yes! The plugin supports daily, weekly, bi-weekly, and monthly recurring events. Just check the "recurring event" option when creating an event.

= Is this plugin GPL licensed? =

Yes! Campaign Office Core is 100% GPL, giving you the freedom to use, modify, and distribute it as you see fit.

== Screenshots ==

1. Custom post types in WordPress admin menu
2. Volunteer signup form on the frontend
3. Volunteer management dashboard
4. Event RSVP form
5. Event RSVPs admin view
6. Recurring event settings
7. Issue/Policy position editor

== Changelog ==

= 1.0.0 - 2024-12-29 =
* Initial release
* Custom post types for campaign content
* Volunteer management system with signup forms
* Event management with RSVP functionality
* Recurring event support
* CSV export for volunteers and RSVPs
* Full REST API support
* WordPress 6.4 compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of Campaign Office Core. Install to add powerful campaign management features to your WordPress site.

== Developer Notes ==

= Hooks and Filters =

Campaign Office Core provides several hooks for developers:

**Actions:**
* `campaign_office_core_loaded` - Fires when plugin is loaded
* `cp_volunteer_signup_success` - After volunteer signup
* `cp_event_rsvp_success` - After event RSVP

**Filters:**
* `campaign_office_core_features` - Modify feature availability

See the [GitHub repository](https://github.com/mrwalker511/campaign-office-core) for full documentation.

= Contributing =

Contributions are welcome! Please visit our [GitHub repository](https://github.com/mrwalker511/campaign-office-core) to submit pull requests or report issues.
