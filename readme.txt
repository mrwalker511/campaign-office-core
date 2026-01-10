=== Campaign Office Core ===
Contributors: mrwalker511
Tags: campaign, political, volunteer, events, crm
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.0
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
* CSV export
* Admin dashboard

**Event Management**
* RSVP system with capacity limits
* Recurring event support
* Dietary restriction tracking
* Event calendar views
* iCal export
* Google Maps integration

**Contact Management**
* Centralized contact database
* Automatic deduplication
* Integration with volunteers and RSVPs
* CSV export

= Perfect For =
* Political campaigns (local, state, federal)
* Advocacy organizations
* Grassroots movements
* Non-profit organizations
* Issue-based campaigns

= Works With Any Theme =
This plugin provides core functionality that works with any WordPress theme. Optimized for the Campaign Office theme but fully functional standalone.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/campaign-office-core/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. New menu items will appear: Issues, Events, Endorsements, Team, Volunteer Opportunities, Press Releases
4. Start adding campaign content!

== Frequently Asked Questions ==

= Does this require the Campaign Office theme? =

No! This plugin works with any WordPress theme. It's optimized for Campaign Office but fully functional as a standalone plugin.

= What happens to my data if I switch themes? =

All your data persists! Custom post types, volunteers, RSVPs, and contacts remain in the database regardless of your active theme.

= Can I export my volunteer and RSVP data? =

Yes! Both volunteers and event RSVPs can be exported to CSV format from the admin dashboard.

= Does this work with page builders? =

Yes! The plugin registers standard WordPress custom post types that work with all page builders including Elementor, Beaver Builder, and Divi.

= How do I add a volunteer signup form? =

Use the shortcode `[cp_volunteer_form]` on any page or post. Customize with parameters like `opportunity_id` to link to specific opportunities.

= Can events recur automatically? =

Yes! When creating an event, check "This is a recurring event" and select the frequency. The plugin automatically generates future events.

= Is the plugin GDPR compliant? =

The plugin provides tools for data export and deletion. Site owners are responsible for implementing appropriate privacy policies and consent mechanisms.

== Changelog ==

= 2.0.0 - 2026-01-10 =
**Major Update - Version Alignment with Campaign Office Theme 2.0**

* **Updated:** Version bumped to 2.0.0 to align with Campaign Office theme
* **Documented:** Press Release CPT now officially documented
* **Improved:** Contact manager database optimization
* **Improved:** Event calendar enhancements with iCal and Google Maps
* **Fixed:** Database table creation timing for better compatibility
* **Tested:** Verified compatibility with WordPress 6.9
* **Enhanced:** REST API support for all custom post types
* **Added:** Developer action hooks for theme integration
* **Updated:** Documentation and code comments throughout

= 1.0.0 - 2024-12-29 =
* Initial release
* Custom post types (Issues, Events, Endorsements, Team, Volunteers, Press Releases)
* Volunteer management system with database
* Event management with RSVP system
* Recurring event support
* Contact manager with deduplication
* Event calendar enhancements
* iCal export functionality
* Google Maps integration
* CSV export for volunteers and RSVPs
* REST API support
* Translation ready
* GDPR data export tools

== Upgrade Notice ==

= 2.0.0 =
Version alignment update with Campaign Office theme 2.0. Fully backward compatible. Recommended for all users.

= 1.0.0 =
Initial release of Campaign Office Core functionality plugin.
